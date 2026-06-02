import { useState } from 'react';
import { Alert, StyleSheet, Switch, Text, View } from 'react-native';
import { FooterActions } from '../components/Buttons';
import FormField from '../components/FormField';
import WizardLayout from '../components/WizardLayout';
import { useDraft } from '../context/DraftContext';
import { colors, spacing } from '../theme';

export default function GiftSetupScreen({ navigation }) {
  const { draft, saveSection, syncing, syncMessage } = useDraft();
  const suggestedReceiver = [draft.groom_data?.groom_nickname, draft.bride_data?.bride_nickname]
    .filter(Boolean)
    .join(' & ');
  const [gift, setGift] = useState({
    is_active: false,
    receiver_name: '',
    receiver_note: '',
    minimum_amount: '10000',
    show_amount_public: false,
    allow_message: true,
    ...draft.gift_data,
  });

  function toggleGift(value) {
    setGift({
      ...gift,
      is_active: value,
      receiver_name: value && !gift.receiver_name ? suggestedReceiver : gift.receiver_name,
    });
  }

  async function next() {
    const minimum = Number(gift.minimum_amount);
    if (gift.is_active && !gift.receiver_name.trim()) {
      Alert.alert('Nama penerima diperlukan', 'Masukkan nama pasangan yang akan menerima Wedding Gift.');
      return;
    }
    if (!Number.isInteger(minimum) || minimum < 10000) {
      Alert.alert('Minimum gift tidak valid', 'Nominal minimum Wedding Gift paling kecil Rp10.000.');
      return;
    }

    await saveSection('gift_data', {
      is_active: gift.is_active,
      receiver_name: gift.receiver_name.trim(),
      receiver_note: gift.receiver_note.trim(),
      minimum_amount: minimum,
      show_amount_public: gift.show_amount_public,
      allow_message: gift.allow_message,
    });
    navigation.navigate('Preview');
  }

  return (
    <WizardLayout
      step={7}
      title="Wedding Gift"
      subtitle="Opsional. Aktifkan bila Anda ingin tamu dapat mengirim tanda kasih melalui QRIS di halaman undangan web."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={next} loading={syncing} />}
    >
      <View style={styles.safety}>
        <Text style={styles.safetyTitle}>Pembayaran dilakukan oleh tamu di browser</Text>
        <Text style={styles.safetyBody}>
          Aplikasi ini hanya mengatur tampilan Wedding Gift. QRIS baru tersedia pada undangan setelah dipublish.
        </Text>
      </View>
      <View style={styles.toggle}>
        <View style={styles.toggleCopy}>
          <Text style={styles.toggleTitle}>Aktifkan Wedding Gift</Text>
          <Text style={styles.help}>Form QRIS akan tampil pada link undangan publik.</Text>
        </View>
        <Switch
          value={gift.is_active}
          onValueChange={toggleGift}
          trackColor={{ true: colors.gold, false: colors.border }}
          thumbColor={colors.text}
        />
      </View>
      {gift.is_active ? (
        <>
          <FormField
            label="Nama penerima *"
            placeholder="Contoh: Made & Ayu"
            value={gift.receiver_name}
            onChangeText={(value) => setGift({ ...gift, receiver_name: value })}
          />
          <FormField
            label="Catatan untuk tamu (opsional)"
            placeholder="Matur suksma atas tanda kasih Anda."
            multiline
            value={gift.receiver_note}
            onChangeText={(value) => setGift({ ...gift, receiver_note: value })}
          />
          <FormField
            label="Minimum nominal gift"
            placeholder="10000"
            keyboardType="numeric"
            value={String(gift.minimum_amount || '')}
            onChangeText={(value) => setGift({ ...gift, minimum_amount: value.replace(/\D/g, '') })}
          />
          <View style={styles.fee}>
            <Text style={styles.feeTitle}>Biaya layanan aplikasi</Text>
            <Text style={styles.feeAmount}>Rp2.000 atau 2%</Text>
            <Text style={styles.help}>Di bawah Rp100.000 dikenakan Rp2.000. Mulai Rp100.000 ke atas dikenakan 2%. Fee ditampilkan transparan di halaman web.</Text>
          </View>
          <ToggleRow
            title="Izinkan tamu menulis ucapan"
            value={gift.allow_message}
            onValueChange={(value) => setGift({ ...gift, allow_message: value })}
          />
        </>
      ) : (
        <Text style={styles.inactive}>Tamu tidak akan melihat form Wedding Gift pada undangan Anda.</Text>
      )}
    </WizardLayout>
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

const styles = StyleSheet.create({
  safety: {
    backgroundColor: colors.surfaceAlt,
    borderColor: colors.gold,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: spacing.lg,
    padding: spacing.md,
  },
  safetyTitle: {
    color: colors.goldLight,
    fontSize: 15,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  safetyBody: {
    color: colors.muted,
    fontSize: 13,
    lineHeight: 20,
  },
  toggle: {
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: spacing.lg,
    padding: spacing.md,
  },
  toggleCopy: {
    flex: 1,
    paddingRight: spacing.sm,
  },
  toggleTitle: {
    color: colors.text,
    fontSize: 16,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  help: {
    color: colors.muted,
    fontSize: 13,
    lineHeight: 20,
  },
  fee: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: spacing.md,
    padding: spacing.md,
  },
  feeTitle: {
    color: colors.muted,
    fontSize: 13,
  },
  feeAmount: {
    color: colors.goldLight,
    fontSize: 20,
    fontWeight: '700',
    marginBottom: spacing.xs,
    marginTop: spacing.xs,
  },
  switchRow: {
    alignItems: 'center',
    borderBottomColor: colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    minHeight: 58,
  },
  switchText: {
    color: colors.text,
    flex: 1,
    paddingRight: spacing.sm,
  },
  inactive: {
    color: colors.muted,
    lineHeight: 21,
    marginBottom: spacing.lg,
  },
});
