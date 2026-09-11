import { NativeModules, Platform } from 'react-native';

let testLabPromise;

async function isRunningInTestLab() {
  if (Platform.OS !== 'android' || !NativeModules.TestLab?.isRunningInTestLab) {
    return false;
  }

  testLabPromise ??= NativeModules.TestLab.isRunningInTestLab().catch(() => false);
  return testLabPromise;
}

export async function testEnvironmentHeaders() {
  return await isRunningInTestLab()
    ? { 'X-Firebase-Test-Lab': 'true' }
    : {};
}
