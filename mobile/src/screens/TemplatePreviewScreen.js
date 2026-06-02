import { ImageBackground, Linking, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useDraft } from '../context/DraftContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function TemplatePreviewScreen({ navigation, route }) {
  const { template } = route.params;
  const { saveSection, syncing } = useDraft();
  const previewImage = `${api.siteUrl}/storage/${template.preview_image}`;

  async function useTemplate() {
    await saveSection('selected_template', template);
    navigation.navigate('GroomBrideForm');
  }

  return (
    <SafeAreaView style={[commonStyles.screen, styles.safe]}>
      <Text style={commonStyles.eyebrow}>Review Template</Text>
      <Text style={commonStyles.title}>{template.name}</Text>
      <Text style={styles.description}>
        Data di bawah hanya contoh. Buka demo lengkap untuk melihat transisi, foto, galeri, dan suasana Bali.
      </Text>

      <ImageBackground source={{ uri: previewImage }} style={styles.hero} imageStyle={styles.heroImage}>
        <View style={styles.overlay}>
          <Text style={styles.smallTitle}>PAWIWAHAN ADAT BALI</Text>
          <Text style={styles.couple}>Wira & Ayu</Text>
          <Text style={styles.event}>18 Agustus 2026 | Bale Banjar Ubud</Text>
        </View>
      </ImageBackground>

      <PrimaryButton
        title="Lihat Undangan Demo Lengkap"
        onPress={() => Linking.openURL(template.preview_url)}
        style={styles.action}
      />
      <PrimaryButton title="Gunakan Template Ini" onPress={useTemplate} loading={syncing} style={styles.action} />
      <SecondaryButton title="Kembali Pilih Template" onPress={() => navigation.goBack()} style={styles.action} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    padding: spacing.lg,
    justifyContent: 'center',
  },
  description: {
    marginTop: spacing.md,
    marginBottom: spacing.lg,
    color: colors.muted,
    fontSize: 15,
    lineHeight: 23,
  },
  hero: {
    minHeight: 300,
    justifyContent: 'flex-end',
    marginBottom: spacing.lg,
  },
  heroImage: {
    borderRadius: 22,
  },
  overlay: {
    minHeight: 300,
    borderRadius: 22,
    justifyContent: 'flex-end',
    alignItems: 'center',
    padding: spacing.xl,
    backgroundColor: 'rgba(20, 10, 7, 0.48)',
  },
  smallTitle: {
    color: colors.gold,
    letterSpacing: 4,
    fontSize: 10,
    marginBottom: spacing.sm,
  },
  couple: {
    color: colors.goldLight,
    fontSize: 34,
    marginBottom: spacing.sm,
  },
  event: {
    color: colors.text,
    textAlign: 'center',
  },
  action: {
    marginBottom: spacing.sm,
  },
});
