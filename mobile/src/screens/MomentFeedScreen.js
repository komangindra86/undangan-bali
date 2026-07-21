import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, FlatList, ImageBackground, Pressable, StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useFocusEffect } from '@react-navigation/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

const feedSession = {
  activeMomentId: null,
  activeMomentIndex: 0,
  hasMore: true,
  items: [],
  page: 1,
  photoIndexes: {},
};

export default function MomentFeedScreen({ navigation }) {
  const { isAuthenticated } = useAuth();
  const feedRef = useRef(null);
  const viewabilityConfig = useRef({ itemVisiblePercentThreshold: 60 });
  const [items, setItems] = useState(feedSession.items);
  const [loading, setLoading] = useState(feedSession.items.length === 0);
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(feedSession.page);
  const [hasMore, setHasMore] = useState(feedSession.hasMore);
  const [error, setError] = useState(null);
  const [feedSize, setFeedSize] = useState({ height: 0, width: 0 });

  const loadFirstPage = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await api.moments(1);
      const nextItems = response.data || [];
      const nextPage = response.meta?.current_page || 1;
      const nextHasMore = nextPage < (response.meta?.last_page || 1);

      feedSession.items = nextItems;
      feedSession.page = nextPage;
      feedSession.hasMore = nextHasMore;
      setItems(nextItems);
      setPage(nextPage);
      setHasMore(nextHasMore);
    } catch (loadError) {
      setError(loadError.message);
    } finally {
      setLoading(false);
    }
  }, []);

  const loadNextPage = useCallback(async () => {
    if (loading || loadingMore || !hasMore) return;

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
  }, [hasMore, loading, loadingMore, page]);

  useEffect(() => {
    if (!feedSession.items.length) loadFirstPage();
  }, [loadFirstPage]);

  const rememberVisibleMoment = useCallback((index, id) => {
    feedSession.activeMomentIndex = index;
    feedSession.activeMomentId = id;
  }, []);

  const onViewableItemsChanged = useRef(({ viewableItems }) => {
    const visibleItem = viewableItems.find((entry) => entry.isViewable && entry.index != null);
    if (visibleItem) {
      feedSession.activeMomentIndex = visibleItem.index;
      feedSession.activeMomentId = visibleItem.item.id;
    }
  });

  useFocusEffect(useCallback(() => {
    if (!feedSize.height || !items.length) return undefined;

    const idIndex = feedSession.activeMomentId == null
      ? -1
      : items.findIndex((item) => item.id === feedSession.activeMomentId);
    const restoreIndex = Math.min(
      Math.max(idIndex >= 0 ? idIndex : feedSession.activeMomentIndex, 0),
      items.length - 1,
    );
    const frame = requestAnimationFrame(() => {
      feedRef.current?.scrollToIndex({ animated: false, index: restoreIndex });
    });

    return () => cancelAnimationFrame(frame);
  }, [feedSize.height, items]));

  return (
    <SafeAreaView
      style={commonStyles.screen}
      onLayout={(event) => setFeedSize({
        height: event.nativeEvent.layout.height,
        width: event.nativeEvent.layout.width,
      })}
    >
      {feedSize.height && feedSize.width ? (
        <FlatList
          ref={feedRef}
          data={items}
          keyExtractor={(item) => String(item.id)}
          renderItem={({ item, index }) => (
            <MomentSlide
              item={item}
              height={feedSize.height}
              width={feedSize.width}
              isFirst={index === 0}
              initialPhotoIndex={feedSession.photoIndexes[item.id] || 0}
              onPhotoChange={(photoIndex) => {
                feedSession.photoIndexes[item.id] = photoIndex;
              }}
              onPress={() => {
                rememberVisibleMoment(index, item.id);
                navigation.navigate('MomentDetail', { id: item.id });
              }}
            />
          )}
          pagingEnabled
          decelerationRate="fast"
          showsVerticalScrollIndicator={false}
          initialNumToRender={1}
          maxToRenderPerBatch={2}
          windowSize={3}
          removeClippedSubviews
          onRefresh={loadFirstPage}
          refreshing={loading}
          onViewableItemsChanged={onViewableItemsChanged.current}
          viewabilityConfig={viewabilityConfig.current}
          onEndReached={loadNextPage}
          onEndReachedThreshold={0.7}
          getItemLayout={(_, index) => ({ length: feedSize.height, offset: feedSize.height * index, index })}
        />
      ) : null}
      {loading && !items.length ? <View style={styles.loading}><ActivityIndicator color={colors.gold} /></View> : null}
      {!loading && !items.length ? <EmptyFeed error={error} onRetry={loadFirstPage} /> : null}
      <View pointerEvents="box-none" style={styles.overlay}>
        <View style={styles.header}>
          <View>
            <Text style={styles.eyebrow}>Moment</Text>
            <Text style={styles.title}>Pernikahan Bali</Text>
          </View>
          <View style={styles.headerActions}>
            {isAuthenticated ? (
              <Pressable accessibilityRole="button" onPress={() => navigation.navigate('Notifications')}>
                <Text style={styles.headerLink}>Notifikasi</Text>
              </Pressable>
            ) : null}
            <Pressable
              accessibilityRole="button"
              onPress={() => navigation.navigate(isAuthenticated ? 'MyInvitations' : 'Login', isAuthenticated ? undefined : { returnTo: 'MyInvitations' })}
            >
              <Text style={styles.headerLink}>Akun</Text>
            </Pressable>
          </View>
        </View>
        <Pressable
          accessibilityLabel="Buat undangan gratis"
          accessibilityRole="button"
          style={({ pressed }) => [styles.floatingCreate, pressed && styles.buttonPressed]}
          onPress={() => navigation.navigate('Template')}
        >
          <Text style={styles.plus}>+</Text>
          <Text style={styles.floatingLabel}>Buat{`\n`}Undangan</Text>
        </Pressable>
      </View>
      {loadingMore ? (
        <View style={styles.loadingMore}>
          <ActivityIndicator color={colors.goldLight} size="small" />
          <Text style={styles.loadingMoreText}>Memuat Moment berikutnya</Text>
        </View>
      ) : null}
    </SafeAreaView>
  );
}

