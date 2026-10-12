export const DEFAULT_OPENING_QUOTE = 'Atas Asung Kertha Wara Nugraha Ida Sang Hyang Widhi Wasa/Tuhan Yang Maha Esa kami bermaksud mengundang Bapak/Ibu/Saudara/i pada Upacara Pawiwahan (Pernikahan) Putra dan Putri Kami.';

export const BIRTHDAY_OPENING_QUOTE = 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan merayakan hari ulang tahun ini. Kehadiran dan doa baik Anda akan membuat momen ini semakin berarti.';

export const MEGEDONG_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Megedong-gedongan. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const PITUNG_DINA_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Abulan Pitung Dina (42 hari) bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const TIGANG_SASIH_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Tigang Sasih (3 bulanan) bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const OTONAN_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Otonan bagi buah hati kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const MEPANDES_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Mepandes (potong gigi) putra-putri kami. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const GIFT_PAYOUT_FEE_PERCENT = 1;

export const INVITATION_TYPES = ['wedding', 'birthday', 'megedong', 'pitung_dina', 'tigang_sasih', 'otonan', 'mepandes'];

// The most people one mepandes invitation lists; the server enforces the same number.
export const MAX_PARTICIPANTS = 10;

// Megedong-gedongan and the child ceremonies keep the father in groom_data and the mother in bride_data.
// `childCeremony` types also use child_data and share ChildCeremonyFormScreen. `childNamed` marks ceremonies
// where the child already has a name: it is required and becomes the title. `numbered` marks ceremonies that
// repeat (otonan), where child_data.ceremony_number may say which one it is.
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
  otonan: {
    childCeremony: true, childNamed: true, numbered: true,
    label: 'otonan', giftLabel: 'Tanda Kasih', personScreen: 'ChildCeremonyForm', personStep: 'Buah Hati & Orang Tua',
    openingQuote: OTONAN_OPENING_QUOTE, eventTypes: ['Otonan'], icon: 'sparkles-outline',
    occasion: 'upacara otonan buah hati kami',
    choice: { title: 'Otonan', body: 'Hari kelahiran menurut kalender Bali, setiap 210 hari. Untuk otonan pertama maupun berikutnya; nama buah hati menjadi judul undangan.' },
    preview: { eyebrow: 'OM SWASTYASTU', names: 'Kirana', caption: 'Otonan', heroTitle: 'UPACARA OTONAN', heroCaption: 'Putri dari Wira & Ayu' },
  },
  // Mepandes lists the people it is held for in mepandes_data.participants; see MepandesFormScreen.
  mepandes: {
    label: 'mepandes', giftLabel: 'Tanda Kasih', personScreen: 'MepandesForm', personStep: 'Yang Mepandes',
    openingQuote: MEPANDES_OPENING_QUOTE, eventTypes: ['Mepandes'], icon: 'diamond-outline',
    choice: { title: 'Mepandes (Potong Gigi)', buttonTitle: 'Mepandes', body: 'Untuk satu orang atau beberapa orang sekaligus, dari satu keluarga maupun beberapa keluarga. Tiap peserta boleh dengan atau tanpa foto.' },
    preview: { eyebrow: 'OM SWASTYASTU', names: 'Kirana, Bagus & Dewi', caption: 'Mepandes · Potong Gigi', heroTitle: 'UPACARA MEPANDES', heroCaption: 'Putra-putri dari Wira & Ayu' },
  },
};

const typeInfo = (invitation) => TYPE_INFO[invitation?.invitation_type] || TYPE_INFO.wedding;

export const isBirthday = (invitation) => invitation?.invitation_type === 'birthday';
export const isMegedong = (invitation) => invitation?.invitation_type === 'megedong';
export const isMepandes = (invitation) => invitation?.invitation_type === 'mepandes';
export const isChildCeremony = (invitation) => Boolean(TYPE_INFO[invitation?.invitation_type]?.childCeremony);
export const requiresChildName = (invitation) => Boolean(TYPE_INFO[invitation?.invitation_type]?.childNamed);
export const isNumberedCeremony = (invitation) => Boolean(TYPE_INFO[invitation?.invitation_type]?.numbered);
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

// A mepandes is titled by its participants' nicknames: "Kirana", "Kirana & Bagus", "Kirana, Bagus & Dewi",
// then "Kirana, Bagus & 3 lainnya".
export function participantNamesFor(invitation) {
  const participants = invitation?.mepandes_data?.participants || invitation?.participants || [];
  const names = participants.map((person) => person?.nickname).filter(Boolean);
  if (!names.length) return 'Yang Mepandes';
  if (names.length === 1) return names[0];
  if (names.length <= 3) return `${names.slice(0, -1).join(', ')} & ${names[names.length - 1]}`;
  return `${names[0]}, ${names[1]} & ${names.length - 2} lainnya`;
}

