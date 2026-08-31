import { useState } from 'react';
import { Alert, Text } from 'react-native';
import { FooterActions } from '../components/Buttons';
import FormField from '../components/FormField';
import PhotoField from '../components/PhotoField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { pickProfilePhoto } from '../services/imageService';
import { colors, spacing } from '../theme';
import { cleanText, firstError, validateName, validateNickname, validateSafeText, validateBirthdayAge } from '../utils/validation';

export default function BirthdayFormScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [person, setPerson] = useState(draft.birthday_data || {});
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);
  const update = (field, value) => setPerson((current) => ({ ...current, [field]: value }));

  async function choosePhoto() {
    try { const photo = await pickProfilePhoto(); if (photo) update('celebrant_photo', photo); }
    catch (error) { Alert.alert('Foto belum dapat dipilih', error.message); }
  }

  async function next() {
    setFormError(null);
    const error = firstError([
      validateName(person.celebrant_full_name, 'Nama lengkap yang berulang tahun', { required: true }),
      validateNickname(person.celebrant_nickname, 'Nama panggilan yang berulang tahun'),
      validateBirthdayAge(person.celebrant_age),
      validateSafeText(person.host_name, 'Nama pengundang', { max: 80 }),
    ]);
    if (error) { setFormError(error); Alert.alert('Periksa data ulang tahun', error); return; }
    setSaving(true);
    try {
      await saveSection('birthday_data', { ...person,
        celebrant_full_name: cleanText(person.celebrant_full_name),
        celebrant_nickname: cleanText(person.celebrant_nickname),
        celebrant_age: cleanText(person.celebrant_age),
        host_name: cleanText(person.host_name),
      });
      navigation.navigate('EventForm');
    } catch (error) { Alert.alert('Data belum tersimpan', error.message); }
    finally { setSaving(false); }
  }

  return (
    <WizardLayout step={2} title="Siapa yang berulang tahun?" subtitle="Cukup isi nama dan foto. Usia boleh dikosongkan; tanggal lahir lengkap tidak diperlukan." syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={saving || syncing} />}>
      {formError ? <Text accessibilityRole="alert" style={{ color: colors.danger, marginBottom: spacing.md }}>{formError}</Text> : null}
      <PhotoField label="Foto utama (opsional)" photo={person.celebrant_photo} onPick={choosePhoto} />
      <FormField label="Nama lengkap yang berulang tahun *" maxLength={80} value={person.celebrant_full_name} onChangeText={(v) => update('celebrant_full_name', v)} />
      <FormField label="Nama panggilan *" maxLength={18} helperText="Maksimal 18 karakter agar cover tetap rapi." value={person.celebrant_nickname} onChangeText={(v) => update('celebrant_nickname', v)} />
      <FormField label="Usia yang dirayakan (opsional)" keyboardType="number-pad" maxLength={3} helperText="Kosongkan jika tidak ingin ditampilkan. Ini bukan tanggal lahir." value={String(person.celebrant_age || '')} onChangeText={(v) => update('celebrant_age', v)} />
      <FormField label="Nama pengundang (opsional)" maxLength={80} placeholder="Contoh: Papa & Mama" value={person.host_name} onChangeText={(v) => update('host_name', v)} />
    </WizardLayout>
  );
}
