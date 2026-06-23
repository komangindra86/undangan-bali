import { StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import KeyboardAwareScrollView from './KeyboardAwareScrollView';
import { colors, commonStyles, spacing } from '../theme';

const labels = ['Template', 'Mempelai', 'Acara', 'Lokasi', 'Galeri', 'Musik', 'Wedding Gift', 'Konfirmasi'];
const totalSteps = labels.length;

export default function WizardLayout({ step, title, subtitle, children, footer, syncMessage }) {
  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={commonStyles.content}>
        <View style={styles.progressHeader}>
          <Text style={commonStyles.eyebrow}>Langkah {step} dari {totalSteps}</Text>
          <Text style={styles.stepLabel}>{labels[step - 1]}</Text>
        </View>
        <View style={styles.bar}>
          <View style={[styles.barValue, { width: `${(step / totalSteps) * 100}%` }]} />
        </View>
        <Text style={commonStyles.title}>{title}</Text>
        <Text style={[commonStyles.body, styles.subtitle]}>{subtitle}</Text>
        {syncMessage ? <Text style={styles.sync}>{syncMessage}</Text> : null}
        <View style={styles.form}>{children}</View>
        {footer}
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  progressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: spacing.sm,
  },
  stepLabel: {
    color: colors.muted,
    fontSize: 13,
  },
  bar: {
    height: 5,
    borderRadius: 5,
    backgroundColor: colors.surfaceAlt,
    marginTop: spacing.md,
    marginBottom: spacing.lg,
    overflow: 'hidden',
  },
  barValue: {
    height: '100%',
    backgroundColor: colors.gold,
  },
  subtitle: {
    marginTop: spacing.sm,
  },
  sync: {
    marginTop: spacing.md,
    color: colors.success,
    fontSize: 12,
  },
  form: {
    marginTop: spacing.xl,
  },
});
