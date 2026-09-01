const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

const songs = [
  { id: 4, title: 'Wedding Piano', artist: 'Paul Yudin', categories: ['pernikahan'], preview_url: 'https://local.test/preview.mp3', audio_url: 'https://local.test/full.mp3' },
  { id: 5, title: 'Happy Birthday', artist: 'Absolute Sound', categories: ['ulang-tahun'], preview_url: 'https://local.test/birthday-preview.mp3' },
  { id: 1, title: 'Bali Romantis', audio_url: 'https://local.test/old.wav' },
];

function harness(options = {}) {
  const slots = [];
  let cursor = 0;
  let pendingEffects = [];
  let onBackground;
  const cleanups = new Map();
  const saved = [];
  const navigated = [];
  const calls = [];
  const status = { playing: false, isLoaded: true, currentTime: 0, duration: 30 };
  const player = {
    pause() { calls.push(['pause']); status.playing = false; },
    replace(source) { calls.push(['replace', source]); },
    play() { calls.push(['play']); status.playing = true; },
    seekTo: async (time) => { calls.push(['seekTo', time]); status.currentTime = time; },
  };
  const depsChanged = (old, deps) => !old || deps.some((value, index) => value !== old[index]);
  const hooks = {
    createElement: (type, props, ...children) => ({ type, props: { ...props, children } }),
    useState(initial) {
      const index = cursor++;
      if (!(index in slots)) slots[index] = typeof initial === 'function' ? initial() : initial;
      return [slots[index], (value) => { slots[index] = typeof value === 'function' ? value(slots[index]) : value; }];
    },
    useRef(value) { const index = cursor++; return slots[index] ||= { current: value }; },
    useCallback(fn, deps) {
      const index = cursor++;
      if (depsChanged(slots[index]?.deps, deps)) slots[index] = { deps, fn };
      return slots[index].fn;
    },
    useEffect(fn, deps) {
      const index = cursor++;
      if (depsChanged(slots[index], deps)) {
        slots[index] = deps;
        pendingEffects.push(() => { cleanups.get(index)?.(); cleanups.set(index, fn()); });
      }
    },
  };
  const mocks = {
    react: hooks,
    '@react-navigation/native': { useFocusEffect: (fn) => hooks.useEffect(fn, [fn]) },
    'expo-audio': { useAudioPlayer: () => player, useAudioPlayerStatus: () => status, setAudioModeAsync: async () => {} },
    'expo-document-picker': {},
    'react-native': {
      StyleSheet: { create: (x) => x }, Alert: { alert: (...args) => calls.push(['alert', ...args]) },
      AppState: { addEventListener: (event, cb) => { onBackground = cb; return { remove() {} }; } },
      ActivityIndicator: 'Spinner', Pressable: 'Pressable', Text: 'Text', View: 'View',
      Linking: { openURL: async (url) => calls.push(['link', url]) },
    },
    '../components/Buttons': { FooterActions: 'Footer', SecondaryButton: 'Button' },
    '../components/FormField': { default: 'Field', __esModule: true },
    '../components/WizardLayout': { default: 'Wizard', __esModule: true },
    '../context/DraftContext': { useDraft: () => ({ draft: { invitation_type: options.invitationType || 'wedding', music_data: options.music || { music_type: 'none', music_id: null } }, saveSection: options.save || (async (...args) => saved.push(args)) }) },
    '../services/api': { api: { siteUrl: 'https://local.test', musics: options.fetch || (async () => ({ data: songs })) } },
    '../services/localMedia': {},
    '../theme': { colors: {}, spacing: {} },
  };
  function load(relative) {
    const filename = path.resolve(__dirname, '..', relative);
    const module = { exports: {} };
    const code = transformSync(readFileSync(filename, 'utf8'), {
      babelrc: false, configFile: false,
      plugins: [require.resolve('@babel/plugin-transform-modules-commonjs'), [require.resolve('@babel/plugin-transform-react-jsx'), { pragma: 'React.createElement' }]],
    }).code;
    vm.runInNewContext(code, { module, exports: module.exports, React: hooks, setTimeout, clearTimeout,
      require: (id) => mocks[id] || load(path.relative(path.resolve(__dirname, '..'), path.resolve(path.dirname(filename), `${id}.js`))),
    });
    return module.exports;
  }
  const Screen = load('src/screens/MusicScreen.js').default;
  function render() {
    cursor = 0;
    const tree = Screen({ navigation: { navigate: (screen) => navigated.push(screen), goBack: () => navigated.push('back') } });
    const effects = pendingEffects;
    pendingEffects = [];
    effects.forEach((fn) => fn());
    return tree;
  }
  function find(tree, predicate) {
    if (!tree) return undefined;
    if (Array.isArray(tree)) return tree.map((x) => find(x, predicate)).find(Boolean);
    if (typeof tree !== 'object') return undefined;
    if (predicate(tree)) return tree;
    return find(tree.props?.children, predicate);
  }
  return { load, render, find, player, status, calls, saved, navigated,
    background: () => onBackground('background'), cleanup: () => [...cleanups.values()].reverse().forEach((fn) => fn?.()) };
}

