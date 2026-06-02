import { createNativeStackNavigator } from '@react-navigation/native-stack';
import AuthGateScreen from '../screens/AuthGateScreen';
import EventFormScreen from '../screens/EventFormScreen';
import GroomBrideFormScreen from '../screens/GroomBrideFormScreen';
import GalleryScreen from '../screens/GalleryScreen';
import GiftSetupScreen from '../screens/GiftSetupScreen';
import LandingScreen from '../screens/LandingScreen';
import LocationScreen from '../screens/LocationScreen';
import LoginScreen from '../screens/LoginScreen';
import MusicScreen from '../screens/MusicScreen';
import MyInvitationsScreen from '../screens/MyInvitationsScreen';
import PreviewScreen from '../screens/PreviewScreen';
import PayoutAccountScreen from '../screens/PayoutAccountScreen';
import PayoutHistoryScreen from '../screens/PayoutHistoryScreen';
import RegisterScreen from '../screens/RegisterScreen';
import RequestPayoutScreen from '../screens/RequestPayoutScreen';
import ShareScreen from '../screens/ShareScreen';
import SplashScreen from '../screens/SplashScreen';
import TemplateScreen from '../screens/TemplateScreen';
import TemplatePreviewScreen from '../screens/TemplatePreviewScreen';
import WeddingGiftDashboardScreen from '../screens/WeddingGiftDashboardScreen';
import WeddingGiftSettingScreen from '../screens/WeddingGiftSettingScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  return (
    <Stack.Navigator initialRouteName="Splash" screenOptions={{ headerShown: false, animation: 'slide_from_right' }}>
      <Stack.Screen name="Splash" component={SplashScreen} />
      <Stack.Screen name="Landing" component={LandingScreen} />
      <Stack.Screen name="Template" component={TemplateScreen} />
      <Stack.Screen name="TemplatePreview" component={TemplatePreviewScreen} />
      <Stack.Screen name="GroomBrideForm" component={GroomBrideFormScreen} />
      <Stack.Screen name="EventForm" component={EventFormScreen} />
      <Stack.Screen name="Location" component={LocationScreen} />
      <Stack.Screen name="Gallery" component={GalleryScreen} />
      <Stack.Screen name="Music" component={MusicScreen} />
      <Stack.Screen name="GiftSetup" component={GiftSetupScreen} />
      <Stack.Screen name="Preview" component={PreviewScreen} />
      <Stack.Screen name="AuthGate" component={AuthGateScreen} />
      <Stack.Screen name="Login" component={LoginScreen} />
      <Stack.Screen name="Register" component={RegisterScreen} />
      <Stack.Screen name="Share" component={ShareScreen} />
      <Stack.Screen name="MyInvitations" component={MyInvitationsScreen} />
      <Stack.Screen name="WeddingGiftSetting" component={WeddingGiftSettingScreen} />
      <Stack.Screen name="WeddingGiftDashboard" component={WeddingGiftDashboardScreen} />
      <Stack.Screen name="PayoutAccount" component={PayoutAccountScreen} />
      <Stack.Screen name="RequestPayout" component={RequestPayoutScreen} />
      <Stack.Screen name="PayoutHistory" component={PayoutHistoryScreen} />
    </Stack.Navigator>
  );
}
