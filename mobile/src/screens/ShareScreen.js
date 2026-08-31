import * as Clipboard from 'expo-clipboard';
import { LinearGradient } from 'expo-linear-gradient';
import { useMemo, useState } from 'react';
import { Alert, Linking, Share, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { invitationName, isBirthday } from '../constants/invitation';
import { colors, commonStyles, spacing } from '../theme';

export default function ShareScreen({ navigation, route }) {
  const publication = route.params?.publication;
  const [guestName, setGuestName] = useState('');
  const normalizedGuestName = guestName.trim().replace(/\s+/g, ' ');
  const url = useMemo(() => {
    if (!publication?.public_url || !normalizedGuestName) return publication?.public_url;
    return `${publication.public_url}?to=${encodeURIComponent(normalizedGuestName)}`;
  }, [normalizedGuestName, publication?.public_url]);
  const recipient = normalizedGuestName || 'Bapak/Ibu/Saudara/i';
  const invitation = publication?.data || publication;
  const occasion = isBirthday(invitation) ? `perayaan ulang tahun ${invitationName(invitation)}` : 'acara pernikahan kami';
  const text = `Kepada Yth. ${recipient}, kami mengundang untuk hadir di ${occasion}. Buka undangan: ${url}`;

  function canShare() {
    if (!url) {
      Alert.alert('Link belum tersedia', 'Publish undangan terlebih dahulu.');
      return false;
    }
    if (/[<>]/.test(guestName)) {
      Alert.alert('Nama tamu tidak valid', 'Nama tamu tidak boleh mengandung karakter < atau >.');
      return false;
    }
    return true;
  }

  async function copyLink() {
    if (!canShare()) return;
    await Clipboard.setStringAsync(url);
    Alert.alert(
      'Link tersalin',
      normalizedGuestName
        ? `Link khusus untuk ${normalizedGuestName} siap dibagikan.`
        : 'Link undangan umum siap dibagikan.'
    );
  }

  async function shareInvitation() {
    if (!canShare()) return;
    await Share.share({ message: text });
  }

  async function shareWhatsApp() {
    if (!canShare()) return;
    const whatsAppUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
    await Linking.openURL(whatsAppUrl);
  }

  async function openInvitation() {
    if (!canShare()) return;
    await Linking.openURL(url);
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
        <KeyboardAwareScrollView contentContainerStyle={styles.content}>
          <View style={styles.check}>
            <Text style={styles.checkText}>OK</Text>
          </View>
          <Text style={commonStyles.eyebrow}>Berhasil dipublish</Text>
          <Text style={styles.title}>Undangan siap dibagikan</Text>
          <Text style={styles.body}>Isi nama tamu untuk membuat sapaan personal pada cover undangan.</Text>
          <View style={styles.guestCard}>
            <FormField
              label="Nama tamu (opsional)"
              value={guestName}
              onChangeText={setGuestName}
              maxLength={80}
              placeholder="Contoh: Komang & Pasangan"
              helperText="Kosongkan untuk membuat link undangan umum."
            />
            <View style={styles.linkCard}>
              <Text style={styles.linkLabel}>{normalizedGuestName ? 'Link personal' : 'Link umum'}</Text>
              <Text style={styles.link} numberOfLines={3}>{url}</Text>
            </View>
          </View>
          <PrimaryButton title="Share ke WhatsApp" onPress={shareWhatsApp} style={styles.button} />
          <SecondaryButton title="Bagikan lewat aplikasi lain" onPress={shareInvitation} style={styles.button} />
          <SecondaryButton title="Copy Link" onPress={copyLink} style={styles.button} />
          <SecondaryButton title="Buka Undangan di Browser" onPress={openInvitation} style={styles.button} />
          <Text style={styles.home} onPress={openMyInvitations}>Lihat Undangan Saya</Text>
        </KeyboardAwareScrollView>
      </SafeAreaView>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
  },
  content: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.xl,
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
    lineHeight: 21,
  },
  guestCard: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 18,
    borderWidth: 1,
    marginVertical: spacing.lg,
    padding: spacing.md,
  },
  linkCard: {
    borderRadius: 12,
    backgroundColor: colors.surfaceAlt,
    borderColor: colors.border,
    borderWidth: 1,
    padding: spacing.sm,
  },
  linkLabel: {
    color: colors.muted,
    fontSize: 11,
    marginBottom: spacing.xs,
    textTransform: 'uppercase',
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
