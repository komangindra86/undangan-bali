import { useCallback, useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import GoogleAuthButton from '../components/GoogleAuthButton';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { colors, commonStyles, spacing } from '../theme';
import { cleanText, validateEmail } from '../utils/validation';

export default function LoginScreen({ navigation, route }) {
  const { login, googleLogin } = useAuth();
  const { publishDraft } = useDraft();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const publishAfterAuth = route.params?.publishAfterAuth;
  const returnTo = route.params?.returnTo;
  const sessionExpired = route.params?.sessionExpired;

  const continueAfterAuth = useCallback(
    async (session) => {
      if (publishAfterAuth) {
        const publication = await publishDraft(session.token);
        navigation.replace('Share', { publication });
      } else if (returnTo === 'MyInvitations') {
        navigation.replace('MyInvitations');
      } else {
        navigation.popTo('Landing');
      }
    },
    [navigation, publishAfterAuth, publishDraft, returnTo],
  );

  async function submit() {
    const normalizedEmail = cleanText(email).toLowerCase();
    const emailError = validateEmail(normalizedEmail);
    if (emailError || !password) {
      Alert.alert('Lengkapi login', emailError || 'Password wajib diisi.');
      return;
    }
    setLoading(true);
    try {
      const session = await login({ email: normalizedEmail, password });
      await continueAfterAuth(session);
    } catch (error) {
      Alert.alert('Login gagal', error.message);
    } finally {
      setLoading(false);
    }
  }

  const submitGoogle = useCallback(
    async (idToken) => {
      try {
        const session = await googleLogin(idToken);
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
        <Text style={commonStyles.title}>Selamat datang kembali</Text>
        <Text style={[commonStyles.body, styles.body]}>
          {sessionExpired
            ? 'Sesi Anda sudah berakhir. Masuk kembali untuk melihat undangan yang tersimpan.'
            : 'Masuk untuk melihat, menyimpan, dan membagikan undangan Anda.'}
        </Text>
        <FormField label="Email" keyboardType="email-address" value={email} onChangeText={setEmail} placeholder="email@contoh.com" />
        <FormField label="Password" secureTextEntry value={password} onChangeText={setPassword} placeholder="Minimal 8 karakter" />
        <PrimaryButton title={publishAfterAuth ? 'Masuk & Publish' : 'Masuk'} onPress={submit} loading={loading} style={styles.submit} />
        <Text style={styles.divider}>atau</Text>
        <GoogleAuthButton title="Masuk dengan Google" onToken={submitGoogle} disabled={loading} style={styles.google} />
        <SecondaryButton
          title="Belum punya akun? Daftar"
          onPress={() => navigation.replace('Register', { publishAfterAuth, returnTo })}
        />
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
  submit: {
    marginTop: spacing.sm,
    marginBottom: spacing.sm,
  },
  divider: {
    color: colors.muted,
    textAlign: 'center',
    marginVertical: spacing.sm,
  },
  google: {
    marginBottom: spacing.sm,
  },
});
