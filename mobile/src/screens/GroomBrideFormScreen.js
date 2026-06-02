import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { FooterActions } from '../components/Buttons';
import FormField from '../components/FormField';
import PhotoField from '../components/PhotoField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { pickProfilePhoto } from '../services/imageService';
import { colors, spacing } from '../theme';

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
    if (!groom.groom_full_name || !groom.groom_nickname || !bride.bride_full_name || !bride.bride_nickname) {
      Alert.alert('Data belum lengkap', 'Nama lengkap dan nama panggilan kedua mempelai wajib diisi.');
      return;
    }
    await saveSections({ groom_data: groom, bride_data: bride });
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
      <FormField label="Nama lengkap *" value={groom.groom_full_name} onChangeText={(value) => setGroom({ ...groom, groom_full_name: value })} />
      <FormField label="Nama panggilan *" value={groom.groom_nickname} onChangeText={(value) => setGroom({ ...groom, groom_nickname: value })} />
      <FormField label="Nama ayah" value={groom.groom_father_name} onChangeText={(value) => setGroom({ ...groom, groom_father_name: value })} />
      <FormField label="Nama ibu" value={groom.groom_mother_name} onChangeText={(value) => setGroom({ ...groom, groom_mother_name: value })} />
      <FormField label="Anak ke-" value={groom.groom_child_order} onChangeText={(value) => setGroom({ ...groom, groom_child_order: value })} />

      <Text style={[styles.section, styles.bride]}>Mempelai Wanita</Text>
      <PhotoField label="Foto mempelai wanita" photo={bride.bride_photo} onPick={() => selectPhoto('bride')} />
      <FormField label="Nama lengkap *" value={bride.bride_full_name} onChangeText={(value) => setBride({ ...bride, bride_full_name: value })} />
      <FormField label="Nama panggilan *" value={bride.bride_nickname} onChangeText={(value) => setBride({ ...bride, bride_nickname: value })} />
      <FormField label="Nama ayah" value={bride.bride_father_name} onChangeText={(value) => setBride({ ...bride, bride_father_name: value })} />
      <FormField label="Nama ibu" value={bride.bride_mother_name} onChangeText={(value) => setBride({ ...bride, bride_mother_name: value })} />
      <FormField label="Anak ke-" value={bride.bride_child_order} onChangeText={(value) => setBride({ ...bride, bride_child_order: value })} />
    </WizardLayout>
  );
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
