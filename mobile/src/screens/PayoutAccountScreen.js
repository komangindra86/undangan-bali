import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

const initialAccount = { bank_code: '', bank_name: '', account_number: '', account_holder_name: '', is_verified: false };

export default function PayoutAccountScreen({ navigation, route }) {
  const invitation = route.params?.invitation;
  const { token, expireSession } = useAuth();
  const [account, setAccount] = useState(initialAccount);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    api.payoutAccount(token)
      .then((response) => response.data && setAccount(response.data))
      .catch(handleError)
      .finally(() => setLoading(false));
  }, [token]);

  async function handleError(error) {
    if (error.status === 401) {
      await expireSession();
      navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
      return;
    }
    Alert.alert('Rekening Pencairan', error.message);
  }

  async function save() {
    if (!account.bank_code.trim() || !account.bank_name.trim() || !account.account_holder_name.trim() || !/^[0-9]{6,30}$/.test(account.account_number)) {
      Alert.alert('Data rekening belum lengkap', 'Isi bank, nama pemilik, dan nomor rekening berupa angka.');
      return;
    }
    setSaving(true);
    try {
      const response = await api.savePayoutAccount({
        bank_code: account.bank_code.trim().toUpperCase(),
        bank_name: account.bank_name.trim(),
        account_number: account.account_number.trim(),
        account_holder_name: account.account_holder_name.trim(),
      }, token);
      setAccount(response.data);
      Alert.alert('Rekening tersimpan', 'Admin akan memverifikasi rekening saat pencairan diproses.');
    } catch (error) {
      await handleError(error);
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return <SafeAreaView style={[commonStyles.screen, styles.loading]}><ActivityIndicator color={colors.gold} /></SafeAreaView>;
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Pencairan Gift</Text>
        <Text style={commonStyles.title}>Rekening Penerima</Text>
        <Text style={styles.help}>Dana gift akan ditransfer manual oleh admin ke rekening ini. Pastikan nama dan nomor rekening benar.</Text>
        {account.is_verified ? <Text style={styles.verified}>Rekening sudah diverifikasi admin.</Text> : null}
        <FormField label="Kode bank *" placeholder="BCA / BNI / MANDIRI" value={account.bank_code} onChangeText={(value) => setAccount({ ...account, bank_code: value })} />
        <FormField label="Nama bank *" placeholder="Bank Central Asia" value={account.bank_name} onChangeText={(value) => setAccount({ ...account, bank_name: value })} />
        <FormField label="Nomor rekening *" placeholder="Nomor rekening tanpa spasi" keyboardType="numeric" value={account.account_number} onChangeText={(value) => setAccount({ ...account, account_number: value.replace(/\D/g, '') })} />
        <FormField label="Nama pemilik rekening *" placeholder="Sesuai rekening bank" value={account.account_holder_name} onChangeText={(value) => setAccount({ ...account, account_holder_name: value })} />
        <PrimaryButton title="Simpan Rekening" onPress={save} loading={saving} style={styles.button} />
        {invitation && account.id ? <SecondaryButton title="Lanjut Ajukan Pencairan" onPress={() => navigation.navigate('RequestPayout', { invitation })} style={styles.secondary} /> : null}
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text>
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  loading: { alignItems: 'center', justifyContent: 'center' },
  help: { color: colors.muted, lineHeight: 21, marginBottom: spacing.lg, marginTop: spacing.md },
  verified: { backgroundColor: '#204232', borderRadius: 12, color: '#b5ebc7', marginBottom: spacing.lg, padding: spacing.md },
  button: { marginTop: spacing.lg },
  secondary: { marginTop: spacing.sm },
  back: { color: colors.goldLight, marginTop: spacing.lg, padding: spacing.sm, textAlign: 'center' },
});
