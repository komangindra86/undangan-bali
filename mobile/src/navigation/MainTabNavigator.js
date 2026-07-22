import Ionicons from '@expo/vector-icons/Ionicons';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { StyleSheet, View } from 'react-native';
import MomentFeedScreen from '../screens/MomentFeedScreen';
import MyInvitationsScreen from '../screens/MyInvitationsScreen';
import NotificationsScreen from '../screens/NotificationsScreen';
import ProfileScreen from '../screens/ProfileScreen';
import { colors } from '../theme';

const Tab = createBottomTabNavigator();

function CreatePlaceholder() {
  return <View style={styles.placeholder} />;
}

export default function MainTabNavigator() {
  return (
    <Tab.Navigator
      initialRouteName="FeedTab"
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: colors.goldLight,
        tabBarInactiveTintColor: colors.muted,
        tabBarHideOnKeyboard: true,
        tabBarIcon: ({ color, focused, size }) => (
          <TabIcon color={color} focused={focused} routeName={route.name} size={size} />
        ),
        tabBarLabelStyle: styles.label,
        tabBarStyle: styles.tabBar,
      })}
    >
      <Tab.Screen name="FeedTab" component={MomentFeedScreen} options={{ tabBarLabel: 'Moment' }} />
      <Tab.Screen name="InvitationsTab" component={MyInvitationsScreen} options={{ tabBarLabel: 'Undangan' }} />
      <Tab.Screen
        name="CreateTab"
        component={CreatePlaceholder}
        listeners={({ navigation }) => ({
          tabPress: (event) => {
            event.preventDefault();
            navigation.getParent()?.navigate('Template');
          },
        })}
        options={{ tabBarLabel: 'Buat' }}
      />
      <Tab.Screen name="NotificationsTab" component={NotificationsScreen} options={{ tabBarLabel: 'Notifikasi' }} />
      <Tab.Screen name="ProfileTab" component={ProfileScreen} options={{ tabBarLabel: 'Profil' }} />
    </Tab.Navigator>
  );
}

function TabIcon({ color, focused, routeName, size }) {
  if (routeName === 'CreateTab') {
    return (
      <View style={styles.createIcon}>
        <Ionicons color={colors.background} name="add" size={30} />
      </View>
    );
  }

  const names = {
    FeedTab: focused ? 'images' : 'images-outline',
    InvitationsTab: focused ? 'mail-open' : 'mail-open-outline',
    NotificationsTab: focused ? 'notifications' : 'notifications-outline',
    ProfileTab: focused ? 'person' : 'person-outline',
  };

  return <Ionicons color={color} name={names[routeName]} size={size} />;
}

const styles = StyleSheet.create({
  tabBar: { backgroundColor: colors.surface, borderTopColor: colors.border, height: 68, paddingBottom: 7, paddingTop: 7 },
  label: { fontSize: 10, fontWeight: '700' },
  createIcon: { alignItems: 'center', backgroundColor: colors.gold, borderColor: colors.goldLight, borderRadius: 22, borderWidth: 2, height: 44, justifyContent: 'center', marginTop: -18, width: 44 },
  placeholder: { backgroundColor: colors.background, flex: 1 },
});
