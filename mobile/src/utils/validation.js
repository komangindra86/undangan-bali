export const NAME_PATTERN = /^[\p{L}\s.'-]+$/u;
export const SAFE_TEXT_PATTERN = /^[^<>]+$/;
export const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
export const MAX_NICKNAME_LENGTH = 18;

export function cleanText(value) {
  return String(value || '').trim().replace(/\s+/g, ' ');
}

export function todayDateString() {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}

export function isPastDate(value) {
  return Boolean(value) && value < todayDateString();
}

export function validateRequired(value, label) {
  return cleanText(value) ? null : `${label} wajib diisi.`;
}

export function validateName(value, label, { required = false, max = 80 } = {}) {
  const text = cleanText(value);
  if (!text) {
    return required ? `${label} wajib diisi.` : null;
  }
  if (text.length > max) {
    return `${label} maksimal ${max} karakter.`;
  }
  if (!NAME_PATTERN.test(text)) {
    return `${label} hanya boleh berisi huruf, spasi, titik, petik, atau tanda hubung.`;
  }
  return null;
}

export function validateNickname(value, label) {
  return validateName(value, label, { required: true, max: MAX_NICKNAME_LENGTH });
}

export function validateSafeText(value, label, { required = false, max = 255 } = {}) {
  const text = cleanText(value);
  if (!text) {
    return required ? `${label} wajib diisi.` : null;
  }
  if (text.length > max) {
    return `${label} maksimal ${max} karakter.`;
  }
  if (!SAFE_TEXT_PATTERN.test(text)) {
    return `${label} tidak boleh mengandung karakter < atau >.`;
  }
  return null;
}

export function validateEmail(value, label = 'Email') {
  const text = cleanText(value).toLowerCase();
  if (!text) {
    return `${label} wajib diisi.`;
  }
  if (!EMAIL_PATTERN.test(text)) {
    return `${label} harus valid, contoh nama@gmail.com.`;
  }
  return null;
}

export function firstError(errors) {
  return errors.find(Boolean) || null;
}
