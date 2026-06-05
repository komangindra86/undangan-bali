import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { FooterActions } from '../components/Buttons';
import FormField from '../components/FormField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { colors, spacing } from '../theme';
import { cleanText } from '../utils/validation';

export default function LocationScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [location, setLocation] = useState(draft.location_data);

  async function next() {
    const googleMapsUrl = cleanText(location.google_maps_url);
    const lat = cleanText(location.latitude);
    const lng = cleanText(location.longitude);
    if (googleMapsUrl && !/^https:\/\/(www\.)?(google\.[a-z.]+\/maps|maps\.app\.goo\.gl|maps\.google\.[a-z.]+)/i.test(googleMapsUrl)) {
      Alert.alert('Link Google Maps tidak valid', 'Gunakan link Google Maps yang diawali https://maps.google... atau https://maps.app.goo.gl/...');
      return;
    }
    if ((lat && !lng) || (!lat && lng)) {
      Alert.alert('Pin belum lengkap', 'Latitude dan longitude harus diisi bersama-sama.');
      return;
    }
    if (lat && (!isCoordinate(lat) || !isCoordinate(lng) || Number(lat) < -90 || Number(lat) > 90 || Number(lng) < -180 || Number(lng) > 180)) {
      Alert.alert('Koordinat tidak valid', 'Periksa kembali latitude dan longitude lokasi.');
      return;
    }
    await saveSection('location_data', {
      ...location,
      google_maps_url: googleMapsUrl,
      latitude: lat || null,
      longitude: lng || null,
    });
    navigation.navigate('Gallery');
  }

  return (
    <WizardLayout
      step={4}
      title="Lokasi acara"
      subtitle="Masukkan link Google Maps. Pin lokasi bersifat opsional agar tamu lebih mudah menemukan tempat."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <FormField
        label="Link Google Maps"
        placeholder="https://maps.google.com/..."
        value={location.google_maps_url}
        onChangeText={(value) => setLocation({ ...location, google_maps_url: value })}
        keyboardType="url"
      />
      <Text style={styles.pinTitle}>Pin lokasi opsional</Text>
      <FormField
        label="Latitude"
        placeholder="-8.5069"
        value={String(location.latitude || '')}
        onChangeText={(value) => setLocation({ ...location, latitude: value })}
        keyboardType="decimal-pad"
      />
      <FormField
        label="Longitude"
        placeholder="115.2625"
        value={String(location.longitude || '')}
        onChangeText={(value) => setLocation({ ...location, longitude: value })}
        keyboardType="decimal-pad"
      />
      <Text style={styles.help}>Anda dapat menyalin koordinat dari pin Google Maps.</Text>
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  pinTitle: {
    color: colors.gold,
    fontWeight: '700',
    fontSize: 16,
    marginTop: spacing.sm,
    marginBottom: spacing.md,
  },
  help: {
    color: colors.muted,
    fontSize: 13,
  },
});

function isCoordinate(value) {
  return /^-?\d+(\.\d+)?$/.test(value);
}
