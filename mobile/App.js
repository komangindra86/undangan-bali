import { NavigationContainer, DarkTheme } from '@react-navigation/native';
import { StatusBar } from 'expo-status-bar';
import PushNotificationManager from './src/components/PushNotificationManager';
import { AuthProvider } from './src/context/AuthContext';
import { DraftProvider } from './src/context/DraftContext';
import AppNavigator from './src/navigation/AppNavigator';
import { navigationRef } from './src/navigation/navigationRef';
import { colors } from './src/theme';

const navigationTheme = {
  ...DarkTheme,
  colors: {
    ...DarkTheme.colors,
    background: colors.background,
    card: colors.surface,
    primary: colors.gold,
    text: colors.text,
    border: colors.border,
  },
};

export default function App() {
  return (
    <AuthProvider>
      <DraftProvider>
        <NavigationContainer ref={navigationRef} theme={navigationTheme}>
          <StatusBar style="light" />
          <PushNotificationManager />
          <AppNavigator />
        </NavigationContainer>
      </DraftProvider>
    </AuthProvider>
  );
}
