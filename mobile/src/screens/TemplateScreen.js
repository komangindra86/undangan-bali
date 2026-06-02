import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions } from '../components/Buttons';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { api } from '../services/api';
import { colors, spacing } from '../theme';

export default function TemplateScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [templates, setTemplates] = useState([]);
  const [selected, setSelected] = useState(draft.selected_template);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.templates()
      .then((response) => setTemplates(response.data))
      .catch((error) => Alert.alert('Template belum termuat', error.message))
      .finally(() => setLoading(false));
  }, []);

  async function next() {
    if (!selected) {
      Alert.alert('Pilih template', 'Silakan review lalu pilih satu desain untuk melanjutkan.');
      return;
    }
    await saveSection('selected_template', selected);
    navigation.navigate('GroomBrideForm');
  }

  return (
    <WizardLayout
      step={1}
      title="Pilih nuansa Bali"
      subtitle="Lihat preview lengkap dengan data dummy, foto, galeri, dan animasi sebelum memutuskan desain."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      {loading ? <ActivityIndicator color={colors.gold} /> : null}
      {templates.map((template) => {
        const active = selected?.id === template.id;
        return (
          <View key={template.id} style={[styles.template, active && styles.active]}>
            <Pressable onPress={() => navigation.navigate('TemplatePreview', { template })}>
              <Image source={{ uri: imageUrl(template.thumbnail) }} style={styles.preview} />
              <View style={styles.overlay}>
                <Text style={styles.ornament}>BALI WEDDING</Text>
                <Text style={styles.names}>Wira & Ayu</Text>
              </View>
            </Pressable>
            <View style={styles.meta}>
              <Text style={styles.name}>{template.name}</Text>
              <Text style={styles.concept}>{conceptFor(template.slug)}</Text>
              <Text style={styles.detail}>{template.is_premium ? 'Premium' : 'Gratis'} | Foto, animasi, galeri</Text>
              <View style={styles.actions}>
                <Pressable onPress={() => navigation.navigate('TemplatePreview', { template })} style={styles.reviewButton}>
                  <Text style={styles.reviewText}>Lihat Preview</Text>
                </Pressable>
                <Pressable onPress={() => setSelected(template)} style={[styles.chooseButton, active && styles.chooseActive]}>
                  <Text style={[styles.chooseText, active && styles.chooseTextActive]}>{active ? 'Dipilih' : 'Gunakan'}</Text>
                </Pressable>
              </View>
            </View>
          </View>
        );
      })}
    </WizardLayout>
  );
}

function imageUrl(path) {
  return `${api.siteUrl}/storage/${path}`;
}

function conceptFor(slug) {
  return {
    'bali-classic': 'Elegan gelap, nuansa upacara dan emas.',
    'pura-sunset': 'Sinematik senja, lilin, kutipan Weda, countdown.',
    'ubud-garden': 'Editorial terang, taman Ubud dan warna natural.',
  }[slug] || 'Undangan pernikahan bernuansa Bali.';
}

const styles = StyleSheet.create({
  template: {
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.surface,
    borderRadius: 20,
    overflow: 'hidden',
    marginBottom: spacing.md,
  },
  active: {
    borderColor: colors.gold,
  },
  preview: {
    height: 180,
    width: '100%',
  },
  overlay: {
    ...StyleSheet.absoluteFillObject,
    height: 180,
    backgroundColor: 'rgba(16, 10, 7, .42)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  ornament: {
    color: colors.gold,
    letterSpacing: 5,
    fontSize: 10,
  },
  names: {
    color: colors.goldLight,
    marginTop: spacing.md,
    fontSize: 28,
  },
  meta: {
    padding: spacing.md,
  },
  name: {
    color: colors.text,
    fontSize: 18,
    fontWeight: '600',
  },
  detail: {
    color: colors.muted,
    marginTop: spacing.xs,
  },
  concept: {
    color: colors.goldLight,
    lineHeight: 20,
    marginTop: spacing.xs,
  },
  actions: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.md,
  },
  reviewButton: {
    flex: 1,
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: colors.gold,
  },
  reviewText: {
    color: colors.goldLight,
    fontWeight: '600',
  },
  chooseButton: {
    flex: 1,
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 12,
    backgroundColor: colors.surfaceAlt,
  },
  chooseActive: {
    backgroundColor: colors.gold,
  },
  chooseText: {
    color: colors.text,
    fontWeight: '600',
  },
  chooseTextActive: {
    color: colors.background,
  },
});
