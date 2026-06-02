import { StyleSheet } from 'react-native';

export const colors = {
  background: '#15110d',
  surface: '#211a14',
  surfaceAlt: '#2c231b',
  gold: '#c59b50',
  goldLight: '#ecd39a',
  text: '#f7f1e7',
  muted: '#b9ad9d',
  border: '#493b2c',
  danger: '#df7569',
  success: '#5ea681',
  white: '#ffffff',
};

export const spacing = {
  xs: 6,
  sm: 10,
  md: 16,
  lg: 24,
  xl: 34,
};

export const commonStyles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.background,
  },
  content: {
    paddingHorizontal: spacing.lg,
    paddingBottom: spacing.xl,
  },
  eyebrow: {
    color: colors.gold,
    fontSize: 12,
    letterSpacing: 3,
    textTransform: 'uppercase',
  },
  title: {
    color: colors.text,
    fontSize: 29,
    lineHeight: 36,
    fontWeight: '600',
    marginTop: spacing.sm,
  },
  body: {
    color: colors.muted,
    lineHeight: 22,
  },
  card: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 20,
    backgroundColor: colors.surface,
    padding: spacing.md,
  },
});
