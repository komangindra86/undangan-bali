import { useCallback, useEffect, useRef, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { setAudioModeAsync, useAudioPlayer, useAudioPlayerStatus } from 'expo-audio';
import * as DocumentPicker from 'expo-document-picker';
import { ActivityIndicator, Alert, AppState, Linking, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions, SecondaryButton } from '../components/Buttons';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { api } from '../services/api';
import { persistLocalFile } from '../services/localMedia';
import { colors, spacing } from '../theme';
import { filterMusicCatalog, MUSIC_CATEGORIES, musicCategoryLabel, musicPreviewSource, musicSelectionError } from '../utils/musicCatalog';

export default function MusicScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [music, setMusic] = useState(draft.music_data || { music_type: 'none', music_id: null });
  const [tracks, setTracks] = useState([]);
  const [previewId, setPreviewId] = useState(null);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState('');
  const [previewError, setPreviewError] = useState('');
  const [category, setCategory] = useState(() => draft.invitation_type === 'birthday' ? 'ulang-tahun' : 'pernikahan');
  const [visibleCount, setVisibleCount] = useState(6);
  const [rightsConfirmed, setRightsConfirmed] = useState(false);
  const player = useAudioPlayer(null, { downloadFirst: false, updateInterval: 250 });
  const status = useAudioPlayerStatus(player);
  const requestVersion = useRef(0);
  const mounted = useRef(true);
  const previewRequest = useRef(0);
  const previewTrack = useRef(null);
  const filteredTracks = filterMusicCatalog(tracks, category);
  const selectedTrack = tracks.find((track) => String(track.id) === String(music.music_id));

  useEffect(() => {
    mounted.current = true;
    loadTracks();
    setAudioModeAsync({ playsInSilentMode: true, shouldPlayInBackground: false, interruptionMode: 'doNotMix' })
      .catch(() => {});
    return () => { mounted.current = false; requestVersion.current += 1; };
  }, []);

  const stopPreview = useCallback(() => {
    previewRequest.current += 1;
    previewTrack.current = null;
    try {
      player.pause();
      player.replace(null);
    } catch {
      // The audio hook may have already released its native player on unmount.
    }
    if (mounted.current) setPreviewId(null);
  }, [player]);

  useFocusEffect(useCallback(() => {
    const subscription = AppState.addEventListener('change', (state) => {
      if (state !== 'active') stopPreview();
    });
    return () => { subscription.remove(); stopPreview(); };
  }, [stopPreview]));

  useEffect(() => {
    setVisibleCount(6);
  }, [category]);

  useEffect(() => {
    if (previewId && status.currentTime >= 30 && status.playing) player.pause();
    if (previewId && status.error) {
      setPreviewError('Cuplikan belum dapat diputar. Coba lagi atau pilih lagu lain.');
      stopPreview();
    }
  }, [previewId, status.currentTime, status.playing, status.error, player, stopPreview]);

  useEffect(() => {
    if (!previewId || (status.isLoaded && !status.isBuffering)) return;
    const timeout = setTimeout(() => {
      setPreviewError('Musik terlalu lama dimuat. Periksa koneksi, lalu coba lagi.');
      stopPreview();
    }, 15000);
    return () => clearTimeout(timeout);
  }, [previewId, status.isLoaded, status.isBuffering, stopPreview]);

  async function loadTracks() {
    const version = ++requestVersion.current;
    setLoading(true);
    setLoadError('');
    try {
      const response = await api.musics();
      if (!Array.isArray(response.data)) throw new Error('Katalog tidak valid.');
      if (mounted.current && version === requestVersion.current) setTracks(response.data);
    } catch {
      if (mounted.current && version === requestVersion.current) setLoadError('Katalog belum termuat. Periksa koneksi lalu coba lagi. Pilihan musik Anda tidak dihapus.');
    } finally {
      if (mounted.current && version === requestVersion.current) setLoading(false);
    }
  }

  function selectMusic(value) {
    if (previewTrack.current !== `default-${value.music_id}`) stopPreview();
    setMusic(value);
  }

  async function togglePreview(source, id) {
    const version = ++previewRequest.current;
    setPreviewError('');
    try {
      if (!source) throw new Error('Audio tidak tersedia.');
      if (previewTrack.current === id && status.playing) {
        player.pause();
        return;
      }
      if (previewTrack.current !== id) {
        player.pause();
        player.replace(source);
        previewTrack.current = id;
        setPreviewId(id);
      } else if (status.didJustFinish || status.currentTime >= 30 || (status.duration > 0 && status.currentTime >= status.duration - 0.1)) {
        await player.seekTo(0);
      }
      if (version === previewRequest.current) player.play();
    } catch {
      if (version === previewRequest.current) {
        setPreviewError('Cuplikan belum dapat diputar. Coba lagi atau pilih lagu lain.');
        stopPreview();
      }
    }
  }

  async function pickCustomMusic() {
    const selection = await DocumentPicker.getDocumentAsync({
      type: ['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/m4a', 'audio/x-m4a'],
      multiple: false,
      copyToCacheDirectory: true,
      base64: false,
    });

    if (selection.canceled) {
      return;
    }

    const asset = selection.assets[0];
    const extension = asset.name?.split('.').pop()?.toLowerCase();
    if (!['mp3', 'wav', 'm4a'].includes(extension)) {
      Alert.alert('Format tidak didukung', 'Gunakan file musik MP3, WAV, atau M4A.');
      return;
    }
    if (asset.size && asset.size > 10 * 1024 * 1024) {
      Alert.alert('File terlalu besar', 'Ukuran musik maksimal 10 MB agar undangan tetap ringan.');
      return;
    }

    stopPreview();
    setRightsConfirmed(false);
    const fileName = asset.name || `musik-undangan.${extension}`;
    setMusic({
      music_type: 'upload',
      music_id: null,
      music_file: {
        uri: await persistLocalFile(asset.uri, fileName),
        fileName,
        mimeType: asset.mimeType || mimeTypeFor(extension),
        size: asset.size || null,
      },
    });
  }

  async function next() {
    stopPreview();
    const validation = musicSelectionError(music, rightsConfirmed);
    if (validation) {
      Alert.alert(validation.title, validation.message);
      return;
    }
    try {
      await saveSection('music_data', music);
      navigation.navigate('GiftSetup');
    } catch {
      Alert.alert('Pilihan belum tersimpan', 'Coba tekan Lanjut lagi. Pilihan musik Anda tetap ada di halaman ini.');
    }
  }

  return (
    <WizardLayout
      step={6}
      title="Pilih musik"
      subtitle="Dengarkan cuplikan terlebih dahulu, lalu pilih musik yang paling cocok dengan undangan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => { stopPreview(); navigation.goBack(); }} onNext={next} loading={syncing} />}
    >
      <Choice
        title="Tanpa musik"
        subtitle="Undangan dibuka dalam keadaan hening."
        selected={music.music_type === 'none'}
        onSelect={() => selectMusic({ music_type: 'none', music_id: null, music_file: null })}
      />
      {music.music_type === 'default' && music.music_id ? (
        <View style={styles.selectionSummary} accessibilityLiveRegion="polite">
          <Text style={styles.checked}>Musik pilihan Anda</Text>
          <Text style={styles.title}>{selectedTrack?.title || 'Musik bawaan tersimpan'}</Text>
          <Text style={styles.subtitle}>Pilihan tetap tersimpan saat Anda berpindah kategori. Kredit musik disertakan otomatis.</Text>
        </View>
      ) : null}
      <Text style={styles.catalogTitle}>Musik sesuai acara</Text>
      <Text style={styles.catalogHint}>Pilih kategori, dengarkan cuplikan 30 detik, lalu tentukan lagu. Audio hanya dimuat saat tombol Putar ditekan agar aplikasi tetap ringan.</Text>
      <View style={styles.categories}>
        {MUSIC_CATEGORIES.map((item) => (
          <Pressable key={item.id} accessibilityRole="button" accessibilityState={{ selected: category === item.id }}
            onPress={() => { if (previewTrack.current) stopPreview(); setCategory(item.id); }} style={[styles.chip, category === item.id && styles.selected]}>
            <Text style={category === item.id ? styles.checkedChip : styles.chipText}>{item.label}</Text>
          </Pressable>
        ))}
      </View>
      {loading ? <ActivityIndicator color={colors.gold} accessibilityLabel="Memuat katalog musik" style={styles.loading} /> : null}
      {loadError ? <View style={styles.notice}><Text style={styles.error}>{loadError}</Text><SecondaryButton title="Coba lagi" onPress={loadTracks} style={styles.selectFile} /></View> : null}
      {previewError ? <Text style={styles.error} accessibilityLiveRegion="polite">{previewError}</Text> : null}
      {!loading && !loadError ? <Text style={styles.resultCount}>{filteredTracks.length ? `${filteredTracks.length} musik ${MUSIC_CATEGORIES.find((item) => item.id === category)?.label.toLowerCase()} tersedia` : 'Musik untuk kategori ini belum tersedia.'}</Text> : null}
      {filteredTracks.slice(0, visibleCount).map((track) => (
        <Choice
          key={track.id}
          title={track.title}
          subtitle={musicCategoryLabel(track)}
          credit={track}
          selected={music.music_type === 'default' && String(music.music_id) === String(track.id)}
          onSelect={() => selectMusic({ music_type: 'default', music_id: track.id, music_file: null })}
          onPreview={() => togglePreview(musicPreviewSource(track), `default-${track.id}`)}
          playing={previewId === `default-${track.id}` && status.playing}
          buffering={previewId === `default-${track.id}` && (!status.isLoaded || status.isBuffering)}
        />
      ))}
      {filteredTracks.length > visibleCount ? <SecondaryButton title="Tampilkan musik lainnya" onPress={() => setVisibleCount((count) => count + 6)} style={styles.more} /> : null}
      <View style={[styles.upload, music.music_type === 'upload' && styles.selected]}>
        <Text style={styles.title}>Upload musik sendiri</Text>
        <Text style={styles.subtitle}>MP3, WAV, atau M4A | Maksimal 10 MB</Text>
        <View style={styles.rightsWarning}>
          <Text style={styles.rightsTitle}>Penting tentang izin penggunaan</Text>
          <Text style={styles.rightsText}>Jangan mengunggah musik dari YouTube, Spotify, atau sumber lain tanpa izin. Anda bertanggung jawab memastikan musik boleh dipakai dan dibagikan pada undangan publik.</Text>
        </View>
        {music.music_file?.uri ? (
          <>
            <Text style={styles.fileName} numberOfLines={1}>{music.music_file.fileName}</Text>
            <View style={styles.uploadActions}>
              <SecondaryButton title="Ganti File" onPress={pickCustomMusic} style={styles.uploadButton} />
              <SecondaryButton
                title={previewId === 'upload' && status.playing ? 'Pause' : 'Play'}
                onPress={() => togglePreview(music.music_file.uri, 'upload')}
                style={styles.uploadButton}
              />
            </View>
            {music.music_type !== 'upload' ? (
              <Pressable onPress={() => { setRightsConfirmed(false); setMusic({ ...music, music_type: 'upload', music_id: null }); }}>
                <Text style={styles.useFile}>Gunakan file ini</Text>
              </Pressable>
            ) : <Text style={styles.checked}>Dipilih</Text>}
            {music.music_type === 'upload' ? (
              <Pressable
                accessibilityRole="checkbox"
                accessibilityState={{ checked: rightsConfirmed }}
                onPress={() => setRightsConfirmed((value) => !value)}
                style={styles.rightsConfirmation}
              >
                <View style={[styles.checkbox, rightsConfirmed && styles.checkboxChecked]}>
                  <Text style={styles.checkboxMark}>{rightsConfirmed ? 'OK' : ''}</Text>
                </View>
                <Text style={styles.rightsConfirmationText}>Saya menyatakan memiliki hak atau izin untuk menggunakan musik ini pada undangan publik.</Text>
              </Pressable>
            ) : null}
          </>
        ) : (
          <SecondaryButton title="Pilih File Musik" onPress={pickCustomMusic} style={styles.selectFile} />
        )}
      </View>
    </WizardLayout>
  );
}

