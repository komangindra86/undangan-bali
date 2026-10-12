const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function harness(responseFor = (url) => ({ data: url.includes('/templates?') ? [
  { id: 1, invitation_type: 'wedding' }, { id: 6, invitation_type: 'birthday' }, { id: 9, invitation_type: 'megedong' }, { id: 12, invitation_type: 'pitung_dina' }, { id: 15, invitation_type: 'tigang_sasih' }, { id: 21, invitation_type: 'otonan' },
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
  assert.equal(kit.templateCardColorsFor({ slug: 'langit-kumara' }).accent, '#1f6f78');
  assert.equal(kit.templatePreviewFor({ invitation_type: 'wedding' }), undefined);
});

test('the type picker lists every invitation type once, in order', () => {
  const { load } = harness();
  const { INVITATION_CHOICES } = load('src/constants/invitation.js');

  assert.deepEqual(Array.from(INVITATION_CHOICES, (choice) => choice.type), ['wedding', 'birthday', 'megedong', 'pitung_dina', 'tigang_sasih', 'otonan', 'mepandes']);
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

test('tigang sasih is titled by the child name and falls back to the parents', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const parents = { invitation_type: 'tigang_sasih', groom_data: { groom_nickname: 'Wira' }, bride_data: { bride_nickname: 'Ayu' } };

  assert.equal(kit.requiresChildName(parents), true);
  assert.equal(kit.requiresChildName({ invitation_type: 'pitung_dina' }), false);
  assert.equal(kit.invitationName({ ...parents, child_data: { child_nickname: 'Kirana', child_gender: 'putri' } }), 'Kirana');
  assert.equal(kit.invitationName({ ...parents, child_data: { child_gender: 'putri' } }), 'Putri Wira & Ayu');
  assert.equal(kit.invitationName({ invitation_type: 'tigang_sasih', child_nickname: 'Kirana', groom_nickname: 'Wira', bride_nickname: 'Ayu' }), 'Kirana');
  // Abulan pitung dina keeps the parents-based title even when a nickname was entered.
  assert.equal(kit.invitationName({ ...parents, invitation_type: 'pitung_dina', child_data: { child_nickname: 'Kirana' } }), 'Buah Hati Wira & Ayu');
});

test('tigang sasih uses its own labels and names the child in the share text', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = { invitation_type: 'tigang_sasih', child_data: { child_nickname: 'Kirana' } };

  assert.equal(kit.giftLabelFor(draft), 'Tanda Kasih');
  assert.equal(kit.personScreenFor(draft), 'ChildCeremonyForm');
  assert.equal(kit.typeLabelFor(draft), 'tigang sasih');
  assert.deepEqual(Array.from(kit.eventTypesFor(draft)), ['Tigang Sasih']);
  assert.equal(kit.occasionPhraseFor(draft), 'upacara tigang sasih (3 bulanan) buah hati kami, Kirana');
  assert.equal(kit.occasionPhraseFor({ invitation_type: 'tigang_sasih' }), 'upacara tigang sasih (3 bulanan) buah hati kami');
  assert.equal(kit.templatePreviewFor(draft).names, 'Kirana');
});

test('a design shared between ceremonies keeps its colours under another slug', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const shared = { slug: 'tigang-sasih-rare-kencana', blade_view: 'invitations.templates.upacara-anak.rare-kencana' };

  assert.equal(kit.templateDesignKey(shared), 'rare-kencana');
  assert.equal(kit.templateCardColorsFor(shared).accent, kit.templateCardColorsFor({ slug: 'rare-kencana' }).accent);
  assert.equal(kit.templateCardColorsFor({ slug: 'surya-alit', blade_view: 'invitations.templates.upacara-anak.surya-alit' }).accent, '#a34a12');
  // Older servers that do not send blade_view still resolve by slug.
  assert.equal(kit.templateDesignKey({ slug: 'tunjung-petak' }), 'tunjung-petak');
});

test('tigang sasih template catalog is requested and filtered by its own type', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');

  const templates = await api.templates('tigang_sasih');
  assert.match(requests[0].url, /invitation_type=tigang_sasih/);
  assert.deepEqual(Array.from(templates.data, (template) => template.id), [15]);
});