export function invitationName(invitation) {
  if (isBirthday(invitation)) return invitation.birthday_data?.celebrant_nickname || invitation.celebrant_nickname || 'Yang berulang tahun';
  if (isMepandes(invitation)) return participantNamesFor(invitation);
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

// "Otonan Pertama" or "Otonan ke-3" for a numbered ceremony; null when no number is given.
export function ceremonyNumberLabelFor(invitation) {
  if (!isNumberedCeremony(invitation)) return null;
  const number = Number(invitation?.child_data?.ceremony_number || invitation?.ceremony_number);
  if (!Number.isInteger(number) || number < 1) return null;
  const title = typeInfo(invitation).choice.title;
  return number === 1 ? `${title} Pertama` : `${title} ke-${number}`;
}

// Reads inside "kami mengundang untuk hadir di ...".
export function occasionPhraseFor(invitation) {
  if (isBirthday(invitation)) return `perayaan ulang tahun ${invitationName(invitation)}`;
  if (isMepandes(invitation)) return `upacara mepandes (potong gigi) ${invitationName(invitation)}`;
  const numberLabel = ceremonyNumberLabelFor(invitation);
  const occasion = numberLabel ? `upacara ${numberLabel.toLowerCase()} buah hati kami` : typeInfo(invitation).occasion || 'acara pernikahan kami';
  const child = requiresChildName(invitation) ? (invitation?.child_data?.child_nickname || invitation?.child_nickname) : null;
  return child ? `${occasion}, ${child}` : occasion;
}

// Templates without a preview photo are drawn as a colour card that matches each theme.
export const TEMPLATE_CARD_COLORS = {
  'garbha-kencana': { background: '#1d140e', accent: '#d4ad61', text: '#f3e7cf', hero: '#2a1d14' },
  'padma-sari': { background: '#fdf3ee', accent: '#b65f7a', text: '#5b3a44', hero: '#a2506b' },
  'tirta-hening': { background: '#f6f8f4', accent: '#476b59', text: '#2c3d36', hero: '#3d5f4f' },
  'rare-kencana': { background: '#fbf5e6', accent: '#8a5f1c', text: '#4a3421', hero: '#8a5f1c' },
  'sekar-jepun': { background: '#fffdf6', accent: '#8f6400', text: '#55482f', hero: '#b98a1a' },
  'langit-kumara': { background: '#e3f2fa', accent: '#1f6f78', text: '#23434a', hero: '#1f6f78' },
  'nyambutin-kencana': { background: '#fff7ef', accent: '#a3432a', text: '#5a2a1c', hero: '#a3432a' },
  'tunjung-petak': { background: '#f7fbf5', accent: '#2f6a48', text: '#2f4a3a', hero: '#2f6a48' },
  'surya-alit': { background: '#fff0dc', accent: '#a34a12', text: '#5a3520', hero: '#c2631f' },
  'oton-kencana': { background: '#fbf6ea', accent: '#8a5d14', text: '#4b3a26', hero: '#8a5d14' },
  'sekar-cempaka': { background: '#fff9f5', accent: '#a8456a', text: '#55323d', hero: '#a8456a' },
  'purnama-alit': { background: '#f6f4fc', accent: '#5a4fa3', text: '#2e2f5b', hero: '#3a3a78' },
  'bale-gading': { background: '#fcf8ee', accent: '#7a5510', text: '#45371f', hero: '#7a5510' },
  'sekar-emas': { background: '#fff9f4', accent: '#8f5226', text: '#4f3530', hero: '#8f5226' },
  'wastra-songket': { background: '#faf6f9', accent: '#7a3f68', text: '#3f2a3a', hero: '#7a4168' },
};

// A design can be offered to several invitation types under different slugs (e.g. tigang-sasih-rare-kencana),
// so the design is identified by the last part of its blade view, falling back to the slug.
export const templateDesignKey = (template) => String(template?.blade_view || '').split('.').pop() || template?.slug;

export const templateCardColorsFor = (template) => TEMPLATE_CARD_COLORS[templateDesignKey(template)]
  || TEMPLATE_CARD_COLORS[template?.slug]
  || TEMPLATE_CARD_COLORS['garbha-kencana'];
