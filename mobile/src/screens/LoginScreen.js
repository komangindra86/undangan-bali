import { useCallback } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import GoogleAuthButton from '../components/GoogleAuthButton';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { colors, commonStyles, spacing } from '../theme';

export default function LoginScreen({ navigation, route }) {
  const { googleLogin } = useAuth();
  const { publishDraft } = useDraft();
  const publishAfterAuth = route.params?.publishAfterAuth;
  const returnTo = route.params?.returnTo;
  const returnTab = route.params?.returnTab;
  const sessionExpired = route.params?.sessionExpired;

  const continueAfterAuth = useCallback(
    async (session) => {
      if (publishAfterAuth) {
        const publication = await publishDraft(session.token);
        navigation.replace('Share', { publication });
      } else if (returnTab || returnTo === 'MyInvitations') {
        navigation.reset({
          index: 0,
          routes: [{ name: 'MainTabs', params: { screen: returnTab || 'InvitationsTab' } }],
        });
      } else if (returnTo) {
        navigation.replace(returnTo, route.params?.returnParams);
      } else {
        navigation.popTo('MainTabs');
      }
    },
    [navigation, publishAfterAuth, publishDraft, returnTab, returnTo, route.params?.returnParams],
  );

  const submitGoogle = useCallback(
    async (exchangeCode) => {
      try {
        const session = await googleLogin(exchangeCode);
        await continueAfterAuth(session);
      } catch (error) {
        Alert.alert('Login Google gagal', error.message);
      }
    },
    [continueAfterAuth, googleLogin],
  );

  return (
    <SafeAreaView style={[commonStyles.screen, styles.safe]}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Masuk</Text>
        <Text style={commonStyles.title}>Selamat datang</Text>
        <Text style={[commonStyles.body, styles.body]}>
          {sessionExpired
            ? 'Sesi Anda sudah berakhir. Masuk kembali dengan Google untuk melihat undangan yang tersimpan.'
            : 'Masuk atau daftar dengan akun Google untuk menyimpan, membagikan, dan mengelola undangan Anda.'}
        </Text>
        <GoogleAuthButton title={publishAfterAuth ? 'Masuk dengan Google & Publish' : 'Masuk dengan Google'} onCode={submitGoogle} style={styles.google} />
        <Text style={styles.note}>Akun baru dibuat otomatis saat pertama kali masuk. Akun lama yang terdaftar dengan email Gmail yang sama tetap bisa dipakai.</Text>
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    paddingHorizontal: spacing.lg,
  },
  content: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingVertical: spacing.xl,
  },
  body: {
    marginTop: spacing.sm,
    marginBottom: spacing.xl,
  },
  google: {
    marginBottom: spacing.md,
  },
  note: {
    color: colors.muted,
    fontSize: 13,
    lineHeight: 20,
    textAlign: 'center',
  },
});