test('catalog exposes only wedding and birthday categories without mutating tracks', () => {
  const h = harness();
  const { filterMusicCatalog, MUSIC_CATEGORIES, musicPreviewSource, musicSelectionError } = h.load('src/utils/musicCatalog.js');
  assert.deepEqual(Array.from(MUSIC_CATEGORIES, (category) => category.id), ['pernikahan', 'ulang-tahun']);
  assert.equal(filterMusicCatalog(songs, 'pernikahan')[0].id, 4);
  assert.equal(filterMusicCatalog(songs, 'ulang-tahun')[0].id, 5);
  assert.equal(songs.length, 3);
  assert.equal(musicPreviewSource(songs[0]), songs[0].preview_url);
  assert.equal(musicPreviewSource(songs[2]), songs[2].audio_url);
  assert.equal(musicSelectionError({ music_type: 'upload', music_file: { uri: 'file://music.mp3' } }, false).title, 'Konfirmasi izin musik');
  assert.equal(musicSelectionError({ music_type: 'upload', music_file: { uri: 'file://music.mp3' } }, true), null);
});

test('catalog loads metadata only; playing a preview does not select a song', async () => {
  const h = harness();
  h.render();
  await new Promise(setImmediate);
  const tree = h.render();
  assert.equal(h.calls.length, 0);
  await h.find(tree, (x) => x.props.title === 'Wedding Piano').props.onPreview();
  assert.ok(h.calls.some(([action, source]) => action === 'replace' && source === songs[0].preview_url));
  assert.equal(h.calls.some(([action, source]) => action === 'replace' && source === songs[0].audio_url), false);
  await h.render().props.footer.props.onNext();
  assert.equal(h.saved[0][1].music_type, 'none');
  assert.equal(h.navigated[0], 'GiftSetup');
  h.cleanup();
});

test('selection saves only existing music fields and survives category changes', async () => {
  const h = harness();
  h.render();
  await new Promise(setImmediate);
  h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onSelect();
  h.find(h.render(), (x) => x.props.accessibilityState?.selected === false && x.props.onPress).props.onPress();
  await h.render().props.footer.props.onNext();
  assert.equal(h.saved[0][0], 'music_data');
  assert.equal(JSON.stringify(h.saved[0][1]), JSON.stringify({ music_type: 'default', music_id: 4, music_file: null }));
  h.cleanup();
});

test('preview stops at 30 seconds, seeks when replayed, and unloads on background', async () => {
  const h = harness();
  h.render();
  await new Promise(setImmediate);
  await h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onPreview();
  h.status.currentTime = 30;
  h.render();
  assert.equal(h.status.playing, false);
  await h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onPreview();
  assert.ok(h.calls.some(([action, time]) => action === 'seekTo' && time === 0));
  h.background();
  assert.equal(h.status.playing, false);
  assert.equal(h.calls.at(-1)[0], 'replace');
  assert.equal(h.calls.at(-1)[1], null);
  h.cleanup();
});

