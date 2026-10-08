import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { FooterActions } from '../components/Buttons';
import FormField from '../components/FormField';
import PhotoField from '../components/PhotoField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { pickProfilePhoto } from '../services/imageService';
import { colors, spacing } from '../theme';
import { cleanText, firstError, MAX_NICKNAME_LENGTH, validateName, validateNickname, validateSafeText } from '../utils/validation';

// The expectant mother is stored in bride_data and the father in groom_data, matching the backend columns.
export default function MegedongFormScreen({ navigation }) {
  const { draft, saveSections, syncing, syncMessage } = useDraft();
  const [mother, setMother] = useState(draft.bride_data || {});
  const [father, setFather] = useState(draft.groom_data || {});
  const [details, setDetails] = useState(draft.megedong_data || {});
  const [formError, setFormError] = useState(null);

  async function selectPhoto(side) {
    try {
      const photo = await pickProfilePhoto();
      if (!photo) return;
      if (side === 'mother') setMother((current) => ({ ...current, bride_photo: photo }));
      else setFather((current) => ({ ...current, groom_photo: photo }));
    } catch (error) {
      Alert.alert('Foto belum dapat dipilih', error.message);
    }
  }

  async function next() {
    setFormError(null);
    const error = firstError([
      validateName(mother.bride_full_name, 'Nama lengkap calon ibu', { required: true }),
      validateNickname(mother.bride_nickname, 'Nama panggilan calon ibu'),
      validateName(father.groom_full_name, 'Nama lengkap calon ayah', { required: true }),
      validateNickname(father.groom_nickname, 'Nama panggilan calon ayah'),
      validateSafeText(details.pregnancy_age, 'Usia kandungan', { max: 40 }),
      validateSafeText(details.child_order, 'Anak ke-', { max: 50 }),
    ]);

    if (error) {
      setFormError(error);
      Alert.alert('Periksa data calon orang tua', error);
      return;
    }

    try {
      await saveSections({
        bride_data: { ...mother, bride_full_name: cleanText(mother.bride_full_name), bride_nickname: cleanText(mother.bride_nickname) },
        groom_data: { ...father, groom_full_name: cleanText(father.groom_full_name), groom_nickname: cleanText(father.groom_nickname) },
        megedong_data: { pregnancy_age: cleanText(details.pregnancy_age), child_order: cleanText(details.child_order) },
      });
      navigation.navigate('EventForm');
    } catch (saveError) {
      Alert.alert('Data belum tersimpan', saveError.message);
    }
  }

  return (
    <WizardLayout
      step={2}
      title="Data calon orang tua"
      subtitle="Isi nama calon ibu dan ayah yang akan tampil pada undangan. Foto, usia kandungan, dan anak ke- boleh dikosongkan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      {formError ? <Text accessibilityRole="alert" style={styles.error}>{formError}</Text> : null}
      <Text style={styles.section}>Calon Ibu</Text>
      <PhotoField label="Foto calon ibu (opsional)" photo={mother.bride_photo} onPick={() => selectPhoto('mother')} />
      <FormField label="Nama lengkap *" maxLength={80} value={mother.bride_full_name} onChangeText={(value) => setMother({ ...mother, bride_full_name: value })} />
      <FormField label="Nama panggilan *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={mother.bride_nickname} onChangeText={(value) => setMother({ ...mother, bride_nickname: value })} />

      <Text style={[styles.section, styles.spaced]}>Calon Ayah</Text>
      <PhotoField label="Foto calon ayah (opsional)" photo={father.groom_photo} onPick={() => selectPhoto('father')} />
      <FormField label="Nama lengkap *" maxLength={80} value={father.groom_full_name} onChangeText={(value) => setFather({ ...father, groom_full_name: value })} />
      <FormField label="Nama panggilan *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={father.groom_nickname} onChangeText={(value) => setFather({ ...father, groom_nickname: value })} />

      <Text style={[styles.section, styles.spaced]}>Kehamilan</Text>
      <FormField label="Usia kandungan (opsional)" maxLength={40} placeholder="Contoh: 7 bulan" value={details.pregnancy_age} onChangeText={(value) => setDetails({ ...details, pregnancy_age: value })} />
      <FormField label="Anak ke- (opsional)" maxLength={50} placeholder="Contoh: Anak pertama" value={details.child_order} onChangeText={(value) => setDetails({ ...details, child_order: value })} />
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  error: { color: colors.danger, marginBottom: spacing.md },
  section: { color: colors.gold, fontSize: 16, fontWeight: '700', marginBottom: spacing.md },
  spaced: { marginTop: spacing.lg },
});
