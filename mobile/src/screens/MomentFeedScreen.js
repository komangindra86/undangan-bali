import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, ImageBackground, Pressable, StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function MomentFeedScreen({ navigation }) {
  const { isAuthenticated } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [feedHeight, setFeedHeight] = useState(0);

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
    <SafeAreaView style={commonStyles.screen} onLayout={(event) => setFeedHeight(event.nativeEvent.layout.height)}>
      {feedHeight ? (
        <FlatList
          data={items}
          keyExtractor={(item) => String(item.id)}
          renderItem={({ item }) => <MomentSlide item={item} height={feedHeight} onPress={() => navigation.navigate('MomentDetail', { id: item.id })} />}
          pagingEnabled
          decelerationRate="fast"
          showsVerticalScrollIndicator={false}
          initialNumToRender={1}
          maxToRenderPerBatch={2}
          windowSize={3}
          removeClippedSubviews
          onRefresh={load}
          refreshing={loading}
          getItemLayout={(_, index) => ({ length: feedHeight, offset: feedHeight * index, index })}
        />
      ) : null}
      {loading && !items.length ? <View style={styles.loading}><ActivityIndicator color={colors.gold} /></View> : null}
      {!loading && !items.length ? <EmptyFeed /> : null}
      <View pointerEvents="box-none" style={styles.overlay}>
        <View style={styles.header}>
          <View>
            <Text style={styles.eyebrow}>Moment</Text>
            <Text style={styles.title}>Pernikahan Bali</Text>
          </View>
          <View style={styles.headerActions}>
            {isAuthenticated ? <Pressable onPress={() => navigation.navigate('Notifications')}><Text style={styles.headerLink}>Notifikasi</Text></Pressable> : null}
            <Pressable onPress={() => navigation.navigate(isAuthenticated ? 'MyInvitations' : 'Login', isAuthenticated ? undefined : { returnTo: 'MyInvitations' })}><Text style={styles.headerLink}>Akun</Text></Pressable>
          </View>
        </View>
        <Pressable style={styles.floatingCreate} onPress={() => navigation.navigate('Template')}>
          <Text style={styles.plus}>+</Text>
          <Text style={styles.floatingLabel}>Buat{`\n`}Undangan</Text>
        </Pressable>
      </View>
    </SafeAreaView>
  );
}

function MomentSlide({ item, height, onPress }) {
  return (
    <Pressable onPress={onPress} style={({ pressed }) => [styles.slide, { height }, pressed && styles.pressed]}>
      {item.cover_photo_url ? <ImageBackground source={{ uri: item.cover_photo_url }} style={styles.cover} resizeMode="cover" /> : <View style={styles.placeholder}><Text style={styles.initials}>{initials(item)}</Text></View>}
      <LinearGradient colors={['transparent', '#15110dcc']} locations={[0.25, 1]} style={styles.shade} />
      <View style={styles.slideInfo}>
        <Text style={styles.names}>{item.names}</Text>
        <Text numberOfLines={2} style={styles.caption}>{item.caption || 'Membagikan cerita menuju hari bahagia.'}</Text>
        <View style={styles.metrics}>
          <Text style={styles.metric}>Like {item.reactions?.like || 0}</Text>
          <Text style={styles.metric}>Love {item.reactions?.love || 0}</Text>
          <Text style={styles.metric}>{item.comments_count || 0} komentar</Text>
        </View>
        <Text style={styles.tapHint}>Ketuk untuk melihat Moment</Text>
      </View>
    </Pressable>
  );
}

function EmptyFeed() {
  return (
    <View style={styles.emptyWrap}>
      <Text style={styles.emptyTitle}>Moment akan hadir di sini</Text>
      <Text style={styles.empty}>Undangan yang dipublish otomatis tampil sebagai Moment dengan foto dan nama panggilan pasangan.</Text>
    </View>
  );
}

function initials(item) {
  return `${item.groom_nickname?.[0] || ''}${item.bride_nickname?.[0] || ''}`.toUpperCase();
}

const styles = StyleSheet.create({
  overlay: { ...StyleSheet.absoluteFillObject, justifyContent: 'space-between', padding: spacing.lg },
  header: { alignItems: 'flex-start', flexDirection: 'row', justifyContent: 'space-between' },
  headerActions: { alignItems: 'flex-end', gap: spacing.sm },
  eyebrow: { color: colors.goldLight, fontSize: 11, fontWeight: '800', letterSpacing: 3, textTransform: 'uppercase' },
  headerLink: { color: colors.goldLight, fontSize: 13, fontWeight: '700', paddingVertical: 3 },
  title: { color: colors.text, fontSize: 24, fontWeight: '700', marginTop: spacing.xs },
  slide: { backgroundColor: colors.background, overflow: 'hidden', width: '100%' },
  cover: { ...StyleSheet.absoluteFillObject },
  placeholder: { ...StyleSheet.absoluteFillObject, alignItems: 'center', backgroundColor: colors.surfaceAlt, justifyContent: 'center' },
  shade: { ...StyleSheet.absoluteFillObject },
  pressed: { opacity: 0.8 },
  initials: { color: colors.goldLight, fontSize: 42, fontWeight: '700', letterSpacing: 3 },
  slideInfo: { bottom: 54, left: spacing.lg, position: 'absolute', right: 96 },
  names: { color: colors.text, fontSize: 28, fontWeight: '800' },
  caption: { color: '#e3d9cb', lineHeight: 21, marginTop: spacing.xs },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md, marginTop: spacing.md },
  metric: { color: colors.goldLight, fontSize: 12 },
  tapHint: { color: '#d5c6b2', fontSize: 11, marginTop: spacing.sm },
  floatingCreate: { alignItems: 'center', alignSelf: 'flex-end', backgroundColor: colors.gold, borderColor: '#efd69e', borderRadius: 42, borderWidth: 2, height: 82, justifyContent: 'center', marginBottom: 6, shadowColor: '#000', shadowOpacity: 0.35, shadowRadius: 10, width: 82 },
  plus: { color: colors.background, fontSize: 28, fontWeight: '400', lineHeight: 27 },
  floatingLabel: { color: colors.background, fontSize: 10, fontWeight: '800', lineHeight: 12, textAlign: 'center' },
  loading: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center' },
  emptyWrap: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center', paddingHorizontal: spacing.xl },
  emptyTitle: { color: colors.text, fontSize: 22, fontWeight: '700', textAlign: 'center' },
  empty: { color: colors.muted, lineHeight: 22, marginTop: spacing.sm, textAlign: 'center' },
});
