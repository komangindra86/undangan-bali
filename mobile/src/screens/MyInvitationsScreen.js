import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, Linking, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function MyInvitationsScreen({ navigation }) {
  const { expireSession, hasAccountOnDevice, isAuthenticated, loading: authLoading, token } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);

  function openStack(screen, params) {
    navigation.getParent()?.navigate(screen, params);
  }

  const loadInvitations = useCallback(async () => {
    if (!token) {
      setItems([]);
      setLoading(false);
      return;
    }

    setLoading(true);
    try {
      const response = await api.invitations(token);
      setItems(response.data || []);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        openStack('Login', { returnTab: 'InvitationsTab', sessionExpired: true });
        return;
      }
      Alert.alert('Tidak dapat memuat undangan', error.message);
    } finally {
      setLoading(false);
    }
  }, [expireSession, token]);

  useFocusEffect(useCallback(() => {
    loadInvitations();
  }, [loadInvitations]));

  async function toggleFeed(item) {
    try {
      const response = await api.setFeedVisibility(item.id, !item.is_hidden_from_feed, token);
      setItems((current) => current.map((entry) => (entry.id === item.id ? response.data : entry)));
    } catch (error) {
      Alert.alert('Feed Moment', error.message);
    }
  }

  if (!authLoading && !isAuthenticated) {
    return (
      <SafeAreaView style={commonStyles.screen}>
        <ScrollView contentContainerStyle={styles.content}>
          <Text style={commonStyles.eyebrow}>Undangan</Text>
          <Text style={commonStyles.title}>Undangan Saya</Text>
          <View style={styles.authCard}>
            <View style={styles.authIcon}>
              <Ionicons color={colors.goldLight} name="mail-open-outline" size={30} />
            </View>
            <Text style={styles.authTitle}>{hasAccountOnDevice ? 'Masuk untuk melanjutkan' : 'Simpan undangan di akun Anda'}</Text>
            <Text style={styles.authBody}>Lihat undangan yang pernah dibuat, kelola Moment, permintaan tamu, dan Wedding Gift dari sini.</Text>
            <PrimaryButton title="Masuk & Lihat Undangan" onPress={() => openStack('Login', { returnTab: 'InvitationsTab' })} style={styles.authButton} />
            <SecondaryButton title="Daftar Akun" onPress={() => openStack('Register', { returnTab: 'InvitationsTab' })} style={styles.secondaryButton} />
            <Pressable accessibilityRole="button" onPress={() => openStack('Template')} style={styles.guestCreate}>
              <Text style={styles.guestCreateText}>Atau buat undangan tanpa login</Text>
              <Ionicons color={colors.goldLight} name="chevron-forward" size={17} />
            </Pressable>
          </View>
        </ScrollView>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <View style={styles.headingRow}>
          <View style={styles.headingCopy}>
            <Text style={commonStyles.eyebrow}>Akun Saya</Text>
            <Text style={commonStyles.title}>Undangan Saya</Text>
          </View>
          <Pressable accessibilityLabel="Buat undangan baru" accessibilityRole="button" onPress={() => openStack('Template')} style={styles.addButton}>
            <Ionicons color={colors.background} name="add" size={25} />
          </Pressable>
        </View>

        {loading || authLoading ? <ActivityIndicator color={colors.gold} style={styles.loading} /> : null}
        {!loading && !authLoading && items.length === 0 ? (
          <View style={styles.emptyCard}>
            <Text style={styles.emptyTitle}>Belum ada undangan tersimpan</Text>
            <Text style={styles.empty}>Mulai dari pilihan template. Anda bisa menyusun semuanya sebelum publish.</Text>
            <PrimaryButton title="Buat Undangan Gratis" onPress={() => openStack('Template')} style={styles.emptyButton} />
          </View>
        ) : null}

        {items.map((item) => (
          <InvitationCard
            item={item}
            key={item.id}
            onOpen={() => Linking.openURL(item.public_url || `${api.siteUrl}/u/${item.slug}`)}
            onNavigate={(screen) => openStack(screen, { invitation: item })}
            onToggleFeed={() => toggleFeed(item)}
          />
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

function InvitationCard({ item, onNavigate, onOpen, onToggleFeed }) {
  const published = item.status === 'published';

  return (
    <View style={styles.card}>
      <View style={styles.cardTop}>
        <View style={styles.coupleIcon}>
          <Ionicons color={colors.goldLight} name="heart-outline" size={20} />
        </View>
        <View style={styles.cardIdentity}>
          <Text style={styles.name}>{item.groom_nickname || 'Mempelai'} & {item.bride_nickname || 'Pasangan'}</Text>
          <Text style={styles.meta}>{item.event_date || 'Tanggal belum diisi'}</Text>
        </View>
        <View style={[styles.statusPill, published && styles.publishedPill]}>
          <Text style={[styles.statusText, published && styles.publishedText]}>{published ? 'Live' : 'Draft'}</Text>
        </View>
      </View>

      {published ? (
        <>
          <PrimaryButton title="Buka Undangan Live" onPress={onOpen} style={styles.openButton} />
          <View style={styles.actionGrid}>
            <SmallAction icon="images-outline" label="Kelola Moment" onPress={() => onNavigate('ManageMoments')} />
            <SmallAction icon="people-outline" label="Permintaan Tamu" onPress={() => onNavigate('InvitationRequests')} />
            <SmallAction icon="gift-outline" label="Wedding Gift" onPress={() => onNavigate('WeddingGiftDashboard')} />
            <SmallAction icon="settings-outline" label="Atur Gift" onPress={() => onNavigate('WeddingGiftSetting')} />
          </View>
          <Pressable accessibilityRole="button" onPress={onToggleFeed} style={styles.feedToggle}>
            <Ionicons color={colors.muted} name={item.is_hidden_from_feed ? 'eye-outline' : 'eye-off-outline'} size={17} />
            <Text style={styles.feedToggleText}>{item.is_hidden_from_feed ? 'Tampilkan kembali di Feed' : 'Sembunyikan dari Feed'}</Text>
          </Pressable>
        </>
      ) : (
        <Text style={styles.draftHelp}>Undangan ini belum dipublish. Lanjutkan draft lokal dari tombol Buat.</Text>
      )}
    </View>
  );
}

function SmallAction({ icon, label, onPress }) {
  return (
    <Pressable accessibilityRole="button" onPress={onPress} style={({ pressed }) => [styles.smallAction, pressed && styles.pressed]}>
      <Ionicons color={colors.goldLight} name={icon} size={19} />
      <Text style={styles.smallActionText}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  headingRow: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between' },
  headingCopy: { flex: 1 },
  addButton: { alignItems: 'center', backgroundColor: colors.gold, borderRadius: 20, height: 40, justifyContent: 'center', width: 40 },
  loading: { marginTop: spacing.xl },
  authCard: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 24, borderWidth: 1, marginTop: spacing.xl, padding: spacing.lg },
  authIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 30, height: 60, justifyContent: 'center', width: 60 },
  authTitle: { color: colors.text, fontSize: 20, fontWeight: '700', marginTop: spacing.md, textAlign: 'center' },
  authBody: { color: colors.muted, lineHeight: 22, marginTop: spacing.sm, textAlign: 'center' },
  authButton: { marginTop: spacing.lg, width: '100%' },
  secondaryButton: { marginTop: spacing.sm, width: '100%' },
  guestCreate: { alignItems: 'center', flexDirection: 'row', gap: spacing.xs, marginTop: spacing.lg, paddingVertical: spacing.xs },
  guestCreateText: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  emptyCard: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, marginTop: spacing.xl, padding: spacing.lg },
  emptyTitle: { color: colors.text, fontSize: 18, fontWeight: '700', textAlign: 'center' },
  empty: { color: colors.muted, lineHeight: 21, marginTop: spacing.xs, textAlign: 'center' },
  emptyButton: { marginTop: spacing.lg },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, marginTop: spacing.md, padding: spacing.md },
  cardTop: { alignItems: 'center', flexDirection: 'row' },
  coupleIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 18, height: 38, justifyContent: 'center', width: 38 },
  cardIdentity: { flex: 1, marginLeft: spacing.sm },
  name: { color: colors.text, fontSize: 16, fontWeight: '700' },
  meta: { color: colors.muted, fontSize: 12, marginTop: 3 },
  statusPill: { backgroundColor: colors.surfaceAlt, borderRadius: 99, paddingHorizontal: 10, paddingVertical: 6 },
  publishedPill: { backgroundColor: '#243127' },
  statusText: { color: colors.muted, fontSize: 10, fontWeight: '800', textTransform: 'uppercase' },
  publishedText: { color: colors.success },
  openButton: { marginTop: spacing.md, minHeight: 48 },
  actionGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm, marginTop: spacing.sm },
  smallAction: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 15, flexBasis: '47%', flexDirection: 'row', gap: spacing.xs, minHeight: 46, paddingHorizontal: spacing.sm },
  smallActionText: { color: colors.text, flex: 1, fontSize: 11, fontWeight: '600' },
  feedToggle: { alignItems: 'center', borderTopColor: colors.border, borderTopWidth: 1, flexDirection: 'row', gap: spacing.xs, marginTop: spacing.md, paddingTop: spacing.md },
  feedToggleText: { color: colors.muted, fontSize: 12, fontWeight: '600' },
  draftHelp: { color: colors.muted, lineHeight: 20, marginTop: spacing.md },
  pressed: { opacity: 0.74 },
});
