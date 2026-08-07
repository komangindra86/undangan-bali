import AsyncStorage from '@react-native-async-storage/async-storage';
import { createContext, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { DEFAULT_OPENING_QUOTE } from '../constants/invitation';
import { api } from '../services/api';
import { clearLocalDraft, emptyDraft, loadDraft, saveDraftSection, SESSION_KEYS } from '../services/draftStorage';
import { useAuth } from './AuthContext';

const DraftContext = createContext(null);

export function DraftProvider({ children }) {
  const { token, loading: authLoading, expireSession } = useAuth();
  const [draft, setDraft] = useState(emptyDraft);
  const [remoteInvitationId, setRemoteInvitationId] = useState(null);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [syncMessage, setSyncMessage] = useState(null);
  const lastMediaSignature = useRef(null);

  useEffect(() => {
    async function restoreDraft() {
      const [storedDraft, storedId] = await Promise.all([
        loadDraft(),
        AsyncStorage.getItem(SESSION_KEYS.remoteInvitationId),
      ]);
      setDraft(storedDraft);
      setRemoteInvitationId(storedId);
      setLoading(false);
    }

    restoreDraft();
  }, []);

  useEffect(() => {
    if (!authLoading && !token) {
      setRemoteInvitationId(null);
      AsyncStorage.removeItem(SESSION_KEYS.remoteInvitationId);
    }
  }, [authLoading, token]);

  function payloadFromDraft(nextDraft) {
    return {
      selected_template: nextDraft.selected_template?.id || nextDraft.selected_template,
      groom_data: nextDraft.groom_data,
      bride_data: nextDraft.bride_data,
      event_data: nextDraft.event_data,
      location_data: nextDraft.location_data,
      gallery_data: nextDraft.gallery_data,
      music_data: nextDraft.music_data,
      gift_data: nextDraft.gift_data,
    };
  }

  function mediaSignature(nextDraft) {
    return JSON.stringify([
      nextDraft.groom_data?.groom_photo?.uri || null,
      nextDraft.bride_data?.bride_photo?.uri || null,
      ...(nextDraft.gallery_data?.photos || []).map((photo) => photo.uri),
      nextDraft.music_data?.music_file?.uri || null,
    ]);
  }

  async function syncOnline(nextDraft, authToken = token) {
    if (!authToken || !nextDraft.selected_template) {
      return null;
    }

    setSyncing(true);
    try {
      const payload = payloadFromDraft(nextDraft);
      const signature = mediaSignature(nextDraft);
      const includeMedia = !remoteInvitationId || lastMediaSignature.current !== signature;
      const response = remoteInvitationId
        ? await api.updateDraft(remoteInvitationId, payload, authToken, includeMedia)
        : await api.syncDraft(payload, authToken, includeMedia);
      const id = String(response.data.id);
      setRemoteInvitationId(id);
      await AsyncStorage.setItem(SESSION_KEYS.remoteInvitationId, id);
      lastMediaSignature.current = signature;
      setSyncMessage('Draft tersimpan online');
      return response.data;
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
      }
      throw error;
    } finally {
      setSyncing(false);
    }
  }

  async function saveSections(updates) {
    const nextDraft = { ...draft, ...updates };
    setDraft(nextDraft);
    await Promise.all(
      Object.entries(updates).map(([section, values]) => saveDraftSection(section, values)),
    );

    try {
      await syncOnline(nextDraft);
    } catch (error) {
      setSyncMessage('Draft tersimpan di perangkat. Sinkronisasi online belum berhasil.');
    }

    return nextDraft;
  }

  async function saveSection(section, values) {
    return saveSections({ [section]: values });
  }

  async function loadRemoteDraft(invitation) {
    const nextDraft = draftFromInvitation(invitation);
    const id = String(invitation.id);

    setDraft(nextDraft);
    setRemoteInvitationId(id);
    setSyncMessage('Draft siap dilanjutkan');
    lastMediaSignature.current = mediaSignature(nextDraft);

    await Promise.all([
      ...Object.entries(nextDraft).map(([section, values]) => saveDraftSection(section, values)),
      AsyncStorage.setItem(SESSION_KEYS.remoteInvitationId, id),
    ]);

    return nextDraft;
  }

  async function publishDraft(authToken = token) {
    setSyncing(true);
    try {
      const synced = await syncOnline(draft, authToken);
      const id = synced?.id || remoteInvitationId;
      if (!id) {
        throw new Error('Pilih template dan lengkapi draft sebelum publish.');
      }
      const result = await api.publish(id, authToken);
      await clearLocalDraft();
      setDraft(emptyDraft);
      setRemoteInvitationId(null);
      setSyncMessage(null);
      lastMediaSignature.current = null;
      return result;
    } finally {
      setSyncing(false);
    }
  }

  async function discardDraft() {
    await clearLocalDraft();
    setDraft(emptyDraft);
    setRemoteInvitationId(null);
    setSyncMessage(null);
    lastMediaSignature.current = null;
  }

  const value = useMemo(
    () => ({ draft, loading, syncing, syncMessage, saveSection, saveSections, loadRemoteDraft, publishDraft, discardDraft }),
    [draft, loading, syncing, syncMessage, token, remoteInvitationId],
  );

  return <DraftContext.Provider value={value}>{children}</DraftContext.Provider>;
}

