export const MUSIC_CATEGORIES = [
  { id: 'pernikahan', label: 'Pernikahan' },
  { id: 'ulang-tahun', label: 'Ulang tahun' },
];

export function filterMusicCatalog(tracks, category = 'pernikahan') {
  return tracks.filter((track) => (track.categories || []).includes(category));
}

export function musicCategoryLabel(track) {
  return (track.categories || []).map((id) => MUSIC_CATEGORIES.find((item) => item.id === id)?.label)
    .filter(Boolean).join(' / ') || 'Musik bawaan';
}

export function musicPreviewSource(track) {
  return track.preview_url || track.audio_url || null;
}

export function musicSelectionError(music, rightsConfirmed) {
  if (music.music_type === 'default' && !music.music_id) {
    return { title: 'Pilih lagu', message: 'Pilih satu musik bawaan atau gunakan opsi tanpa musik.' };
  }
  if (music.music_type === 'upload' && !music.music_file?.uri) {
    return { title: 'Upload musik belum selesai', message: 'Pilih file MP3, WAV, atau M4A terlebih dahulu.' };
  }
  if (music.music_type === 'upload' && !rightsConfirmed) {
    return { title: 'Konfirmasi izin musik', message: 'Centang pernyataan bahwa Anda memiliki hak atau izin menggunakan musik tersebut.' };
  }
  return null;
}
