export function templateMatchesType(template, type = 'wedding') {
  if (!template?.id || !['wedding', 'birthday'].includes(type)) return false;

  // Only wedding catalogs existed before invitation_type was added to the API.
  return (template.invitation_type ?? 'wedding') === type;
}

export function templatesForType(data, type = 'wedding') {
  if (!Array.isArray(data)) {
    throw new Error('Daftar template belum dapat dibaca. Silakan coba lagi.');
  }

  const templates = data.filter((template) => templateMatchesType(template, type));
  if (templates.length === 0) {
    const label = type === 'birthday' ? 'ulang tahun' : 'pernikahan';
    throw new Error(`Template ${label} belum tersedia di server yang terhubung. Pastikan backend sudah diperbarui dan template sudah diaktifkan, lalu coba lagi.`);
  }

  return templates;
}
