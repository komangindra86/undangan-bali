import * as FileSystem from 'expo-file-system/legacy';

const MEDIA_DIR = `${FileSystem.documentDirectory}draft-media/`;

export async function persistLocalFile(uri, fileName) {
  if (!uri || uri.startsWith(MEDIA_DIR)) {
    return uri;
  }

  await FileSystem.makeDirectoryAsync(MEDIA_DIR, { intermediates: true });

  const safeName = (fileName || `media-${Date.now()}`)
    .replace(/[^a-zA-Z0-9._-]/g, '-')
    .replace(/-+/g, '-');
  const destination = `${MEDIA_DIR}${Date.now()}-${safeName}`;

  await FileSystem.copyAsync({ from: uri, to: destination });

  return destination;
}

export async function ensureLocalFileExists(file, label = 'file') {
  if (!file?.uri) {
    return;
  }

  const info = await FileSystem.getInfoAsync(file.uri);

  if (!info.exists) {
    throw new Error(`${label} tidak ditemukan lagi di perangkat. Silakan pilih ulang ${label} lalu publish kembali.`);
  }
}
