const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function harness(responseFor = (url) => ({ data: url.includes('/templates?') ? [
  { id: 1, invitation_type: 'wedding' }, { id: 6, invitation_type: 'birthday' },
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
    'react-native': { Platform: { OS: 'android' } },
    './localMedia': { ensureLocalFileExists: async () => {} },
  };
  function load(relativePath) {
    const filename = path.resolve(__dirname, '..', relativePath);
    if (modules.has(filename)) return modules.get(filename);
    const module = { exports: {} };
    const code = transformSync(readFileSync(filename, 'utf8'), {
      babelrc: false, configFile: false, plugins: ['@babel/plugin-transform-modules-commonjs'],
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

test('old local drafts default to wedding and retain their data', async () => {
  const { load, storage } = harness();
  storage.set('groom_data', JSON.stringify({ groom_nickname: 'Wira' }));
  const draft = await load('src/services/draftStorage.js').loadDraft();
  assert.equal(draft.invitation_type, 'wedding');
  assert.equal(draft.groom_data.groom_nickname, 'Wira');
});

test('birthday local draft survives restore and clearing leaves the login token intact', async () => {
  const { load, storage } = harness();
  const store = load('src/services/draftStorage.js');
  const draft = store.createEmptyDraft('birthday');
  draft.birthday_data = { celebrant_nickname: 'Kirana', celebrant_age: '' };
  for (const [key, value] of Object.entries(draft)) await store.saveDraftSection(key, value);
  storage.set('auth_token', 'local-test-token');
  storage.set('remote_invitation_id', '42');
  const restored = await store.loadDraft();
  assert.equal(restored.invitation_type, 'birthday');
  assert.equal(restored.event_data.event_type, 'Ulang Tahun');
  assert.equal(restored.birthday_data.celebrant_nickname, 'Kirana');
  assert.equal(restored.event_data.opening_quote.includes('Pawiwahan'), false);
  await store.clearLocalDraft();
  assert.equal(storage.has('birthday_data'), false);
  assert.equal(storage.has('remote_invitation_id'), false);
  assert.equal(storage.get('auth_token'), 'local-test-token');
});

test('new draft objects do not share mutable nested values', () => {
  const { load } = harness();
  const { createEmptyDraft } = load('src/services/draftStorage.js');
  const birthday = createEmptyDraft('birthday');
  birthday.gallery_data.photos.push({ uri: 'file://one.jpg' });
  assert.equal(createEmptyDraft().gallery_data.photos.length, 0);
});

test('birthday labels and navigation work for both API records and local drafts', () => {
  const { load } = harness();
  const { personScreenFor, giftLabelFor, invitationName } = load('src/constants/invitation.js');
  assert.equal(personScreenFor({ invitation_type: 'birthday' }), 'BirthdayForm');
  assert.equal(personScreenFor({}), 'GroomBrideForm');
  assert.equal(giftLabelFor({ invitation_type: 'birthday' }), 'Kado Digital');
  assert.equal(invitationName({ invitation_type: 'birthday', birthday_data: { celebrant_nickname: 'Kirana' } }), 'Kirana');
  assert.equal(invitationName({ invitation_type: 'birthday', celebrant_nickname: 'Kirana' }), 'Kirana');
});

test('optional age and nickname validation reject invalid values before Next', () => {
  const { load } = harness();
  const { validateBirthdayAge, validateNickname } = load('src/utils/validation.js');
  for (const age of ['', '1', '7', '150']) assert.equal(validateBirthdayAge(age), null);
  for (const age of ['0', '-1', '2.5', '151', 'abc']) assert.ok(validateBirthdayAge(age));
  assert.ok(validateNickname('A'.repeat(19), 'Nama panggilan'));
  assert.ok(validateNickname('<script>', 'Nama panggilan'));
});

test('multipart sync carries birthday fields, owner media and optional blank age', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  await api.syncDraft({
    invitation_type: 'birthday', selected_template: 6,
    birthday_data: { celebrant_nickname: 'Kirana', celebrant_age: '', celebrant_photo: { uri: 'file://birthday.jpg', fileName: 'birthday.jpg', mimeType: 'image/jpeg' } },
    event_data: { event_type: 'Ulang Tahun' }, gallery_data: { photos: [] },
  }, 'test-token');
  const fields = new Map(requests[0].body.entries);
  assert.equal(fields.get('invitation_type'), 'birthday');
  assert.equal(fields.get('birthday_data[celebrant_nickname]'), 'Kirana');
  assert.equal(fields.get('birthday_data[celebrant_age]'), '');
  assert.equal(fields.get('celebrant_photo').uri, 'file://birthday.jpg');
  assert.equal(requests[0].headers.Authorization, 'Bearer test-token');
  assert.equal(requests[0].headers['Content-Type'], undefined);
});

test('feed consent is not silently granted and template catalogs are filtered', async () => {
  const { load, requests } = harness();
  const { api } = load('src/services/api.js');
  const wedding = await api.templates();
  const birthday = await api.templates('birthday');
  assert.equal(wedding.data.length, 1);
  assert.equal(wedding.data[0].id, 1);
  assert.equal(birthday.data.length, 1);
  assert.equal(birthday.data[0].id, 6);
  await api.setFeedVisibility(42, false, 'test-token');
  await api.setFeedVisibility(42, false, 'test-token', true);
  assert.ok(requests[0].url.endsWith('invitation_type=wedding'));
  assert.ok(requests[1].url.endsWith('invitation_type=birthday'));
  assert.equal(JSON.parse(requests[2].body).privacy_acknowledged, false);
  assert.equal(JSON.parse(requests[3].body).privacy_acknowledged, true);
});

test('legacy server ignoring the birthday filter never supplies wedding previews to that flow', async () => {
  const legacy = ['bali-classic', 'pura-sunset', 'ubud-garden', 'royal-kamasan', 'puspa-kencana']
    .map((slug, index) => ({ id: index + 1, slug, preview_url: `https://example.test/preview/templates/${slug}` }));
  const { load } = harness(() => ({ data: legacy }));
  const { api } = load('src/services/api.js');
  await assert.rejects(api.templates('birthday'), /Template ulang tahun belum tersedia di server/);
  assert.equal((await api.templates()).data.length, 5);
});

test('explicit birthday templates retain only their own preview links and catalog metadata', async () => {
  const data = ['ceria-confetti', 'ruang-putih', 'bali-pradnyan'].map((slug, index) => ({
    id: index + 6, invitation_type: 'birthday', slug, preview_url: `http://127.0.0.1:8015/preview/templates/${slug}`,
  }));
  const { load } = harness(() => ({ data, revision: 2 }));
  const result = await load('src/services/api.js').api.templates('birthday');
  assert.equal(JSON.stringify(result.data), JSON.stringify(data));
  assert.equal(result.revision, 2);
});

test('preview and selection type guard rejects mismatched, legacy and missing birthday templates', () => {
  const { load } = harness();
  const { templateMatchesType } = load('src/utils/templateCatalog.js');
  for (const template of [null, undefined, 1, {}, { id: 1 }, { id: 1, invitation_type: null }, { id: 1, invitation_type: 'wedding' }]) {
    assert.equal(templateMatchesType(template, 'birthday'), false);
  }
  assert.equal(templateMatchesType({ id: 6, invitation_type: 'birthday' }, 'birthday'), true);
  assert.equal(templateMatchesType({ id: 6, invitation_type: 'birthday' }, 'wedding'), false);
  assert.equal(templateMatchesType({ id: 1 }), true);
  assert.equal(templateMatchesType({ id: 1, invitation_type: 'unknown' }), false);
  assert.equal(templateMatchesType({ id: 1, invitation_type: 'unknown' }, 'unknown'), false);
});

test('empty, invalid and wrong-type catalogs show an error instead of a wedding fallback', async () => {
  for (const data of [null, {}, [], [{ id: 1, invitation_type: 'wedding' }], [null, { invitation_type: 'birthday' }]]) {
    const { load } = harness(() => ({ data }));
    await assert.rejects(load('src/services/api.js').api.templates('birthday'), /[Tt]emplate/);
  }
});

test('local launcher overrides both API and preview origin without editing release env files', () => {
  const batch = readFileSync(path.resolve(__dirname, '../../JALANKAN-DI-LAPTOP.bat'), 'utf8');
  assert.match(batch, /setlocal/i);
  assert.match(batch, /set "EXPO_PUBLIC_API_URL=http:\/\/127\.0\.0\.1:8015\/api"/);
  assert.match(batch, /set "APP_URL=http:\/\/127\.0\.0\.1:8015"/);
  assert.match(batch, /expo start --web --port 8082 --clear/);
  assert.doesNotMatch(batch, /(?:Set-Content|Out-File|>).*\.env/i);
});
