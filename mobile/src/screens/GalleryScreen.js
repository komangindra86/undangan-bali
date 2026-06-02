import { useState } from 'react';
import { Alert, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions, SecondaryButton } from '../components/Buttons';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { pickGalleryPhotos } from '../services/imageService';
import { colors, spacing } from '../theme';

const MAX_PHOTOS = 6;

export default function GalleryScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [photos, setPhotos] = useState(draft.gallery_data?.photos || []);

  async function addPhotos() {
    const remaining = MAX_PHOTOS - photos.length;
    if (!remaining) {
      Alert.alert('Galeri penuh', `Maksimal ${MAX_PHOTOS} foto untuk menjaga undangan tetap ringan.`);
      return;
    }

    const selected = await pickGalleryPhotos(remaining);
    setPhotos([...photos, ...selected]);
  }

  async function next() {
    await saveSection('gallery_data', { photos });
    navigation.navigate('Music');
  }

  return (
    <WizardLayout
      step={5}
      title="Galeri foto"
      subtitle="Tambahkan hingga 6 foto pilihan. Foto otomatis diperkecil agar undangan cepat dibuka."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <SecondaryButton title="Tambah Foto Galeri" onPress={addPhotos} style={styles.addButton} />
      <Text style={styles.counter}>{photos.length} / {MAX_PHOTOS} foto dipilih</Text>
      <View style={styles.grid}>
        {photos.map((photo, index) => (
          <View key={`${photo.uri}-${index}`} style={styles.tile}>
            <Image source={{ uri: photo.uri }} style={styles.image} />
            <Pressable onPress={() => setPhotos(photos.filter((_, photoIndex) => photoIndex !== index))} style={styles.remove}>
              <Text style={styles.removeText}>Hapus</Text>
            </Pressable>
          </View>
        ))}
      </View>
      {!photos.length ? <Text style={styles.empty}>Galeri opsional, tetapi sangat disarankan agar undangan terasa personal.</Text> : null}
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  addButton: {
    marginBottom: spacing.md,
  },
  counter: {
    color: colors.muted,
    marginBottom: spacing.md,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  tile: {
    width: '48%',
  },
  image: {
    aspectRatio: 1,
    borderRadius: 14,
    width: '100%',
  },
  remove: {
    alignItems: 'center',
    paddingVertical: spacing.sm,
  },
  removeText: {
    color: colors.danger,
    fontWeight: '600',
  },
  empty: {
    color: colors.muted,
    lineHeight: 20,
    marginTop: spacing.lg,
    textAlign: 'center',
  },
});
