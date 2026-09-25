import { useCallback, useState } from 'react';
import { ActivityIndicator, Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { SecondaryButton } from '../components/Buttons';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function BlockedUsersScreen({ navigation }) {
  const { token } = useAuth();
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [unblockingId, setUnblockingId] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const response = await api.blockedUsers(token);
      setUsers(response.data || []);
    } catch (error) {
      Alert.alert('Daftar blokir belum dapat dimuat', error.message);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  async function unblock(user) {
    setUnblockingId(user.id);
    try {
      await api.unblockUser(user.id, token);
      setUsers((current) => current.filter((entry) => entry.id !== user.id));
    } catch (error) {
      Alert.alert('Blokir belum dapat dibuka', error.message);
    } finally {
      setUnblockingId(null);
    }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali</Text>
        <Text style={commonStyles.eyebrow}>Privasi</Text>
        <Text style={commonStyles.title}>Pengguna Diblokir</Text>
        <Text style={styles.help}>Moment dan komentar dari pengguna yang diblokir tidak tampil untuk Anda, dan mereka tidak dapat berinteraksi dengan Moment Anda.</Text>
        {loading ? <ActivityIndicator color={colors.gold} style={styles.loading} /> : null}
        {!loading && users.length === 0 ? <Text style={styles.empty}>Belum ada pengguna yang diblokir.</Text> : null}
        {users.map((user) => (
          <View key={user.id} style={styles.row}>
            <Text style={styles.name}>{user.name}</Text>
            <SecondaryButton
              title={unblockingId === user.id ? 'Membuka...' : 'Buka Blokir'}
              onPress={() => unblock(user)}
              disabled={unblockingId === user.id}
              style={styles.unblock}
            />
          </View>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg },
  back: { color: colors.goldLight, marginBottom: spacing.md },
  help: { color: colors.muted, lineHeight: 21, marginTop: spacing.sm },
  loading: { marginTop: spacing.xl },
  empty: { color: colors.muted, marginTop: spacing.xl },
  row: { alignItems: 'center', borderBottomColor: colors.border, borderBottomWidth: 1, flexDirection: 'row', justifyContent: 'space-between', paddingVertical: spacing.md },
  name: { color: colors.text, flex: 1, fontSize: 16, fontWeight: '600' },
  unblock: { marginLeft: spacing.md, paddingHorizontal: spacing.md },
});