function mimeTypeFor(extension) {
  return {
    mp3: 'audio/mpeg',
    wav: 'audio/wav',
    m4a: 'audio/mp4',
  }[extension] || 'audio/mpeg';
}

function Choice({ title, subtitle, selected, onSelect, onPreview, playing, buffering, credit, disabled = false }) {
  return (
    <View style={[styles.choice, selected && styles.selected, disabled && styles.disabled]}>
      <View style={styles.choiceRow}>
        <Pressable accessibilityRole="radio" accessibilityLabel={`Pilih ${title}`} accessibilityState={{ checked: selected, disabled }} disabled={disabled} onPress={onSelect} style={styles.details}>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.subtitle}>{subtitle}</Text>
          <Text style={styles.checked}>{selected ? 'Dipilih' : 'Ketuk untuk memilih'}</Text>
        </Pressable>
        {onPreview ? (
          <Pressable accessibilityRole="button" accessibilityLabel={`${playing ? 'Jeda' : 'Putar cuplikan'} ${title}`} onPress={onPreview} style={styles.preview}>
            {buffering ? <ActivityIndicator color={colors.gold} /> : <Text style={styles.previewText}>{playing ? 'Jeda' : 'Putar'}</Text>}
          </Pressable>
        ) : null}
      </View>
      {credit?.attribution ? (
        <View style={styles.credit}>
          <Text style={styles.creditText}>{credit.attribution}</Text>
          <View style={styles.creditLinks}>
            <CreditLink url={credit.source_url} label="Sumber musik" />
            <CreditLink url={credit.license_url} label={credit.license_code || 'Lisensi'} />
          </View>
          {credit.modifications ? <Text style={styles.creditText}>{credit.modifications}</Text> : null}
        </View>
      ) : null}
    </View>
  );
}

