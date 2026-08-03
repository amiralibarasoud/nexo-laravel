/**
 * Resolve a public media path to a browser URL.
 * Works with absolute URLs, /storage/... paths, and relative disk paths.
 */
export function mediaUrl(path) {
  if (!path) {
    return null;
  }

  const value = String(path).trim().replace(/\\/g, '/');

  if (!value) {
    return null;
  }

  if (/^(https?:)?\/\//i.test(value) || value.startsWith('data:') || value.startsWith('blob:')) {
    return value;
  }

  if (value.startsWith('/storage/') || value.startsWith('/uploads/')) {
    return value;
  }

  let clean = value.replace(/^\/+/, '');

  if (clean.startsWith('storage/')) {
    clean = clean.slice('storage/'.length);
  }

  if (clean.startsWith('public/')) {
    clean = clean.slice('public/'.length);
  }

  return `/storage/${clean}`;
}
