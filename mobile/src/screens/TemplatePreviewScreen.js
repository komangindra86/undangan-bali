import { Alert, ImageBackground, Linking, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useDraft } from '../context/DraftContext';
import { isBirthday, personScreenFor } from '../constants/invitation';
import { api } from '../services/api';
import { templateMatchesType } from '../utils/templateCatalog';
import { colors, commonStyles, spacing } from '../theme';

export default function TemplatePreviewScreen({ navigation, route }) {
  const template = route.params?.template;
  const { draft, saveSection, syncing } = useDraft();
  const birthday = isBirthday(draft);
  const matchingType = templateMatchesType(template, draft.invitation_type || 'wedding');
  const previewImage = `${api.siteUrl}/storage/${template?.preview_image}`;

  async function useTemplate() {
    if (!matchingType) return;
    try {
      await saveSection('selected_template', template);
      navigation.navigate(personScreenFor(draft));
    } catch (error) { Alert.alert('Template belum tersimpan', error.message); }
  }

  if (!matchingType) {
    return (
      <SafeAreaView style={commonStyles.screen}>
        <View style={styles.safe}>
          <Text style={commonStyles.title}>Pilih template yang sesuai</Text>
          <Text style={styles.description}>
            Template ini bukan untuk undangan {birthday ? 'ulang tahun' : 'pernikahan'}. Silakan kembali dan pilih desain yang sesuai. Data yang sudah diisi tetap tersimpan.
          </Text>
          <SecondaryButton title="Kembali Pilih Template" onPress={() => navigation.navigate('Template')} />
        </View>
      </SafeAreaView>
    );
  }

  async function openPreview() {
    if (!matchingType) return;
    try { await Linking.openURL(template.preview_url); }
    catch { Alert.alert('Demo belum terbuka', 'Tidak dapat membuka demo template. Silakan coba lagi.'); }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.safe}>
      <Text style={commonStyles.eyebrow}>Review Template</Text>
      <Text style={commonStyles.title}>{template.name}</Text>
      <Text style={styles.description}>
        Data di bawah hanya contoh. Buka demo lengkap untuk melihat desain, transisi, galeri, dan musik.
      </Text>

      <ImageBackground source={birthday ? undefined : { uri: previewImage }} style={[styles.hero, birthday && { backgroundColor: template.slug === 'ceria-confetti' ? '#6b46a4' : template.slug === 'ruang-putih' ? '#83796f' : '#163f36', borderRadius: 22 }]} imageStyle={styles.heroImage}>
        <View style={styles.overlay}>
          <Text style={styles.smallTitle}>{birthday ? 'PERAYAAN ULANG TAHUN' : 'PAWIWAHAN ADAT BALI'}</Text>
          <Text style={styles.couple}>{birthday ? 'Kirana' : 'Wira & Ayu'}</Text>
          <Text style={styles.event}>{birthday ? 'Merayakan 7 tahun penuh cerita' : '18 Agustus 2026 | Bale Banjar Ubud'}</Text>
        </View>
      </ImageBackground>

      <PrimaryButton
        title="Lihat Undangan Demo Lengkap"
        onPress={openPreview}
        style={styles.action}
      />
      <PrimaryButton title="Gunakan Template Ini" onPress={useTemplate} loading={syncing} style={styles.action} />
      <SecondaryButton title="Kembali Pilih Template" onPress={() => navigation.goBack()} style={styles.action} />
      </ScrollView>
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
