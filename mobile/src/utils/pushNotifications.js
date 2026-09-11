export function isAndroidDevicePushToken(deviceToken) {
  return ['android', 'fcm'].includes(deviceToken?.type)
    && typeof deviceToken?.data === 'string'
    && deviceToken.data.length > 0;
}
