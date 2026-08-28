import Ionicons from '@expo/vector-icons/Ionicons';
import { Alert, Linking, Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, spacing } from '../theme';

const WHATSAPP_NUMBER = '6285113243800';
const WHATSAPP_MESSAGE = 'Halo Undangan Bali Santih, saya ingin berkonsultasi mengenai desain undangan custom. Mohon informasi proses pengerjaannya.';

export const CUSTOM_INVITATION_WHATSAPP_URL = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(WHATSAPP_MESSAGE)}`;

export default function CustomInvitationCard({ compact = false, style }) {
  async function openConsultation() {
    try {
      await Linking.openURL(CUSTOM_INVITATION_WHATSAPP_URL);
    } catch {
      Alert.alert('WhatsApp belum dapat dibuka', 'Silakan coba lagi atau kunjungi undangan.balisantih.com untuk menghubungi penyedia.');
    }
  }

  return (
    <View style={[styles.card, compact && styles.compactCard, style]}>
      <View style={styles.iconWrap}>
        <Ionicons color={colors.goldLight} name="color-palette-outline" size={compact ? 22 : 26} />
      </View>
      <View style={styles.copy}>
        <Text style={styles.eyebrow}>Layanan personal</Text>
        <Text style={styles.title}>Belum menemukan desain yang terasa tepat?</Text>
        <Text style={styles.body}>
          Template di aplikasi tetap bisa digunakan gratis. Untuk konsep yang lebih khusus, konsultasikan kebutuhan kalian langsung dengan penyedia.
        </Text>
        <Pressable
          accessibilityHint="Membuka percakapan WhatsApp dengan penyedia Undangan Bali Santih"
          accessibilityRole="button"
          onPress={openConsultation}
          style={({ pressed }) => [styles.button, pressed && styles.pressed]}
        >
          <Ionicons color={colors.background} name="logo-whatsapp" size={18} />
          <Text style={styles.buttonText}>Konsultasi Desain via WhatsApp</Text>
          <Ionicons color={colors.background} name="arrow-forward" size={17} />
        </Pressable>
        <Text style={styles.note}>Konsultasi bersifat pilihan dan tidak mengurangi fasilitas gratis.</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#193528',
    borderColor: '#496d58',
    borderRadius: 22,
    borderWidth: 1,
    padding: spacing.md,
  },
  compactCard: {
    marginTop: spacing.xl,
  },
  iconWrap: {
    alignItems: 'center',
    backgroundColor: '#294c3a',
    borderRadius: 18,
    height: 44,
    justifyContent: 'center',
    width: 44,
  },
  copy: {
    marginTop: spacing.md,
  },
  eyebrow: {
    color: colors.gold,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.5,
    textTransform: 'uppercase',
  },
  title: {
    color: colors.text,
    fontSize: 18,
    fontWeight: '700',
    lineHeight: 23,
    marginTop: spacing.xs,
  },
  body: {
    color: '#c5d5ca',
    fontSize: 13,
    lineHeight: 20,
    marginTop: spacing.sm,
  },
  button: {
    alignItems: 'center',
    alignSelf: 'stretch',
    backgroundColor: colors.gold,
    borderRadius: 13,
    flexDirection: 'row',
    gap: spacing.xs,
    justifyContent: 'center',
    marginTop: spacing.md,
    minHeight: 46,
    paddingHorizontal: spacing.md,
  },
  buttonText: {
    color: colors.background,
    flex: 1,
    fontSize: 13,
    fontWeight: '800',
    textAlign: 'center',
  },
  note: {
    color: '#94ad9d',
    fontSize: 11,
    lineHeight: 16,
    marginTop: spacing.sm,
  },
  pressed: {
    opacity: 0.78,
  },
});
