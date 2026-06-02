import AsyncStorage from '@react-native-async-storage/async-storage';

export const DRAFT_STORAGE_KEYS = {
  selected_template: 'selected_template',
  groom_data: 'groom_data',
  bride_data: 'bride_data',
  event_data: 'event_data',
  location_data: 'location_data',
  gallery_data: 'gallery_data',
  music_data: 'music_data',
  gift_data: 'gift_data',
};

export const SESSION_KEYS = {
  token: 'auth_token',
  user: 'auth_user',
  hasAccountOnDevice: 'has_account_on_device',
  remoteInvitationId: 'remote_invitation_id',
};

export const emptyDraft = {
  selected_template: null,
  groom_data: {},
  bride_data: {},
  event_data: {},
  location_data: {},
  gallery_data: { photos: [] },
  music_data: { music_type: 'none', music_id: null },
  gift_data: {
    is_active: false,
    receiver_name: '',
    receiver_note: '',
    minimum_amount: '10000',
    show_amount_public: false,
    allow_message: true,
  },
};

export async function loadDraft() {
  const entries = await AsyncStorage.multiGet(Object.values(DRAFT_STORAGE_KEYS));
  const draft = { ...emptyDraft };

  Object.entries(DRAFT_STORAGE_KEYS).forEach(([section, key]) => {
    const stored = entries.find(([entryKey]) => entryKey === key)?.[1];
    if (stored) {
      draft[section] = JSON.parse(stored);
    }
  });

  return draft;
}

export async function saveDraftSection(section, value) {
  await AsyncStorage.setItem(DRAFT_STORAGE_KEYS[section], JSON.stringify(value));
}

export async function clearLocalDraft() {
  await AsyncStorage.multiRemove([
    ...Object.values(DRAFT_STORAGE_KEYS),
    SESSION_KEYS.remoteInvitationId,
  ]);
}
