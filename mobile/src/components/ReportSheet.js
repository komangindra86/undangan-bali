import { useEffect, useState } from 'react';
import { Modal, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { PrimaryButton, SecondaryButton } from './Buttons';
import { colors, spacing } from '../theme';

export const REPORT_REASONS = [
  { value: 'spam', label: 'Spam atau promosi' },
  { value: 'harassment', label: 'Pelecehan atau ujaran kebencian' },
  { value: 'inappropriate', label: 'Konten tidak pantas' },
  { value: 'privacy', label: 'Melanggar privasi' },
  { value: 'other', label: 'Lainnya' },
];

export default function ReportSheet({ visible, title, onCancel, onSubmit }) {
  const [reason, setReason] = useState(null);
  const [note, setNote] = useState('');
  const [sending, setSending] = useState(false);

  useEffect(() => {
    if (visible) {
      setReason(null);
      setNote('');
    }
  }, [visible]);

  async function submit() {
    if (!reason || sending) return;
    setSending(true);
    try {
      await onSubmit({ reason, note: note.trim() || null });
    } finally {
      setSending(false);
    }
  }

  return (
    <Modal animationType="slide" onRequestClose={onCancel} transparent visible={visible}>
      <View style={styles.backdrop}>
        <View style={styles.sheet}>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.help}>Pilih alasan. Admin akan meninjau laporan dan menyembunyikan konten yang melanggar.</Text>
          {REPORT_REASONS.map((option) => (
            <Pressable
              accessibilityRole="radio"
              accessibilityState={{ checked: reason === option.value }}
              key={option.value}
              onPress={() => setReason(option.value)}
              style={[styles.option, reason === option.value && styles.optionActive]}
            >
              <Text style={[styles.optionText, reason === option.value && styles.optionTextActive]}>{option.label}</Text>
            </Pressable>
          ))}
          <TextInput
            accessibilityLabel="Catatan laporan"
            maxLength={500}
            multiline
            onChangeText={setNote}
            placeholder="Catatan tambahan (opsional)"
            placeholderTextColor={colors.muted}
            style={styles.note}
            value={note}
          />
          <PrimaryButton title="Kirim Laporan" onPress={submit} loading={sending} disabled={!reason} style={styles.submit} />
          <SecondaryButton title="Batal" onPress={onCancel} disabled={sending} style={styles.cancel} />
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: { backgroundColor: 'rgba(0,0,0,0.6)', flex: 1, justifyContent: 'flex-end' },
  sheet: { backgroundColor: colors.surface, borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: spacing.lg },
  title: { color: colors.text, fontSize: 20, fontWeight: '700' },
  help: { color: colors.muted, lineHeight: 20, marginBottom: spacing.md, marginTop: spacing.xs },
  option: { borderColor: colors.border, borderRadius: 14, borderWidth: 1, marginBottom: spacing.sm, paddingHorizontal: spacing.md, paddingVertical: 12 },
  optionActive: { backgroundColor: colors.gold, borderColor: colors.gold },
  optionText: { color: colors.text, fontWeight: '600' },
  optionTextActive: { color: colors.background },
  note: { backgroundColor: colors.background, borderColor: colors.border, borderRadius: 14, borderWidth: 1, color: colors.text, marginTop: spacing.xs, minHeight: 70, padding: spacing.md, textAlignVertical: 'top' },
  submit: { marginTop: spacing.md },
  cancel: { marginTop: spacing.sm },
});
