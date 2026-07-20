import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Linking, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function MyInvitationsScreen({ navigation }) {
  const { token, expireSession } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  async function toggleFeed(item) {
    try {
      const response = await api.setFeedVisibility(item.id, !item.is_hidden_from_feed, token);
      setItems((current) => current.map((entry) => entry.id === item.id ? { ...entry, is_hidden_from_feed: response.data.is_hidden_from_feed } : entry));
    } catch (error) {
      Alert.alert('Feed Moment', error.message);
    }
  }

  useEffect(() => {
    if (!token) {
      navigation.replace('Login', { returnTo: 'MyInvitations' });
      return;
    }

    api.invitations(token)
      .then((response) => setItems(response.data))
      .catch(async (error) => {
        if (error.status === 401) {
          await expireSession();
          navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
          return;
        }
        Alert.alert('Tidak dapat memuat data', error.message);
      })
      .finally(() => setLoading(false));
  }, [token, navigation]);

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Akun Saya</Text>
        <Text style={commonStyles.title}>Undangan Saya</Text>
        <SecondaryButton title="Kembali" onPress={() => navigation.goBack()} style={styles.back} />
        {loading ? <ActivityIndicator color={colors.gold} style={styles.loading} /> : null}
        {!loading && items.length === 0 ? <Text style={styles.empty}>Belum ada undangan tersimpan.</Text> : null}
        {items.map((item) => (
          <View style={styles.card} key={item.id}>
            <Text style={styles.name}>{item.groom_nickname || 'Mempelai'} & {item.bride_nickname || 'Pasangan'}</Text>
            <Text style={styles.meta}>{item.event_date || 'Tanggal belum diisi'} | {item.status}</Text>
            {item.status === 'published' ? (
              <>
                <Text style={styles.open} onPress={() => Linking.openURL(item.public_url || `${api.siteUrl}/u/${item.slug}`)}>
                  Buka undangan
                </Text>
                <SecondaryButton
                  title="Atur Wedding Gift"
                  onPress={() => navigation.navigate('WeddingGiftSetting', { invitation: item })}
                  style={styles.giftButton}
                />
                <SecondaryButton
                  title="Dashboard Gift"
                  onPress={() => navigation.navigate('WeddingGiftDashboard', { invitation: item })}
                  style={styles.giftButton}
                />
                <SecondaryButton title="Kelola Timeline Moment" onPress={() => navigation.navigate('ManageMoments', { invitation: item })} style={styles.giftButton} />
                <SecondaryButton title="Permintaan Undangan" onPress={() => navigation.navigate('InvitationRequests', { invitation: item })} style={styles.giftButton} />
                <SecondaryButton
                  title={item.is_hidden_from_feed ? 'Tampilkan di Feed Moment' : 'Sembunyikan dari Feed Moment'}
                  onPress={() => toggleFeed(item)}
                  style={styles.giftButton}
                />
              </>
            ) : null}
          </View>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: {
    padding: spacing.lg,
  },
  back: {
    alignSelf: 'flex-start',
    marginTop: spacing.lg,
    marginBottom: spacing.xl,
    minHeight: 46,
  },
  loading: {
    marginTop: spacing.lg,
  },
  empty: {
    color: colors.muted,
  },
  card: {
    backgroundColor: colors.surface,
    borderRadius: 18,
    borderColor: colors.border,
    borderWidth: 1,
    padding: spacing.md,
    marginBottom: spacing.sm,
  },
  name: {
    color: colors.text,
    fontWeight: '700',
    fontSize: 17,
  },
  meta: {
    color: colors.muted,
    marginTop: spacing.xs,
    textTransform: 'capitalize',
  },
  open: {
    color: colors.gold,
    fontWeight: '700',
    marginTop: spacing.md,
  },
  giftButton: {
    marginTop: spacing.sm,
    minHeight: 44,
  },
});
