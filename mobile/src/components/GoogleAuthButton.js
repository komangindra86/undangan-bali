import * as Google from 'expo-auth-session/providers/google';
import * as WebBrowser from 'expo-web-browser';
import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, Platform, Pressable, StyleSheet, Text } from 'react-native';
import { colors, spacing } from '../theme';

WebBrowser.maybeCompleteAuthSession();

const googleConfig = {
  androidClientId: process.env.EXPO_PUBLIC_GOOGLE_ANDROID_CLIENT_ID,
  iosClientId: process.env.EXPO_PUBLIC_GOOGLE_IOS_CLIENT_ID,
  webClientId: process.env.EXPO_PUBLIC_GOOGLE_WEB_CLIENT_ID,
  expoClientId: process.env.EXPO_PUBLIC_GOOGLE_EXPO_CLIENT_ID,
  scopes: ['openid', 'profile', 'email'],
};

const hasGoogleClient = Boolean(
  googleConfig.androidClientId ||
    googleConfig.iosClientId ||
    googleConfig.webClientId ||
    googleConfig.expoClientId,
);

export default function GoogleAuthButton({ title = 'Lanjutkan dengan Google', onToken, disabled = false, style }) {
  const platformClientId =
    Platform.OS === 'android'
      ? googleConfig.androidClientId
      : Platform.OS === 'ios'
        ? googleConfig.iosClientId
        : googleConfig.webClientId || googleConfig.expoClientId;

  if (!platformClientId) {
    return <DisabledGoogleAuthButton title={title} disabled={disabled} style={style} />;
  }

  return <ReadyGoogleAuthButton title={title} onToken={onToken} disabled={disabled} style={style} />;
}

function ReadyGoogleAuthButton({ title, onToken, disabled, style }) {
  const [request, response, promptAsync] = Google.useAuthRequest(googleConfig);
  const [loading, setLoading] = useState(false);
  const handledResponse = useRef(null);

  useEffect(() => {
    async function handleResponse() {
      if (response?.type !== 'success') {
        if (response?.type === 'error') {
          Alert.alert('Google gagal', response.error?.message || 'Login Google belum berhasil.');
        }
        setLoading(false);
        return;
      }

      const idToken = response.authentication?.idToken || response.params?.id_token;
      if (!idToken) {
        setLoading(false);
        Alert.alert('Google gagal', 'Token Google tidak diterima. Silakan coba lagi.');
        return;
      }

      if (handledResponse.current === idToken) {
        return;
      }

      handledResponse.current = idToken;

      try {
        await onToken(idToken);
      } finally {
        setLoading(false);
      }
    }

    handleResponse();
  }, [response, onToken]);

  async function startGoogleLogin() {
    if (!hasGoogleClient) {
      Alert.alert(
        'Google Login belum aktif',
        'Google Client ID belum dikonfigurasi. Isi EXPO_PUBLIC_GOOGLE_ANDROID_CLIENT_ID dan GOOGLE_CLIENT_IDS di backend.',
      );
      return;
    }

    if (!request) {
      Alert.alert('Google belum siap', 'Silakan coba lagi beberapa detik.');
      return;
    }

    setLoading(true);
    const result = await promptAsync();
    if (result.type !== 'success') {
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

function DisabledGoogleAuthButton({ title, disabled, style }) {
  function showMissingConfig() {
    const envName =
      Platform.OS === 'android'
        ? 'EXPO_PUBLIC_GOOGLE_ANDROID_CLIENT_ID'
        : Platform.OS === 'ios'
          ? 'EXPO_PUBLIC_GOOGLE_IOS_CLIENT_ID'
          : 'EXPO_PUBLIC_GOOGLE_WEB_CLIENT_ID';

    Alert.alert(
      'Google Login belum aktif',
      `Client ID untuk platform ini belum dikonfigurasi. Isi ${envName} lalu jalankan ulang aplikasi.`,
    );
  }

  return (
    <Pressable
      disabled={disabled}
      onPress={showMissingConfig}
      style={({ pressed }) => [styles.button, pressed && styles.pressed, disabled && styles.disabled, style]}
    >
      <Text style={styles.text}>{title}</Text>
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
