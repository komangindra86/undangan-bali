import { Image, StyleSheet, Text, View } from 'react-native';
import { SecondaryButton } from './Buttons';
import { colors, spacing } from '../theme';

export default function PhotoField({ label, photo, onPick }) {
  return (
    <View style={styles.wrap}>
      <Text style={styles.label}>{label}</Text>
      {photo?.uri ? <Image source={{ uri: photo.uri }} style={styles.photo} /> : <View style={styles.placeholder}><Text style={styles.help}>Belum ada foto</Text></View>}
      <SecondaryButton title={photo ? 'Ganti Foto' : 'Upload Foto'} onPress={onPick} style={styles.button} />
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    marginBottom: spacing.lg,
  },
  label: {
    color: colors.text,
    fontWeight: '600',
    marginBottom: spacing.sm,
  },
  photo: {
    height: 190,
    width: 152,
    borderRadius: 18,
    marginBottom: spacing.sm,
  },
  placeholder: {
    alignItems: 'center',
    borderColor: colors.border,
    borderRadius: 18,
    borderStyle: 'dashed',
    borderWidth: 1,
    height: 116,
    justifyContent: 'center',
    marginBottom: spacing.sm,
  },
  help: {
    color: colors.muted,
  },
  button: {
    minHeight: 46,
  },
});
