import { createElement } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, spacing } from '../theme';

export default function DateTimeField({ label, mode, value, onChange, optional = false, minimumDate, maximumDate, clearLabel = 'Hapus jam selesai' }) {
  const inputDate = (date) => (date && mode === 'date'
    ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    : undefined);
  const minDate = inputDate(minimumDate);
  return (
    <View style={styles.group}>
      <Text style={styles.label}>{label}</Text>
      {createElement('input', {
        type: mode,
        value: value || '',
        min: minDate,
        max: inputDate(maximumDate),
        onInput: (event) => onChange(event.target.value),
        onChange: (event) => onChange(event.target.value),
        style: webInputStyle,
        'aria-label': label,
      })}
      {optional && value ? (
        <Pressable onPress={() => onChange('')}>
          <Text style={styles.clear}>{clearLabel}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

const webInputStyle = {
  backgroundColor: colors.surface,
  border: `1px solid ${colors.border}`,
  borderRadius: 14,
  boxSizing: 'border-box',
  color: colors.text,
  colorScheme: 'dark',
  fontFamily: 'inherit',
  fontSize: 15,
  height: 52,
  outline: 'none',
  padding: `0 ${spacing.md}px`,
  width: '100%',
};

const styles = StyleSheet.create({
  group: {
    marginBottom: spacing.md,
  },
  label: {
    color: colors.goldLight,
    fontWeight: '600',
    marginBottom: spacing.xs,
  },
  clear: {
    color: colors.goldLight,
    fontSize: 13,
    marginTop: spacing.xs,
  },
});
