function encodePathBase64UrlSafe(path: string): string {
  // 1️⃣ Normalisation Unicode (clé du problème)
  const normalized = path.normalize('NFC');

  // 2️⃣ UTF-8 → base64
  const base64 = btoa(
    unescape(encodeURIComponent(normalized))
  );

  // 3️⃣ Base64 URL-safe
  return base64
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/, '');
}
