const assert = require('node:assert/strict');
const { existsSync, readFileSync } = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');

function source(file) {
  return readFileSync(path.resolve(__dirname, '..', file), 'utf8');
}

test('accounts are made and opened with Google only', () => {
  const login = source('src/screens/LoginScreen.js');
  const api = source('src/services/api.js');

  assert.match(login, /GoogleAuthButton/);
  assert.doesNotMatch(login, /secureTextEntry|FormField|password/i);
  assert.doesNotMatch(api, /request\('\/(register|login)'/);
  assert.equal(existsSync(path.resolve(__dirname, '..', 'src/screens/RegisterScreen.js')), false);
  for (const file of ['src/navigation/AppNavigator.js', 'src/screens/AuthGateScreen.js', 'src/screens/MyInvitationsScreen.js', 'src/screens/NotificationsScreen.js', 'src/screens/ProfileScreen.js']) {
    assert.doesNotMatch(source(file), /'Register'|"Register"/, file);
  }
});

test('the account can be deleted from the app and its invitations one by one', () => {
  const profile = source('src/screens/ProfileScreen.js');
  const screen = source('src/screens/DeleteAccountScreen.js');
  const invitations = source('src/screens/MyInvitationsScreen.js');

  assert.match(profile, /openStack\('DeleteAccount'\)/);
  assert.match(source('src/navigation/AppNavigator.js'), /name="DeleteAccount"/);
  assert.match(screen, /api\.accountDeletionSummary\(/);
  assert.match(screen, /api\.deleteAccount\(/);
  assert.match(screen, /HAPUS/);
  assert.match(screen, /forfeit_balance/);
  assert.match(screen, /forgetSession\(\)/);
  assert.match(invitations, /api\.deleteInvitation\(item\.id, token\)/);
});
