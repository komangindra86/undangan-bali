const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function loadFeedSession() {
  const filename = path.resolve(__dirname, '../src/utils/feedSession.js');
  const module = { exports: {} };
  const code = transformSync(readFileSync(filename, 'utf8'), {
    babelrc: false, configFile: false, plugins: [require.resolve('@babel/plugin-transform-modules-commonjs')],
  }).code;
  vm.runInNewContext(code, { module, exports: module.exports }, { filename });
  return module.exports;
}

test('detail screen counts flow back into the cached feed', () => {
  const { feedSession, patchFeedItem } = loadFeedSession();
  feedSession.items = [{ id: 1, reactions: { like: 0, love: 0 }, comments_count: 0 }, { id: 2 }];

  patchFeedItem(1, { reactions: { like: 1, love: 0 }, comments_count: 3 });

  assert.equal(feedSession.version, 1);
  assert.deepEqual(feedSession.items[0].reactions, { like: 1, love: 0 });
  assert.equal(feedSession.items[0].comments_count, 3);
  assert.deepEqual(feedSession.items[1], { id: 2 });
});

test('patching a moment that is not in the feed leaves the feed untouched', () => {
  const { feedSession, patchFeedItem } = loadFeedSession();
  feedSession.items = [{ id: 1 }];

  patchFeedItem(99, { comments_count: 5 });

  assert.equal(feedSession.version, 0);
  assert.deepEqual(feedSession.items, [{ id: 1 }]);
});