function draftFromInvitation(invitation) {
  const gift = invitation.gift_setting || invitation.giftSetting;

  return {
    selected_template: invitation.template || { id: invitation.template_id },
    groom_data: {
      groom_full_name: invitation.groom_full_name || '',
      groom_nickname: invitation.groom_nickname || '',
      groom_father_name: invitation.groom_father_name || '',
      groom_mother_name: invitation.groom_mother_name || '',
      groom_child_order: invitation.groom_child_order || '',
      groom_photo: remoteMedia(invitation.groom_photo, 'image/jpeg'),
    },
    bride_data: {
      bride_full_name: invitation.bride_full_name || '',
      bride_nickname: invitation.bride_nickname || '',
      bride_father_name: invitation.bride_father_name || '',
      bride_mother_name: invitation.bride_mother_name || '',
      bride_child_order: invitation.bride_child_order || '',
      bride_photo: remoteMedia(invitation.bride_photo, 'image/jpeg'),
    },
    event_data: {
      event_type: invitation.event_type || null,
      event_date: invitation.event_date?.slice(0, 10) || '',
      start_time: invitation.start_time?.slice(0, 5) || '',
      end_time: invitation.end_time?.slice(0, 5) || '',
      venue_name: invitation.venue_name || '',
      venue_address: invitation.venue_address || '',
      opening_quote: invitation.opening_quote ?? DEFAULT_OPENING_QUOTE,
    },
    location_data: {
      latitude: invitation.latitude || '',
      longitude: invitation.longitude || '',
      google_maps_url: invitation.google_maps_url || '',
    },
    gallery_data: {
      photos: (invitation.gallery_photos || []).map((path) => remoteMedia(path, 'image/jpeg')),
    },
    music_data: {
      music_type: invitation.music_type || 'none',
      music_id: invitation.music_id || null,
      music_file: invitation.music_type === 'upload'
        ? remoteMedia(invitation.music_file, mimeTypeForPath(invitation.music_file))
        : null,
    },
    gift_data: {
      is_active: Boolean(gift?.is_active),
      receiver_name: gift?.receiver_name || '',
      receiver_note: gift?.receiver_note || '',
      minimum_amount: String(gift?.minimum_amount || 10000),
      show_amount_public: Boolean(gift?.show_amount_public),
      allow_message: gift ? Boolean(gift.allow_message) : true,
    },
  };
}

function remoteMedia(path, mimeType) {
  if (!path) return null;

  const uri = /^https?:\/\//i.test(path)
    ? path
    : `${api.siteUrl}/storage/${String(path).replace(/^\/+/, '')}`;

  return {
    uri,
    isRemote: true,
    remotePath: /^https?:\/\//i.test(path) ? null : path,
    fileName: String(path).split('/').pop(),
    mimeType,
  };
}

function mimeTypeForPath(path = '') {
  const extension = String(path).split('.').pop()?.toLowerCase();
  return {
    wav: 'audio/wav',
    m4a: 'audio/mp4',
  }[extension] || 'audio/mpeg';
}

export function useDraft() {
  return useContext(DraftContext);
}
