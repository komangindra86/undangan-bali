import { useState } from 'react';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions } from '../components/Buttons';
import DateTimeField from '../components/DateTimeField';
import FormField from '../components/FormField';
import PhotoField from '../components/PhotoField';
import WizardLayout from '../components/WizardLayout';
import { invitationName } from '../constants/invitation';
import { useDraft } from '../context/DraftContext';
import { pickProfilePhoto } from '../services/imageService';
import { colors, spacing } from '../theme';
import { cleanText, firstError, MAX_NICKNAME_LENGTH, validateName, validateSafeText } from '../utils/validation';

const GENDERS = [{ value: 'putra', label: 'Putra' }, { value: 'putri', label: 'Putri' }];

// Shared by every ceremony held for a baby or child. The father is stored in groom_data and the
// mother in bride_data, matching the backend columns; only the parents are required.
export default function ChildCeremonyFormScreen({ navigation }) {
  const { draft, saveSections, syncing, syncMessage } = useDraft();
  const [father, setFather] = useState(draft.groom_data || {});
  const [mother, setMother] = useState(draft.bride_data || {});
  const [child, setChild] = useState(draft.child_data || {});
  const [formError, setFormError] = useState(null);
  const title = invitationName({ ...draft, groom_data: father, bride_data: mother, child_data: child });

  async function choosePhoto() {
    try {
      const photo = await pickProfilePhoto();
      if (photo) setChild((current) => ({ ...current, child_photo: photo }));
    } catch (error) {
      Alert.alert('Foto belum dapat dipilih', error.message);
    }
  }

  async function next() {
    setFormError(null);
    const error = firstError([
      validateName(father.groom_full_name, 'Nama lengkap ayah', { required: true }),
      validateName(father.groom_nickname, 'Nama panggilan ayah', { required: true, max: MAX_NICKNAME_LENGTH }),
      validateName(mother.bride_full_name, 'Nama lengkap ibu', { required: true }),
      validateName(mother.bride_nickname, 'Nama panggilan ibu', { required: true, max: MAX_NICKNAME_LENGTH }),
      validateName(child.child_full_name, 'Nama lengkap buah hati'),
      validateName(child.child_nickname, 'Nama panggilan buah hati', { max: MAX_NICKNAME_LENGTH }),
      validateSafeText(child.child_order, 'Anak ke-', { max: 50 }),
    ]);

    if (error) {
      setFormError(error);
      Alert.alert('Periksa data undangan', error);
      return;
    }

    try {
      await saveSections({
        groom_data: { ...father, groom_full_name: cleanText(father.groom_full_name), groom_nickname: cleanText(father.groom_nickname) },
        bride_data: { ...mother, bride_full_name: cleanText(mother.bride_full_name), bride_nickname: cleanText(mother.bride_nickname) },
        child_data: {
          ...child,
          child_full_name: cleanText(child.child_full_name),
          child_nickname: cleanText(child.child_nickname),
          child_gender: child.child_gender || '',
          child_order: cleanText(child.child_order),
          child_birth_date: child.child_birth_date || '',
        },
      });
      navigation.navigate('EventForm');
    } catch (saveError) {
      Alert.alert('Data belum tersimpan', saveError.message);
    }
  }

  return (
    <WizardLayout
      step={2}
      title="Buah hati dan orang tua"
      subtitle="Nama ayah dan ibu wajib diisi. Data buah hati boleh dikosongkan bila belum ada, misalnya nama yang belum diresmikan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      {formError ? <Text accessibilityRole="alert" style={styles.error}>{formError}</Text> : null}
      <Text style={styles.section}>Ayah</Text>
      <FormField label="Nama lengkap ayah *" maxLength={80} value={father.groom_full_name} onChangeText={(value) => setFather({ ...father, groom_full_name: value })} />
      <FormField label="Nama panggilan ayah *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={father.groom_nickname} onChangeText={(value) => setFather({ ...father, groom_nickname: value })} />

      <Text style={[styles.section, styles.spaced]}>Ibu</Text>
      <FormField label="Nama lengkap ibu *" maxLength={80} value={mother.bride_full_name} onChangeText={(value) => setMother({ ...mother, bride_full_name: value })} />
      <FormField label="Nama panggilan ibu *" maxLength={MAX_NICKNAME_LENGTH} helperText={`Maksimal ${MAX_NICKNAME_LENGTH} karakter agar desain tetap rapi.`} value={mother.bride_nickname} onChangeText={(value) => setMother({ ...mother, bride_nickname: value })} />

      <Text style={[styles.section, styles.spaced]}>Buah hati (opsional)</Text>
      <Text style={styles.label}>Putra atau putri</Text>
      <View style={styles.chips}>
        {GENDERS.map((gender) => {
          const selected = child.child_gender === gender.value;
          return (
            <Pressable
              accessibilityRole="button"
              accessibilityState={{ selected }}
              key={gender.value}
              onPress={() => setChild({ ...child, child_gender: selected ? '' : gender.value })}
              style={[styles.chip, selected && styles.chipSelected]}
            >
              <Text style={[styles.chipText, selected && styles.chipTextSelected]}>{gender.label}</Text>
            </Pressable>
          );
        })}
      </View>
      <Text style={styles.titlePreview}>Judul undangan: {title}</Text>
      <PhotoField label="Foto buah hati (opsional)" photo={child.child_photo} onPick={choosePhoto} />
      <FormField label="Nama lengkap buah hati (opsional)" maxLength={80} value={child.child_full_name} onChangeText={(value) => setChild({ ...child, child_full_name: value })} />
      <FormField label="Nama panggilan buah hati (opsional)" maxLength={MAX_NICKNAME_LENGTH} value={child.child_nickname} onChangeText={(value) => setChild({ ...child, child_nickname: value })} />
      <FormField label="Anak ke- (opsional)" maxLength={50} placeholder="Contoh: Anak pertama" value={child.child_order} onChangeText={(value) => setChild({ ...child, child_order: value })} />
      <DateTimeField label="Tanggal lahir (opsional)" mode="date" optional clearLabel="Hapus tanggal lahir" maximumDate={new Date()} value={child.child_birth_date} onChange={(value) => setChild({ ...child, child_birth_date: value })} />
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  error: { color: colors.danger, marginBottom: spacing.md },
  section: { color: colors.gold, fontSize: 16, fontWeight: '700', marginBottom: spacing.md },
  spaced: { marginTop: spacing.lg },
  label: { color: colors.goldLight, fontSize: 13, fontWeight: '600', marginBottom: spacing.sm },
  chips: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.sm },
  chip: { borderColor: colors.border, borderRadius: 99, borderWidth: 1, paddingHorizontal: spacing.lg, paddingVertical: spacing.sm },
  chipSelected: { backgroundColor: colors.gold, borderColor: colors.gold },
  chipText: { color: colors.text, fontWeight: '700' },
  chipTextSelected: { color: colors.background },
  titlePreview: { color: colors.muted, fontSize: 13, marginBottom: spacing.md },
});
