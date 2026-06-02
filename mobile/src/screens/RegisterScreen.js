import { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { commonStyles, spacing } from '../theme';

export default function RegisterScreen({ navigation, route }) {
  const { register } = useAuth();
  const { publishDraft } = useDraft();
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
  const [loading, setLoading] = useState(false);
  const publishAfterAuth = route.params?.publishAfterAuth;
  const returnTo = route.params?.returnTo;

  async function submit() {
    if (!form.name || !form.email || !form.password || form.password !== form.password_confirmation) {
      Alert.alert('Periksa data', 'Isi seluruh data dan pastikan konfirmasi password sesuai.');
      return;
    }
    setLoading(true);
    try {
      const session = await register(form);
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
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <Text style={commonStyles.eyebrow}>Daftar</Text>
        <Text style={commonStyles.title}>Buat akun Anda</Text>
        <Text style={[commonStyles.body, styles.body]}>Satu langkah lagi untuk menerbitkan undangan.</Text>
        <FormField label="Nama" value={form.name} onChangeText={(value) => setForm({ ...form, name: value })} />
        <FormField label="Email" keyboardType="email-address" value={form.email} onChangeText={(value) => setForm({ ...form, email: value })} />
        <FormField label="Password" secureTextEntry value={form.password} onChangeText={(value) => setForm({ ...form, password: value })} />
        <FormField label="Konfirmasi password" secureTextEntry value={form.password_confirmation} onChangeText={(value) => setForm({ ...form, password_confirmation: value })} />
        <PrimaryButton title={publishAfterAuth ? 'Daftar & Publish' : 'Daftar'} onPress={submit} loading={loading} style={styles.submit} />
        <SecondaryButton title="Sudah punya akun? Masuk" onPress={() => navigation.replace('Login', { publishAfterAuth, returnTo })} />
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
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
