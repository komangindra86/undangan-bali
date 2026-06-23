import { useRef } from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';
import { useKeyboardAwareScroll } from './KeyboardAwareScrollView';
import { colors, spacing } from '../theme';

export default function FormField({
  label,
  value,
  onChangeText,
  placeholder,
  multiline = false,
  keyboardType,
  secureTextEntry,
  maxLength,
  helperText,
  onFocus,
}) {
  const preventCorrection = keyboardType === 'email-address' || keyboardType === 'url';
  const groupRef = useRef(null);
  const scrollToFocusedInput = useKeyboardAwareScroll();

  return (
    <View ref={groupRef} style={styles.group}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        value={value || ''}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={colors.muted}
        multiline={multiline}
        keyboardType={keyboardType}
        secureTextEntry={secureTextEntry}
        maxLength={maxLength}
        autoCapitalize={preventCorrection ? 'none' : 'sentences'}
        autoCorrect={!preventCorrection}
        onFocus={(event) => {
          scrollToFocusedInput?.(groupRef);
          onFocus?.(event);
        }}
        style={[styles.input, multiline && styles.multiline]}
      />
      {helperText ? <Text style={styles.help}>{helperText}</Text> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  group: {
    marginBottom: spacing.md,
  },
  label: {
    color: colors.goldLight,
    marginBottom: spacing.xs,
    fontWeight: '600',
  },
  input: {
    minHeight: 52,
    paddingHorizontal: spacing.md,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: colors.border,
    color: colors.text,
    backgroundColor: colors.surface,
    fontSize: 15,
  },
  multiline: {
    minHeight: 100,
    paddingTop: spacing.md,
    textAlignVertical: 'top',
  },
  help: {
    color: colors.muted,
    fontSize: 12,
    marginTop: spacing.xs,
  },
});
