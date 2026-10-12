const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function harness(responseFor = (url) => ({ data: url.includes('/templates?') ? [
  { id: 1, invitation_type: 'wedding' }, { id: 6, invitation_type: 'birthday' }, { id: 9, invitation_type: 'megedong' }, { id: 12, invitation_type: 'pitung_dina' }, { id: 15, invitation_type: 'tigang_sasih' }, { id: 21, invitation_type: 'otonan' }, { id: 30, invitation_type: 'mepandes' },
] : { id: 42 } })) {
  const storage = new Map();
  const requests = [];
  const modules = new Map();
  class TestFormData {
    entries = [];
    append(key, value) { this.entries.push([key, value]); }
  }
  const mocks = {
    '@react-native-async-storage/async-storage': {
      multiGet: async (keys) => keys.map((key) => [key, storage.get(key) ?? null]),
      setItem: async (key, value) => storage.set(key, value),
      multiRemove: async (keys) => keys.forEach((key) => storage.delete(key)),
    },
    'react-native': { NativeModules: {}, Platform: { OS: 'android' } },
    './localMedia': { ensureLocalFileExists: async () => {} },
  };
  function load(relativePath) {
    const filename = path.resolve(__dirname, '..', relativePath);
    if (modules.has(filename)) return modules.get(filename);
    const module = { exports: {} };
    const code = transformSync(readFileSync(filename, 'utf8'), {
      babelrc: false, configFile: false, plugins: [require.resolve('@babel/plugin-transform-modules-commonjs')],
    }).code;
    vm.runInNewContext(code, {
      module, exports: module.exports, FormData: TestFormData, __DEV__: true,
      process: { env: { EXPO_PUBLIC_API_URL: 'http://localhost:8015/api' } },
      fetch: async (url, options) => { requests.push({ url, ...options }); return { ok: true, json: async () => responseFor(url) }; },
      require: (id) => {
        if (mocks[id]) return mocks[id];
        return load(path.relative(path.resolve(__dirname, '..'), path.resolve(path.dirname(filename), id + '.js')));
      },
    }, { filename });
    modules.set(filename, module.exports);
    return module.exports;
  }
  return { load, requests, storage };
}


test('mepandes is titled by its participants and uses its own labels', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = (nicknames) => ({ invitation_type: 'mepandes', mepandes_data: { participants: nicknames.map((nickname) => ({ nickname })) } });

  assert.equal(kit.isMepandes(draft([])), true);
  assert.equal(kit.isChildCeremony(draft([])), false);
  assert.equal(kit.invitationName(draft([])), 'Yang Mepandes');
  assert.equal(kit.invitationName(draft(['Kirana'])), 'Kirana');
  assert.equal(kit.invitationName(draft(['Kirana', 'Bagus'])), 'Kirana & Bagus');
  assert.equal(kit.invitationName(draft(['Kirana', 'Bagus', 'Dewi'])), 'Kirana, Bagus & Dewi');
  assert.equal(kit.invitationName(draft(['Kirana', 'Bagus', 'Dewi', 'Agus', 'Sari'])), 'Kirana, Bagus & 3 lainnya');
  // A participant without a nickname yet is left out; server records carry the list at the top level.
  assert.equal(kit.invitationName(draft(['Kirana', '', 'Dewi'])), 'Kirana & Dewi');
  assert.equal(kit.invitationName({ invitation_type: 'mepandes', participants: [{ nickname: 'Kirana' }, { nickname: 'Bagus' }] }), 'Kirana & Bagus');

  assert.equal(kit.typeLabelFor(draft([])), 'mepandes');
  assert.equal(kit.giftLabelFor(draft([])), 'Tanda Kasih');
  assert.equal(kit.personScreenFor(draft([])), 'MepandesForm');
  assert.deepEqual(Array.from(kit.eventTypesFor(draft([]))), ['Mepandes']);
  assert.equal(kit.occasionPhraseFor(draft(['Kirana', 'Bagus'])), 'upacara mepandes (potong gigi) Kirana & Bagus');
  assert.equal(kit.templateCardColorsFor({ slug: 'wastra-songket', blade_view: 'invitations.templates.upacara-anak.wastra-songket' }).accent, '#7a3f68');
  assert.equal(kit.INVITATION_CHOICES.at(-1).title, 'Mepandes (Potong Gigi)');
  assert.equal(kit.MAX_PARTICIPANTS, 10);
});

test('a new mepandes draft preselects its event and its templates come from its own catalog', async () => {
  const { load, requests } = harness();
  const { createEmptyDraft } = load('src/services/draftStorage.js');
  const { MEPANDES_OPENING_QUOTE } = load('src/constants/invitation.js');
  const { api } = load('src/services/api.js');

  const draft = createEmptyDraft('mepandes');
  assert.equal(draft.event_data.event_type, 'Mepandes');
  assert.equal(draft.event_data.opening_quote, MEPANDES_OPENING_QUOTE);
  assert.equal(JSON.stringify(draft.mepandes_data), '{}');

  const templates = await api.templates('mepandes');
  assert.match(requests[0].url, /invitation_type=mepandes/);
  assert.deepEqual(Array.from(templates.data, (template) => template.id), [30]);
});

