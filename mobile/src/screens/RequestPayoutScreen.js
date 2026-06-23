import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function RequestPayoutScreen({ navigation, route }) {
  const invitation = route.params?.invitation;
  const { token, expireSession } = useAuth();
  const [dashboard, setDashboard] = useState(null);
  const [amount, setAmount] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);

  useEffect(() => {
    api.weddingGifts(invitation.id, token)
      .then((response) => setDashboard(response))
      .catch(handleError)
      .finally(() => setLoading(false));
  }, [invitation.id, token]);

  async function handleError(error) {
    if (error.status === 401) {
      await expireSession();
      navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
      return;
    }
    Alert.alert('Pencairan Gift', error.message);
  }

  async function submit() {
    const value = Number(amount);
    const available = dashboard.summary.available_balance;
    const minimum = dashboard.summary.payout_minimum_amount;
    if (!dashboard.payout_account) {
      Alert.alert('Rekening belum diisi', 'Simpan rekening pencairan terlebih dahulu.');
      return;
    }
    if (!Number.isInteger(value) || value < minimum || value > available) {
      Alert.alert('Nominal tidak valid', `Pencairan minimal ${rupiah(minimum)} dan tidak boleh melebihi saldo tersedia.`);
      return;
    }
    setSending(true);
    try {
      await api.requestPayout(invitation.id, {
        payout_account_id: dashboard.payout_account.id,
        amount: value,
      }, token);
      Alert.alert('Pengajuan dikirim', 'Admin akan memverifikasi rekening dan mentransfer dana secara manual.');
      navigation.replace('PayoutHistory', { invitation });
    } catch (error) {
      await handleError(error);
    } finally {
      setSending(false);
    }
  }

  if (loading || !dashboard) {
    return <SafeAreaView style={[commonStyles.screen, styles.loading]}><ActivityIndicator color={colors.gold} /></SafeAreaView>;
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Klaim Wedding Gift</Text>
        <Text style={commonStyles.title}>Ajukan Pencairan</Text>
        <View style={styles.balance}>
          <Text style={styles.label}>Saldo tersedia</Text>
          <Text style={styles.value}>{rupiah(dashboard.summary.available_balance)}</Text>
          <Text style={styles.note}>Minimum pencairan {rupiah(dashboard.summary.payout_minimum_amount)}</Text>
        </View>
        {dashboard.payout_account ? (
          <View style={styles.account}>
            <Text style={styles.label}>Transfer ke</Text>
            <Text style={styles.accountText}>{dashboard.payout_account.bank_name} - {dashboard.payout_account.account_number}</Text>
            <Text style={styles.note}>{dashboard.payout_account.account_holder_name}</Text>
          </View>
        ) : (
          <View style={styles.warning}>
            <Text style={styles.note}>Anda belum menambahkan rekening pencairan.</Text>
            <SecondaryButton title="Tambah Rekening" onPress={() => navigation.navigate('PayoutAccount', { invitation })} style={styles.addAccount} />
          </View>
        )}
        <FormField label="Nominal yang dicairkan *" placeholder="50000" keyboardType="numeric" value={amount} onChangeText={(value) => setAmount(value.replace(/\D/g, ''))} />
        <Text style={styles.disclaimer}>Admin akan memeriksa pengajuan dan mentransfer dana ke rekening tersimpan. Nominal yang diajukan tidak dapat diajukan ulang selama diproses.</Text>
        <PrimaryButton title="Kirim Pengajuan" onPress={submit} loading={sending} disabled={!dashboard.payout_account || !dashboard.summary.available_balance} style={styles.button} />
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text>
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

function rupiah(value) {
  return `Rp${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  loading: { alignItems: 'center', justifyContent: 'center' },
  balance: { backgroundColor: colors.surface, borderColor: colors.gold, borderRadius: 18, borderWidth: 1, marginBottom: spacing.md, marginTop: spacing.lg, padding: spacing.md },
  label: { color: colors.muted, fontSize: 13 },
  value: { color: colors.goldLight, fontSize: 30, fontWeight: '700', marginVertical: spacing.xs },
  note: { color: colors.muted, fontSize: 13, lineHeight: 20 },
  account: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, marginBottom: spacing.lg, padding: spacing.md },
  accountText: { color: colors.text, fontSize: 16, fontWeight: '700', marginBottom: spacing.xs, marginTop: spacing.xs },
  warning: { marginBottom: spacing.lg },
  addAccount: { marginTop: spacing.sm },
  disclaimer: { color: colors.muted, fontSize: 13, lineHeight: 20, marginTop: spacing.sm },
  button: { marginTop: spacing.lg },
  back: { color: colors.goldLight, marginTop: spacing.lg, padding: spacing.sm, textAlign: 'center' },
});
