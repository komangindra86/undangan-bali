import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import PhotoField from '../components/PhotoField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { useAuth } from '../context/AuthContext';
import { pickProfilePhoto } from '../services/imageService';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function ManageMomentsScreen({ navigation, route }) {
  const { invitation } = route.params;
  const { token, expireSession } = useAuth();
  const [items, setItems] = useState([]);
  const [form, setForm] = useState({ title: '', body: '', photo: null });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const load = useCallback(async () => {
    try {
      const response = await api.invitationMoments(invitation.id, token);
      setItems(response.data || []);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
        return;
      }
      Alert.alert('Timeline Moment', error.message);
    } finally { setLoading(false); }
  }, [expireSession, invitation.id, navigation, token]);

  useEffect(() => { load(); }, [load]);

  async function pickPhoto() {
    const photo = await pickProfilePhoto();
    if (photo) setForm({ ...form, photo });
  }

  async function save() {
    if (form.title.trim().length < 2) {
      Alert.alert('Moment', 'Judul Moment minimal 2 karakter.');
      return;
    }
    setSaving(true);
    try {
      await api.createInvitationMoment(invitation.id, { ...form, title: form.title.trim(), body: form.body.trim() }, token);
      setForm({ title: '', body: '', photo: null });
      await load();
    } catch (error) {
      Alert.alert('Moment belum disimpan', error.message);
    } finally { setSaving(false); }
  }

  async function remove(item) {
    Alert.alert('Hapus Moment', `Hapus “${item.title}”?`, [
      { text: 'Batal', style: 'cancel' },
      { text: 'Hapus', style: 'destructive', onPress: async () => {
        try { await api.deleteInvitationMoment(invitation.id, item.id, token); await load(); } catch (error) { Alert.alert('Tidak dapat menghapus', error.message); }
      } },
    ]);
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Moment Saya</Text>
        <Text style={commonStyles.title}>Timeline Pernikahan</Text>
        <Text style={styles.note}>Bagikan persiapan prewedding hingga hari H. Jangan masukkan alamat atau jadwal detail di sini karena Moment dapat dilihat publik.</Text>
        <FormField label="Judul Moment" value={form.title} onChangeText={(title) => setForm({ ...form, title })} maxLength={100} placeholder="Contoh: Prewedding di Ubud" />
        <FormField label="Cerita singkat" value={form.body} onChangeText={(body) => setForm({ ...form, body })} maxLength={500} multiline placeholder="Bagikan cerita hangat kami" />
        <PhotoField label="Foto Moment (opsional)" photo={form.photo} onPick={pickPhoto} />
        <PrimaryButton title="Tambahkan ke Timeline" onPress={save} loading={saving} />
        <SecondaryButton title="Kembali" onPress={() => navigation.goBack()} style={styles.back} />
        <Text style={styles.section}>Moment yang sudah dibuat</Text>
        {loading ? <ActivityIndicator color={colors.gold} /> : null}
        {!loading && !items.length ? <Text style={styles.empty}>Belum ada Moment. Tambahkan cerita pertama Anda.</Text> : null}
        {items.map((item) => (
          <View key={item.id} style={styles.card}>
            {item.photo_path ? <Image source={{ uri: `${api.siteUrl}/storage/${item.photo_path}` }} style={styles.image} /> : null}
            <Text style={styles.itemTitle}>{item.title}</Text>
            {item.body ? <Text style={styles.itemBody}>{item.body}</Text> : null}
            <Pressable onPress={() => remove(item)}><Text style={styles.remove}>Hapus Moment</Text></Pressable>
          </View>
        ))}
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  note: { color: colors.muted, lineHeight: 21, marginBottom: spacing.xl, marginTop: spacing.md },
  back: { marginTop: spacing.sm },
  section: { color: colors.text, fontSize: 19, fontWeight: '700', marginBottom: spacing.md, marginTop: spacing.xl },
  empty: { color: colors.muted },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 17, borderWidth: 1, marginBottom: spacing.sm, padding: spacing.md },
  image: { aspectRatio: 1.35, borderRadius: 12, marginBottom: spacing.md, width: '100%' },
  itemTitle: { color: colors.goldLight, fontSize: 16, fontWeight: '700' },
  itemBody: { color: colors.muted, lineHeight: 20, marginTop: spacing.xs },
  remove: { color: colors.danger, fontWeight: '700', marginTop: spacing.md },
});