test('sync sends every participant in order, uploading new photos and naming the ones to keep', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  await api.syncDraft({
    invitation_type: 'mepandes', selected_template: 30,
    groom_data: { groom_full_name: 'I Made Wira Adnyana', groom_nickname: 'Wira' },
    bride_data: { bride_full_name: 'Ni Putu Ayu Lestari', bride_nickname: 'Ayu' },
    mepandes_data: {
      family_mode: 'multi', host_name: 'Keluarga Besar I Made Wira',
      participants: [
        { full_name: 'Ni Luh Kirana Dewi', nickname: 'Kirana', gender: 'putri', age: '19', father_name: 'I Made Wira', mother_name: 'Ni Putu Ayu', photo: { uri: 'file://kirana.jpg', fileName: 'kirana.jpg', mimeType: 'image/jpeg' } },
        { full_name: '', nickname: '', photo: null },
        { full_name: 'Ni Komang Dewi', nickname: 'Dewi', gender: '', age: '', photo: { uri: 'https://server/storage/invitations/photos/dewi.jpg', isRemote: true, remotePath: 'invitations/photos/dewi.jpg' } },
      ],
    },
    child_data: {}, megedong_data: {}, event_data: { event_type: 'Mepandes' }, gallery_data: { photos: [] },
  }, 'test-token');

  const fields = new Map(requests[0].body.entries);
  assert.equal(fields.get('invitation_type'), 'mepandes');
  assert.equal(fields.get('mepandes_data[family_mode]'), 'multi');
  assert.equal(fields.get('mepandes_data[host_name]'), 'Keluarga Besar I Made Wira');
  assert.equal(fields.get('mepandes_data[participants][0][nickname]'), 'Kirana');
  assert.equal(fields.get('mepandes_data[participants][0][age]'), '19');
  assert.equal(fields.get('mepandes_data[participants][0][father_name]'), 'I Made Wira');
  // The empty row is still sent, so the third participant stays third.
  assert.equal(fields.get('mepandes_data[participants][1][full_name]'), '');
  assert.equal(fields.get('mepandes_data[participants][2][nickname]'), 'Dewi');
  assert.equal(fields.get('participant_photos_changed'), '1');
  assert.equal(fields.get('participant_photos[0]').uri, 'file://kirana.jpg');
  assert.equal(fields.has('participant_photos[1]'), false);
  assert.equal(fields.has('participant_photos[2]'), false);
  assert.equal(fields.get('mepandes_data[participants][2][photo_path]'), 'invitations/photos/dewi.jpg');
});

test('a text-only update leaves the photos alone, and other types never send participants', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  const participants = [{ full_name: 'Ni Luh Kirana Dewi', nickname: 'Kirana', photo: { uri: 'file://kirana.jpg' } }];

  await api.updateDraft(42, { invitation_type: 'mepandes', selected_template: 30, mepandes_data: { family_mode: 'single', participants }, event_data: {}, gallery_data: { photos: [] } }, 'test-token', false);
  let keys = requests[0].body.entries.map(([key]) => key);
  assert.equal(keys.includes('mepandes_data[participants][0][nickname]'), true);
  assert.equal(keys.some((key) => key.startsWith('participant_photos')), false);

  await api.syncDraft({ invitation_type: 'otonan', selected_template: 21, mepandes_data: { family_mode: 'single', participants }, child_data: {}, event_data: {}, gallery_data: { photos: [] } }, 'test-token');
  keys = requests[1].body.entries.map(([key]) => key);
  assert.equal(keys.some((key) => key.startsWith('mepandes_data') || key.startsWith('participant_photos')), false);
});

test('age is optional and must be a whole number up to 120', () => {
  const { load } = harness();
  const { validateWholeNumber, validateCeremonyNumber } = load('src/utils/validation.js');

  assert.equal(validateWholeNumber('', 'Usia', 120), null);
  assert.equal(validateWholeNumber('17', 'Usia', 120), null);
  assert.match(validateWholeNumber('121', 'Usia peserta 2', 120), /Usia peserta 2 harus angka bulat 1 sampai 120/);
  assert.match(validateWholeNumber('tujuh belas', 'Usia', 120), /Usia harus angka bulat/);
  // The otonan number keeps its own range.
  assert.equal(validateCeremonyNumber('200', 'Otonan ke-'), null);
  assert.match(validateCeremonyNumber('201', 'Otonan ke-'), /1 sampai 200/);
});
