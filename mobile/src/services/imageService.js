import { manipulateAsync, SaveFormat } from 'expo-image-manipulator';
import * as ImagePicker from 'expo-image-picker';

async function optimizeImage(asset, maxWidth) {
  const actions = asset.width > maxWidth ? [{ resize: { width: maxWidth } }] : [];
  const result = await manipulateAsync(asset.uri, actions, {
    compress: 0.58,
    format: SaveFormat.JPEG,
  });

  return {
    uri: result.uri,
    width: result.width,
    height: result.height,
    mimeType: 'image/jpeg',
    fileName: `photo-${Date.now()}-${Math.round(Math.random() * 1000)}.jpg`,
  };
}

export async function pickProfilePhoto() {
  const selection = await ImagePicker.launchImageLibraryAsync({
    mediaTypes: ['images'],
    allowsEditing: true,
    aspect: [4, 5],
    quality: 0.8,
  });

  if (selection.canceled) {
    return null;
  }

  return optimizeImage(selection.assets[0], 760);
}

export async function pickGalleryPhotos(remainingSlots) {
  const selection = await ImagePicker.launchImageLibraryAsync({
    mediaTypes: ['images'],
    allowsMultipleSelection: true,
    selectionLimit: remainingSlots,
    orderedSelection: true,
    quality: 0.8,
  });

  if (selection.canceled) {
    return [];
  }

  return Promise.all(selection.assets.slice(0, remainingSlots).map((asset) => optimizeImage(asset, 960)));
}
