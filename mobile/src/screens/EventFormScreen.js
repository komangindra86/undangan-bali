import { useState } from 'react';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions } from '../components/Buttons';
import DateTimeField from '../components/DateTimeField';
import FormField from '../components/FormField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { colors, spacing } from '../theme';

const EVENT_TYPES = ['Pawiwahan', 'Resepsi'];

export default function EventFormScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [event, setEvent] = useState(() => ({
    ...draft.event_data,
    event_type: EVENT_TYPES.includes(draft.event_data?.event_type) ? draft.event_data.event_type : null,
  }));
  const [formError, setFormError] = useState(null);

  async function next() {
    setFormError(null);
    const required = ['event_type', 'event_date', 'start_time', 'venue_name', 'venue_address'];
    if (required.some((field) => !event[field])) {
      const message = 'Pilih jenis acara dan isi tanggal, jam mulai, nama tempat, serta alamat lengkap.';
      setFormError(message);
      Alert.alert('Data acara belum lengkap', message);
      return;
    }

    const eventDate = event.event_date;
    const startTime = event.start_time;
    const endTime = event.end_time || '';
    if (endTime && endTime <= startTime) {
      const message = 'Jam selesai harus setelah jam mulai.';
      setFormError(message);
      Alert.alert('Jam tidak valid', message);
      return;
    }

    try {
      await saveSection('event_data', {
        ...event,
        event_date: eventDate,
        start_time: startTime,
        end_time: endTime || null,
      });
      navigation.navigate('Location');
    } catch (error) {
      const message = 'Data belum berhasil disimpan. Silakan tekan Lanjut sekali lagi.';
      setFormError(message);
      Alert.alert('Penyimpanan gagal', message);
    }
  }

  return (
    <WizardLayout
      step={3}
      title="Detail acara"
      subtitle="Pilih jenis acara, lalu gunakan kalender dan pemilih jam agar jadwal tercatat tepat."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <Text style={styles.label}>Jenis acara *</Text>
      <View style={styles.chips}>
        {EVENT_TYPES.map((type) => (
          <Pressable key={type} onPress={() => setEvent({ ...event, event_type: type })} style={[styles.chip, event.event_type === type && styles.selected]}>
            <Text style={[styles.chipText, event.event_type === type && styles.selectedText]}>{type}</Text>
          </Pressable>
        ))}
      </View>
      {formError ? <Text style={styles.error}>{formError}</Text> : null}
      <DateTimeField label="Tanggal acara *" mode="date" value={event.event_date} onChange={(value) => setEvent({ ...event, event_date: value })} />
      <View style={styles.row}>
        <View style={styles.column}>
          <DateTimeField label="Jam mulai *" mode="time" value={event.start_time} onChange={(value) => setEvent({ ...event, start_time: value })} />
        </View>
        <View style={styles.column}>
          <DateTimeField label="Jam selesai" mode="time" optional value={event.end_time} onChange={(value) => setEvent({ ...event, end_time: value })} />
        </View>
      </View>
      <FormField label="Nama tempat *" value={event.venue_name} onChangeText={(value) => setEvent({ ...event, venue_name: value })} />
      <FormField label="Alamat lengkap *" multiline value={event.venue_address} onChangeText={(value) => setEvent({ ...event, venue_address: value })} />
      <FormField label="Kutipan pembuka" multiline value={event.opening_quote} onChangeText={(value) => setEvent({ ...event, opening_quote: value })} />
    </WizardLayout>
  );
}

const styles = StyleSheet.create({
  label: {
    color: colors.goldLight,
    fontWeight: '600',
    marginBottom: spacing.sm,
  },
  chips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
    marginBottom: spacing.lg,
  },
  chip: {
    borderRadius: 18,
    borderWidth: 1,
    borderColor: colors.border,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  selected: {
    backgroundColor: colors.gold,
    borderColor: colors.gold,
  },
  chipText: {
    color: colors.goldLight,
  },
  selectedText: {
    color: colors.background,
    fontWeight: '700',
  },
  row: {
    flexDirection: 'row',
    gap: spacing.sm,
  },
  column: {
    flex: 1,
  },
  error: {
    backgroundColor: '#412522',
    borderColor: colors.danger,
    borderWidth: 1,
    borderRadius: 12,
    color: '#ffd3cd',
    lineHeight: 20,
    marginBottom: spacing.md,
    padding: spacing.md,
  },
});
