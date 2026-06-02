import { NavigationContainer, DarkTheme } from '@react-navigation/native';
import { StatusBar } from 'expo-status-bar';
import { AuthProvider } from './src/context/AuthContext';
import { DraftProvider } from './src/context/DraftContext';
import AppNavigator from './src/navigation/AppNavigator';
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
        <NavigationContainer theme={navigationTheme}>
          <StatusBar style="light" />
          <AppNavigator />
        </NavigationContainer>
      </DraftProvider>
    </AuthProvider>
  );
}
