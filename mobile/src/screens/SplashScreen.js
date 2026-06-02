import { useEffect } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { colors, commonStyles } from '../theme';

export default function SplashScreen({ navigation }) {
  const { loading: authLoading } = useAuth();
  const { loading: draftLoading } = useDraft();

  useEffect(() => {
    if (!authLoading && !draftLoading) {
      const timer = setTimeout(() => navigation.replace('Landing'), 900);
      return () => clearTimeout(timer);
    }
    return undefined;
  }, [authLoading, draftLoading, navigation]);

  return (
    <LinearGradient colors={['#14100c', '#261d13']} style={styles.container}>
      <View style={styles.mark} />
      <Text style={commonStyles.eyebrow}>Undangan</Text>
      <Text style={styles.title}>Pernikahan Bali</Text>
      <Text style={styles.caption}>Cerita indah dimulai dari sini</Text>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  mark: {
    width: 62,
    height: 62,
    marginBottom: 30,
    borderWidth: 1,
    borderColor: colors.gold,
    transform: [{ rotate: '45deg' }],
  },
  title: {
    marginTop: 12,
    color: colors.text,
    fontSize: 37,
    fontWeight: '500',
  },
  caption: {
    color: colors.muted,
    marginTop: 18,
  },
});
