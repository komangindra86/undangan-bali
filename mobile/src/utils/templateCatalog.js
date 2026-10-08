const TEMPLATE_TYPE_LABELS = { wedding: 'pernikahan', birthday: 'ulang tahun', megedong: 'megedong-gedongan' };

export function templateMatchesType(template, type = 'wedding') {
  if (!template?.id || !TEMPLATE_TYPE_LABELS[type]) return false;

  // Only wedding catalogs existed before invitation_type was added to the API.
  return (template.invitation_type ?? 'wedding') === type;
}

export function templatesForType(data, type = 'wedding') {
  if (!Array.isArray(data)) {
    throw new Error('Daftar template belum dapat dibaca. Silakan coba lagi.');
  }

  const templates = data.filter((template) => templateMatchesType(template, type));
  if (templates.length === 0) {
    const label = TEMPLATE_TYPE_LABELS[type] || 'pernikahan';
    throw new Error(`Template ${label} belum tersedia di server yang terhubung. Pastikan backend sudah diperbarui dan template sudah diaktifkan, lalu coba lagi.`);
  }

  return templates;
}
