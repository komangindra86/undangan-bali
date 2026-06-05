import DateTimePicker from '@react-native-community/datetimepicker';
import { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, spacing } from '../theme';

export default function DateTimeField({ label, mode, value, onChange, optional = false, minimumDate }) {
  const [show, setShow] = useState(false);
  const selectedValue = parsePickerValue(value, mode);

  function handleChange(event, selected) {
    setShow(false);
    if (event.type === 'set' && selected) {
      onChange(formatPickerValue(selected, mode));
    }
  }

  return (
    <View style={styles.group}>
      <Text style={styles.label}>{label}</Text>
      <Pressable onPress={() => setShow(true)} style={styles.input}>
        <Text style={value ? styles.value : styles.placeholder}>
          {value ? displayPickerValue(value, mode) : mode === 'date' ? 'Pilih tanggal' : 'Pilih jam'}
        </Text>
      </Pressable>
      {optional && value ? (
        <Pressable onPress={() => onChange('')}>
          <Text style={styles.clear}>Hapus jam selesai</Text>
        </Pressable>
      ) : null}
      {show ? (
        <DateTimePicker
          value={selectedValue}
          mode={mode}
          display="default"
          is24Hour
          minimumDate={minimumDate}
          onChange={handleChange}
        />
      ) : null}
    </View>
  );
}

function parsePickerValue(value, mode) {
  if (mode === 'date' && value) {
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, month - 1, day);
  }
  if (mode === 'time' && value) {
    const [hours, minutes] = value.split(':').map(Number);
    const date = new Date();
    date.setHours(hours, minutes, 0, 0);
    return date;
  }
  return new Date();
}

function formatPickerValue(date, mode) {
  if (mode === 'date') {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  }
  return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

function displayPickerValue(value, mode) {
  if (mode === 'date') {
    const [year, month, day] = value.split('-');
    return `${day}/${month}/${year}`;
  }
  return `${value} WITA`;
}

const styles = StyleSheet.create({
  group: {
    marginBottom: spacing.md,
  },
  label: {
    color: colors.goldLight,
    fontWeight: '600',
    marginBottom: spacing.xs,
  },
  input: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 14,
    borderWidth: 1,
    justifyContent: 'center',
    minHeight: 52,
    paddingHorizontal: spacing.md,
  },
  value: {
    color: colors.text,
    fontSize: 15,
  },
  placeholder: {
    color: colors.muted,
    fontSize: 15,
  },
  clear: {
    color: colors.goldLight,
    fontSize: 13,
    marginTop: spacing.xs,
  },
});
