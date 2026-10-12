import { useState } from 'react';
import { Alert, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import WizardLayout from '../components/WizardLayout';
import { invitationName, MAX_PARTICIPANTS } from '../constants/invitation';
import { useDraft } from '../context/DraftContext';
import { pickProfilePhoto } from '../services/imageService';
import { colors, spacing } from '../theme';
import { cleanText, firstError, MAX_NICKNAME_LENGTH, validateName, validateSafeText, validateWholeNumber } from '../utils/validation';

const GENDERS = [{ value: 'putra', label: 'Putra' }, { value: 'putri', label: 'Putri' }];
const FAMILY_MODES = [
  { value: 'single', title: 'Satu keluarga', body: 'Kakak-adik dengan satu ayah dan ibu.' },
  { value: 'multi', title: 'Beberapa keluarga', body: 'Sepupu atau kerabat yang mepandes bersama.' },
];

// Mepandes is held for one or several people. With one family the parents are entered once (father in
// groom_data, mother in bride_data); with several, each participant names their own and a host invites.
export default function MepandesFormScreen({ navigation }) {
  const { draft, saveSections, syncing, syncMessage } = useDraft();
  const saved = draft.mepandes_data || {};
  const [mode, setMode] = useState(saved.family_mode || 'single');
  const [hostName, setHostName] = useState(saved.host_name || '');
  const [father, setFather] = useState(draft.groom_data || {});
  const [mother, setMother] = useState(draft.bride_data || {});
  const [people, setPeople] = useState(saved.participants?.length ? saved.participants : [{}]);
  const [formError, setFormError] = useState(null);
  const multi = mode === 'multi';
  const title = invitationName({ ...draft, mepandes_data: { participants: people } });

  function updatePerson(index, changes) {
    setPeople((current) => current.map((person, position) => (position === index ? { ...person, ...changes } : person)));
  }

  async function choosePhoto(index) {
    try {
      const photo = await pickProfilePhoto();
      if (photo) updatePerson(index, { photo });
    } catch (error) {
      Alert.alert('Foto belum dapat dipilih', error.message);
    }
  }

  async function next() {
    setFormError(null);
    const error = firstError([
      ...(multi ? [validateSafeText(hostName, 'Nama pengundang', { required: true, max: 80 })] : [
        validateName(father.groom_full_name, 'Nama lengkap ayah', { required: true }),
        validateName(father.groom_nickname, 'Nama panggilan ayah', { required: true, max: MAX_NICKNAME_LENGTH }),
        validateName(mother.bride_full_name, 'Nama lengkap ibu', { required: true }),
        validateName(mother.bride_nickname, 'Nama panggilan ibu', { required: true, max: MAX_NICKNAME_LENGTH }),
      ]),
      ...people.flatMap((person, index) => {
        const who = people.length > 1 ? ` peserta ${index + 1}` : '';
        return [
          validateName(person.full_name, `Nama lengkap${who}`, { required: true }),
          validateName(person.nickname, `Nama panggilan${who}`, { required: true, max: MAX_NICKNAME_LENGTH }),
          validateWholeNumber(person.age, `Usia${who}`, 120),
          ...(multi ? [
            validateName(person.father_name, `Nama ayah${who}`, { required: true }),
            validateName(person.mother_name, `Nama ibu${who}`, { required: true }),
          ] : []),
        ];
      }),
    ]);

    if (error) {
      setFormError(error);
      Alert.alert('Periksa data undangan', error);
      return;
    }

    try {
      await saveSections({
        mepandes_data: {
          family_mode: mode,
          host_name: multi ? cleanText(hostName) : '',
          participants: people.map((person) => ({
            full_name: cleanText(person.full_name),
            nickname: cleanText(person.nickname),
            gender: person.gender || '',
            age: cleanText(person.age),
            father_name: multi ? cleanText(person.father_name) : '',
            mother_name: multi ? cleanText(person.mother_name) : '',
            photo: person.photo || null,
          })),
        },
        groom_data: { ...father, groom_full_name: cleanText(father.groom_full_name), groom_nickname: cleanText(father.groom_nickname) },
        bride_data: { ...mother, bride_full_name: cleanText(mother.bride_full_name), bride_nickname: cleanText(mother.bride_nickname) },
      });
      navigation.navigate('EventForm');
    } catch (saveError) {
      Alert.alert('Data belum tersimpan', saveError.message);
    }
  }

  return (
    <WizardLayout
      step={2}
      title="Yang mepandes"
      subtitle="Isi siapa saja yang diupacarai. Foto tiap peserta boleh ditambahkan atau dikosongkan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      {formError ? <Text accessibilityRole="alert" style={styles.error}>{formError}</Text> : null}

      <Text style={styles.section}>Yang mepandes dari</Text>
      <View style={styles.modes}>
        {FAMILY_MODES.map((option) => {
          const selected = mode === option.value;
          return (
            <Pressable
              accessibilityRole="radio"
              accessibilityState={{ selected }}
              key={option.value}
              onPress={() => setMode(option.value)}
              style={[styles.mode, selected && styles.modeSelected]}
            >
              <Text style={[styles.modeTitle, selected && styles.modeTitleSelected]}>{option.title}</Text>
              <Text style={styles.modeBody}>{option.body}</Text>
            </Pressable>
          );
        })}
      </View>

      {multi ? (
        <FormField label="Nama pengundang *" maxLength={80} placeholder="Contoh: Keluarga Besar I Made Wira" helperText="Tampil di penutup undangan." value={hostName} onChangeText={setHostName} />
      ) : (
        <>
          <Text style={[styles.section, styles.spaced]}>Orang tua</Text>
          <FormField label="Nama lengkap ayah *" maxLength={80} value={father.groom_full_name} onChangeText={(value) => setFather((current) => ({ ...current, groom_full_name: value }))} />
          <FormField label="Nama panggilan ayah *" maxLength={MAX_NICKNAME_LENGTH} value={father.groom_nickname} onChangeText={(value) => setFather((current) => ({ ...current, groom_nickname: value }))} />
          <FormField label="Nama lengkap ibu *" maxLength={80} value={mother.bride_full_name} onChangeText={(value) => setMother((current) => ({ ...current, bride_full_name: value }))} />
          <FormField label="Nama panggilan ibu *" maxLength={MAX_NICKNAME_LENGTH} value={mother.bride_nickname} onChangeText={(value) => setMother((current) => ({ ...current, bride_nickname: value }))} />
        </>
      )}

      <Text style={styles.titlePreview}>Judul undangan: {title}</Text>

      {people.map((person, index) => (
        <View key={index} style={styles.person}>
          <View style={styles.personHeader}>
            <Text style={styles.personTitle}>{people.length > 1 ? `Peserta ${index + 1}` : 'Yang mepandes'}</Text>
            {people.length > 1 ? (
              <Text accessibilityRole="button" onPress={() => setPeople((current) => current.filter((_, position) => position !== index))} style={styles.remove}>Hapus</Text>
            ) : null}
          </View>
          <View style={styles.photoRow}>
            {person.photo?.uri ? <Image source={{ uri: person.photo.uri }} style={styles.photo} /> : <View style={[styles.photo, styles.photoEmpty]}><Text style={styles.photoHelp}>Tanpa foto</Text></View>}
            <View style={styles.photoActions}>
              <SecondaryButton title={person.photo ? 'Ganti Foto' : 'Tambah Foto'} onPress={() => choosePhoto(index)} style={styles.photoButton} />
              {person.photo ? <Text accessibilityRole="button" onPress={() => updatePerson(index, { photo: null })} style={styles.remove}>Hapus foto</Text> : null}
            </View>
          </View>
          <FormField label="Nama lengkap *" maxLength={80} value={person.full_name} onChangeText={(value) => updatePerson(index, { full_name: value })} />
          <FormField label="Nama panggilan *" maxLength={MAX_NICKNAME_LENGTH} helperText="Menjadi judul undangan." value={person.nickname} onChangeText={(value) => updatePerson(index, { nickname: value })} />
          <View style={styles.chips}>
            {GENDERS.map((gender) => {
              const selected = person.gender === gender.value;
              return (
                <Pressable
                  accessibilityRole="button"
                  accessibilityState={{ selected }}
                  key={gender.value}
                  onPress={() => updatePerson(index, { gender: selected ? '' : gender.value })}
                  style={[styles.chip, selected && styles.chipSelected]}
                >
                  <Text style={[styles.chipText, selected && styles.chipTextSelected]}>{gender.label}</Text>
                </Pressable>
              );
            })}
          </View>
          <FormField label="Usia (opsional)" keyboardType="number-pad" maxLength={3} placeholder="Contoh: 17" value={String(person.age || '')} onChangeText={(value) => updatePerson(index, { age: value })} />
          {multi ? (
            <>
              <FormField label="Nama ayah *" maxLength={80} value={person.father_name} onChangeText={(value) => updatePerson(index, { father_name: value })} />
              <FormField label="Nama ibu *" maxLength={80} value={person.mother_name} onChangeText={(value) => updatePerson(index, { mother_name: value })} />
            </>
          ) : null}
        </View>
      ))}

      {people.length < MAX_PARTICIPANTS ? (
        <SecondaryButton title="+ Tambah yang mepandes" onPress={() => setPeople((current) => [...current, {}])} />
      ) : (
        <Text style={styles.titlePreview}>Paling banyak {MAX_PARTICIPANTS} orang dalam satu undangan.</Text>
      )}
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  error: { color: colors.danger, marginBottom: spacing.md },
  section: { color: colors.gold, fontSize: 16, fontWeight: '700', marginBottom: spacing.md },
  spaced: { marginTop: spacing.lg },
  modes: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.lg },
  mode: { borderColor: colors.border, borderRadius: 18, borderWidth: 1, flex: 1, padding: spacing.md },
  modeSelected: { backgroundColor: colors.surfaceAlt, borderColor: colors.gold },
  modeTitle: { color: colors.text, fontWeight: '700', marginBottom: 4 },
  modeTitleSelected: { color: colors.gold },
  modeBody: { color: colors.muted, fontSize: 12, lineHeight: 17 },
  titlePreview: { color: colors.muted, fontSize: 13, marginBottom: spacing.md, marginTop: spacing.sm },
  person: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 20, borderWidth: 1, marginBottom: spacing.md, padding: spacing.md },
  personHeader: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.md },
  personTitle: { color: colors.gold, fontSize: 15, fontWeight: '700' },
  remove: { color: colors.danger, fontSize: 13, fontWeight: '600', paddingVertical: 6 },
  photoRow: { alignItems: 'center', flexDirection: 'row', gap: spacing.md, marginBottom: spacing.md },
  photo: { borderRadius: 16, height: 76, width: 76 },
  photoEmpty: { alignItems: 'center', borderColor: colors.border, borderStyle: 'dashed', borderWidth: 1, justifyContent: 'center' },
  photoHelp: { color: colors.muted, fontSize: 11 },
  photoActions: { alignItems: 'flex-start', flex: 1, gap: 2 },
  photoButton: { minHeight: 42 },
  chips: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.md },
  chip: { borderColor: colors.border, borderRadius: 99, borderWidth: 1, paddingHorizontal: spacing.lg, paddingVertical: spacing.sm },
  chipSelected: { backgroundColor: colors.gold, borderColor: colors.gold },
  chipText: { color: colors.text, fontWeight: '700' },
  chipTextSelected: { color: colors.background },
});
