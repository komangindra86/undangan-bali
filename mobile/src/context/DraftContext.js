import AsyncStorage from '@react-native-async-storage/async-storage';
import { createContext, useContext, useEffect, useMemo, useRef, useState } from 'react';
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
    () => ({ draft, loading, syncing, syncMessage, saveSection, saveSections, publishDraft, discardDraft }),
    [draft, loading, syncing, syncMessage, token, remoteInvitationId],
  );

  return <DraftContext.Provider value={value}>{children}</DraftContext.Provider>;
}

export function useDraft() {
  return useContext(DraftContext);
}
