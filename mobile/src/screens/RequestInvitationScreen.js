import { useState } from 'react';
import { Alert, StyleSheet, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { PrimaryButton, SecondaryButton } from '../components/Buttons';
import FormField from '../components/FormField';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';
import { api } from '../services/api';
import { colors, commonStyles, spacing } from '../theme';

export default function RequestInvitationScreen({ navigation, route }) {
  const { invitation } = route.params;
  const [name, setName] = useState('');
  const [whatsapp, setWhatsapp] = useState('');
  const [loading, setLoading] = useState(false);

  async function submit() {
    if (name.trim().length < 2 || whatsapp.replace(/\D/g, '').length < 9) {
      Alert.alert('Periksa data', 'Masukkan nama dan nomor WhatsApp yang valid.');
      return;
    }
    setLoading(true);
    try {
      const response = await api.requestInvitation(invitation.id, { requester_name: name.trim(), requester_whatsapp: whatsapp });
      Alert.alert('Permintaan terkirim', response.message, [{ text: 'Selesai', onPress: () => navigation.goBack() }]);
    } catch (error) {
      Alert.alert('Permintaan belum terkirim', error.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <SafeAreaView style={commonStyles.screen}>
      <KeyboardAwareScrollView contentContainerStyle={styles.content}>
        <Text style={commonStyles.eyebrow}>Minta Undangan</Text>
        <Text style={commonStyles.title}>{invitation.names}</Text>
        <Text style={styles.body}>Pasangan akan menerima nama dan nomor WhatsApp Anda, lalu dapat mengirim link undangan secara pribadi.</Text>
        <FormField label="Nama Anda" value={name} onChangeText={setName} maxLength={80} placeholder="Nama lengkap atau panggilan" />
        <FormField label="Nomor WhatsApp" value={whatsapp} onChangeText={setWhatsapp} keyboardType="phone-pad" maxLength={24} placeholder="0812xxxxxxx" helperText="Nomor ini hanya dilihat oleh pasangan." />
        <PrimaryButton title="Kirim Permintaan" onPress={submit} loading={loading} style={styles.submit} />
        <SecondaryButton title="Kembali" onPress={() => navigation.goBack()} style={styles.back} />
      </KeyboardAwareScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  content: { padding: spacing.lg, paddingBottom: spacing.xl },
  body: { color: colors.muted, lineHeight: 22, marginBottom: spacing.xl, marginTop: spacing.md },
  submit: { marginTop: spacing.sm },
  back: { marginTop: spacing.sm },
});
