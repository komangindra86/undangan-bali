import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';
import * as Device from 'expo-device';
import * as Notifications from 'expo-notifications';
import { useEffect } from 'react';
import { Platform } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { navigateFromPush } from '../navigation/navigationRef';
import { api } from '../services/api';
import { SESSION_KEYS } from '../services/draftStorage';

if (Platform.OS !== 'web') {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldPlaySound: true,
      shouldSetBadge: true,
      shouldShowBanner: true,
      shouldShowList: true,
    }),
  });
}

const handledNotificationIds = new Set();

export default function PushNotificationManager() {
  const { token, loading } = useAuth();

  useEffect(() => {
    if (loading || !token || Platform.OS === 'web') return undefined;

    registerDevice(token).catch((error) => {
      if (__DEV__) console.warn('Push registration skipped:', error.message);
    });

    return undefined;
  }, [loading, token]);

  useEffect(() => {
    if (Platform.OS === 'web') return undefined;

    let active = true;

    const responseSubscription = Notifications.addNotificationResponseReceivedListener((response) => {
      if (active) handleNotificationResponse(response);
    });

    Notifications.getLastNotificationResponseAsync()
      .then((response) => {
        if (!active || !response) return;
        setTimeout(() => handleNotificationResponse(response), 1000);
      })
      .catch(() => {});

    return () => {
      active = false;
      responseSubscription.remove();
    };
  }, []);

  return null;
}

async function registerDevice(authToken) {
  if (!Device.isDevice) return;

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('social', {
      name: 'Aktivitas undangan',
      description: 'Permintaan undangan, komentar, reaksi, dan Wedding Gift.',
      importance: Notifications.AndroidImportance.HIGH,
      vibrationPattern: [0, 250, 200, 250],
      lightColor: '#c59b50',
      sound: 'default',
    });
  }

  const existingPermission = await Notifications.getPermissionsAsync();
  const permission = existingPermission.status === 'granted'
    ? existingPermission
    : await Notifications.requestPermissionsAsync();
  if (permission.status !== 'granted') return;

  const projectId = process.env.EXPO_PUBLIC_EAS_PROJECT_ID
    || Constants.easConfig?.projectId
    || Constants.expoConfig?.extra?.eas?.projectId;
  if (!projectId) {
    throw new Error('EXPO_PUBLIC_EAS_PROJECT_ID belum dikonfigurasi.');
  }

  const pushToken = (await Notifications.getExpoPushTokenAsync({ projectId })).data;
  await api.registerPushToken({
    token: pushToken,
    platform: Platform.OS,
    device_name: Device.deviceName || null,
    app_version: Constants.expoConfig?.version || null,
  }, authToken);
  await AsyncStorage.setItem(SESSION_KEYS.pushToken, pushToken);
  await Notifications.setBadgeCountAsync(0);
}

function handleNotificationResponse(response) {
  const identifier = response?.notification?.request?.identifier;
  if (identifier && handledNotificationIds.has(identifier)) return;
  if (identifier) handledNotificationIds.add(identifier);

  Notifications.setBadgeCountAsync(0).catch(() => {});
  navigateFromPush(response?.notification?.request?.content?.data || {});
}