test('pending replay cannot restart audio after Next', async () => {
  const h = harness();
  h.render();
  await new Promise(setImmediate);
  await h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onPreview();
  h.status.currentTime = 30;
  h.status.playing = false;
  let finishSeek;
  h.player.seekTo = () => new Promise((resolve) => { finishSeek = resolve; });
  const replay = h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onPreview();
  await h.render().props.footer.props.onNext();
  const plays = h.calls.filter(([action]) => action === 'play').length;
  finishSeek();
  await replay;
  assert.equal(h.calls.filter(([action]) => action === 'play').length, plays);
  h.cleanup();
});

test('catalog failure retains a resumed draft and presents retry without deleting its selection', async () => {
  const h = harness({ music: { music_type: 'default', music_id: 42 }, fetch: async () => { throw new Error('offline'); } });
  h.render();
  await new Promise(setImmediate);
  const tree = h.render();
  assert.ok(h.find(tree, (x) => x.props.title === 'Coba lagi'));
  await tree.props.footer.props.onNext();
  assert.equal(h.saved[0][1].music_id, 42);
  h.cleanup();
});

test('changing category stops playback before its credit card leaves the results', async () => {
  const h = harness();
  h.render();
  await new Promise(setImmediate);
  await h.find(h.render(), (x) => x.props.title === 'Wedding Piano').props.onPreview();
  assert.equal(h.status.playing, true);
  h.find(h.render(), (x) => x.props.accessibilityState?.selected === false && x.props.onPress).props.onPress();
  assert.equal(h.status.playing, false);
  h.cleanup();
});

test('birthday invitations open the birthday catalog first and search input is absent', async () => {
  const h = harness({ invitationType: 'birthday' });
  h.render();
  await new Promise(setImmediate);
  const tree = h.render();
  assert.ok(h.find(tree, (x) => x.props.title === 'Happy Birthday'));
  assert.equal(h.find(tree, (x) => x.props.label === 'Cari musik'), undefined);
  h.cleanup();
});

test('custom upload opens copyright terms and cannot continue until checkbox is accepted', async () => {
  const h = harness({ music: { music_type: 'upload', music_id: null, music_file: { uri: 'file://music.mp3', fileName: 'music.mp3' } } });
  h.render();
  await new Promise(setImmediate);
  let tree = h.render();
  const terms = h.find(tree, (x) => x.props.accessibilityLabel === 'Buka Ketentuan Penggunaan Audio dan Musik Latar');
  await terms.props.onPress();
  assert.ok(h.calls.some(([action, url]) => action === 'link' && url === 'https://local.test/audio-copyright-terms'));

  await tree.props.footer.props.onNext();
  assert.equal(h.saved.length, 0);
  assert.ok(h.calls.some(([action, title]) => action === 'alert' && title === 'Konfirmasi izin musik'));

  tree = h.render();
  h.find(tree, (x) => x.props.accessibilityRole === 'checkbox').props.onPress();
  await h.render().props.footer.props.onNext();
  assert.equal(h.saved[0][1].music_rights_confirmed, true);
  assert.equal(h.navigated[0], 'GiftSetup');
  h.cleanup();
});

test('invalid catalog response shows retry instead of crashing the screen', async () => {
  const h = harness({ fetch: async () => ({ data: {} }) });
  h.render();
  await new Promise(setImmediate);
  assert.ok(h.find(h.render(), (x) => x.props.title === 'Coba lagi'));
  h.cleanup();
});

test('failed local save stays on the music screen and offers a clear error', async () => {
  const h = harness({ save: async () => { throw new Error('Storage unavailable'); } });
  h.render();
  await new Promise(setImmediate);
  await h.render().props.footer.props.onNext();
  assert.equal(h.navigated.length, 0);
  assert.ok(h.calls.some(([action, title]) => action === 'alert' && title === 'Pilihan belum tersimpan'));
  h.cleanup();
});
