import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, spacing } from '../theme';

export function PrimaryButton({ title, onPress, loading = false, disabled = false, style }) {
  return (
    <Pressable
      disabled={loading || disabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.primary,
        pressed && styles.pressed,
        (loading || disabled) && styles.disabled,
        style,
      ]}
    >
      {loading ? <ActivityIndicator color={colors.background} /> : <Text style={styles.primaryText}>{title}</Text>}
    </Pressable>
  );
}

export function SecondaryButton({ title, onPress, style }) {
  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.secondary, pressed && styles.pressed, style]}>
      <Text style={styles.secondaryText}>{title}</Text>
    </Pressable>
  );
}

export function FooterActions({ onBack, onNext, nextTitle = 'Lanjut', loading }) {
  return (
    <View style={styles.footer}>
      <SecondaryButton title="Kembali" onPress={onBack} style={styles.back} />
      <PrimaryButton title={nextTitle} onPress={onNext} loading={loading} style={styles.next} />
    </View>
  );
}

const styles = StyleSheet.create({
  primary: {
    minHeight: 54,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    backgroundColor: colors.gold,
  },
  primaryText: {
    color: colors.background,
    fontSize: 16,
    fontWeight: '700',
  },
  secondary: {
    minHeight: 54,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    borderColor: colors.border,
    borderWidth: 1,
  },
  secondaryText: {
    color: colors.goldLight,
    fontSize: 16,
    fontWeight: '600',
  },
  pressed: {
    opacity: 0.78,
  },
  disabled: {
    opacity: 0.55,
  },
  footer: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.lg,
  },
  back: {
    flex: 1,
  },
  next: {
    flex: 1.35,
  },
});
