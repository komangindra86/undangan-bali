const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function harness(responseFor = (url) => ({ data: url.includes('/templates?') ? [
  { id: 1, invitation_type: 'wedding' }, { id: 6, invitation_type: 'birthday' }, { id: 9, invitation_type: 'megedong' },
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


test('megedong helpers use their own labels and put the mother first', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = { invitation_type: 'megedong', bride_data: { bride_nickname: 'Ayu' }, groom_data: { groom_nickname: 'Wira' } };

  assert.equal(kit.isMegedong(draft), true);
  assert.equal(kit.invitationName(draft), 'Ayu & Wira');
  assert.equal(kit.invitationName({ invitation_type: 'megedong', bride_nickname: 'Ayu', groom_nickname: 'Wira' }), 'Ayu & Wira');
  assert.equal(kit.giftLabelFor(draft), 'Tanda Kasih');
  assert.equal(kit.personScreenFor(draft), 'MegedongForm');
  assert.equal(kit.typeLabelFor(draft), 'megedong-gedongan');
  assert.deepEqual(Array.from(kit.eventTypesFor(draft)), ['Megedong-gedongan']);
  assert.equal(kit.occasionPhraseFor(draft), 'upacara megedong-gedongan kami');
  assert.equal(Object.keys(kit.MEGEDONG_TEMPLATE_COLORS).length, 3);
});

test('existing types keep their wording and unknown types fall back to wedding', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');

  assert.equal(kit.invitationName({ invitation_type: 'wedding', groom_nickname: 'Wira', bride_nickname: 'Ayu' }), 'Wira & Ayu');
  assert.equal(kit.giftLabelFor({ invitation_type: 'wedding' }), 'Wedding Gift');
  assert.equal(kit.giftLabelFor({ invitation_type: 'birthday' }), 'Kado Digital');
  assert.equal(kit.personScreenFor({}), 'GroomBrideForm');
  assert.equal(kit.occasionPhraseFor({ invitation_type: 'birthday', celebrant_nickname: 'Kirana' }), 'perayaan ulang tahun Kirana');
  assert.equal(kit.typeLabelFor({ invitation_type: 'something-new' }), 'pernikahan');
});

test('a new megedong draft preselects its event type and opening words', () => {
  const { load } = harness();
  const { createEmptyDraft } = load('src/services/draftStorage.js');
  const { MEGEDONG_OPENING_QUOTE } = load('src/constants/invitation.js');

  const megedong = createEmptyDraft('megedong');
  assert.equal(megedong.invitation_type, 'megedong');
  assert.equal(megedong.event_data.event_type, 'Megedong-gedongan');
  assert.equal(megedong.event_data.opening_quote, MEGEDONG_OPENING_QUOTE);
  assert.equal(JSON.stringify(megedong.megedong_data), '{}');
  assert.equal(createEmptyDraft('birthday').event_data.event_type, 'Ulang Tahun');
  assert.equal(createEmptyDraft('wedding').event_data.event_type, undefined);
});

test('megedong draft survives a restart with its pregnancy details', async () => {
  const { load } = harness();
  const storage = load('src/services/draftStorage.js');
  await storage.saveDraftSection('invitation_type', 'megedong');
  await storage.saveDraftSection('megedong_data', { pregnancy_age: '7 bulan', child_order: 'Anak pertama' });

  const draft = await storage.loadDraft();
  assert.equal(draft.invitation_type, 'megedong');
  assert.equal(draft.megedong_data.pregnancy_age, '7 bulan');

  await storage.clearLocalDraft();
  assert.equal(JSON.stringify((await storage.loadDraft()).megedong_data), '{}');
});

test('multipart sync carries both parents, pregnancy details and their photos', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  await api.syncDraft({
    invitation_type: 'megedong', selected_template: 9,
    bride_data: { bride_full_name: 'Ni Putu Ayu Lestari', bride_nickname: 'Ayu', bride_photo: { uri: 'file://ibu.jpg', fileName: 'ibu.jpg', mimeType: 'image/jpeg' } },
    groom_data: { groom_full_name: 'I Made Wira Adnyana', groom_nickname: 'Wira' },
    megedong_data: { pregnancy_age: '7 bulan', child_order: 'Anak pertama' },
    event_data: { event_type: 'Megedong-gedongan' }, gallery_data: { photos: [] },
  }, 'test-token');

  const fields = new Map(requests[0].body.entries);
  assert.equal(fields.get('invitation_type'), 'megedong');
  assert.equal(fields.get('bride_data[bride_nickname]'), 'Ayu');
  assert.equal(fields.get('groom_data[groom_nickname]'), 'Wira');
  assert.equal(fields.get('megedong_data[pregnancy_age]'), '7 bulan');
  assert.equal(fields.get('megedong_data[child_order]'), 'Anak pertama');
  assert.equal(fields.get('event_data[event_type]'), 'Megedong-gedongan');
  assert.equal(fields.get('bride_photo').uri, 'file://ibu.jpg');
  assert.equal(fields.has('celebrant_photo'), false);
});

test('megedong template catalog is requested and filtered by its own type', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');

  const templates = await api.templates('megedong');
  assert.match(requests[0].url, /invitation_type=megedong/);
  assert.deepEqual(templates.data.map((template) => template.id), [9]);

  const legacy = harness(() => ({ data: [{ id: 1 }] }));
  await assert.rejects(
    legacy.load('src/services/api.js').api.templates('megedong'),
    /Template megedong-gedongan belum tersedia di server/,
  );
});
