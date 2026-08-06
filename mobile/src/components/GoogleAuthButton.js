import * as AuthSession from 'expo-auth-session';
import * as WebBrowser from 'expo-web-browser';
import { useState } from 'react';
import { ActivityIndicator, Alert, Pressable, StyleSheet, Text } from 'react-native';
import { api } from '../services/api';
import { colors, spacing } from '../theme';

WebBrowser.maybeCompleteAuthSession();

const redirectUri = AuthSession.makeRedirectUri({
  scheme: 'undanganbali',
  path: 'auth/google',
});

const errorMessages = {
  account_not_allowed: 'Akun Google ini tidak dapat digunakan untuk masuk ke aplikasi.',
  invalid_state: 'Sesi login Google tidak valid. Silakan coba lagi.',
  server_error: 'Server belum dapat menyelesaikan login Google. Silakan coba lagi.',
  token_exchange_failed: 'Google belum dapat memverifikasi login. Silakan coba lagi.',
};

export default function GoogleAuthButton({ title = 'Lanjutkan dengan Google', onCode, disabled = false, style }) {
  const [loading, setLoading] = useState(false);

  async function startGoogleLogin() {
    setLoading(true);

    try {
      const result = await WebBrowser.openAuthSessionAsync(
        `${api.siteUrl}/auth/google/mobile`,
        redirectUri,
      );

      if (result.type === 'cancel' || result.type === 'dismiss') {
        return;
      }

      if (result.type !== 'success' || !result.url) {
        throw new Error('Login Google belum selesai. Silakan coba lagi.');
      }

      const callback = new URL(result.url);
      const error = callback.searchParams.get('error');
      const code = callback.searchParams.get('code');

      if (error === 'cancelled') {
        return;
      }

      if (error) {
        throw new Error(errorMessages[error] || 'Login Google belum berhasil.');
      }

      if (!code) {
        throw new Error('Kode login Google tidak diterima. Silakan coba lagi.');
      }

      await onCode(code);
    } catch (error) {
      Alert.alert('Login Google gagal', error.message || 'Silakan coba lagi.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <Pressable
      disabled={disabled || loading}
      onPress={startGoogleLogin}
      style={({ pressed }) => [styles.button, pressed && styles.pressed, (disabled || loading) && styles.disabled, style]}
    >
      {loading ? <ActivityIndicator color={colors.text} /> : <Text style={styles.text}>{title}</Text>}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    minHeight: 54,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    borderColor: colors.text,
    borderWidth: 1,
    backgroundColor: colors.white,
  },
  text: {
    color: '#171717',
    fontSize: 16,
    fontWeight: '700',
  },
  pressed: {
    opacity: 0.78,
  },
  disabled: {
    opacity: 0.55,
  },
});
