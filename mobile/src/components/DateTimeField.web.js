import { createElement } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, spacing } from '../theme';

export default function DateTimeField({ label, mode, value, onChange, optional = false }) {
  return (
    <View style={styles.group}>
      <Text style={styles.label}>{label}</Text>
      {createElement('input', {
        type: mode,
        value: value || '',
        onChange: (event) => onChange(event.target.value),
        style: webInputStyle,
        'aria-label': label,
      })}
      {optional && value ? (
        <Pressable onPress={() => onChange('')}>
          <Text style={styles.clear}>Hapus jam selesai</Text>
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
