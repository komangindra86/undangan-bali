import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function WeddingGiftDashboardScreen({ navigation, route }) {
  const invitation = route.params?.invitation;
  const { token, expireSession } = useAuth();
  const [summary, setSummary] = useState({ total_gift_paid: 0, total_service_fee: 0, giver_count: 0, available_balance: 0, payout_pending: 0, paid_out: 0 });
  const [gifts, setGifts] = useState([]);
  const [loading, setLoading] = useState(true);

  async function load() {
    setLoading(true);
    try {
      const response = await api.weddingGifts(invitation.id, token);
      setSummary(response.summary);
      setGifts(response.data);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
        return;
      }
      Alert.alert('Dashboard Gift', error.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, [invitation.id, token]);

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Monitoring</Text>
        <Text style={commonStyles.title}>Dashboard Gift</Text>
        <Text style={styles.subtitle}>{invitation.groom_nickname} & {invitation.bride_nickname}</Text>
        <View style={styles.summary}>
          <Stat label="Gift diterima" value={rupiah(summary.total_gift_paid)} wide />
          <Stat label="Saldo tersedia" value={rupiah(summary.available_balance)} wide accent />
          <Stat label="Sedang diproses" value={rupiah(summary.payout_pending)} />
          <Stat label="Sudah dicairkan" value={rupiah(summary.paid_out)} />
          <Stat label="Jumlah pemberi" value={String(summary.giver_count)} />
          <Stat label="Fee aplikasi" value={rupiah(summary.total_service_fee)} />
        </View>
        <Text style={styles.security}>Nominal diterima hanya dihitung dari pembayaran berstatus paid yang dikonfirmasi backend Midtrans.</Text>
        <PrimaryButton title="Muat Ulang Data" onPress={load} loading={loading} style={styles.refresh} />
        <PrimaryButton
          title="Ajukan Pencairan"
          onPress={() => navigation.navigate('RequestPayout', { invitation })}
          disabled={!summary.available_balance}
          style={styles.setting}
        />
        <SecondaryButton title="Kelola Rekening" onPress={() => navigation.navigate('PayoutAccount', { invitation })} style={styles.setting} />
        <SecondaryButton title="Riwayat Pencairan" onPress={() => navigation.navigate('PayoutHistory', { invitation })} style={styles.setting} />
        <SecondaryButton title="Atur Wedding Gift" onPress={() => navigation.navigate('WeddingGiftSetting', { invitation })} style={styles.setting} />
        <Text style={styles.listTitle}>Riwayat Gift</Text>
        {loading ? <ActivityIndicator color={colors.gold} style={styles.spinner} /> : null}
        {!loading && gifts.length === 0 ? <Text style={styles.empty}>Belum ada transaksi Wedding Gift.</Text> : null}
        {gifts.map((gift) => (
          <View style={styles.card} key={gift.id}>
            <View style={styles.heading}>
              <Text style={styles.name}>{gift.guest_name}</Text>
              <Text style={[styles.badge, gift.transaction_status === 'paid' && styles.paid]}>{gift.transaction_status}</Text>
            </View>
            <Text style={styles.amount}>{rupiah(gift.gift_amount)}</Text>
            <Text style={styles.detail}>Fee {rupiah(gift.service_fee)} | Total dibayar {rupiah(gift.total_amount)}</Text>
            {gift.message ? <Text style={styles.message}>"{gift.message}"</Text> : null}
            {gift.paid_at ? <Text style={styles.date}>Dibayar: {new Date(gift.paid_at).toLocaleString('id-ID')}</Text> : null}
          </View>
        ))}
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

function Stat({ label, value, wide = false, accent = false }) {
  return (
    <View style={[styles.stat, wide && styles.statWide, accent && styles.statAccent]}>
      <Text style={styles.statLabel}>{label}</Text>
      <Text style={styles.statValue}>{value}</Text>
    </View>
  );
}

function rupiah(value) {
  return `Rp${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  subtitle: { color: colors.muted, marginBottom: spacing.lg, marginTop: spacing.xs },
  summary: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  stat: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, flex: 1, minWidth: '45%', padding: spacing.md },
  statWide: { flexBasis: '100%' },
  statAccent: { borderColor: colors.gold },
  statLabel: { color: colors.muted, fontSize: 12, marginBottom: spacing.xs },
  statValue: { color: colors.goldLight, fontSize: 22, fontWeight: '700' },
  security: { color: colors.muted, fontSize: 13, lineHeight: 20, marginTop: spacing.md },
  refresh: { marginTop: spacing.lg },
  setting: { marginTop: spacing.sm },
  listTitle: { color: colors.text, fontSize: 20, fontWeight: '700', marginBottom: spacing.md, marginTop: spacing.xl },
  spinner: { marginTop: spacing.md },
  empty: { color: colors.muted },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 17, borderWidth: 1, marginBottom: spacing.sm, padding: spacing.md },
  heading: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between' },
  name: { color: colors.text, flex: 1, fontSize: 16, fontWeight: '700' },
  badge: { backgroundColor: colors.surfaceAlt, borderRadius: 20, color: colors.goldLight, fontSize: 11, fontWeight: '700', overflow: 'hidden', paddingHorizontal: 10, paddingVertical: 5, textTransform: 'uppercase' },
  paid: { backgroundColor: '#204232', color: '#b5ebc7' },
  amount: { color: colors.goldLight, fontSize: 21, fontWeight: '700', marginTop: spacing.sm },
  detail: { color: colors.muted, fontSize: 13, marginTop: spacing.xs },
  message: { color: colors.text, fontStyle: 'italic', lineHeight: 20, marginTop: spacing.sm },
  date: { color: colors.success, fontSize: 12, marginTop: spacing.sm },
  back: { color: colors.goldLight, marginTop: spacing.lg, padding: spacing.sm, textAlign: 'center' },
});
