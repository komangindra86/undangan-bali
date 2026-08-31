import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions } from '../components/Buttons';
import CustomInvitationCard from '../components/CustomInvitationCard';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { api } from '../services/api';
import { isBirthday, personScreenFor } from '../constants/invitation';
import { colors, spacing } from '../theme';

export default function TemplateScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const birthday = isBirthday(draft);
  const [templates, setTemplates] = useState([]);
  const [selected, setSelected] = useState(draft.selected_template);
  const [choosingId, setChoosingId] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.templates(draft.invitation_type || 'wedding')
      .then((response) => setTemplates(response.data))
      .catch((error) => Alert.alert('Template belum termuat', error.message))
      .finally(() => setLoading(false));
  }, [draft.invitation_type]);

  async function useTemplate(template = selected) {
    if (!template) {
      Alert.alert('Pilih template', 'Silakan review lalu pilih satu desain untuk melanjutkan.');
      return;
    }

    setSelected(template);
    setChoosingId(template.id);
    try {
      await saveSection('selected_template', template);
      navigation.navigate(personScreenFor(draft));
    } catch (error) {
      Alert.alert('Template belum tersimpan', error.message);
    } finally {
      setChoosingId(null);
    }
  }

  return (
    <WizardLayout
      step={1}
      title={birthday ? 'Pilih suasana perayaan' : 'Pilih nuansa Bali'}
      subtitle="Lihat preview lengkap dengan data dummy, foto, galeri, dan animasi sebelum memutuskan desain."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={() => useTemplate()} loading={syncing || choosingId != null} />}
    >
      {loading ? <ActivityIndicator color={colors.gold} /> : null}
      {templates.map((template) => {
        const active = selected?.id === template.id;
        return (
          <View key={template.id} style={[styles.template, active && styles.active]}>
            <Pressable onPress={() => navigation.navigate('TemplatePreview', { template })}>
              {birthday ? (
                <View style={[styles.birthdayPreview, { backgroundColor: ({ 'ceria-confetti': '#f8d6e8', 'ruang-putih': '#faf9f6', 'bali-pradnyan': '#163c31' })[template.slug] || colors.surface }]}>
                  <Text style={[styles.ornament, { color: template.slug === 'bali-pradnyan' ? '#ecd19d' : '#5d4377' }]}>SELAMAT ULANG TAHUN</Text>
                  <Text style={[styles.names, { color: template.slug === 'bali-pradnyan' ? '#ecd19d' : '#425d61', fontWeight: template.slug === 'ceria-confetti' ? '900' : '400' }]}>Kirana</Text>
                  <Text style={{ color: template.slug === 'bali-pradnyan' ? '#ecd19d' : '#5d4377', marginTop: 12 }}>7 tahun penuh cerita</Text>
                </View>
              ) : <>
                <Image source={{ uri: imageUrl(template.thumbnail) }} style={styles.preview} />
                <View style={styles.overlay}>
                  <Text style={styles.ornament}>BALI WEDDING</Text>
                  <Text style={styles.names}>Wira & Ayu</Text>
                </View>
              </>}
            </Pressable>
            <View style={styles.meta}>
              <Text style={styles.name}>{template.name}</Text>
              <Text style={styles.concept}>{conceptFor(template.slug)}</Text>
              <Text style={styles.detail}>{template.is_premium ? 'Premium' : 'Gratis'} | Foto, animasi, galeri</Text>
              <View style={styles.actions}>
                <Pressable
                  accessibilityRole="button"
                  disabled={choosingId != null}
                  onPress={() => navigation.navigate('TemplatePreview', { template })}
                  style={styles.reviewButton}
                >
                  <Text style={styles.reviewText}>Lihat Preview</Text>
                </Pressable>
                <Pressable
                  accessibilityRole="button"
                  disabled={choosingId != null}
                  onPress={() => useTemplate(template)}
                  style={[styles.chooseButton, active && styles.chooseActive, choosingId != null && styles.disabled]}
                >
                  {choosingId === template.id ? (
                    <ActivityIndicator color={colors.background} size="small" />
                  ) : (
                    <Text style={[styles.chooseText, active && styles.chooseTextActive]}>{active ? 'Lanjutkan' : 'Gunakan'}</Text>
                  )}
                </Pressable>
              </View>
            </View>
          </View>
        );
      })}
      {!loading ? <CustomInvitationCard compact style={styles.customCard} /> : null}
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
    'royal-kamasan': 'Adat mewah, patra emas, songket gelap, countdown.',
    'puspa-kencana': 'Ivory hangat, ukiran Bali, bunga kamboja, dan kilau emas.',
    'ceria-confetti': 'Warna ceria, kartu foto playful, dan suasana pesta.',
    'ruang-putih': 'Minimalis, ruang lega, tipografi editorial yang tenang.',
    'bali-pradnyan': 'Hijau dalam, ornamen emas dan kehangatan perayaan Bali.',
  }[slug] || 'Undangan pernikahan bernuansa Bali.';
}

const styles = StyleSheet.create({
  birthdayPreview: { height: 180, alignItems: 'center', justifyContent: 'center', padding: spacing.md },
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
  disabled: {
    opacity: 0.55,
  },
  customCard: {
    marginBottom: spacing.lg,
  },
});
