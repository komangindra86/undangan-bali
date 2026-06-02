import { useEffect, useState } from 'react';
import { useAudioPlayer, useAudioPlayerStatus } from 'expo-audio';
import * as DocumentPicker from 'expo-document-picker';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { FooterActions, SecondaryButton } from '../components/Buttons';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { api } from '../services/api';
import { colors, spacing } from '../theme';

export default function MusicScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const [music, setMusic] = useState(draft.music_data || { music_type: 'none', music_id: null });
  const [tracks, setTracks] = useState([]);
  const [previewId, setPreviewId] = useState(null);
  const player = useAudioPlayer(null);
  const status = useAudioPlayerStatus(player);

  useEffect(() => {
    api.musics()
      .then((response) => setTracks(response.data))
      .catch((error) => Alert.alert('Musik belum termuat', error.message));
  }, []);

  async function togglePreview(source, id) {
    if (previewId === id && status.playing) {
      player.pause();
      return;
    }

    if (previewId !== id) {
      player.replace(source);
      setPreviewId(id);
    }

    player.play();
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

    player.pause();
    setPreviewId(null);
    setMusic({
      music_type: 'upload',
      music_id: null,
      music_file: {
        uri: asset.uri,
        fileName: asset.name || `musik-undangan.${extension}`,
        mimeType: asset.mimeType || mimeTypeFor(extension),
        size: asset.size || null,
      },
    });
  }

  async function next() {
    player.pause();
    if (music.music_type === 'default' && !music.music_id) {
      Alert.alert('Pilih lagu', 'Pilih satu musik bawaan atau gunakan opsi tanpa musik.');
      return;
    }
    if (music.music_type === 'upload' && !music.music_file?.uri) {
      Alert.alert('Upload musik belum selesai', 'Pilih file MP3, WAV, atau M4A terlebih dahulu.');
      return;
    }
    await saveSection('music_data', music);
    navigation.navigate('GiftSetup');
  }

  return (
    <WizardLayout
      step={6}
      title="Pilih musik"
      subtitle="Dengarkan cuplikan terlebih dahulu, lalu pilih musik yang paling cocok dengan undangan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <Choice
        title="Tanpa musik"
        subtitle="Undangan dibuka dalam keadaan hening."
        selected={music.music_type === 'none'}
        onSelect={() => setMusic({ music_type: 'none', music_id: null, music_file: null })}
      />
      {tracks.map((track) => (
        <Choice
          key={track.id}
          title={track.title}
          subtitle="Instrumental Bali ringan | Cuplikan 16 detik"
          selected={music.music_type === 'default' && music.music_id === track.id}
          onSelect={() => setMusic({ music_type: 'default', music_id: track.id, music_file: null })}
          onPreview={() => togglePreview(track.audio_url, `default-${track.id}`)}
          playing={previewId === `default-${track.id}` && status.playing}
        />
      ))}
      <View style={[styles.upload, music.music_type === 'upload' && styles.selected]}>
        <Text style={styles.title}>Upload musik sendiri</Text>
        <Text style={styles.subtitle}>MP3, WAV, atau M4A | Maksimal 10 MB</Text>
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
              <Pressable onPress={() => setMusic({ ...music, music_type: 'upload', music_id: null })}>
                <Text style={styles.useFile}>Gunakan file ini</Text>
              </Pressable>
            ) : <Text style={styles.checked}>Dipilih</Text>}
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

function Choice({ title, subtitle, selected, onSelect, onPreview, playing, disabled = false }) {
  return (
    <View style={[styles.choice, selected && styles.selected, disabled && styles.disabled]}>
      <Pressable disabled={disabled} onPress={onSelect} style={styles.details}>
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.subtitle}>{subtitle}</Text>
        {selected ? <Text style={styles.checked}>Dipilih</Text> : null}
      </Pressable>
      {onPreview ? (
        <Pressable onPress={onPreview} style={styles.preview}>
          <Text style={styles.previewText}>{playing ? 'Pause' : 'Play'}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  choice: {
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    flexDirection: 'row',
    marginBottom: spacing.sm,
    overflow: 'hidden',
  },
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