function CreditLink({ url, label }) {
  if (!url || !url.startsWith('https://')) return null;
  return <Pressable accessibilityRole="link" onPress={() => Linking.openURL(url).catch(() => Alert.alert('Tautan belum terbuka', 'Coba lagi setelah koneksi tersedia.'))} style={styles.creditLink}><Text style={styles.linkText}>{label}</Text></Pressable>;
}

const styles = StyleSheet.create({
  choice: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: spacing.sm,
    overflow: 'hidden',
  },
  choiceRow: { flexDirection: 'row', alignItems: 'center' },
  catalogTitle: { color: colors.text, fontWeight: '700', fontSize: 20, marginTop: spacing.md },
  catalogHint: { color: colors.muted, fontSize: 13, lineHeight: 20, marginTop: spacing.xs, marginBottom: spacing.md },
  categories: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.xs, marginBottom: spacing.md },
  chip: { borderWidth: 1, borderColor: colors.border, borderRadius: 22, paddingHorizontal: 14, minHeight: 44, justifyContent: 'center', backgroundColor: colors.surface },
  chipText: { color: colors.muted, fontSize: 13 },
  checkedChip: { color: colors.goldLight, fontWeight: '700', fontSize: 13 },
  resultCount: { color: colors.muted, marginBottom: spacing.md, lineHeight: 21 },
  selectionSummary: { backgroundColor: colors.surfaceAlt, borderRadius: 16, padding: spacing.md, marginBottom: spacing.md },
  credit: { borderTopWidth: 1, borderTopColor: colors.border, padding: spacing.md },
  creditText: { color: colors.muted, fontSize: 12, lineHeight: 18 },
  creditLinks: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md },
  creditLink: { minHeight: 44, justifyContent: 'center' },
  linkText: { color: colors.goldLight, fontSize: 12, textDecorationLine: 'underline' },
  error: { color: colors.danger, lineHeight: 21, marginBottom: spacing.sm },
  loading: { marginVertical: spacing.md },
  notice: { marginBottom: spacing.md },
  more: { marginBottom: spacing.lg },
  selected: {
    borderColor: colors.gold,
  },
  disabled: {
    opacity: 0.45,
  },
  details: {
    flex: 1,
    padding: spacing.md,
  },
  title: {
    color: colors.text,
    fontSize: 16,
    fontWeight: '600',
  },
  subtitle: {
    color: colors.muted,
    marginTop: 4,
  },
  checked: {
    color: colors.gold,
    fontWeight: '700',
    marginTop: spacing.sm,
  },
  preview: {
    alignItems: 'center',
    backgroundColor: colors.surfaceAlt,
    borderColor: colors.border,
    borderLeftWidth: 1,
    justifyContent: 'center',
    minHeight: 84,
    paddingHorizontal: spacing.md,
  },
  previewText: {
    color: colors.goldLight,
    fontWeight: '700',
  },
  upload: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: spacing.sm,
    padding: spacing.md,
  },
  rightsWarning: {
    backgroundColor: colors.surfaceAlt,
    borderColor: colors.gold,
    borderRadius: 12,
    borderWidth: 1,
    marginTop: spacing.md,
    padding: spacing.md,
  },
  rightsTitle: { color: colors.goldLight, fontWeight: '700', marginBottom: spacing.xs },
  rightsText: { color: colors.muted, fontSize: 13, lineHeight: 20 },
  rightsConfirmation: { alignItems: 'flex-start', flexDirection: 'row', gap: spacing.sm, marginTop: spacing.md, minHeight: 48 },
  rightsConfirmationText: { color: colors.text, flex: 1, fontSize: 13, lineHeight: 20 },
  checkbox: { alignItems: 'center', borderColor: colors.border, borderRadius: 5, borderWidth: 1, height: 24, justifyContent: 'center', width: 24 },
  checkboxChecked: { backgroundColor: colors.gold, borderColor: colors.gold },
  checkboxMark: { color: colors.background, fontWeight: '900' },
  fileName: {
    color: colors.goldLight,
    fontWeight: '600',
    marginTop: spacing.md,
  },
  selectFile: {
    marginTop: spacing.md,
    minHeight: 46,
  },
  uploadActions: {
    flexDirection: 'row',
    gap: spacing.sm,
    marginTop: spacing.md,
  },
  uploadButton: {
    flex: 1,
    minHeight: 46,
    paddingHorizontal: spacing.sm,
  },
  useFile: {
    color: colors.goldLight,
    fontWeight: '700',
    marginTop: spacing.md,
    paddingVertical: spacing.xs,
  },
});
