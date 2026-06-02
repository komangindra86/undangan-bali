import { LinearGradient } from 'expo-linear-gradient';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { colors, commonStyles, spacing } from '../theme';

export default function LandingScreen({ navigation }) {
  const { hasAccountOnDevice, isAuthenticated, user, logout } = useAuth();

  return (
    <LinearGradient colors={['#15110d', '#271e14']} style={commonStyles.screen}>
      <SafeAreaView style={styles.safe}>
        <View style={styles.header}>
          <Text style={commonStyles.eyebrow}>Undangan Pernikahan Bali</Text>
          {isAuthenticated ? (
            <Pressable onPress={logout}>
              <Text style={styles.account}>Keluar</Text>
            </Pressable>
          ) : null}
        </View>
        <View style={styles.hero}>
          <Text style={styles.title}>Buat undangan pernikahan yang hangat dan berkesan.</Text>
          <Text style={styles.body}>
            Mulai tanpa login. Susun undangan hingga konfirmasi data, lalu masuk hanya ketika siap membagikannya.
          </Text>
        </View>
        <View style={styles.panel}>
          {user ? <Text style={styles.welcome}>Halo, {user.name}</Text> : null}
          <PrimaryButton title="Buat Undangan Gratis" onPress={() => navigation.navigate('Template')} />
          {isAuthenticated ? (
            <SecondaryButton
              title="Undangan Saya"
              onPress={() => navigation.navigate('MyInvitations')}
              style={styles.secondary}
            />
          ) : hasAccountOnDevice ? (
            <>
              <SecondaryButton
                title="Masuk untuk Lihat Undangan Saya"
                onPress={() => navigation.navigate('Login', { returnTo: 'MyInvitations' })}
                style={styles.secondary}
              />
              <Text style={styles.note}>Buat baru tetap bisa tanpa login.</Text>
            </>
          ) : (
            <>
              <Text style={styles.note}>Tidak perlu akun untuk mulai mencoba.</Text>
              <Text style={styles.loginLink} onPress={() => navigation.navigate('Login', { returnTo: 'MyInvitations' })}>
                Sudah punya undangan? Masuk
              </Text>
            </>
          )}
        </View>
      </SafeAreaView>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    justifyContent: 'space-between',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  account: {
    color: colors.goldLight,
    fontWeight: '600',
  },
  hero: {
    marginTop: 56,
    flex: 1,
    justifyContent: 'center',
  },
  title: {
    color: colors.text,
    fontSize: 39,
    lineHeight: 48,
    fontWeight: '500',
  },
  body: {
    color: colors.muted,
    fontSize: 16,
    lineHeight: 25,
    marginTop: spacing.lg,
  },
  panel: {
    borderRadius: 26,
    backgroundColor: colors.surface,
    padding: spacing.lg,
    borderWidth: 1,
    borderColor: colors.border,
  },
  welcome: {
    color: colors.goldLight,
    marginBottom: spacing.md,
    fontSize: 15,
  },
  secondary: {
    marginTop: spacing.sm,
  },
  note: {
    textAlign: 'center',
    color: colors.muted,
    fontSize: 13,
    marginTop: spacing.md,
  },
  loginLink: {
    color: colors.goldLight,
    fontSize: 13,
    marginTop: spacing.md,
    paddingVertical: spacing.xs,
    textAlign: 'center',
  },
});
