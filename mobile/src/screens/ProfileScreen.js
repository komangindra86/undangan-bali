import Ionicons from '@expo/vector-icons/Ionicons';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { colors, commonStyles, spacing } from '../theme';

export default function ProfileScreen({ navigation }) {
  const { hasAccountOnDevice, isAuthenticated, loading, logout, user } = useAuth();

  function openStack(screen, params) {
    navigation.getParent()?.navigate(screen, params);
  }

  async function confirmLogout() {
    Alert.alert('Keluar dari akun?', 'Draft lokal tetap tersimpan di perangkat ini.', [
      { text: 'Batal', style: 'cancel' },
      {
        text: 'Keluar',
        style: 'destructive',
        onPress: async () => {
          await logout();
          navigation.navigate('FeedTab');
        },
      },
    ]);
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Akun</Text>
        <Text style={commonStyles.title}>{isAuthenticated ? 'Profil Saya' : 'Profil & Undangan Saya'}</Text>

        {loading ? <Text style={styles.status}>Memeriksa akun...</Text> : null}
        {!loading && !isAuthenticated ? (
          <View style={styles.guestCard}>
            <View style={styles.iconCircle}>
              <Ionicons color={colors.goldLight} name="person-outline" size={30} />
            </View>
            <Text style={styles.guestTitle}>{hasAccountOnDevice ? 'Masuk kembali ke akun Anda' : 'Sudah pernah membuat undangan?'}</Text>
            <Text style={styles.guestBody}>
              Masuk untuk melihat undangan yang sudah dibuat, permintaan tamu, notifikasi, dan Wedding Gift. Membuat undangan baru tetap bisa tanpa login.
            </Text>
            <PrimaryButton title="Masuk" onPress={() => openStack('Login', { returnTab: 'ProfileTab' })} style={styles.primary} />
            <SecondaryButton title="Daftar Akun" onPress={() => openStack('Register', { returnTab: 'ProfileTab' })} style={styles.secondary} />
            <Pressable accessibilityRole="button" onPress={() => openStack('Template')} style={styles.tryButton}>
              <Text style={styles.tryText}>Buat undangan tanpa login</Text>
              <Ionicons color={colors.goldLight} name="chevron-forward" size={17} />
            </Pressable>
          </View>
        ) : null}

        {!loading && isAuthenticated ? (
          <>
            <View style={styles.profileCard}>
              <View style={styles.avatar}>
                <Text style={styles.avatarText}>{accountInitials(user?.name)}</Text>
              </View>
              <View style={styles.identity}>
                <Text style={styles.name}>{user?.name || 'Pengguna'}</Text>
                <Text style={styles.email}>{user?.email}</Text>
              </View>
            </View>

            <Text style={styles.sectionTitle}>Kelola akun dan undangan</Text>
            <ProfileMenu icon="mail-open-outline" label="Undangan Saya" onPress={() => navigation.navigate('InvitationsTab')} />
            <ProfileMenu icon="notifications-outline" label="Notifikasi" onPress={() => navigation.navigate('NotificationsTab')} />
            <ProfileMenu icon="add-circle-outline" label="Buat Undangan Baru" onPress={() => openStack('Template')} />
            <ProfileMenu danger icon="log-out-outline" label="Keluar" onPress={confirmLogout} />
          </>
        ) : null}
      </ScrollView>
    </SafeAreaView>
  );
}

function ProfileMenu({ danger = false, icon, label, onPress }) {
  return (
    <Pressable accessibilityRole="button" onPress={onPress} style={({ pressed }) => [styles.menu, pressed && styles.pressed]}>
      <View style={styles.menuIcon}>
        <Ionicons color={danger ? colors.danger : colors.goldLight} name={icon} size={21} />
      </View>
      <Text style={[styles.menuLabel, danger && styles.danger]}>{label}</Text>
      <Ionicons color={colors.muted} name="chevron-forward" size={18} />
    </Pressable>
  );
}

function accountInitials(name) {
  return (name || 'U').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  status: { color: colors.muted, marginTop: spacing.lg },
  guestCard: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 24, borderWidth: 1, marginTop: spacing.xl, padding: spacing.lg },
  iconCircle: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 31, height: 62, justifyContent: 'center', width: 62 },
  guestTitle: { color: colors.text, fontSize: 20, fontWeight: '700', marginTop: spacing.md, textAlign: 'center' },
  guestBody: { color: colors.muted, lineHeight: 22, marginTop: spacing.sm, textAlign: 'center' },
  primary: { marginTop: spacing.lg, width: '100%' },
  secondary: { marginTop: spacing.sm, width: '100%' },
  tryButton: { alignItems: 'center', flexDirection: 'row', gap: spacing.xs, marginTop: spacing.lg, paddingVertical: spacing.xs },
  tryText: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  profileCard: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, flexDirection: 'row', marginTop: spacing.xl, padding: spacing.md },
  avatar: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderColor: colors.gold, borderRadius: 28, borderWidth: 1, height: 56, justifyContent: 'center', width: 56 },
  avatarText: { color: colors.goldLight, fontSize: 18, fontWeight: '800' },
  identity: { flex: 1, marginLeft: spacing.md },
  name: { color: colors.text, fontSize: 18, fontWeight: '700' },
  email: { color: colors.muted, fontSize: 13, marginTop: 3 },
  sectionTitle: { color: colors.goldLight, fontSize: 13, fontWeight: '800', letterSpacing: 1, marginBottom: spacing.sm, marginTop: spacing.xl, textTransform: 'uppercase' },
  menu: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 17, borderWidth: 1, flexDirection: 'row', marginBottom: spacing.sm, minHeight: 58, paddingHorizontal: spacing.md },
  menuIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 15, height: 34, justifyContent: 'center', width: 34 },
  menuLabel: { color: colors.text, flex: 1, fontSize: 15, fontWeight: '600', marginLeft: spacing.sm },
  danger: { color: colors.danger },
  pressed: { opacity: 0.76 },
});