test('otonan is titled by the child name and may say which otonan it is', () => {
  const { load } = harness();
  const kit = load('src/constants/invitation.js');
  const draft = { invitation_type: 'otonan', groom_data: { groom_nickname: 'Wira' }, bride_data: { bride_nickname: 'Ayu' }, child_data: { child_nickname: 'Kirana' } };

  assert.equal(kit.isChildCeremony(draft), true);
  assert.equal(kit.requiresChildName(draft), true);
  assert.equal(kit.isNumberedCeremony(draft), true);
  assert.equal(kit.isNumberedCeremony({ invitation_type: 'tigang_sasih' }), false);
  assert.equal(kit.invitationName(draft), 'Kirana');
  assert.equal(kit.typeLabelFor(draft), 'otonan');
  assert.equal(kit.giftLabelFor(draft), 'Tanda Kasih');
  assert.deepEqual(Array.from(kit.eventTypesFor(draft)), ['Otonan']);

  assert.equal(kit.ceremonyNumberLabelFor(draft), null);
  assert.equal(kit.occasionPhraseFor(draft), 'upacara otonan buah hati kami, Kirana');
  const third = { ...draft, child_data: { ...draft.child_data, ceremony_number: '3' } };
  assert.equal(kit.ceremonyNumberLabelFor(third), 'Otonan ke-3');
  assert.equal(kit.occasionPhraseFor(third), 'upacara otonan ke-3 buah hati kami, Kirana');
  // Server records carry the number at the top level.
  assert.equal(kit.ceremonyNumberLabelFor({ invitation_type: 'otonan', ceremony_number: 1 }), 'Otonan Pertama');
  // Ceremonies held once never show a number, even if an old draft carries one.
  assert.equal(kit.ceremonyNumberLabelFor({ invitation_type: 'tigang_sasih', child_data: { ceremony_number: '3' } }), null);
});

test('otonan number is optional and must be a whole number from 1 to 200', () => {
  const { load } = harness();
  const { validateCeremonyNumber } = load('src/utils/validation.js');

  assert.equal(validateCeremonyNumber('', 'Otonan ke-'), null);
  assert.equal(validateCeremonyNumber('1', 'Otonan ke-'), null);
  assert.equal(validateCeremonyNumber('200', 'Otonan ke-'), null);
  for (const value of ['0', '201', '2.5', 'tiga']) {
    assert.match(validateCeremonyNumber(value, 'Otonan ke-'), /Otonan ke- harus angka bulat 1 sampai 200/);
  }
});

test('a new otonan draft preselects its event, and sync sends the number', async () => {
  const { load, requests } = harness();
  const { createEmptyDraft } = load('src/services/draftStorage.js');
  const { OTONAN_OPENING_QUOTE, templateCardColorsFor } = load('src/constants/invitation.js');
  const { api } = load('src/services/api.js');

  const draft = createEmptyDraft('otonan');
  assert.equal(draft.event_data.event_type, 'Otonan');
  assert.equal(draft.event_data.opening_quote, OTONAN_OPENING_QUOTE);
  assert.equal(templateCardColorsFor({ slug: 'otonan-rare-kencana', blade_view: 'invitations.templates.upacara-anak.rare-kencana' }).accent, '#8a5f1c');
  assert.equal(templateCardColorsFor({ slug: 'purnama-alit', blade_view: 'invitations.templates.upacara-anak.purnama-alit' }).accent, '#5a4fa3');

  const templates = await api.templates('otonan');
  assert.match(requests[0].url, /invitation_type=otonan/);
  assert.deepEqual(Array.from(templates.data, (template) => template.id), [21]);

  await api.syncDraft({
    ...draft, selected_template: 21,
    groom_data: { groom_full_name: 'I Made Wira Adnyana', groom_nickname: 'Wira' },
    bride_data: { bride_full_name: 'Ni Putu Ayu Lestari', bride_nickname: 'Ayu' },
    child_data: { child_full_name: 'Ni Luh Kirana Dewi', child_nickname: 'Kirana', ceremony_number: '3' },
  }, 'test-token');
  const fields = new Map(requests[1].body.entries);
  assert.equal(fields.get('invitation_type'), 'otonan');
  assert.equal(fields.get('child_data[ceremony_number]'), '3');
});
