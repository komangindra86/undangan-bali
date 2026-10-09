const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function harness(responseFor = (url) => ({ data: url.includes('/templates?') ? [
  { id: 1, invitation_type: 'wedding' }, { id: 6, invitation_type: 'birthday' }, { id: 9, invitation_type: 'megedong' }, { id: 12, invitation_type: 'pitung_dina' },
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


test('child ceremony title names the parents and follows the optional gender', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = { invitation_type: 'pitung_dina', groom_data: { groom_nickname: 'Wira' }, bride_data: { bride_nickname: 'Ayu' }, child_data: {} };

  assert.equal(kit.isChildCeremony(draft), true);
  assert.equal(kit.isChildCeremony({ invitation_type: 'megedong' }), false);
  assert.equal(kit.invitationName(draft), 'Buah Hati Wira & Ayu');
  assert.equal(kit.invitationName({ ...draft, child_data: { child_gender: 'putra' } }), 'Putra Wira & Ayu');
  assert.equal(kit.invitationName({ ...draft, child_data: { child_gender: 'putri', child_nickname: 'Kirana' } }), 'Putri Wira & Ayu');
  // Server records carry the same fields at the top level.
  assert.equal(kit.invitationName({ invitation_type: 'pitung_dina', groom_nickname: 'Wira', bride_nickname: 'Ayu', child_gender: 'putri' }), 'Putri Wira & Ayu');
});

test('abulan pitung dina uses its own labels', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = { invitation_type: 'pitung_dina' };

  assert.equal(kit.giftLabelFor(draft), 'Tanda Kasih');
  assert.equal(kit.personScreenFor(draft), 'ChildCeremonyForm');
  assert.equal(kit.typeLabelFor(draft), 'abulan pitung dina');
  assert.deepEqual(Array.from(kit.eventTypesFor(draft)), ['Abulan Pitung Dina']);
  assert.equal(kit.occasionPhraseFor(draft), 'upacara abulan pitung dina (42 hari) buah hati kami');
  assert.equal(kit.templatePreviewFor(draft).names, 'Putra Wira & Ayu');
  assert.equal(kit.templateCardColorsFor({ slug: 'langit-kumara' }).accent, '#236f77');
  assert.equal(kit.templatePreviewFor({ invitation_type: 'wedding' }), undefined);
});

test('the type picker lists every invitation type once, in order', () => {
  const { load } = harness();
  const { INVITATION_CHOICES } = load('src/constants/invitation.js');

  assert.deepEqual(Array.from(INVITATION_CHOICES, (choice) => choice.type), ['wedding', 'birthday', 'megedong', 'pitung_dina']);
  assert.equal(INVITATION_CHOICES[3].title, 'Abulan Pitung Dina (42 Hari)');
  assert.ok(Array.from(INVITATION_CHOICES).every((choice) => choice.icon && choice.title && choice.body));
});

test('a new abulan pitung dina draft preselects its event and opening words', () => {
  const { load } = harness();
  const { createEmptyDraft } = load('src/services/draftStorage.js');
  const { PITUNG_DINA_OPENING_QUOTE } = load('src/constants/invitation.js');

  const draft = createEmptyDraft('pitung_dina');
  assert.equal(draft.event_data.event_type, 'Abulan Pitung Dina');
  assert.equal(draft.event_data.opening_quote, PITUNG_DINA_OPENING_QUOTE);
  assert.equal(JSON.stringify(draft.child_data), '{}');
});

test('multipart sync carries parents, optional child details and the child photo', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  await api.syncDraft({
    invitation_type: 'pitung_dina', selected_template: 12,
    groom_data: { groom_full_name: 'I Made Wira Adnyana', groom_nickname: 'Wira' },
    bride_data: { bride_full_name: 'Ni Putu Ayu Lestari', bride_nickname: 'Ayu' },
    child_data: { child_full_name: 'Ni Luh Kirana Dewi', child_gender: 'putri', child_order: '', child_birth_date: '2026-09-01', child_photo: { uri: 'file://bayi.jpg', fileName: 'bayi.jpg', mimeType: 'image/jpeg' } },
    megedong_data: {}, event_data: { event_type: 'Abulan Pitung Dina' }, gallery_data: { photos: [] },
  }, 'test-token');

  const fields = new Map(requests[0].body.entries);
  assert.equal(fields.get('invitation_type'), 'pitung_dina');
  assert.equal(fields.get('groom_data[groom_nickname]'), 'Wira');
  assert.equal(fields.get('bride_data[bride_nickname]'), 'Ayu');
  assert.equal(fields.get('child_data[child_full_name]'), 'Ni Luh Kirana Dewi');
  assert.equal(fields.get('child_data[child_gender]'), 'putri');
  assert.equal(fields.get('child_data[child_birth_date]'), '2026-09-01');
  assert.equal(fields.get('child_photo').uri, 'file://bayi.jpg');
});

test('other invitation types never send child details', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  await api.syncDraft({
    invitation_type: 'wedding', selected_template: 1,
    groom_data: { groom_nickname: 'Wira' }, bride_data: { bride_nickname: 'Ayu' },
    child_data: { child_full_name: 'Sisa Draft Lama', child_photo: { uri: 'file://lama.jpg' } },
    event_data: {}, gallery_data: { photos: [] },
  }, 'test-token');

  const keys = requests[0].body.entries.map(([key]) => key);
  assert.equal(keys.some((key) => key.startsWith('child_data') || key === 'child_photo'), false);
});

test('abulan pitung dina template catalog is requested and filtered by its own type', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');

  const templates = await api.templates('pitung_dina');
  assert.match(requests[0].url, /invitation_type=pitung_dina/);
  assert.deepEqual(templates.data.map((template) => template.id), [12]);

  const legacy = harness(() => ({ data: [{ id: 1 }] }));
  await assert.rejects(
    legacy.load('src/services/api.js').api.templates('pitung_dina'),
    /Template abulan pitung dina belum tersedia di server/,
  );
});
