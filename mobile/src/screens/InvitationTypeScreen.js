import Ionicons from '@expo/vector-icons/Ionicons';
import { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { invitationName, isBirthday } from '../constants/invitation';
import { useDraft } from '../context/DraftContext';
import { colors, commonStyles, spacing } from '../theme';

export default function InvitationTypeScreen({ navigation }) {
  const { draft, loading, syncing, startDraft } = useDraft();
  const [pendingType, setPendingType] = useState(null);
  const [starting, setStarting] = useState(false);
  const busy = loading || syncing || starting;

  function openTemplates() {
    navigation.reset({ index: 2, routes: [{ name: 'MainTabs' }, { name: 'InvitationType' }, { name: 'Template' }] });
  }

  async function start(type, confirmed = false) {
    if (draft.selected_template && !confirmed) { setPendingType(type); return; }
    setStarting(true);
    try { await startDraft(type); openTemplates(); }
    catch (error) { Alert.alert('Draft belum siap', error.message); }
    finally { setStarting(false); }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={commonStyles.content}>
        <Text style={commonStyles.eyebrow}>Undangan Bali Santih</Text>
        <Text style={commonStyles.title}>Rayakan momen apa?</Text>
        <Text style={commonStyles.body}>Pilih jenis undangan. Gratis, dan tidak perlu login untuk mulai menyusunnya.</Text>
        {draft.selected_template ? (
          <View style={styles.resume}>
            <Text style={styles.title}>Draft {isBirthday(draft) ? 'ulang tahun' : 'pernikahan'} tersedia</Text>
            <Text style={styles.body}>{invitationName(draft)} · {draft.selected_template.name}</Text>
            <SecondaryButton title="Lanjutkan Draft Tersimpan" disabled={busy} onPress={openTemplates} />
          </View>
        ) : null}
        {!pendingType ? [
          { type: 'wedding', icon: 'heart-outline', title: 'Pernikahan', body: 'Rangkai cerita dan undang orang tersayang di hari bahagia kalian.' },
          { type: 'birthday', icon: 'gift-outline', title: 'Ulang Tahun', body: 'Untuk anak maupun dewasa. Usia opsional, foto dan undangan tidak otomatis muncul di feed publik.' },
        ].map((choice) => (
          <View key={choice.type} style={styles.card}>
            <Ionicons name={choice.icon} size={32} color={colors.goldLight} />
            <Text style={styles.title}>{choice.title}</Text>
            <Text style={styles.body}>{choice.body}</Text>
            <PrimaryButton title={`Buat Undangan ${choice.title}`} disabled={busy} onPress={() => start(choice.type)} />
          </View>
        )) : null}
        {pendingType ? (
          <View style={styles.resume} accessibilityLiveRegion="polite">
            <Text style={styles.title}>Mulai draft baru?</Text>
            <Text style={styles.body}>Draft lokal yang sekarang akan diganti. Undangan yang sudah tersimpan online tetap ada di akun Anda.</Text>
            <PrimaryButton title="Ya, Mulai Draft Baru" loading={starting} onPress={() => start(pendingType, true)} />
            <SecondaryButton title="Tetap Gunakan Draft Lama" disabled={busy} onPress={() => setPendingType(null)} style={styles.cancel} />
          </View>
        ) : null}
        <SecondaryButton title="Kembali" disabled={busy} onPress={() => navigation.goBack()} style={styles.cancel} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  card: { marginTop: spacing.lg, padding: spacing.lg, backgroundColor: colors.surface, borderColor: colors.border, borderWidth: 1, borderRadius: 24 },
  resume: { marginTop: spacing.lg, padding: spacing.md, backgroundColor: colors.surfaceAlt, borderColor: colors.gold, borderWidth: 1, borderRadius: 16 },
  title: { color: colors.text, fontSize: 20, fontWeight: '700', marginVertical: spacing.sm },
  body: { color: colors.muted, lineHeight: 22, marginBottom: spacing.md },
  cancel: { marginTop: spacing.md },
});
