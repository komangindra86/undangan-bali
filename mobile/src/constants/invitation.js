export const DEFAULT_OPENING_QUOTE = 'Atas Asung Kertha Wara Nugraha Ida Sang Hyang Widhi Wasa/Tuhan Yang Maha Esa kami bermaksud mengundang Bapak/Ibu/Saudara/i pada Upacara Pawiwahan (Pernikahan) Putra dan Putri Kami.';

export const BIRTHDAY_OPENING_QUOTE = 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan merayakan hari ulang tahun ini. Kehadiran dan doa baik Anda akan membuat momen ini semakin berarti.';

export const MEGEDONG_OPENING_QUOTE = 'Atas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami bermaksud melaksanakan upacara Megedong-gedongan. Merupakan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.';

export const GIFT_PAYOUT_FEE_PERCENT = 1;

export const INVITATION_TYPES = ['wedding', 'birthday', 'megedong'];

// Megedong-gedongan keeps the expectant father in groom_data and the mother in bride_data.
const TYPE_INFO = {
  wedding: { label: 'pernikahan', giftLabel: 'Wedding Gift', personScreen: 'GroomBrideForm', personStep: 'Mempelai', openingQuote: DEFAULT_OPENING_QUOTE, eventTypes: ['Pawiwahan', 'Resepsi'], icon: 'heart-outline' },
  birthday: { label: 'ulang tahun', giftLabel: 'Kado Digital', personScreen: 'BirthdayForm', personStep: 'Yang Berulang Tahun', openingQuote: BIRTHDAY_OPENING_QUOTE, eventTypes: ['Ulang Tahun'], icon: 'gift-outline' },
  megedong: { label: 'megedong-gedongan', giftLabel: 'Tanda Kasih', personScreen: 'MegedongForm', personStep: 'Calon Orang Tua', openingQuote: MEGEDONG_OPENING_QUOTE, eventTypes: ['Megedong-gedongan'], icon: 'flower-outline' },
};

const typeInfo = (invitation) => TYPE_INFO[invitation?.invitation_type] || TYPE_INFO.wedding;

export const isBirthday = (invitation) => invitation?.invitation_type === 'birthday';
export const isMegedong = (invitation) => invitation?.invitation_type === 'megedong';
export const typeLabelFor = (invitation) => typeInfo(invitation).label;
export const typeIconFor = (invitation) => typeInfo(invitation).icon;
export const giftLabelFor = (invitation) => typeInfo(invitation).giftLabel;
export const personScreenFor = (invitation) => typeInfo(invitation).personScreen;
export const personStepLabelFor = (invitation) => typeInfo(invitation).personStep;
export const openingQuoteFor = (invitation) => typeInfo(invitation).openingQuote;
export const eventTypesFor = (invitation) => typeInfo(invitation).eventTypes;

export function invitationName(invitation) {
  if (isBirthday(invitation)) return invitation.birthday_data?.celebrant_nickname || invitation.celebrant_nickname || 'Yang berulang tahun';
  const groom = invitation?.groom_data?.groom_nickname || invitation?.groom_nickname;
  const bride = invitation?.bride_data?.bride_nickname || invitation?.bride_nickname;
  if (isMegedong(invitation)) return `${bride || 'Calon Ibu'} & ${groom || 'Calon Ayah'}`;
  return `${groom || 'Mempelai'} & ${bride || 'Pasangan'}`;
}

// Reads inside "kami mengundang untuk hadir di ...".
export function occasionPhraseFor(invitation) {
  if (isBirthday(invitation)) return `perayaan ulang tahun ${invitationName(invitation)}`;
  if (isMegedong(invitation)) return 'upacara megedong-gedongan kami';
  return 'acara pernikahan kami';
}

// Megedong-gedongan templates have no preview photo, so the app draws a colour card that matches each theme.
export const MEGEDONG_TEMPLATE_COLORS = {
  'garbha-kencana': { background: '#1d140e', accent: '#d4ad61', text: '#f3e7cf', hero: '#2a1d14' },
  'padma-sari': { background: '#fdf3ee', accent: '#b65f7a', text: '#5b3a44', hero: '#a2506b' },
  'tirta-hening': { background: '#f6f8f4', accent: '#476b59', text: '#2c3d36', hero: '#3d5f4f' },
};
