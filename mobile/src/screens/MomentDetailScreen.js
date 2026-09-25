import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, Image, Keyboard, KeyboardAvoidingView, Linking, Platform, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { giftLabelFor } from '../constants/invitation';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';
import ReportSheet from '../components/ReportSheet';
import { patchFeedItem, removeFeedItem } from '../utils/feedSession';

export default function MomentDetailScreen({ navigation, route }) {
  const { id } = route.params;
  const { token, isAuthenticated } = useAuth();
  const [moment, setMoment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [comment, setComment] = useState('');
  const [sending, setSending] = useState(false);
  const [reacting, setReacting] = useState(false);
  const [loadingOlder, setLoadingOlder] = useState(false);
  const [reportTarget, setReportTarget] = useState(null);
  const sendingCommentRef = useRef(false);
  const pendingCommentRef = useRef(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.moment(id, token);
      setMoment(response.data);
      patchFeedItem(response.data.id, {
        reactions: response.data.reactions,
        comments_count: response.data.comments_count,
      });
    } catch (error) {
      Alert.alert('Moment tidak tersedia', error.message);
    } finally {
      setLoading(false);
    }
  }, [id, token]);

  useEffect(() => { load(); }, [load]);

  function requireLogin() {
    if (isAuthenticated) return true;
    navigation.navigate('Login', { returnTo: 'MomentDetail', returnParams: { id } });
    return false;
  }

  async function react(type) {
    if (reacting || !requireLogin()) return;
    setReacting(true);
    try {
      // Tapping the reaction already given removes it, like other social apps.
      if (moment.my_reaction === type) {
        await api.removeMomentReaction(id, token);
      } else {
        await api.reactToMoment(id, type, token);
      }
      const response = await api.moment(id, token);
      setMoment((current) => ({
        ...current,
        reactions: response.data.reactions,
        my_reaction: response.data.my_reaction,
      }));
      patchFeedItem(response.data.id, { reactions: response.data.reactions });
    } catch (error) {
      Alert.alert('Reaksi belum tersimpan', error.message);
    } finally {
      setReacting(false);
    }
  }

  async function loadOlderComments() {
    const oldest = moment.comments?.[moment.comments.length - 1];
    if (loadingOlder || !oldest) return;
    setLoadingOlder(true);
    try {
      const response = await api.momentComments(id, oldest.id, token);
      setMoment((current) => ({
        ...current,
        comments: [...current.comments, ...response.data.filter((entry) => !current.comments.some((known) => known.id === entry.id))],
        has_more_comments: response.has_more,
      }));
    } catch (error) {
      Alert.alert('Komentar belum dapat dimuat', error.message);
    } finally {
      setLoadingOlder(false);
    }
  }

  function openReport(target) {
    if (!requireLogin()) return;
    setReportTarget(target);
  }

  async function submitReport(values) {
    try {
      const response = reportTarget.comment
        ? await api.reportComment(id, reportTarget.comment.id, values, token)
        : await api.reportMoment(id, values, token);
      setReportTarget(null);
      Alert.alert('Laporan terkirim', response.message);
    } catch (error) {
      Alert.alert('Laporan belum terkirim', error.message);
    }
  }

  function confirmBlock(userId, name, isOwner) {
    if (!requireLogin()) return;
    Alert.alert(`Blokir ${name}?`, 'Moment dan komentarnya tidak akan tampil untuk Anda, dan dia tidak dapat berinteraksi dengan Moment Anda. Blokir dapat dibuka dari Profil.', [
      { text: 'Batal', style: 'cancel' },
      {
        text: 'Blokir',
        style: 'destructive',
        onPress: async () => {
          try {
            await api.blockUser(userId, token);
            if (isOwner) {
              removeFeedItem(moment.id);
              navigation.goBack();
              return;
            }
            await load();
          } catch (error) {
            Alert.alert('Belum dapat memblokir', error.message);
          }
        },
      },
    ]);
  }

  function confirmDeleteComment(entry) {
    Alert.alert('Hapus komentar', 'Komentar ini akan dihapus dari Moment.', [
      { text: 'Batal', style: 'cancel' },
      {
        text: 'Hapus',
        style: 'destructive',
        onPress: async () => {
          try {
            await api.deleteMomentComment(id, entry.id, token);
            await load();
          } catch (error) {
            Alert.alert('Tidak dapat menghapus', error.message);
          }
        },
      },
    ]);
  }

  async function sendComment() {
    if (sendingCommentRef.current) return;
    if (!requireLogin()) return;
    const body = comment.trim();
    if (body.length < 2) {
      Alert.alert('Komentar', 'Tulis komentar minimal 2 karakter.');
      return;
    }
    sendingCommentRef.current = true;
    setSending(true);
    if (!pendingCommentRef.current || pendingCommentRef.current.body !== body) {
      pendingCommentRef.current = { body, id: createCommentRequestId() };
    }
    try {
      await api.commentOnMoment(id, body, pendingCommentRef.current.id, token);
      setComment('');
      pendingCommentRef.current = null;
      Keyboard.dismiss();
      await load();
    } catch (error) {
      Alert.alert('Komentar belum terkirim', error.message);
    } finally {
      sendingCommentRef.current = false;
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
          <Text style={commonStyles.eyebrow}>Moment Perayaan</Text>
          <Text style={commonStyles.title}>{moment.names}</Text>
          <Text style={styles.caption}>{moment.caption || 'Membagikan cerita menuju hari bahagia.'}</Text>
          {!moment.viewer_is_owner ? (
            <View style={styles.moderation}>
              <Text accessibilityRole="button" onPress={() => openReport({})} style={styles.moderationLink}>Laporkan Moment</Text>
              {moment.owner_id ? (
                <Text accessibilityRole="button" onPress={() => confirmBlock(moment.owner_id, moment.names, true)} style={styles.moderationLink}>Blokir pemilik</Text>
              ) : null}
            </View>
          ) : null}
          <View style={styles.reactions}>
            <Reaction active={moment.my_reaction === 'like'} disabled={reacting} label={`Like ${moment.reactions?.like || 0}`} onPress={() => react('like')} />
            <Reaction active={moment.my_reaction === 'love'} disabled={reacting} label={`Love ${moment.reactions?.love || 0}`} onPress={() => react('love')} />
          </View>
          <PrimaryButton title="Minta Undangan" onPress={() => navigation.navigate('RequestInvitation', { invitation: moment })} style={styles.action} />
          {moment.gift_active ? <SecondaryButton title={`Kirim ${giftLabelFor(moment)} di Browser`} onPress={() => Linking.openURL(moment.gift_url)} style={styles.gift} /> : null}
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
              <View style={styles.commentHeader}>
                <Text style={styles.commentName}>{entry.user.name}</Text>
                <View style={styles.commentActions}>
                  {!entry.is_mine ? (
                    <>
                      <Text accessibilityRole="button" onPress={() => openReport({ comment: entry })} style={styles.commentDelete}>Laporkan</Text>
                      <Text accessibilityRole="button" onPress={() => confirmBlock(entry.user.id, entry.user.name, false)} style={styles.commentDelete}>Blokir</Text>
                    </>
                  ) : null}
                  {entry.can_delete ? (
                    <Text accessibilityRole="button" onPress={() => confirmDeleteComment(entry)} style={styles.commentDelete}>Hapus</Text>
                  ) : null}
                </View>
              </View>
              <Text style={styles.commentBody}>{entry.body}</Text>
            </View>
          )) : <Text style={styles.noComments}>Belum ada komentar. Jadilah yang pertama memberi ucapan.</Text>}
          {moment.has_more_comments ? (
            <SecondaryButton title={loadingOlder ? 'Memuat...' : 'Lihat komentar sebelumnya'} onPress={loadOlderComments} disabled={loadingOlder} style={styles.olderComments} />
          ) : null}
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
      <ReportSheet
        visible={Boolean(reportTarget)}
        title={reportTarget?.comment ? 'Laporkan komentar' : 'Laporkan Moment'}
        onCancel={() => setReportTarget(null)}
        onSubmit={submitReport}
      />
    </SafeAreaView>
  );
}