function MomentSlide({ item, height, width, isFirst, initialPhotoIndex, onPhotoChange, onPress }) {
  const photos = item.photo_urls?.length
    ? item.photo_urls
    : item.cover_photo_url ? [item.cover_photo_url] : [];
  const safeInitialPhotoIndex = Math.min(Math.max(initialPhotoIndex, 0), Math.max(photos.length - 1, 0));
  const [activePhoto, setActivePhoto] = useState(safeInitialPhotoIndex);

  function updateActivePhoto(event) {
    const index = Math.round(event.nativeEvent.contentOffset.x / width);
    const nextIndex = Math.max(0, Math.min(index, photos.length - 1));
    setActivePhoto((currentIndex) => {
      if (currentIndex === nextIndex) return currentIndex;
      onPhotoChange(nextIndex);
      return nextIndex;
    });
  }

  return (
    <View style={[styles.slide, { height, width }]}>
      {photos.length ? (
        <FlatList
          data={photos}
          horizontal
          pagingEnabled
          nestedScrollEnabled
          directionalLockEnabled
          decelerationRate="fast"
          keyExtractor={(photo, index) => `${photo}-${index}`}
          showsHorizontalScrollIndicator={false}
          initialNumToRender={1}
          maxToRenderPerBatch={2}
          windowSize={3}
          initialScrollIndex={safeInitialPhotoIndex}
          onScroll={updateActivePhoto}
          scrollEventThrottle={16}
          onMomentumScrollEnd={updateActivePhoto}
          getItemLayout={(_, index) => ({ length: width, offset: width * index, index })}
          renderItem={({ item: photo, index }) => (
            <Pressable
              accessibilityLabel={`Lihat Moment ${item.names}, foto ${index + 1}`}
              accessibilityRole="button"
              onPress={onPress}
              style={({ pressed }) => [{ height, width }, pressed && styles.pressed]}
            >
              <ImageBackground source={{ uri: photo }} style={styles.cover} resizeMode="cover" />
            </Pressable>
          )}
        />
      ) : (
        <Pressable accessibilityRole="button" onPress={onPress} style={[styles.placeholder, { height, width }]}>
          <Text style={styles.initials}>{initials(item)}</Text>
          <Text style={styles.noPhoto}>Belum ada foto</Text>
        </Pressable>
      )}
      <LinearGradient pointerEvents="none" colors={['#15110d55', 'transparent', '#15110de8']} locations={[0, 0.35, 1]} style={styles.shade} />
      {photos.length > 1 ? (
        <>
          <View pointerEvents="none" style={styles.photoGuide}>
            <Text style={styles.photoGuideText}>Geser foto</Text>
            <Text style={styles.photoGuideArrow}>↔</Text>
          </View>
          <View pointerEvents="none" style={styles.photoCounter}>
            <Text style={styles.photoCounterText}>{activePhoto + 1} / {photos.length}</Text>
          </View>
        </>
      ) : null}
      <View pointerEvents="none" style={styles.slideInfo}>
        {photos.length > 1 ? <PhotoDots count={photos.length} active={activePhoto} /> : null}
        <Text style={styles.names}>{item.names}</Text>
        <Text numberOfLines={2} style={styles.caption}>{item.caption || 'Membagikan cerita menuju hari bahagia.'}</Text>
        <View style={styles.metrics}>
          <View style={styles.metricPill}><Text style={styles.metric}>Suka {item.reactions?.like || 0}</Text></View>
          <View style={styles.metricPill}><Text style={styles.metric}>Love {item.reactions?.love || 0}</Text></View>
          <View style={styles.metricPill}><Text style={styles.metric}>{item.comments_count || 0} komentar</Text></View>
        </View>
        <View style={styles.detailHint}>
          <Text style={styles.detailHintText}>Ketuk foto untuk lihat cerita & interaksi</Text>
          <Text style={styles.detailHintArrow}>›</Text>
        </View>
        {isFirst ? <Text style={styles.verticalHint}>Geser ke atas untuk Moment berikutnya ↑</Text> : null}
      </View>
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
      <Text style={styles.emptyTitle}>{error ? 'Feed belum dapat dimuat' : 'Moment akan hadir di sini'}</Text>
      <Text style={styles.empty}>{error || 'Undangan yang dipublish otomatis tampil sebagai Moment dengan foto dan nama panggilan pasangan.'}</Text>
      {error ? (
        <Pressable accessibilityRole="button" onPress={onRetry} style={styles.retryButton}>
          <Text style={styles.retryText}>Coba Lagi</Text>
        </Pressable>
      ) : null}
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
  headerLink: { color: colors.text, fontSize: 13, fontWeight: '700', paddingHorizontal: spacing.sm, paddingVertical: 4, textShadowColor: '#000', textShadowRadius: 8 },
  title: { color: colors.text, fontSize: 24, fontWeight: '700', marginTop: spacing.xs, textShadowColor: '#000', textShadowRadius: 8 },
  slide: { backgroundColor: colors.background, overflow: 'hidden' },
  cover: { flex: 1 },
  placeholder: { alignItems: 'center', backgroundColor: colors.surfaceAlt, justifyContent: 'center' },
  shade: { ...StyleSheet.absoluteFillObject },
  pressed: { opacity: 0.88 },
  buttonPressed: { opacity: 0.82, transform: [{ scale: 0.96 }] },
  initials: { color: colors.goldLight, fontSize: 48, fontWeight: '700', letterSpacing: 3 },
  noPhoto: { color: colors.muted, marginTop: spacing.sm },
  photoGuide: { alignItems: 'center', backgroundColor: '#15110daa', borderRadius: 99, flexDirection: 'row', left: spacing.lg, paddingHorizontal: 12, paddingVertical: 7, position: 'absolute', top: 88 },
  photoGuideText: { color: colors.text, fontSize: 11, fontWeight: '700' },
  photoGuideArrow: { color: colors.goldLight, fontSize: 16, marginLeft: 7 },
  photoCounter: { backgroundColor: '#15110dbb', borderRadius: 99, paddingHorizontal: 11, paddingVertical: 7, position: 'absolute', right: spacing.lg, top: 88 },
  photoCounterText: { color: colors.text, fontSize: 11, fontWeight: '800' },
  slideInfo: { bottom: 34, left: spacing.lg, position: 'absolute', right: 100 },
  dots: { flexDirection: 'row', gap: 5, marginBottom: spacing.sm },
  dot: { backgroundColor: '#ffffff66', borderRadius: 3, height: 5, width: 5 },
  dotActive: { backgroundColor: colors.goldLight, width: 18 },
  names: { color: colors.text, fontSize: 28, fontWeight: '800', textShadowColor: '#000', textShadowRadius: 8 },
  caption: { color: '#f0e7dc', lineHeight: 21, marginTop: spacing.xs, textShadowColor: '#000', textShadowRadius: 7 },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.xs, marginTop: spacing.md },
  metricPill: { backgroundColor: '#15110daa', borderRadius: 99, paddingHorizontal: 10, paddingVertical: 6 },
  metric: { color: colors.goldLight, fontSize: 11, fontWeight: '700' },
  detailHint: { alignItems: 'center', flexDirection: 'row', marginTop: spacing.sm },
  detailHintText: { color: colors.text, fontSize: 12, fontWeight: '700' },
  detailHintArrow: { color: colors.goldLight, fontSize: 22, marginLeft: 6 },
  verticalHint: { color: '#d5c6b2', fontSize: 11, marginTop: spacing.xs },
  floatingCreate: { alignItems: 'center', alignSelf: 'flex-end', backgroundColor: colors.gold, borderColor: '#efd69e', borderRadius: 42, borderWidth: 2, elevation: 8, height: 82, justifyContent: 'center', marginBottom: 6, shadowColor: '#000', shadowOffset: { height: 5, width: 0 }, shadowOpacity: 0.38, shadowRadius: 10, width: 82 },
  plus: { color: colors.background, fontSize: 28, fontWeight: '400', lineHeight: 27 },
  floatingLabel: { color: colors.background, fontSize: 10, fontWeight: '800', lineHeight: 12, textAlign: 'center' },
  loading: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center' },
  loadingMore: { alignItems: 'center', alignSelf: 'center', backgroundColor: '#15110ddd', borderRadius: 99, bottom: 20, flexDirection: 'row', paddingHorizontal: spacing.md, paddingVertical: spacing.sm, position: 'absolute' },
  loadingMoreText: { color: colors.text, fontSize: 11, fontWeight: '700', marginLeft: spacing.sm },
  emptyWrap: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center', paddingHorizontal: spacing.xl },
  emptyTitle: { color: colors.text, fontSize: 22, fontWeight: '700', textAlign: 'center' },
  empty: { color: colors.muted, lineHeight: 22, marginTop: spacing.sm, textAlign: 'center' },
  retryButton: { backgroundColor: colors.gold, borderRadius: 14, marginTop: spacing.lg, paddingHorizontal: spacing.lg, paddingVertical: 13 },
  retryText: { color: colors.background, fontWeight: '800' },
});
