export const DEFAULT_OPENING_QUOTE = 'Atas Asung Kertha Wara Nugraha Ida Sang Hyang Widhi Wasa/Tuhan Yang Maha Esa kami bermaksud mengundang Bapak/Ibu/Saudara/i pada Upacara Pawiwahan (Pernikahan) Putra dan Putri Kami.';

export const BIRTHDAY_OPENING_QUOTE = 'Dengan penuh sukacita, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan merayakan hari ulang tahun ini. Kehadiran dan doa baik Anda akan membuat momen ini semakin berarti.';

export const isBirthday = (invitation) => invitation?.invitation_type === 'birthday';
export const giftLabelFor = (invitation) => isBirthday(invitation) ? 'Kado Digital' : 'Wedding Gift';
export const personScreenFor = (invitation) => isBirthday(invitation) ? 'BirthdayForm' : 'GroomBrideForm';
export const openingQuoteFor = (invitation) => isBirthday(invitation) ? BIRTHDAY_OPENING_QUOTE : DEFAULT_OPENING_QUOTE;
export function invitationName(invitation) {
  if (isBirthday(invitation)) return invitation.birthday_data?.celebrant_nickname || invitation.celebrant_nickname || 'Yang berulang tahun';
  return `${invitation?.groom_data?.groom_nickname || invitation?.groom_nickname || 'Mempelai'} & ${invitation?.bride_data?.bride_nickname || invitation?.bride_nickname || 'Pasangan'}`;
}
