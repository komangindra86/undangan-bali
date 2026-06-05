import { useEffect, useState } from 'react';
import { ActivityIndicator, Alert, ScrollView, StyleSheet, Switch, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';
import { cleanText, firstError, validateSafeText } from '../utils/validation';

const initialSetting = {
  is_active: false,
  receiver_name: '',
  receiver_note: '',
  minimum_amount: '10000',
  show_amount_public: false,
  allow_message: true,
};

export default function WeddingGiftSettingScreen({ navigation, route }) {
  const invitation = route.params?.invitation;
  const { token, expireSession } = useAuth();
  const [setting, setSetting] = useState(initialSetting);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    api.giftSetting(invitation.id, token)
      .then((response) => setSetting({
        ...response.data,
        minimum_amount: String(response.data.minimum_amount),
      }))
      .catch((error) => handleError(error, 'Pengaturan belum dapat dimuat.'))
      .finally(() => setLoading(false));
  }, [invitation.id, token]);

  async function handleError(error, fallback) {
    if (error.status === 401) {
      await expireSession();
      navigation.replace('Login', { returnTo: 'MyInvitations', sessionExpired: true });
      return;
    }
    Alert.alert('Wedding Gift', error.message || fallback);
  }

  async function save() {
    const minimum = Number(setting.minimum_amount);
    if (setting.is_active && !setting.receiver_name.trim()) {
      Alert.alert('Nama penerima dibutuhkan', 'Masukkan nama penerima Wedding Gift.');
      return;
    }
    const error = firstError([
      validateSafeText(setting.receiver_name, 'Nama penerima Wedding Gift', { required: setting.is_active, max: 80 }),
      validateSafeText(setting.receiver_note, 'Catatan penerima', { max: 300 }),
    ]);
    if (error) {
      Alert.alert('Periksa Wedding Gift', error);
      return;
    }
    if (!Number.isInteger(minimum) || minimum < 10000) {
      Alert.alert('Nominal minimum tidak valid', 'Minimum gift paling kecil Rp10.000.');
      return;
    }

    setSaving(true);
    try {
      const response = await api.saveGiftSetting(invitation.id, {
        is_active: setting.is_active,
        receiver_name: cleanText(setting.receiver_name) || null,
        receiver_note: cleanText(setting.receiver_note) || null,
        minimum_amount: minimum,
        show_amount_public: setting.show_amount_public,
        allow_message: setting.allow_message,
      }, token);
      setSetting({ ...response.data, minimum_amount: String(response.data.minimum_amount) });
      Alert.alert('Tersimpan', 'Pengaturan Wedding Gift sudah diperbarui.');
    } catch (error) {
      await handleError(error, 'Pengaturan belum berhasil disimpan.');
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <SafeAreaView style={[commonStyles.screen, styles.loading]}>
        <ActivityIndicator color={colors.gold} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Pengaturan</Text>
        <Text style={commonStyles.title}>Wedding Gift</Text>
        <Text style={styles.subtitle}>{invitation.groom_nickname} & {invitation.bride_nickname}</Text>
        <View style={styles.notice}>
          <Text style={styles.noticeTitle}>Pembayaran hanya di halaman web</Text>
          <Text style={styles.noticeText}>Aplikasi ini hanya untuk mengatur dan memantau gift. Tamu membayar QRIS melalui link undangan publik.</Text>
        </View>
        <View style={styles.toggle}>
          <View style={styles.toggleCopy}>
            <Text style={styles.toggleTitle}>Aktifkan Wedding Gift</Text>
            <Text style={styles.small}>Tampilkan form gift pada undangan publik.</Text>
          </View>
          <Switch
            value={setting.is_active}
            onValueChange={(value) => setSetting({ ...setting, is_active: value })}
            trackColor={{ true: colors.gold, false: colors.border }}
            thumbColor={colors.text}
          />
        </View>
        <FormField
          label="Nama penerima"
          placeholder="Contoh: Made & Ayu"
          maxLength={80}
          value={setting.receiver_name}
          onChangeText={(value) => setSetting({ ...setting, receiver_name: value })}
        />
        <FormField
          label="Catatan penerima (opsional)"
          placeholder="Terima kasih atas tanda kasih Anda."
          multiline
          maxLength={300}
          value={setting.receiver_note}
          onChangeText={(value) => setSetting({ ...setting, receiver_note: value })}
        />
        <FormField
          label="Minimum gift"
          placeholder="10000"
          keyboardType="numeric"
          value={setting.minimum_amount}
          onChangeText={(value) => setSetting({ ...setting, minimum_amount: value.replace(/\D/g, '') })}
        />
        <View style={styles.feeCard}>
          <Text style={styles.feeLabel}>Biaya layanan ditetapkan aplikasi</Text>
          <Text style={styles.feeValue}>{feeText()}</Text>
          <Text style={styles.small}>Fee tampil transparan pada halaman pembayaran dan tidak mengurangi nominal gift pasangan.</Text>
        </View>
        <ToggleRow
          title="Tampilkan nominal secara publik"
          value={setting.show_amount_public}
          onValueChange={(value) => setSetting({ ...setting, show_amount_public: value })}
        />
        <ToggleRow
          title="Izinkan ucapan dari tamu"
          value={setting.allow_message}
          onValueChange={(value) => setSetting({ ...setting, allow_message: value })}
        />
        <PrimaryButton title="Simpan Pengaturan" onPress={save} loading={saving} style={styles.button} />
        <SecondaryButton title="Lihat Dashboard Gift" onPress={() => navigation.navigate('WeddingGiftDashboard', { invitation })} style={styles.secondary} />
        <Text onPress={() => navigation.goBack()} style={styles.back}>Kembali ke Undangan Saya</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

function ToggleRow({ title, value, onValueChange }) {
  return (
    <View style={styles.switchRow}>
      <Text style={styles.switchText}>{title}</Text>
      <Switch value={value} onValueChange={onValueChange} trackColor={{ true: colors.gold, false: colors.border }} thumbColor={colors.text} />
    </View>
  );
}

function feeText() {
  return 'Rp2.000 untuk gift di bawah Rp100.000, lalu 2% untuk Rp100.000 ke atas';
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  loading: { alignItems: 'center', justifyContent: 'center' },
  subtitle: { color: colors.muted, fontSize: 15, marginBottom: spacing.lg, marginTop: spacing.xs },
  notice: { backgroundColor: colors.surfaceAlt, borderColor: colors.gold, borderRadius: 16, borderWidth: 1, marginBottom: spacing.lg, padding: spacing.md },
  noticeTitle: { color: colors.goldLight, fontSize: 15, fontWeight: '700', marginBottom: spacing.xs },
  noticeText: { color: colors.muted, fontSize: 13, lineHeight: 20 },
  toggle: { alignItems: 'center', backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, flexDirection: 'row', justifyContent: 'space-between', marginBottom: spacing.lg, padding: spacing.md },
  toggleCopy: { flex: 1, paddingRight: spacing.sm },
  toggleTitle: { color: colors.text, fontWeight: '700', marginBottom: 4 },
  small: { color: colors.muted, fontSize: 13, lineHeight: 19 },
  feeCard: { backgroundColor: colors.surface, borderColor: colors.border, borderRadius: 16, borderWidth: 1, marginBottom: spacing.md, padding: spacing.md },
  feeLabel: { color: colors.muted, fontSize: 13 },
  feeValue: { color: colors.goldLight, fontSize: 20, fontWeight: '700', marginBottom: spacing.xs, marginTop: spacing.xs },
  switchRow: { alignItems: 'center', borderBottomColor: colors.border, borderBottomWidth: 1, flexDirection: 'row', justifyContent: 'space-between', minHeight: 58 },
  switchText: { color: colors.text, flex: 1, paddingRight: spacing.sm },
  button: { marginTop: spacing.xl },
  secondary: { marginTop: spacing.sm },
  back: { color: colors.goldLight, marginTop: spacing.lg, padding: spacing.sm, textAlign: 'center' },
});
