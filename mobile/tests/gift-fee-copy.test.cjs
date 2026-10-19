const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');

function source(file) {
  return readFileSync(path.resolve(__dirname, '..', file), 'utf8');
}

test('gift setup and settings explain the payout fee consistently', () => {
  const setup = source('src/screens/GiftSetupScreen.js');
  const settings = source('src/screens/WeddingGiftSettingScreen.js');
  const combined = `${setup}\n${settings}`;

  assert.match(setup, /Biaya pencairan platform/);
  assert.match(settings, /Biaya pencairan platform/);
  assert.match(combined, /tanpa biaya tambahan/);
  assert.doesNotMatch(combined, /Rp2\.000|Rp100\.000 ke atas|2% untuk/);
});

test('the payout fee comes from the server, never a percentage written into the app', () => {
  const screens = ['GiftSetupScreen', 'WeddingGiftSettingScreen', 'WeddingGiftDashboardScreen', 'PayoutHistoryScreen', 'RequestPayoutScreen']
    .map((name) => source(`src/screens/${name}.js`))
    .join('\n');
  const fee = source('src/utils/giftFee.js');

  assert.doesNotMatch(screens, /(fee|biaya)[^\n]{0,24}\d+%|\}%/i);
  assert.match(fee, /api\.giftFee\(\)/);
  assert.match(source('src/screens/GiftSetupScreen.js'), /useGiftFee\(\)/);
  assert.match(source('src/screens/WeddingGiftSettingScreen.js'), /useGiftFee\(\)/);
  assert.match(source('src/screens/RequestPayoutScreen.js'), /api\.payoutQuote\(/);
  assert.doesNotMatch(source('src/constants/invitation.js'), /GIFT_PAYOUT_FEE_PERCENT/);
});
