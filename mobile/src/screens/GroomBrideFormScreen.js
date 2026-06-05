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

export default function GroomBrideFormScreen({ navigation }) {
  const { draft, saveSections, syncing, syncMessage } = useDraft();
  const [groom, setGroom] = useState(draft.groom_data);
  const [bride, setBride] = useState(draft.bride_data);

  async function selectPhoto(side) {
    const photo = await pickProfilePhoto();
    if (!photo) {
      return;
    }
    if (side === 'groom') {
      setGroom({ ...groom, groom_photo: photo });
      return;
    }
    setBride({ ...bride, bride_photo: photo });
  }

  async function next() {
    const error = firstError([
      validateName(groom.groom_full_name, 'Nama lengkap mempelai pria', { required: true }),
      validateNickname(groom.groom_nickname, 'Nama panggilan mempelai pria'),
      validateName(groom.groom_father_name, 'Nama ayah mempelai pria'),
      validateName(groom.groom_mother_name, 'Nama ibu mempelai pria'),
      validateSafeText(groom.groom_child_order, 'Anak ke mempelai pria', { max: 50 }),
      validateName(bride.bride_full_name, 'Nama lengkap mempelai wanita', { required: true }),
      validateNickname(bride.bride_nickname, 'Nama panggilan mempelai wanita'),
      validateName(bride.bride_father_name, 'Nama ayah mempelai wanita'),
      validateName(bride.bride_mother_name, 'Nama ibu mempelai wanita'),
      validateSafeText(bride.bride_child_order, 'Anak ke mempelai wanita', { max: 50 }),
    ]);

    if (error) {
      Alert.alert('Periksa data mempelai', error);
      return;
    }
    await saveSections({
      groom_data: normalizePerson(groom, 'groom'),
      bride_data: normalizePerson(bride, 'bride'),
    });
    navigation.navigate('EventForm');
  }

  return (
    <WizardLayout
      step={2}
      title="Data mempelai"
      subtitle="Isi nama yang akan tampil pada halaman undangan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <Text style={styles.section}>Mempelai Pria</Text>
      <PhotoField label="Foto mempelai pria" photo={groom.groom_photo} onPick={() => selectPhoto('groom')} />
      <FormField label="Nama lengkap *" maxLength={80} value={groom.groom_full_name} onChangeText={(value) => setGroom({ ...groom, groom_full_name: value })} />
      <FormField label="Nama panggilan *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={groom.groom_nickname} onChangeText={(value) => setGroom({ ...groom, groom_nickname: value })} />
      <FormField label="Nama ayah" maxLength={80} value={groom.groom_father_name} onChangeText={(value) => setGroom({ ...groom, groom_father_name: value })} />
      <FormField label="Nama ibu" maxLength={80} value={groom.groom_mother_name} onChangeText={(value) => setGroom({ ...groom, groom_mother_name: value })} />
      <FormField label="Anak ke-" maxLength={50} value={groom.groom_child_order} onChangeText={(value) => setGroom({ ...groom, groom_child_order: value })} />

      <Text style={[styles.section, styles.bride]}>Mempelai Wanita</Text>
      <PhotoField label="Foto mempelai wanita" photo={bride.bride_photo} onPick={() => selectPhoto('bride')} />
      <FormField label="Nama lengkap *" maxLength={80} value={bride.bride_full_name} onChangeText={(value) => setBride({ ...bride, bride_full_name: value })} />
      <FormField label="Nama panggilan *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={bride.bride_nickname} onChangeText={(value) => setBride({ ...bride, bride_nickname: value })} />
      <FormField label="Nama ayah" maxLength={80} value={bride.bride_father_name} onChangeText={(value) => setBride({ ...bride, bride_father_name: value })} />
      <FormField label="Nama ibu" maxLength={80} value={bride.bride_mother_name} onChangeText={(value) => setBride({ ...bride, bride_mother_name: value })} />
      <FormField label="Anak ke-" maxLength={50} value={bride.bride_child_order} onChangeText={(value) => setBride({ ...bride, bride_child_order: value })} />
    </WizardLayout>
  );
}

function normalizePerson(values, prefix) {
  return {
    ...values,
    [`${prefix}_full_name`]: cleanText(values[`${prefix}_full_name`]),
    [`${prefix}_nickname`]: cleanText(values[`${prefix}_nickname`]),
    [`${prefix}_father_name`]: cleanText(values[`${prefix}_father_name`]),
    [`${prefix}_mother_name`]: cleanText(values[`${prefix}_mother_name`]),
    [`${prefix}_child_order`]: cleanText(values[`${prefix}_child_order`]),
  };
}

const styles = StyleSheet.create({
  section: {
    color: colors.gold,
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.md,
  },
  bride: {
    marginTop: spacing.lg,
  },
});
