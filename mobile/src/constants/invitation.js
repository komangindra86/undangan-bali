export const DEFAULT_OPENING_QUOTE = 'Atas Asung Kertha Wara Nugraha Ida Sang Hyang Widhi Wasa/Tuhan Yang Maha Esa kami bermaksud mengundang Bapak/Ibu/Saudara/i pada Upacara Pawiwahan (Pernikahan) Putra dan Putri Kami.';

export const BIRTHDAY_OPENING_QUOTE = 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan merayakan hari ulang tahun ini. Kehadiran dan doa baik Anda akan membuat momen ini semakin berarti.';

export const MEGEDONG_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Megedong-gedongan. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const PITUNG_DINA_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Abulan Pitung Dina (42 hari) bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const TIGANG_SASIH_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Tigang Sasih (3 bulanan) bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const GIFT_PAYOUT_FEE_PERCENT = 1;

export const INVITATION_TYPES = ['wedding', 'birthday', 'megedong', 'pitung_dina', 'tigang_sasih'];

// Megedong-gedongan and the child ceremonies keep the father in groom_data and the mother in bride_data.
// `childCeremony` types also use child_data and share ChildCeremonyFormScreen. `childNamed` marks ceremonies
// where the child already has a name: it is required and becomes the title.
const TYPE_INFO = {
  wedding: {
    label: 'pernikahan', giftLabel: 'Wedding Gift', personScreen: 'GroomBrideForm', personStep: 'Mempelai',
    openingQuote: DEFAULT_OPENING_QUOTE, eventTypes: ['Pawiwahan', 'Resepsi'], icon: 'heart-outline',
    choice: { title: 'Pernikahan', body: 'Rangkai cerita dan undang orang tersayang di hari bahagia kalian.' },
  },
  birthday: {
    label: 'ulang tahun', giftLabel: 'Kado Digital', personScreen: 'BirthdayForm', personStep: 'Yang Berulang Tahun',
    openingQuote: BIRTHDAY_OPENING_QUOTE, eventTypes: ['Ulang Tahun'], icon: 'gift-outline',
    choice: { title: 'Ulang Tahun', body: 'Untuk anak maupun dewasa. Usia opsional, foto dan undangan tidak otomatis muncul di feed publik.' },
  },
  megedong: {
    label: 'megedong-gedongan', giftLabel: 'Tanda Kasih', personScreen: 'MegedongForm', personStep: 'Calon Orang Tua',
    openingQuote: MEGEDONG_OPENING_QUOTE, eventTypes: ['Megedong-gedongan'], icon: 'flower-outline',
    occasion: 'upacara megedong-gedongan kami',
    choice: { title: 'Megedong-gedongan', body: 'Undang keluarga dan kerabat untuk memberi doa restu bagi ibu dan calon buah hati.' },
    preview: { eyebrow: 'OM SWASTYASTU', names: 'Ayu & Wira', caption: 'Upacara Megedong-gedongan', heroTitle: 'UPACARA MEGEDONG-GEDONGAN', heroCaption: 'Doa restu untuk ibu dan calon buah hati' },
  },
  pitung_dina: {
    childCeremony: true,
    label: 'abulan pitung dina', giftLabel: 'Tanda Kasih', personScreen: 'ChildCeremonyForm', personStep: 'Buah Hati & Orang Tua',
    openingQuote: PITUNG_DINA_OPENING_QUOTE, eventTypes: ['Abulan Pitung Dina'], icon: 'happy-outline',
    occasion: 'upacara abulan pitung dina (42 hari) buah hati kami',
    choice: { title: 'Abulan Pitung Dina (42 Hari)', buttonTitle: 'Abulan Pitung Dina', body: 'Upacara saat buah hati berumur 42 hari. Cukup isi nama ayah dan ibu; nama dan foto bayi boleh menyusul.' },
    preview: { eyebrow: 'OM SWASTYASTU', names: 'Putra Wira & Ayu', caption: 'Abulan Pitung Dina · 42 Hari', heroTitle: 'UPACARA ABULAN PITUNG DINA', heroCaption: '42 hari buah hati kami' },
  },
  tigang_sasih: {
    childCeremony: true, childNamed: true,
    label: 'tigang sasih', giftLabel: 'Tanda Kasih', personScreen: 'ChildCeremonyForm', personStep: 'Buah Hati & Orang Tua',
    openingQuote: TIGANG_SASIH_OPENING_QUOTE, eventTypes: ['Tigang Sasih'], icon: 'sunny-outline',
    occasion: 'upacara tigang sasih (3 bulanan) buah hati kami',
    choice: { title: 'Tigang Sasih (3 Bulanan)', buttonTitle: 'Tigang Sasih', body: 'Upacara saat buah hati berumur tiga bulan Bali (105 hari). Nama buah hati menjadi judul undangan.' },
    preview: { eyebrow: 'OM SWASTYASTU', names: 'Kirana', caption: 'Tigang Sasih · 3 Bulanan', heroTitle: 'UPACARA TIGANG SASIH', heroCaption: 'Putri dari Wira & Ayu' },
  },
};

