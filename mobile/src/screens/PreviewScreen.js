import { Alert, StyleSheet, Text, View } from 'react-native';
import { FooterActions, SecondaryButton } from '../components/Buttons';
import WizardLayout from '../components/WizardLayout';
import { useAuth } from '../context/AuthContext';
import { useDraft } from '../context/DraftContext';
import { colors, spacing } from '../theme';

export default function PreviewScreen({ navigation }) {
  const { isAuthenticated } = useAuth();
  const { draft, publishDraft, syncing, syncMessage } = useDraft();
  const groom = draft.groom_data;
  const bride = draft.bride_data;
  const event = draft.event_data;
  const location = draft.location_data;
  const galleryCount = draft.gallery_data?.photos?.length || 0;
  const couplePhotos = Number(Boolean(groom.groom_photo?.uri)) + Number(Boolean(bride.bride_photo?.uri));
  const templateName = draft.selected_template?.name || 'Template belum dipilih';
  const musicLabel = musicDescription(draft.music_data);
  const giftLabel = giftDescription(draft.gift_data);

  async function publish() {
    if (!isAuthenticated) {
      navigation.navigate('AuthGate');
      return;
    }
    try {
      const result = await publishDraft();
      navigation.replace('Share', { publication: result });
    } catch (error) {
      if (error.status === 401) {
        navigation.navigate('Login', { publishAfterAuth: true, sessionExpired: true });
        return;
      }
      Alert.alert('Belum dapat dipublish', error.message);
    }
  }

  return (
    <WizardLayout
      step={8}
      title="Konfirmasi data undangan"
      subtitle="Ini ringkasan data yang Anda isi, bukan tampilan undangan final. Periksa dahulu sebelum membuat link undangan."
      syncMessage={syncMessage}
      footer={<FooterActions onBack={() => navigation.goBack()} onNext={publish} nextTitle="Publish & Lihat Hasil" loading={syncing} />}
    >
      <View style={styles.notice}>
        <Text style={styles.noticeTitle}>Undangan belum dipublish</Text>
        <Text style={styles.noticeBody}>
          Setelah publish, desain lengkap, animasi, musik, dan link yang bisa dibagikan akan dibuat sesuai template pilihan Anda.
        </Text>
      </View>

      <Text style={styles.sectionTitle}>Data yang akan digunakan</Text>
      <View style={styles.summary}>
        <SummaryRow label="Template" value={templateName} />
        <SummaryRow label="Mempelai" value={`${groom.groom_nickname || '-'} & ${bride.bride_nickname || '-'}`} />
        <SummaryRow label="Acara" value={`${event.event_type || '-'} | ${formatDate(event.event_date)}`} />
        <SummaryRow label="Waktu" value={`${event.start_time || '--:--'}${event.end_time ? ` - ${event.end_time}` : ''} WITA`} />
        <SummaryRow label="Lokasi" value={event.venue_name || '-'} subvalue={event.venue_address} />
        <SummaryRow label="Peta" value={location.google_maps_url ? 'Link Google Maps ditambahkan' : 'Belum ditambahkan'} optional={!location.google_maps_url} />
        <SummaryRow label="Foto mempelai" value={`${couplePhotos} dari 2 foto ditambahkan`} optional={!couplePhotos} />
        <SummaryRow label="Galeri" value={`${galleryCount} foto ditambahkan`} optional={!galleryCount} />
        <SummaryRow label="Musik" value={musicLabel} optional={draft.music_data?.music_type === 'none'} />
        <SummaryRow label="Wedding Gift" value={giftLabel} optional={!draft.gift_data?.is_active} last />
      </View>

      <Text style={styles.editTitle}>Ada yang perlu diperbaiki?</Text>
      <Text style={styles.editHelp}>Pilih bagian di bawah untuk mengubah data sebelum publish.</Text>
      <View style={styles.actions}>
        <SecondaryButton title="Template" onPress={() => navigation.navigate('Template')} style={styles.action} />
        <SecondaryButton title="Mempelai" onPress={() => navigation.navigate('GroomBrideForm')} style={styles.action} />
        <SecondaryButton title="Acara" onPress={() => navigation.navigate('EventForm')} style={styles.action} />
        <SecondaryButton title="Lokasi" onPress={() => navigation.navigate('Location')} style={styles.action} />
        <SecondaryButton title="Galeri" onPress={() => navigation.navigate('Gallery')} style={styles.action} />
        <SecondaryButton title="Musik" onPress={() => navigation.navigate('Music')} style={styles.action} />
        <SecondaryButton title="Gift" onPress={() => navigation.navigate('GiftSetup')} style={styles.action} />
      </View>
    </WizardLayout>
  );
}

