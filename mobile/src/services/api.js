import { Platform } from 'react-native';
import { ensureLocalFileExists } from './localMedia';

const DEFAULT_API_URL = __DEV__ ? 'http://10.0.2.2:8000/api' : 'https://undangan.balisantih.com/api';
const API_URL = (process.env.EXPO_PUBLIC_API_URL || DEFAULT_API_URL).replace(/\/$/, '');

async function request(path, options = {}, token = null) {
  const isFormData = options.body instanceof FormData;
  let response;

  try {
    response = await fetch(`${API_URL}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(!isFormData ? { 'Content-Type': 'application/json' } : {}),
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(options.headers || {}),
      },
    });
  } catch (error) {
    const detail = error?.message ? ` Detail: ${error.message}` : '';
    throw new Error(`Upload atau koneksi ke server gagal (${API_URL}). Pastikan internet stabil, lalu coba publish lagi.${detail}`);
  }

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const errors = data.errors ? Object.values(data.errors).flat().join('\n') : null;
    const error = new Error(errors || data.message || 'Terjadi kesalahan pada server.');
    error.status = response.status;
    throw error;
  }

  return data;
}

function appendValues(form, group, values = {}) {
  Object.entries(values).forEach(([key, value]) => {
    if (value !== null && value !== undefined && typeof value !== 'object') {
      form.append(`${group}[${key}]`, typeof value === 'boolean' ? (value ? '1' : '0') : String(value));
    }
  });
}

async function appendImage(form, key, photo) {
  if (!photo?.uri) {
    return;
  }

  await ensureLocalFileExists(photo, 'foto');

  if (Platform.OS === 'web') {
    const blob = await fetch(photo.uri).then((response) => response.blob());
    form.append(key, blob, photo.fileName || 'photo.jpg');
    return;
  }

  form.append(key, {
    uri: photo.uri,
    name: photo.fileName || 'photo.jpg',
    type: photo.mimeType || 'image/jpeg',
  });
}

async function appendFile(form, key, file) {
  if (!file?.uri) {
    return;
  }

  await ensureLocalFileExists(file, 'file musik');

  if (Platform.OS === 'web') {
    const blob = await fetch(file.uri).then((response) => response.blob());
    form.append(key, blob, file.fileName || 'music.mp3');
    return;
  }

  form.append(key, {
    uri: file.uri,
    name: file.fileName || 'music.mp3',
    type: file.mimeType || 'audio/mpeg',
  });
}

async function draftFormData(draft, includeMedia, methodOverride = null) {
  const form = new FormData();
  if (methodOverride) {
    form.append('_method', methodOverride);
  }
  form.append('selected_template', String(draft.selected_template?.id || draft.selected_template));
  appendValues(form, 'groom_data', draft.groom_data);
  appendValues(form, 'bride_data', draft.bride_data);
  appendValues(form, 'event_data', draft.event_data);
  appendValues(form, 'location_data', draft.location_data);
  appendValues(form, 'music_data', draft.music_data);
  appendValues(form, 'gift_data', draft.gift_data);

  if (includeMedia) {
    form.append('gallery_photos_changed', '1');
    await appendImage(form, 'groom_photo', draft.groom_data?.groom_photo);
    await appendImage(form, 'bride_photo', draft.bride_data?.bride_photo);
    for (const photo of draft.gallery_data?.photos || []) {
      await appendImage(form, 'gallery_photos[]', photo);
    }
    await appendFile(form, 'music_file', draft.music_data?.music_file);
  }

  return form;
}

async function socialMomentFormData(values) {
  const form = new FormData();
  form.append('title', values.title);
  if (values.body) form.append('body', values.body);
  if (values.occurred_at) form.append('occurred_at', values.occurred_at);
  await appendImage(form, 'photo', values.photo);
  return form;
}

export const api = {
  baseUrl: API_URL,
  siteUrl: API_URL.replace(/\/api$/, ''),
  register: (values) => request('/register', { method: 'POST', body: JSON.stringify(values) }),
  login: (values) => request('/login', { method: 'POST', body: JSON.stringify(values) }),
  googleLogin: (idToken) => request('/auth/google', { method: 'POST', body: JSON.stringify({ id_token: idToken }) }),
  logout: (token) => request('/logout', { method: 'POST' }, token),
  me: (token) => request('/me', {}, token),
  templates: () => request('/templates'),
  musics: () => request('/musics'),
  moments: (page = 1) => request(`/moments?page=${page}`),
  moment: (id) => request(`/moments/${id}`),
  requestInvitation: (id, values) => request(`/moments/${id}/request-invitation`, { method: 'POST', body: JSON.stringify(values) }),
  reactToMoment: (id, type, token) => request(`/moments/${id}/reaction`, { method: 'POST', body: JSON.stringify({ type }) }, token),
  removeMomentReaction: (id, token) => request(`/moments/${id}/reaction`, { method: 'DELETE' }, token),
  commentOnMoment: (id, body, token) => request(`/moments/${id}/comments`, { method: 'POST', body: JSON.stringify({ body }) }, token),
  notifications: (token) => request('/social/notifications', {}, token),
  readNotification: (id, token) => request(`/social/notifications/${id}/read`, { method: 'PUT' }, token),
  invitations: (token) => request('/invitations', {}, token),
  syncDraft: async (draft, token, includeMedia = true) =>
    request('/invitations/sync-local-draft', { method: 'POST', body: await draftFormData(draft, includeMedia) }, token),
  updateDraft: async (id, draft, token, includeMedia = false) =>
    request(`/invitations/${id}`, { method: 'POST', body: await draftFormData(draft, includeMedia, 'PUT') }, token),
  publish: (id, token) => request(`/invitations/${id}/publish`, { method: 'POST' }, token),
  setFeedVisibility: (id, isHidden, token) => request(`/invitations/${id}/feed-visibility`, { method: 'PUT', body: JSON.stringify({ is_hidden_from_feed: isHidden }) }, token),
  invitationRequests: (id, token) => request(`/invitations/${id}/invitation-requests`, {}, token),
  markInvitationRequestShared: (id, requestId, token) => request(`/invitations/${id}/invitation-requests/${requestId}/shared`, { method: 'PUT' }, token),
  invitationMoments: (id, token) => request(`/invitations/${id}/moments`, {}, token),
  createInvitationMoment: async (id, values, token) => request(`/invitations/${id}/moments`, { method: 'POST', body: await socialMomentFormData(values) }, token),
  deleteInvitationMoment: (id, momentId, token) => request(`/invitations/${id}/moments/${momentId}`, { method: 'DELETE' }, token),
  giftSetting: (id, token) => request(`/invitations/${id}/gift-setting`, {}, token),
  saveGiftSetting: (id, values, token) =>
    request(`/invitations/${id}/gift-setting`, { method: 'POST', body: JSON.stringify(values) }, token),
  weddingGifts: (id, token) => request(`/invitations/${id}/gifts`, {}, token),
  payoutAccount: (token) => request('/payout-account', {}, token),
  savePayoutAccount: (values, token) =>
    request('/payout-account', { method: 'POST', body: JSON.stringify(values) }, token),
  payoutRequests: (id, token) => request(`/invitations/${id}/payout-requests`, {}, token),
  requestPayout: (id, values, token) =>
    request(`/invitations/${id}/payout-requests`, { method: 'POST', body: JSON.stringify(values) }, token),
};
