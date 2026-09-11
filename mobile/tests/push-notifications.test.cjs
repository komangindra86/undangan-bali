const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');
const { transformSync } = require('@babel/core');

function loadUtility() {
  const filename = path.resolve(__dirname, '..', 'src/utils/pushNotifications.js');
  const module = { exports: {} };
  const code = transformSync(readFileSync(filename, 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: [require.resolve('@babel/plugin-transform-modules-commonjs')],
  }).code;
  vm.runInNewContext(code, { module, exports: module.exports }, { filename });

  return module.exports;
}

test('Expo Android native token is accepted for FCM registration', () => {
  const { isAndroidDevicePushToken } = loadUtility();

  assert.equal(isAndroidDevicePushToken({ type: 'android', data: 'native-fcm-token' }), true);
  assert.equal(isAndroidDevicePushToken({ type: 'fcm', data: 'legacy-fcm-token' }), true);
  assert.equal(isAndroidDevicePushToken({ type: 'expo', data: 'ExponentPushToken[value]' }), false);
  assert.equal(isAndroidDevicePushToken({ type: 'android', data: '' }), false);
});
