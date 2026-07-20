import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function NotificationsScreen({ navigation }) {
  const { token, expireSession } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const response = await api.notifications(token);
      setItems(response.data || []);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
        return;
      }
      Alert.alert('Notifikasi', error.message);
    } finally {
      setLoading(false);
    }
  }, [expireSession, navigation, token]);

  useEffect(() => { load(); }, [load]);

  async function openNotification(item) {
    try { await api.readNotification(item.id, token); } catch (_) {}
    if (item.type === 'invitation_request' && item.invitation_id) {
      navigation.navigate('InvitationRequests', { invitation: { id: item.invitation_id } });
    }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Akun Saya</Text>
        <Text style={commonStyles.title}>Notifikasi</Text>
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text>
        {loading ? <ActivityIndicator color={colors.gold} /> : null}
        {!loading && !items.length ? <Text style={styles.empty}>Belum ada notifikasi.</Text> : null}
        {items.map((item) => (
          <Pressable key={item.id} onPress={() => openNotification(item)} style={[styles.card, !item.read_at && styles.unread]}>
            <Text style={styles.type}>{labelFor(item.type)}</Text>
            <Text style={styles.message}>{item.data?.message || 'Ada pembaruan pada Moment Anda.'}</Text>
          </Pressable>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

function labelFor(type) {
  return ({ invitation_request: 'Permintaan Undangan', reaction: 'Reaksi Baru', comment: 'Komentar Baru', wedding_gift_paid: 'Wedding Gift Diterima' })[type] || 'Pembaruan';
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  back: { color: colors.goldLight, marginBottom: spacing.xl, marginTop: spacing.md },
  empty: { color: colors.muted },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, marginBottom: spacing.sm, padding: spacing.md },
  unread: { borderColor: colors.gold },
  type: { color: colors.goldLight, fontSize: 12, fontWeight: '800', textTransform: 'uppercase' },
  message: { color: colors.text, lineHeight: 21, marginTop: spacing.xs },
});
