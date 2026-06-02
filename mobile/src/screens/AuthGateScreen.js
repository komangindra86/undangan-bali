import { StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { colors, commonStyles, spacing } from '../theme';

export default function AuthGateScreen({ navigation }) {
  return (
    <SafeAreaView style={[commonStyles.screen, styles.container]}>
      <View style={styles.icon}>
        <Text style={styles.iconText}>LINK</Text>
      </View>
      <Text style={commonStyles.eyebrow}>Data sudah diperiksa</Text>
      <Text style={styles.title}>Masuk untuk membuat hasil undangan final</Text>
      <Text style={styles.body}>
        Data Anda sudah aman tersimpan di perangkat. Login atau daftar sekarang untuk membuat desain undangan lengkap dan link publik.
      </Text>
      <PrimaryButton title="Masuk" onPress={() => navigation.navigate('Login', { publishAfterAuth: true })} style={styles.button} />
      <SecondaryButton title="Daftar Akun Baru" onPress={() => navigation.navigate('Register', { publishAfterAuth: true })} style={styles.secondary} />
      <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali ke ringkasan data</Text>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
  },
  icon: {
    width: 66,
    height: 66,
    borderRadius: 22,
    borderColor: colors.gold,
    borderWidth: 1,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  iconText: {
    color: colors.gold,
    fontSize: 10,
    letterSpacing: 2,
  },
  title: {
    color: colors.text,
    fontSize: 30,
    lineHeight: 38,
    fontWeight: '600',
    marginTop: spacing.md,
  },
  body: {
    color: colors.muted,
    lineHeight: 23,
    marginTop: spacing.md,
    marginBottom: spacing.xl,
  },
  button: {
    marginBottom: spacing.sm,
  },
  secondary: {
    marginBottom: spacing.lg,
  },
  back: {
    textAlign: 'center',
    color: colors.goldLight,
    padding: spacing.md,
  },
});
