import * as Clipboard from 'expo-clipboard';
import { LinearGradient } from 'expo-linear-gradient';
import { Alert, Linking, Share, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { colors, commonStyles, spacing } from '../theme';

export default function ShareScreen({ navigation, route }) {
  const publication = route.params?.publication;
  const url = publication?.public_url;
  const text = publication?.share_text;

  async function copyLink() {
    await Clipboard.setStringAsync(url);
    Alert.alert('Link tersalin', 'Link undangan siap ditempel dan dibagikan.');
  }

  async function shareInvitation() {
    await Share.share({ message: text });
  }

  async function shareWhatsApp() {
    const whatsAppUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
    await Linking.openURL(whatsAppUrl);
  }

  function openMyInvitations() {
    navigation.reset({
      index: 0,
      routes: [{ name: 'MainTabs', params: { screen: 'InvitationsTab' } }],
    });
  }

  return (
    <LinearGradient colors={['#15110d', '#281e13']} style={commonStyles.screen}>
      <SafeAreaView style={styles.safe}>
        <View style={styles.check}>
          <Text style={styles.checkText}>OK</Text>
        </View>
        <Text style={commonStyles.eyebrow}>Berhasil dipublish</Text>
        <Text style={styles.title}>Undangan siap dibagikan</Text>
        <Text style={styles.body}>Kirim link ini kepada keluarga dan sahabat Anda.</Text>
        <View style={styles.linkCard}>
          <Text style={styles.link} numberOfLines={2}>{url}</Text>
        </View>
        <PrimaryButton title="Share ke WhatsApp" onPress={shareWhatsApp} style={styles.button} />
        <SecondaryButton title="Bagikan lewat aplikasi lain" onPress={shareInvitation} style={styles.button} />
        <SecondaryButton title="Copy Link" onPress={copyLink} style={styles.button} />
        <SecondaryButton title="Buka Undangan di Browser" onPress={() => Linking.openURL(url)} style={styles.button} />
        <Text style={styles.home} onPress={openMyInvitations}>Lihat Undangan Saya</Text>
      </SafeAreaView>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
  },
  check: {
    width: 66,
    height: 66,
    borderRadius: 33,
    backgroundColor: colors.gold,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  checkText: {
    color: colors.background,
    fontWeight: '800',
  },
  title: {
    color: colors.text,
    fontSize: 32,
    fontWeight: '600',
    marginTop: spacing.md,
  },
  body: {
    color: colors.muted,
    marginTop: spacing.sm,
  },
  linkCard: {
    borderRadius: 15,
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderWidth: 1,
    padding: spacing.md,
    marginVertical: spacing.xl,
  },
  link: {
    color: colors.goldLight,
  },
  button: {
    marginBottom: spacing.sm,
  },
  home: {
    color: colors.goldLight,
    textAlign: 'center',
    padding: spacing.md,
  },
});
