import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function NotificationsScreen({ navigation, route }) {
  const { expireSession, isAuthenticated, loading: authLoading, token } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const embeddedInTab = route.name === 'NotificationsTab';

  function openStack(screen, params) {
    if (embeddedInTab) {
      navigation.getParent()?.navigate(screen, params);
    } else {
      navigation.navigate(screen, params);
    }
  }

  const load = useCallback(async () => {
    if (!token) {
      setItems([]);
      setLoading(false);
      return;
    }

    setLoading(true);
    try {
      const response = await api.notifications(token);
      setItems(response.data || []);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        openStack('Login', { returnTab: 'NotificationsTab', sessionExpired: true });
        return;
      }
      Alert.alert('Notifikasi', error.message);
    } finally {
      setLoading(false);
    }
  }, [expireSession, token]);

  useFocusEffect(useCallback(() => {
    load();
  }, [load]));

  async function openNotification(item) {
    try {
      await api.readNotification(item.id, token);
      setItems((current) => current.map((entry) => (entry.id === item.id ? { ...entry, read_at: new Date().toISOString() } : entry)));
    } catch (_) {}

    if (item.type === 'invitation_request' && item.invitation_id) {
      openStack('InvitationRequests', { invitation: { id: item.invitation_id } });
    }
  }

  if (!authLoading && !isAuthenticated) {
    return (
      <SafeAreaView style={commonStyles.screen}>
        <ScrollView contentContainerStyle={styles.content}>
          <Text style={commonStyles.eyebrow}>Aktivitas</Text>
          <Text style={commonStyles.title}>Notifikasi</Text>
          <View style={styles.authCard}>
            <View style={styles.authIcon}>
              <Ionicons color={colors.goldLight} name="notifications-outline" size={30} />
            </View>
            <Text style={styles.authTitle}>Masuk untuk melihat aktivitas</Text>
            <Text style={styles.authBody}>Permintaan undangan, komentar, reaksi, dan Wedding Gift akan tampil di sini.</Text>
            <PrimaryButton title="Masuk" onPress={() => openStack('Login', { returnTab: 'NotificationsTab' })} style={styles.authButton} />
            <SecondaryButton title="Daftar Akun" onPress={() => openStack('Register', { returnTab: 'NotificationsTab' })} style={styles.secondaryButton} />
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Aktivitas</Text>
        <Text style={commonStyles.title}>Notifikasi</Text>
        {!embeddedInTab ? <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text> : null}
        {loading || authLoading ? <ActivityIndicator color={colors.gold} style={styles.loading} /> : null}
        {!loading && !authLoading && !items.length ? (
          <View style={styles.emptyCard}>
            <Ionicons color={colors.goldLight} name="notifications-off-outline" size={28} />
            <Text style={styles.emptyTitle}>Belum ada notifikasi</Text>
            <Text style={styles.empty}>Aktivitas terbaru dari Moment dan undangan Anda akan muncul di sini.</Text>
          </View>
        ) : null}
        {items.map((item) => (
          <Pressable key={item.id} onPress={() => openNotification(item)} style={[styles.card, !item.read_at && styles.unread]}>
            <View style={[styles.itemIcon, !item.read_at && styles.unreadIcon]}>
              <Ionicons color={colors.goldLight} name={iconFor(item.type)} size={20} />
            </View>
            <View style={styles.itemCopy}>
              <Text style={styles.type}>{labelFor(item.type)}</Text>
              <Text style={styles.message}>{item.data?.message || 'Ada pembaruan pada Moment Anda.'}</Text>
            </View>
            {!item.read_at ? <View style={styles.unreadDot} /> : null}
          </Pressable>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

function labelFor(type) {
  return ({ invitation_request: 'Permintaan Undangan', reaction: 'Reaksi Baru', comment: 'Komentar Baru', wedding_gift_paid: 'Wedding Gift Diterima' })[type] || 'Pembaruan';
}

function iconFor(type) {
  return ({ invitation_request: 'mail-unread-outline', reaction: 'heart-outline', comment: 'chatbubble-outline', wedding_gift_paid: 'gift-outline' })[type] || 'notifications-outline';
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  back: { color: colors.goldLight, marginBottom: spacing.xl, marginTop: spacing.md },
  loading: { marginTop: spacing.xl },
  authCard: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 24, borderWidth: 1, marginTop: spacing.xl, padding: spacing.lg },
  authIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 30, height: 60, justifyContent: 'center', width: 60 },
  authTitle: { color: colors.text, fontSize: 20, fontWeight: '700', marginTop: spacing.md, textAlign: 'center' },
  authBody: { color: colors.muted, lineHeight: 22, marginTop: spacing.sm, textAlign: 'center' },
  authButton: { marginTop: spacing.lg, width: '100%' },
  secondaryButton: { marginTop: spacing.sm, width: '100%' },
  emptyCard: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, marginTop: spacing.xl, padding: spacing.xl },
  emptyTitle: { color: colors.text, fontSize: 17, fontWeight: '700', marginTop: spacing.sm },
  empty: { color: colors.muted, lineHeight: 20, marginTop: spacing.xs, textAlign: 'center' },
  card: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 18, borderWidth: 1, flexDirection: 'row', marginTop: spacing.sm, padding: spacing.md },
  unread: { borderColor: colors.gold },
  itemIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 18, height: 38, justifyContent: 'center', width: 38 },
  unreadIcon: { backgroundColor: '#342817' },
  itemCopy: { flex: 1, marginLeft: spacing.sm },
  type: { color: colors.goldLight, fontSize: 11, fontWeight: '800', textTransform: 'uppercase' },
  message: { color: colors.text, lineHeight: 20, marginTop: spacing.xs },
  unreadDot: { backgroundColor: colors.gold, borderRadius: 4, height: 8, marginLeft: spacing.sm, width: 8 },
});
