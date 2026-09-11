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

test('mobile payout percentage has one explicit source of truth', () => {
  const constants = source('src/constants/invitation.js');
  const setup = source('src/screens/GiftSetupScreen.js');
  const settings = source('src/screens/WeddingGiftSettingScreen.js');

  assert.match(constants, /GIFT_PAYOUT_FEE_PERCENT = 1/);
  assert.match(setup, /GIFT_PAYOUT_FEE_PERCENT/);
  assert.match(settings, /GIFT_PAYOUT_FEE_PERCENT/);
});
