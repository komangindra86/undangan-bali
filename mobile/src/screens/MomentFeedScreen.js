import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, FlatList, Image, Pressable, StyleSheet, Text, useWindowDimensions, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

const feedSession = {
  hasMore: true,
  items: [],
  page: 1,
  photoIndexes: {},
  scrollOffset: 0,
};

export default function MomentFeedScreen({ navigation }) {
  const { isAuthenticated } = useAuth();
  const { width: screenWidth } = useWindowDimensions();
  const feedRef = useRef(null);
  const [items, setItems] = useState(feedSession.items);
  const [loading, setLoading] = useState(feedSession.items.length === 0);
  const [refreshing, setRefreshing] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(feedSession.page);
  const [hasMore, setHasMore] = useState(feedSession.hasMore);
  const [error, setError] = useState(null);
  const cardWidth = Math.min(screenWidth - (spacing.md * 2), 680);

  const loadFirstPage = useCallback(async (refresh = false) => {
    refresh ? setRefreshing(true) : setLoading(true);
    setError(null);
    try {
      const response = await api.moments(1);
      const nextItems = response.data || [];
      const nextPage = response.meta?.current_page || 1;
      const nextHasMore = nextPage < (response.meta?.last_page || 1);

      feedSession.items = nextItems;
      feedSession.page = nextPage;
      feedSession.hasMore = nextHasMore;
      if (refresh) feedSession.scrollOffset = 0;
      setItems(nextItems);
      setPage(nextPage);
      setHasMore(nextHasMore);
    } catch (loadError) {
      setError(loadError.message);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  const loadNextPage = useCallback(async () => {
    if (loading || refreshing || loadingMore || !hasMore) return;

    setLoadingMore(true);
    try {
      const response = await api.moments(page + 1);
      setItems((current) => {
        const existingIds = new Set(current.map((item) => item.id));
        const nextItems = [...current, ...(response.data || []).filter((item) => !existingIds.has(item.id))];
        feedSession.items = nextItems;
        return nextItems;
      });
      const nextPage = response.meta?.current_page || page + 1;
      const nextHasMore = nextPage < (response.meta?.last_page || page + 1);
      feedSession.page = nextPage;
      feedSession.hasMore = nextHasMore;
      setPage(nextPage);
      setHasMore(nextHasMore);
    } catch (loadError) {
      setError(loadError.message);
    } finally {
      setLoadingMore(false);
    }
  }, [hasMore, loading, loadingMore, page, refreshing]);

  useEffect(() => {
    if (!feedSession.items.length) loadFirstPage();
  }, [loadFirstPage]);

  useFocusEffect(useCallback(() => {
    if (!items.length || feedSession.scrollOffset <= 0) return undefined;
    const frame = requestAnimationFrame(() => {
      feedRef.current?.scrollToOffset({ animated: false, offset: feedSession.scrollOffset });
    });
    return () => cancelAnimationFrame(frame);
  }, [items.length]));

  return (
    <SafeAreaView edges={['top']} style={commonStyles.screen}>
      <FlatList
        ref={feedRef}
        data={items}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <MomentCard
            item={item}
            width={cardWidth}
            initialPhotoIndex={feedSession.photoIndexes[item.id] || 0}
            onPhotoChange={(photoIndex) => {
              feedSession.photoIndexes[item.id] = photoIndex;
            }}
            onPress={() => navigation.navigate('MomentDetail', { id: item.id })}
          />
        )}
        contentContainerStyle={styles.listContent}
        ListHeaderComponent={(
          <FeedHeader
            isAuthenticated={isAuthenticated}
            onNotifications={() => navigation.navigate('NotificationsTab')}
          />
        )}
        ListEmptyComponent={!loading ? <EmptyFeed error={error} onRetry={() => loadFirstPage()} /> : null}
        ListFooterComponent={<FeedFooter loading={loadingMore} hasMore={hasMore} itemCount={items.length} />}
        ItemSeparatorComponent={() => <View style={styles.separator} />}
        refreshing={refreshing}
        onRefresh={() => loadFirstPage(true)}
        onEndReached={loadNextPage}
        onEndReachedThreshold={0.55}
        onScroll={(event) => {
          feedSession.scrollOffset = event.nativeEvent.contentOffset.y;
        }}
        scrollEventThrottle={80}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
        initialNumToRender={3}
        maxToRenderPerBatch={5}
        windowSize={7}
      />
      {loading && !items.length ? (
        <View style={styles.loading}>
          <ActivityIndicator color={colors.gold} />
          <Text style={styles.loadingText}>Memuat Moment...</Text>
        </View>
      ) : null}
    </SafeAreaView>
  );
}

function FeedHeader({ isAuthenticated, onNotifications }) {
  return (
    <View style={styles.header}>
      <View style={styles.headerCopy}>
        <Text style={styles.eyebrow}>Moment Pernikahan Bali</Text>
        <Text style={styles.title}>Cerita menuju hari bahagia</Text>
        <Text style={styles.subtitle}>Geser foto untuk melihat galeri, lalu ketuk kartu untuk berinteraksi.</Text>
      </View>
      {isAuthenticated ? (
        <Pressable accessibilityLabel="Buka notifikasi" accessibilityRole="button" onPress={onNotifications} style={styles.notificationButton}>
          <Ionicons color={colors.goldLight} name="notifications-outline" size={22} />
        </Pressable>
      ) : null}
    </View>
  );
}

function MomentCard({ item, width, initialPhotoIndex, onPhotoChange, onPress }) {
  const photos = item.photo_urls?.length
    ? item.photo_urls
    : item.cover_photo_url ? [item.cover_photo_url] : [];
  const safeInitialIndex = Math.min(Math.max(initialPhotoIndex, 0), Math.max(photos.length - 1, 0));
  const [activePhoto, setActivePhoto] = useState(safeInitialIndex);
  const mediaWidth = Math.max(width - 2, 1);
  const mediaHeight = Math.round(Math.min(mediaWidth * 1.08, 610));

  function updatePhotoIndex(event) {
    const nextIndex = Math.max(0, Math.min(
      Math.round(event.nativeEvent.contentOffset.x / mediaWidth),
      photos.length - 1,
    ));
    setActivePhoto(nextIndex);
    onPhotoChange(nextIndex);
  }

  return (
    <View style={[styles.card, { width }]}>
      <View style={styles.cardHeader}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>{initials(item)}</Text>
        </View>
        <View style={styles.cardIdentity}>
          <Text numberOfLines={1} style={styles.cardNames}>{item.names}</Text>
          <Text style={styles.cardMeta}>Moment pernikahan</Text>
        </View>
        <View style={styles.publicPill}>
          <Ionicons color={colors.success} name="earth-outline" size={12} />
          <Text style={styles.publicText}>Publik</Text>
        </View>
      </View>

      {photos.length ? (
        <View style={[styles.media, { height: mediaHeight, width: mediaWidth }]}>
          <FlatList
            data={photos}
            horizontal
            pagingEnabled
            nestedScrollEnabled
            directionalLockEnabled
            decelerationRate="fast"
            keyExtractor={(photo, index) => `${photo}-${index}`}
            showsHorizontalScrollIndicator={false}
            initialScrollIndex={safeInitialIndex}
            getItemLayout={(_, index) => ({ index, length: mediaWidth, offset: mediaWidth * index })}
            onMomentumScrollEnd={updatePhotoIndex}
            renderItem={({ item: photo, index }) => (
              <Pressable
                accessibilityLabel={`Buka Moment ${item.names}, foto ${index + 1}`}
                accessibilityRole="button"
                onPress={onPress}
                style={{ height: mediaHeight, width: mediaWidth }}
              >
                <Image source={{ uri: photo }} resizeMode="cover" style={styles.photo} />
              </Pressable>
            )}
          />
          {photos.length > 1 ? (
            <>
              <View pointerEvents="none" style={styles.photoCounter}>
                <Text style={styles.photoCounterText}>{activePhoto + 1} / {photos.length}</Text>
              </View>
              <View pointerEvents="none" style={styles.swipeHint}>
                <Ionicons color={colors.goldLight} name="swap-horizontal" size={15} />
                <Text style={styles.swipeHintText}>Geser foto</Text>
              </View>
            </>
          ) : null}
        </View>
      ) : (
        <Pressable accessibilityRole="button" onPress={onPress} style={[styles.placeholder, { height: mediaHeight }]}>
          <Text style={styles.placeholderInitials}>{initials(item)}</Text>
          <Text style={styles.placeholderText}>Belum ada foto</Text>
        </Pressable>
      )}

      <View style={styles.cardBody}>
        {photos.length > 1 ? <PhotoDots count={photos.length} active={activePhoto} /> : null}
        <View style={styles.metrics}>
          <Metric icon="thumbs-up-outline" label={`${item.reactions?.like || 0} suka`} />
          <Metric icon="heart-outline" label={`${item.reactions?.love || 0} love`} />
          <Metric icon="chatbubble-outline" label={`${item.comments_count || 0} komentar`} />
        </View>
        <Text style={styles.caption}>
          <Text style={styles.captionNames}>{item.names} </Text>
          {item.caption || 'Membagikan cerita menuju hari bahagia.'}
        </Text>
        <Pressable accessibilityRole="button" onPress={onPress} style={styles.detailButton}>
          <Text style={styles.detailButtonText}>Lihat cerita dan interaksi</Text>
          <Ionicons color={colors.goldLight} name="chevron-forward" size={17} />
        </Pressable>
      </View>
    </View>
  );
}

function Metric({ icon, label }) {
  return (
    <View style={styles.metric}>
      <Ionicons color={colors.goldLight} name={icon} size={16} />
      <Text style={styles.metricText}>{label}</Text>
    </View>
  );
}

function PhotoDots({ count, active }) {
  const visibleCount = Math.min(count, 7);
  const activeDot = count <= visibleCount
    ? active
    : Math.round((active / Math.max(count - 1, 1)) * (visibleCount - 1));

  return (
    <View style={styles.dots}>
      {Array.from({ length: visibleCount }).map((_, index) => (
        <View key={index} style={[styles.dot, index === activeDot && styles.dotActive]} />
      ))}
    </View>
  );
}

function EmptyFeed({ error, onRetry }) {
  return (
    <View style={styles.emptyWrap}>
      <View style={styles.emptyIcon}>
        <Ionicons color={colors.goldLight} name="images-outline" size={30} />
      </View>
      <Text style={styles.emptyTitle}>{error ? 'Feed belum dapat dimuat' : 'Moment akan hadir di sini'}</Text>
      <Text style={styles.emptyText}>{error || 'Undangan yang dipublish otomatis tampil dengan foto dan nama panggilan pasangan.'}</Text>
      {error ? (
        <Pressable accessibilityRole="button" onPress={onRetry} style={styles.retryButton}>
          <Text style={styles.retryText}>Coba Lagi</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

function FeedFooter({ loading, hasMore, itemCount }) {
  if (loading) {
    return <ActivityIndicator color={colors.gold} style={styles.footerLoading} />;
  }
  if (itemCount && !hasMore) {
    return <Text style={styles.endText}>Semua Moment sudah ditampilkan.</Text>;
  }
  return null;
}

function initials(item) {
  return `${item.groom_nickname?.[0] || ''}${item.bride_nickname?.[0] || ''}`.toUpperCase() || 'UB';
}

const styles = StyleSheet.create({
  listContent: { paddingBottom: spacing.xl, paddingHorizontal: spacing.md },
  header: { alignItems: 'flex-start', alignSelf: 'center', flexDirection: 'row', justifyContent: 'space-between', maxWidth: 680, paddingBottom: spacing.lg, paddingTop: spacing.md, width: '100%' },
  headerCopy: { flex: 1, paddingRight: spacing.md },
  eyebrow: { color: colors.gold, fontSize: 11, fontWeight: '800', letterSpacing: 2.4, textTransform: 'uppercase' },
  title: { color: colors.text, fontSize: 27, fontWeight: '700', lineHeight: 34, marginTop: spacing.xs },
  subtitle: { color: colors.muted, fontSize: 13, lineHeight: 19, marginTop: spacing.xs },
  notificationButton: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 18, borderWidth: 1, height: 44, justifyContent: 'center', width: 44 },
  separator: { height: spacing.md },
  card: { alignSelf: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, overflow: 'hidden' },
  cardHeader: { alignItems: 'center', flexDirection: 'row', padding: spacing.sm },
  avatar: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderColor: colors.border, borderRadius: 17, borderWidth: 1, height: 34, justifyContent: 'center', width: 34 },
  avatarText: { color: colors.goldLight, fontSize: 11, fontWeight: '800' },
  cardIdentity: { flex: 1, marginLeft: spacing.sm },
  cardNames: { color: colors.text, fontSize: 14, fontWeight: '700' },
  cardMeta: { color: colors.muted, fontSize: 11, marginTop: 2 },
  publicPill: { alignItems: 'center', backgroundColor: '#243127', borderRadius: 99, flexDirection: 'row', gap: 4, paddingHorizontal: 8, paddingVertical: 5 },
  publicText: { color: colors.success, fontSize: 9, fontWeight: '800', textTransform: 'uppercase' },
  media: { backgroundColor: colors.background, overflow: 'hidden', position: 'relative' },
  photo: { height: '100%', width: '100%' },
  placeholder: { alignItems: 'center', backgroundColor: colors.surfaceAlt, justifyContent: 'center' },
  placeholderInitials: { color: colors.goldLight, fontSize: 46, fontWeight: '700', letterSpacing: 3 },
  placeholderText: { color: colors.muted, marginTop: spacing.sm },
  photoCounter: { backgroundColor: '#15110dcc', borderRadius: 99, paddingHorizontal: 10, paddingVertical: 6, position: 'absolute', right: spacing.sm, top: spacing.sm },
  photoCounterText: { color: colors.text, fontSize: 11, fontWeight: '800' },
  swipeHint: { alignItems: 'center', backgroundColor: '#15110dcc', borderRadius: 99, bottom: spacing.sm, flexDirection: 'row', gap: 5, left: spacing.sm, paddingHorizontal: 10, paddingVertical: 6, position: 'absolute' },
  swipeHintText: { color: colors.text, fontSize: 10, fontWeight: '700' },
  cardBody: { padding: spacing.md },
  dots: { alignSelf: 'center', flexDirection: 'row', gap: 5, marginBottom: spacing.sm },
  dot: { backgroundColor: colors.border, borderRadius: 3, height: 5, width: 5 },
  dotActive: { backgroundColor: colors.gold, width: 18 },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  metric: { alignItems: 'center', flexDirection: 'row', gap: 5 },
  metricText: { color: colors.muted, fontSize: 11, fontWeight: '600' },
  caption: { color: colors.muted, lineHeight: 21, marginTop: spacing.md },
  captionNames: { color: colors.text, fontWeight: '700' },
  detailButton: { alignItems: 'center', borderTopColor: colors.border, borderTopWidth: 1, flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.md, paddingTop: spacing.md },
  detailButtonText: { color: colors.goldLight, fontSize: 13, fontWeight: '700' },
  loading: { ...StyleSheet.absoluteFillObject, alignItems: 'center', backgroundColor: colors.background, justifyContent: 'center' },
  loadingText: { color: colors.muted, marginTop: spacing.sm },
  emptyWrap: { alignItems: 'center', alignSelf: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 22, borderWidth: 1, maxWidth: 680, padding: spacing.xl, width: '100%' },
  emptyIcon: { alignItems: 'center', backgroundColor: colors.surfaceAlt, borderRadius: 28, height: 56, justifyContent: 'center', width: 56 },
  emptyTitle: { color: colors.text, fontSize: 20, fontWeight: '700', marginTop: spacing.md, textAlign: 'center' },
  emptyText: { color: colors.muted, lineHeight: 21, marginTop: spacing.xs, textAlign: 'center' },
  retryButton: { backgroundColor: colors.gold, borderRadius: 14, marginTop: spacing.lg, paddingHorizontal: spacing.lg, paddingVertical: 12 },
  retryText: { color: colors.background, fontWeight: '800' },
  footerLoading: { marginVertical: spacing.lg },
  endText: { color: colors.muted, fontSize: 12, marginVertical: spacing.lg, textAlign: 'center' },
});
