import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Image, Keyboard, KeyboardAvoidingView, Linking, Platform, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function MomentDetailScreen({ navigation, route }) {
  const { id } = route.params;
  const { token, isAuthenticated } = useAuth();
  const [moment, setMoment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [comment, setComment] = useState('');
  const [sending, setSending] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.moment(id);
      setMoment(response.data);
    } catch (error) {
      Alert.alert('Moment tidak tersedia', error.message);
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => { load(); }, [load]);

  function requireLogin() {
    if (isAuthenticated) return true;
    navigation.navigate('Login', { returnTo: 'MomentDetail', returnParams: { id } });
    return false;
  }

  async function react(type) {
    if (!requireLogin()) return;
    try {
      await api.reactToMoment(id, type, token);
      await load();
    } catch (error) {
      Alert.alert('Reaksi belum tersimpan', error.message);
    }
  }

  async function sendComment() {
    if (!requireLogin()) return;
    if (comment.trim().length < 2) {
      Alert.alert('Komentar', 'Tulis komentar minimal 2 karakter.');
      return;
    }
    setSending(true);
    try {
      await api.commentOnMoment(id, comment.trim(), token);
      setComment('');
      Keyboard.dismiss();
      await load();
    } catch (error) {
      Alert.alert('Komentar belum terkirim', error.message);
    } finally {
      setSending(false);
    }
  }

  if (loading && !moment) return <SafeAreaView style={[commonStyles.screen, styles.center]}><ActivityIndicator color={colors.gold} /></SafeAreaView>;
  if (!moment) return <SafeAreaView style={[commonStyles.screen, styles.center]}><Text style={styles.empty}>Moment tidak dapat dimuat.</Text></SafeAreaView>;

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={0}
        style={styles.fill}
      >
        <ScrollView
          automaticallyAdjustKeyboardInsets={Platform.OS === 'ios'}
          contentContainerStyle={styles.content}
          keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
          keyboardShouldPersistTaps="handled"
        >
          <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali ke Moment</Text>
          {moment.cover_photo_url ? <Image source={{ uri: moment.cover_photo_url }} style={styles.cover} /> : null}
          <Text style={commonStyles.eyebrow}>Moment Pernikahan</Text>
          <Text style={commonStyles.title}>{moment.names}</Text>
          <Text style={styles.caption}>{moment.caption || 'Membagikan cerita menuju hari bahagia.'}</Text>
          <View style={styles.reactions}>
            <Reaction label={`Like ${moment.reactions?.like || 0}`} onPress={() => react('like')} />
            <Reaction label={`Love ${moment.reactions?.love || 0}`} onPress={() => react('love')} />
          </View>
          <PrimaryButton title="Minta Undangan" onPress={() => navigation.navigate('RequestInvitation', { invitation: moment })} style={styles.action} />
          {moment.gift_active ? <SecondaryButton title="Kirim Wedding Gift" onPress={() => Linking.openURL(moment.gift_url)} style={styles.gift} /> : null}
          <Text style={styles.privacy}>Jadwal, alamat, dan peta acara tidak ditampilkan di Moment. Pasangan membagikan link undangan secara pribadi.</Text>
          {moment.timeline?.length ? <Text style={styles.sectionTitle}>Perjalanan Mereka</Text> : null}
          {moment.timeline?.map((entry) => (
            <View key={entry.id} style={styles.timeline}>
              {entry.photo_url ? <Image source={{ uri: entry.photo_url }} style={styles.timelinePhoto} /> : null}
              <Text style={styles.timelineTitle}>{entry.title}</Text>
              {entry.body ? <Text style={styles.timelineBody}>{entry.body}</Text> : null}
            </View>
          ))}
          <Text style={styles.sectionTitle}>Komentar</Text>
          {moment.comments?.length ? moment.comments.map((entry) => (
            <View key={entry.id} style={styles.comment}>
              <Text style={styles.commentName}>{entry.user.name}</Text>
              <Text style={styles.commentBody}>{entry.body}</Text>
            </View>
          )) : <Text style={styles.noComments}>Belum ada komentar. Jadilah yang pertama memberi ucapan.</Text>}
        </ScrollView>
        <View style={styles.composer}>
          <TextInput
            accessibilityLabel="Tulis komentar"
            maxLength={500}
            multiline
            onChangeText={setComment}
            placeholder="Tulis ucapan hangat..."
            placeholderTextColor={colors.muted}
            style={styles.input}
            value={comment}
          />
          <Pressable
            accessibilityRole="button"
            disabled={sending}
            onPress={sendComment}
            style={({ pressed }) => [styles.send, pressed && styles.sendPressed, sending && styles.sendDisabled]}
          >
            {sending ? <ActivityIndicator color={colors.background} size="small" /> : <Text style={styles.sendText}>Kirim</Text>}
          </Pressable>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function Reaction({ label, onPress }) {
  return <Pressable onPress={onPress} style={styles.reaction}><Text style={styles.reactionText}>{label}</Text></Pressable>;
}

const styles = StyleSheet.create({
  fill: { flex: 1 },
  content: { padding: spacing.lg, paddingBottom: spacing.lg },
  center: { alignItems: 'center', justifyContent: 'center' },
  back: { color: colors.goldLight, marginBottom: spacing.md },
  cover: { aspectRatio: 1.2, borderRadius: 20, marginBottom: spacing.lg, width: '100%' },
  caption: { color: colors.muted, fontSize: 16, lineHeight: 23, marginTop: spacing.md },
  reactions: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.lg },
  reaction: { borderColor: colors.border, borderRadius: 99, borderWidth: 1, paddingHorizontal: spacing.md, paddingVertical: spacing.sm },
  reactionText: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  action: { marginTop: spacing.lg },
  gift: { marginTop: spacing.sm },
  privacy: { color: colors.muted, fontSize: 12, lineHeight: 18, marginTop: spacing.md },
  sectionTitle: { color: colors.text, fontSize: 21, fontWeight: '700', marginTop: spacing.xl, marginBottom: spacing.md },
  timeline: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, marginBottom: spacing.sm, overflow: 'hidden', padding: spacing.md },
  timelinePhoto: { aspectRatio: 1.3, borderRadius: 12, marginBottom: spacing.md, width: '100%' },
  timelineTitle: { color: colors.goldLight, fontSize: 16, fontWeight: '700' },
  timelineBody: { color: colors.muted, lineHeight: 20, marginTop: spacing.xs },
  composer: { alignItems: 'flex-end', backgroundColor: colors.surface, borderTopColor: colors.border, borderTopWidth: 1, flexDirection: 'row', gap: spacing.sm, paddingHorizontal: spacing.md, paddingVertical: spacing.sm },
  input: { backgroundColor: colors.background, borderColor: colors.border, borderRadius: 18, borderWidth: 1, color: colors.text, flex: 1, maxHeight: 112, minHeight: 52, paddingHorizontal: spacing.md, paddingVertical: 14, textAlignVertical: 'top' },
  send: { alignItems: 'center', backgroundColor: colors.gold, borderRadius: 17, height: 52, justifyContent: 'center', paddingHorizontal: spacing.md },
  sendPressed: { opacity: 0.8 },
  sendDisabled: { opacity: 0.55 },
  sendText: { color: colors.background, fontSize: 14, fontWeight: '800' },
  comment: { borderBottomColor: colors.border, borderBottomWidth: 1, paddingVertical: spacing.md },
  commentName: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  commentBody: { color: colors.text, lineHeight: 20, marginTop: spacing.xs },
  noComments: { color: colors.muted },
  empty: { color: colors.muted },
});