function createCommentRequestId() {
  const random = () => Math.random().toString(36).slice(2, 12);
  return `comment-${Date.now().toString(36)}-${random()}-${random()}`;
}

function Reaction({ active, disabled, label, onPress }) {
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ selected: active, disabled }}
      disabled={disabled}
      onPress={onPress}
      style={[styles.reaction, active && styles.reactionActive]}
    >
      <Text style={[styles.reactionText, active && styles.reactionTextActive]}>{label}</Text>
    </Pressable>
  );
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
  reactionActive: { backgroundColor: colors.gold, borderColor: colors.gold },
  reactionText: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  reactionTextActive: { color: colors.background },
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
  commentHeader: { alignItems: 'center', flexDirection: 'row', justifyContent: 'space-between' },
  commentName: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  commentActions: { flexDirection: 'row' },
  commentDelete: { color: colors.muted, fontSize: 12, fontWeight: '700', paddingLeft: spacing.md },
  moderation: { flexDirection: 'row', gap: spacing.lg, marginTop: spacing.sm },
  moderationLink: { color: colors.muted, fontSize: 12, fontWeight: '700' },
  olderComments: { marginTop: spacing.md },
  commentBody: { color: colors.text, lineHeight: 20, marginTop: spacing.xs },
  noComments: { color: colors.muted },
  empty: { color: colors.muted },
});