const typeInfo = (invitation) => TYPE_INFO[invitation?.invitation_type] || TYPE_INFO.wedding;

export const isBirthday = (invitation) => invitation?.invitation_type === 'birthday';
export const isMegedong = (invitation) => invitation?.invitation_type === 'megedong';
export const isChildCeremony = (invitation) => Boolean(TYPE_INFO[invitation?.invitation_type]?.childCeremony);
export const requiresChildName = (invitation) => Boolean(TYPE_INFO[invitation?.invitation_type]?.childNamed);
export const typeLabelFor = (invitation) => typeInfo(invitation).label;
export const typeIconFor = (invitation) => typeInfo(invitation).icon;
export const giftLabelFor = (invitation) => typeInfo(invitation).giftLabel;
export const personScreenFor = (invitation) => typeInfo(invitation).personScreen;
export const personStepLabelFor = (invitation) => typeInfo(invitation).personStep;
export const openingQuoteFor = (invitation) => typeInfo(invitation).openingQuote;
export const eventTypesFor = (invitation) => typeInfo(invitation).eventTypes;
// Sample texts for the colour card shown instead of a preview photo; undefined for types with real previews.
export const templatePreviewFor = (invitation) => typeInfo(invitation).preview;

// Cards on the "Rayakan momen apa?" screen, in display order.
export const INVITATION_CHOICES = INVITATION_TYPES.map((type) => ({ type, icon: TYPE_INFO[type].icon, ...TYPE_INFO[type].choice }));

// Putra, Putri, or Buah Hati when the gender is left out.
export function childLabelFor(invitation) {
  const gender = invitation?.child_data?.child_gender || invitation?.child_gender;
  return { putra: 'Putra', putri: 'Putri' }[gender] || 'Buah Hati';
}

export function invitationName(invitation) {
  if (isBirthday(invitation)) return invitation.birthday_data?.celebrant_nickname || invitation.celebrant_nickname || 'Yang berulang tahun';
  const groom = invitation?.groom_data?.groom_nickname || invitation?.groom_nickname;
  const bride = invitation?.bride_data?.bride_nickname || invitation?.bride_nickname;
  if (isMegedong(invitation)) return `${bride || 'Calon Ibu'} & ${groom || 'Calon Ayah'}`;
  if (requiresChildName(invitation)) {
    const child = invitation?.child_data?.child_nickname || invitation?.child_nickname;
    if (child) return child;
  }
  // Without a child name the title names the parents: "Putra Wira & Ayu".
  if (isChildCeremony(invitation)) return `${childLabelFor(invitation)} ${groom || 'Ayah'} & ${bride || 'Ibu'}`;
  return `${groom || 'Mempelai'} & ${bride || 'Pasangan'}`;
}

// Reads inside "kami mengundang untuk hadir di ...".
export function occasionPhraseFor(invitation) {
  if (isBirthday(invitation)) return `perayaan ulang tahun ${invitationName(invitation)}`;
  const occasion = typeInfo(invitation).occasion || 'acara pernikahan kami';
  const child = requiresChildName(invitation) ? (invitation?.child_data?.child_nickname || invitation?.child_nickname) : null;
  return child ? `${occasion}, ${child}` : occasion;
}

// Templates without a preview photo are drawn as a colour card that matches each theme.
export const TEMPLATE_CARD_COLORS = {
  'garbha-kencana': { background: '#1d140e', accent: '#d4ad61', text: '#f3e7cf', hero: '#2a1d14' },
  'padma-sari': { background: '#fdf3ee', accent: '#b65f7a', text: '#5b3a44', hero: '#a2506b' },
  'tirta-hening': { background: '#f6f8f4', accent: '#476b59', text: '#2c3d36', hero: '#3d5f4f' },
  'rare-kencana': { background: '#fbf3e4', accent: '#a8792c', text: '#4a3421', hero: '#6f4d1c' },
  'sekar-jepun': { background: '#fffdf6', accent: '#9c6b00', text: '#55482f', hero: '#b98a1a' },
  'langit-kumara': { background: '#eef6f6', accent: '#236f77', text: '#23434a', hero: '#236f77' },
  'nyambutin-kencana': { background: '#4a121b', accent: '#e2b65a', text: '#f6e6c8', hero: '#4a121b' },
  'tunjung-petak': { background: '#f7fbf5', accent: '#34704d', text: '#2f4a3a', hero: '#34704d' },
  'surya-alit': { background: '#fff6ec', accent: '#ad4f17', text: '#5a3520', hero: '#c2631f' },
};

// A design can be offered to several invitation types under different slugs (e.g. tigang-sasih-rare-kencana),
// so the design is identified by the last part of its blade view, falling back to the slug.
export const templateDesignKey = (template) => String(template?.blade_view || '').split('.').pop() || template?.slug;

export const templateCardColorsFor = (template) => TEMPLATE_CARD_COLORS[templateDesignKey(template)]
  || TEMPLATE_CARD_COLORS[template?.slug]
  || TEMPLATE_CARD_COLORS['garbha-kencana'];
