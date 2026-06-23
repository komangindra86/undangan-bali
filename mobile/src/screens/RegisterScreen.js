import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { commonStyles, spacing } from '../theme';
import { cleanText, firstError, validateEmail, validateName } from '../utils/validation';

export default function RegisterScreen({ navigation, route }) {
  const { register } = useAuth();
  const { publishDraft } = useDraft();
  const [form, setForm] = useState({ name: '', email: '', email_confirmation: '', password: '', password_confirmation: '' });
  const [loading, setLoading] = useState(false);
  const publishAfterAuth = route.params?.publishAfterAuth;
  const returnTo = route.params?.returnTo;

  async function submit() {
    const email = cleanText(form.email).toLowerCase();
    const emailConfirmation = cleanText(form.email_confirmation).toLowerCase();
    const error = firstError([
      validateName(form.name, 'Nama akun', { required: true, max: 80 }),
      validateEmail(email),
      validateEmail(emailConfirmation, 'Konfirmasi email'),
      email && emailConfirmation && email !== emailConfirmation ? 'Konfirmasi email harus sama dengan email.' : null,
      form.password.length < 8 ? 'Password minimal 8 karakter.' : null,
      form.password !== form.password_confirmation ? 'Konfirmasi password harus sama dengan password.' : null,
    ]);

    if (error) {
      Alert.alert('Periksa data daftar', error);
      return;
    }
    setLoading(true);
    try {
      const session = await register({
        ...form,
        name: cleanText(form.name),
        email,
        email_confirmation: emailConfirmation,
      });
      if (publishAfterAuth) {
        const publication = await publishDraft(session.token);
        navigation.replace('Share', { publication });
      } else if (returnTo === 'MyInvitations') {
        navigation.replace('MyInvitations');
      } else {
        navigation.popTo('Landing');
      }
    } catch (error) {
      Alert.alert('Registrasi gagal', error.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <SafeAreaView style={[commonStyles.screen, styles.safe]}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Daftar</Text>
        <Text style={commonStyles.title}>Buat akun Anda</Text>
        <Text style={[commonStyles.body, styles.body]}>Satu langkah lagi untuk menerbitkan undangan.</Text>
        <FormField label="Nama" maxLength={80} value={form.name} onChangeText={(value) => setForm({ ...form, name: value })} />
        <FormField label="Email" keyboardType="email-address" value={form.email} onChangeText={(value) => setForm({ ...form, email: value })} placeholder="nama@gmail.com" />
        <FormField label="Konfirmasi email" keyboardType="email-address" value={form.email_confirmation} onChangeText={(value) => setForm({ ...form, email_confirmation: value })} placeholder="nama@gmail.com" />
        <FormField label="Password" secureTextEntry value={form.password} onChangeText={(value) => setForm({ ...form, password: value })} helperText="Minimal 8 karakter." />
        <FormField label="Konfirmasi password" secureTextEntry value={form.password_confirmation} onChangeText={(value) => setForm({ ...form, password_confirmation: value })} />
        <PrimaryButton title={publishAfterAuth ? 'Daftar & Publish' : 'Daftar'} onPress={submit} loading={loading} style={styles.submit} />
        <SecondaryButton title="Sudah punya akun? Masuk" onPress={() => navigation.replace('Login', { publishAfterAuth, returnTo })} />
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
});
