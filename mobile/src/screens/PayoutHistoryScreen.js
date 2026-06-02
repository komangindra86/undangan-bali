import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function PayoutHistoryScreen({ navigation, route }) {
  const invitation = route.params?.invitation;
  const { token, expireSession } = useAuth();
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.payoutRequests(invitation.id, token)
      .then((response) => setRequests(response.data))
      .catch(async (error) => {
        if (error.status === 401) {
          await expireSession();
          navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
          return;
        }
        Alert.alert('Riwayat Pencairan', error.message);
      })
      .finally(() => setLoading(false));
  }, [invitation.id, token]);

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Wedding Gift</Text>
        <Text style={commonStyles.title}>Riwayat Pencairan</Text>
        <SecondaryButton title="Kembali ke Dashboard" onPress={() => navigation.navigate('WeddingGiftDashboard', { invitation })} style={styles.button} />
        {loading ? <ActivityIndicator color={colors.gold} style={styles.loading} /> : null}
        {!loading && !requests.length ? <Text style={styles.empty}>Belum ada pengajuan pencairan.</Text> : null}
        {requests.map((request) => (
          <View style={styles.card} key={request.id}>
            <View style={styles.row}>
              <Text style={styles.amount}>{rupiah(request.amount)}</Text>
              <Text style={[styles.badge, styles[request.status]]}>{statusText(request.status)}</Text>
            </View>
            <Text style={styles.destination}>{request.bank_name} - {request.account_number}</Text>
            <Text style={styles.meta}>{new Date(request.requested_at).toLocaleString('id-ID')}</Text>
            {request.transfer_reference ? <Text style={styles.success}>Referensi transfer: {request.transfer_reference}</Text> : null}
            {request.admin_note ? <Text style={styles.note}>{request.admin_note}</Text> : null}
          </View>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

function rupiah(value) {
  return `Rp${new Intl.NumberFormat('id-ID').format(Number(value || 0))}`;
}

function statusText(status) {
  return {
    pending: 'Menunggu',
    approved: 'Disetujui',
    processing: 'Diproses',
    paid: 'Terkirim',
    rejected: 'Ditolak',
  }[status] || status;
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  button: { alignSelf: 'flex-start', marginBottom: spacing.xl, marginTop: spacing.lg, minHeight: 46 },
  loading: { marginTop: spacing.lg },
  empty: { color: colors.muted },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 17, borderWidth: 1, marginBottom: spacing.sm, padding: spacing.md },
  row: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between' },
  amount: { color: colors.goldLight, fontSize: 21, fontWeight: '700' },
  badge: { borderRadius: 20, color: colors.goldLight, fontSize: 11, fontWeight: '700', overflow: 'hidden', paddingHorizontal: 10, paddingVertical: 5 },
  pending: { backgroundColor: colors.surfaceAlt },
  approved: { backgroundColor: '#363121' },
  processing: { backgroundColor: '#253047' },
  paid: { backgroundColor: '#204232', color: '#b5ebc7' },
  rejected: { backgroundColor: '#412522', color: '#ffd3cd' },
  destination: { color: colors.text, marginTop: spacing.sm },
  meta: { color: colors.muted, fontSize: 12, marginTop: spacing.xs },
  success: { color: colors.success, fontSize: 13, marginTop: spacing.sm },
  note: { color: colors.muted, fontSize: 13, lineHeight: 19, marginTop: spacing.sm },
});
