import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, StyleSheet, Switch, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton } from '../components/Buttons';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

const rupiah = (value) => `Rp${Number(value || 0).toLocaleString('id-ID')}`;

export default function DeleteAccountScreen({ navigation }) {
  const { token, forgetSession, expireSession } = useAuth();
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(true);
  const [confirm, setConfirm] = useState('');
  const [forfeit, setForfeit] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const hasBalance = (summary?.unpaid_balance || 0) > 0;
  const ready = confirm.trim() === 'HAPUS' && (!hasBalance || forfeit);

  useEffect(() => {
    api.accountDeletionSummary(token)
      .then((response) => setSummary(response.data))
      .catch(handleError)
      .finally(() => setLoading(false));
  }, [token]);

  async function handleError(error) {
    if (error.status === 401) {
      await expireSession();
      navigation.replace('Login', { returnTab: 'ProfileTab', sessionExpired: true });
      return;
    }
    Alert.alert('Hapus Akun', error.message);
  }

  function askDelete() {
    Alert.alert('Hapus akun sekarang?', 'Akun dan undangan Anda dihapus permanen dan tidak bisa dikembalikan.', [
      { text: 'Batal', style: 'cancel' },
      { text: 'Hapus Permanen', style: 'destructive', onPress: deleteAccount },
    ]);
  }

  async function deleteAccount() {
    setDeleting(true);
    try {
      await api.deleteAccount({ confirm: 'HAPUS', forfeit_balance: forfeit }, token);
      await forgetSession();
      Alert.alert('Akun dihapus', 'Terima kasih sudah memakai Undangan Bali Santih.');
      navigation.reset({ index: 0, routes: [{ name: 'MainTabs', params: { screen: 'ProfileTab' } }] });
    } catch (error) {
      await handleError(error);
    } finally {
      setDeleting(false);
    }
  }

  if (loading) {
    return (
      <SafeAreaView style={[commonStyles.screen, styles.center]}>
        <ActivityIndicator color={colors.gold} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Akun</Text>
        <Text style={commonStyles.title}>Hapus Akun</Text>
        <Text style={styles.body}>Yang akan dihapus permanen:</Text>
        <View style={styles.card}>
          <Text style={styles.item}>• Nama dan email akun, serta login di semua perangkat</Text>
          <Text style={styles.item}>• {summary?.invitations || 0} undangan beserta foto, musik, dan Moment-nya</Text>
          <Text style={styles.item}>• Komentar, reaksi, notifikasi, dan rekening pencairan tersimpan</Text>
        </View>
        {summary?.invitations_with_gifts ? (
          <Text style={styles.note}>Catatan tanda kasih dari tamu dan riwayat pencairan pada {summary.invitations_with_gifts} undangan tetap disimpan untuk pembukuan, tanpa nama atau email Anda.</Text>
        ) : null}
        {hasBalance ? (
          <View style={styles.warning}>
            <Text style={styles.warningTitle}>Saldo {rupiah(summary.unpaid_balance)} belum dicairkan</Text>
            <Text style={styles.warningText}>Saldo ini hangus bila akun dihapus sekarang. Batalkan dan ajukan pencairan dulu bila ingin menerimanya.</Text>
            <View style={styles.switchRow}>
              <Text style={styles.switchText}>Saya setuju saldo tersebut hangus</Text>
              <Switch value={forfeit} onValueChange={setForfeit} trackColor={{ true: colors.danger || '#c0392b', false: colors.border }} thumbColor={colors.text} />
            </View>
          </View>
        ) : null}
        <Text style={styles.label}>Ketik HAPUS untuk mengonfirmasi</Text>
        <TextInput
          accessibilityLabel="Ketik HAPUS untuk mengonfirmasi"
          autoCapitalize="characters"
          autoCorrect={false}
          onChangeText={setConfirm}
          placeholder="HAPUS"
          placeholderTextColor={colors.muted}
          style={styles.input}
          value={confirm}
        />
        <PrimaryButton title="Hapus Akun Permanen" onPress={askDelete} loading={deleting} disabled={!ready || deleting} style={styles.button} />
        <Text onPress={() => navigation.goBack()} style={styles.back}>Batal</Text>
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  center: { alignItems: 'center', justifyContent: 'center' },
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  body: { color: colors.muted, fontSize: 14, lineHeight: 21, marginTop: spacing.md },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, marginTop: spacing.sm, padding: spacing.md },
  item: { color: colors.text, fontSize: 14, lineHeight: 22 },
  note: { color: colors.muted, fontSize: 13, lineHeight: 20, marginTop: spacing.md },
  warning: { backgroundColor: '#3b1512', borderColor: '#8f3a32', borderRadius: 16, borderWidth: 1, marginTop: spacing.md, padding: spacing.md },
  warningTitle: { color: '#f6cfc9', fontSize: 15, fontWeight: '700' },
  warningText: { color: '#f0c4bd', fontSize: 13, lineHeight: 20, marginTop: spacing.xs },
  switchRow: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.sm },
  switchText: { color: colors.text, flex: 1, fontSize: 14, paddingRight: spacing.sm },
  label: { color: colors.text, fontSize: 14, fontWeight: '600', marginTop: spacing.lg },
  input: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 14, borderWidth: 1, color: colors.text, fontSize: 16, marginTop: spacing.sm, padding: spacing.md },
  button: { marginTop: spacing.lg },
  back: { color: colors.muted, marginTop: spacing.md, textAlign: 'center' },
});
