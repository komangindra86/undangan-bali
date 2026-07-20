import { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, Alert, Linking, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function InvitationRequestsScreen({ navigation, route }) {
  const { invitation } = route.params;
  const { token, expireSession } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [sharingId, setSharingId] = useState(null);

  const load = useCallback(async () => {
    try {
      const response = await api.invitationRequests(invitation.id, token);
      setItems(response.data || []);
    } catch (error) {
      if (error.status === 401) {
        await expireSession();
        navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
        return;
      }
      Alert.alert('Permintaan undangan', error.message);
    } finally { setLoading(false); }
  }, [expireSession, invitation.id, navigation, token]);

  useEffect(() => { load(); }, [load]);

  async function share(item) {
    setSharingId(item.id);
    try {
      const response = await api.markInvitationRequestShared(invitation.id, item.id, token);
      await Linking.openURL(response.whatsapp_url);
      await load();
    } catch (error) {
      Alert.alert('Belum dapat membuka WhatsApp', error.message);
    } finally { setSharingId(null); }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Moment Saya</Text>
        <Text style={commonStyles.title}>Permintaan Undangan</Text>
        <Text style={styles.note}>Bagikan link undangan langsung lewat WhatsApp. Nomor peminta tidak muncul di feed publik.</Text>
        <SecondaryButton title="Kembali" onPress={() => navigation.goBack()} style={styles.back} />
        {loading ? <ActivityIndicator color={colors.gold} /> : null}
        {!loading && !items.length ? <Text style={styles.empty}>Belum ada permintaan undangan.</Text> : null}
        {items.map((item) => (
          <View key={item.id} style={styles.card}>
            <Text style={styles.name}>{item.requester_name}</Text>
            <Text style={styles.phone}>+{item.requester_whatsapp}</Text>
            <Text style={[styles.status, item.status === 'shared' && styles.shared]}>{item.status === 'shared' ? 'Sudah dibagikan' : 'Menunggu dibagikan'}</Text>
            <PrimaryButton title={item.status === 'shared' ? 'Bagikan Lagi' : 'Bagikan via WhatsApp'} onPress={() => share(item)} loading={sharingId === item.id} style={styles.share} />
          </View>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  note: { color: colors.muted, lineHeight: 21, marginTop: spacing.md },
  back: { marginBottom: spacing.lg, marginTop: spacing.lg },
  empty: { color: colors.muted },
  card: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 17, borderWidth: 1, marginBottom: spacing.sm, padding: spacing.md },
  name: { color: colors.text, fontSize: 17, fontWeight: '700' },
  phone: { color: colors.goldLight, marginTop: spacing.xs },
  status: { color: colors.muted, fontSize: 12, marginTop: spacing.sm },
  shared: { color: colors.success },
  share: { marginTop: spacing.md, minHeight: 46 },
});