function SummaryRow({ label, value, subvalue, optional = false, last = false }) {
  return (
    <View style={[styles.row, last && styles.lastRow]}>
      <View style={[styles.check, optional && styles.optional]}>
        <Text style={[styles.checkText, optional && styles.optionalText]}>{optional ? 'Opsional' : 'OK'}</Text>
      </View>
      <View style={styles.rowContent}>
        <Text style={styles.rowLabel}>{label}</Text>
        <Text style={styles.rowValue}>{value}</Text>
        {subvalue ? <Text style={styles.rowSubvalue}>{subvalue}</Text> : null}
      </View>
    </View>
  );
}

function formatDate(date) {
  if (!date) {
    return '-';
  }
  const [year, month, day] = date.split('-');
  return day && month && year ? `${day}/${month}/${year}` : date;
}

function musicDescription(music) {
  if (music?.music_type === 'default') {
    return 'Musik pilihan ditambahkan';
  }
  if (music?.music_type === 'upload') {
    return 'Musik upload ditambahkan';
  }
  return 'Tanpa musik';
}

function giftDescription(gift) {
  if (!gift?.is_active) {
    return 'Tidak diaktifkan';
  }
  return `Aktif | Minimum Rp${new Intl.NumberFormat('id-ID').format(Number(gift.minimum_amount || 10000))}`;
}

const styles = StyleSheet.create({
  notice: {
    backgroundColor: '#282116',
    borderColor: colors.gold,
    borderRadius: 18,
    borderWidth: 1,
    padding: spacing.md,
  },
  noticeTitle: {
    color: colors.goldLight,
    fontSize: 17,
    fontWeight: '700',
    marginBottom: spacing.xs,
  },
  noticeBody: {
    color: colors.muted,
    lineHeight: 21,
  },
  sectionTitle: {
    color: colors.text,
    fontSize: 17,
    fontWeight: '700',
    marginBottom: spacing.md,
    marginTop: spacing.lg,
  },
  summary: {
    backgroundColor: colors.surface,
    borderColor: colors.border,
    borderRadius: 20,
    borderWidth: 1,
    paddingHorizontal: spacing.md,
  },
  row: {
    borderBottomColor: colors.border,
    borderBottomWidth: 1,
    flexDirection: 'row',
    gap: spacing.sm,
    paddingVertical: spacing.md,
  },
  lastRow: {
    borderBottomWidth: 0,
  },
  check: {
    alignItems: 'center',
    backgroundColor: '#243127',
    borderRadius: 11,
    height: 24,
    justifyContent: 'center',
    marginTop: 2,
    paddingHorizontal: spacing.xs,
    minWidth: 31,
  },
  checkText: {
    color: colors.success,
    fontSize: 10,
    fontWeight: '800',
  },
  optional: {
    backgroundColor: colors.surfaceAlt,
  },
  optionalText: {
    color: colors.muted,
    fontSize: 9,
  },
  rowContent: {
    flex: 1,
  },
  rowLabel: {
    color: colors.muted,
    fontSize: 12,
    marginBottom: 3,
  },
  rowValue: {
    color: colors.text,
    fontSize: 15,
    fontWeight: '600',
  },
  rowSubvalue: {
    color: colors.muted,
    lineHeight: 19,
    marginTop: 3,
  },
  editTitle: {
    color: colors.goldLight,
    fontSize: 17,
    fontWeight: '600',
    marginTop: spacing.lg,
  },
  editHelp: {
    color: colors.muted,
    marginBottom: spacing.md,
    marginTop: spacing.xs,
  },
  actions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing.sm,
  },
  action: {
    minHeight: 46,
    paddingHorizontal: spacing.md,
  },
});
