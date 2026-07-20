import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Image, Pressable, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function MomentFeedScreen({ navigation }) {
  const { isAuthenticated } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const response = await api.moments();
      setItems(response.data || []);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content} refreshControl={<RefreshControl refreshing={loading} onRefresh={load} tintColor={colors.gold} />}>
        <View style={styles.header}>
          <View>
            <Text style={commonStyles.eyebrow}>Moment</Text>
            <Text style={styles.title}>Cerita Pernikahan Bali</Text>
          </View>
          <View style={styles.headerActions}>
            {isAuthenticated ? <Pressable onPress={() => navigation.navigate('Notifications')}><Text style={styles.headerLink}>Notifikasi</Text></Pressable> : null}
            <Pressable onPress={() => navigation.navigate(isAuthenticated ? 'MyInvitations' : 'Login', isAuthenticated ? undefined : { returnTo: 'MyInvitations' })}><Text style={styles.headerLink}>Akun</Text></Pressable>
          </View>
        </View>
        <Text style={styles.subtitle}>Rayakan momen bahagia pasangan Bali. Detail acara tetap dibagikan langsung oleh pemilik undangan.</Text>
        <Pressable style={styles.create} onPress={() => navigation.navigate('Template')}>
          <Text style={styles.createText}>+ Buat Undangan Gratis</Text>
        </Pressable>
        {loading && !items.length ? <ActivityIndicator color={colors.gold} style={styles.loader} /> : null}
        {!loading && !items.length ? <Text style={styles.empty}>Moment pernikahan akan tampil di sini setelah undangan dipublish.</Text> : null}
        {items.map((item) => <MomentCard key={item.id} item={item} onPress={() => navigation.navigate('MomentDetail', { id: item.id })} />)}
      </ScrollView>
    </SafeAreaView>
  );
}

function MomentCard({ item, onPress }) {
  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.card, pressed && styles.pressed]}>
      {item.cover_photo_url ? <Image source={{ uri: item.cover_photo_url }} style={styles.photo} /> : <View style={styles.photoPlaceholder}><Text style={styles.initials}>{initials(item)}</Text></View>}
      <View style={styles.cardBody}>
        <Text style={styles.names}>{item.names}</Text>
        {item.caption ? <Text numberOfLines={2} style={styles.caption}>{item.caption}</Text> : <Text style={styles.caption}>Membagikan cerita menuju hari bahagia.</Text>}
        <View style={styles.metrics}>
          <Text style={styles.metric}>Like {item.reactions?.like || 0}</Text>
          <Text style={styles.metric}>Love {item.reactions?.love || 0}</Text>
          <Text style={styles.metric}>{item.comments_count || 0} komentar</Text>
        </View>
      </View>
    </Pressable>
  );
}

function initials(item) {
  return `${item.groom_nickname?.[0] || ''}${item.bride_nickname?.[0] || ''}`.toUpperCase();
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  header: { alignItems: 'flex-start', flexDirection: 'row', justifyContent: 'space-between' },
  headerActions: { alignItems: 'flex-end', gap: spacing.sm },
  headerLink: { color: colors.goldLight, fontSize: 13, fontWeight: '700', paddingVertical: 3 },
  title: { color: colors.text, fontSize: 27, fontWeight: '700', marginTop: spacing.xs },
  subtitle: { color: colors.muted, lineHeight: 21, marginTop: spacing.md },
  create: { alignItems: 'center', backgroundColor: colors.gold, borderRadius: 15, marginTop: spacing.lg, minHeight: 50, justifyContent: 'center' },
  createText: { color: colors.background, fontWeight: '800' },
  loader: { marginTop: spacing.xl },
  empty: { color: colors.muted, lineHeight: 22, marginTop: spacing.xl, textAlign: 'center' },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 20, borderWidth: 1, marginTop: spacing.md, overflow: 'hidden' },
  pressed: { opacity: 0.8 },
  photo: { aspectRatio: 1.25, width: '100%' },
  photoPlaceholder: { alignItems: 'center', aspectRatio: 1.25, backgroundColor: colors.surfaceAlt, justifyContent: 'center' },
  initials: { color: colors.goldLight, fontSize: 42, fontWeight: '700', letterSpacing: 3 },
  cardBody: { padding: spacing.md },
  names: { color: colors.text, fontSize: 21, fontWeight: '700' },
  caption: { color: colors.muted, lineHeight: 20, marginTop: spacing.xs },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md, marginTop: spacing.md },
  metric: { color: colors.goldLight, fontSize: 12 },
});
