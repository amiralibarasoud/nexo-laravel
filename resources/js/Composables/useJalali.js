import jalaali from 'jalaali-js';

const PERSIAN_MONTHS = [
  'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
  'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

function toPersian(n) {
  return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function pad(n) {
  return String(n).padStart(2, '0');
}

function parseDate(input) {
  if (!input) return null;
  if (input instanceof Date) return input;
  const d = new Date(input);
  return isNaN(d.getTime()) ? null : d;
}

/**
 * تبدیل تاریخ میلادی به شمسی
 * @param {'date'|'datetime'|'long'} format
 */
export function jalali(input, format = 'date') {
  const d = parseDate(input);
  if (!d) return '—';

  try {
    const { jy, jm, jd } = jalaali.toJalaali(d.getFullYear(), d.getMonth() + 1, d.getDate());
    const dateStr = `${toPersian(jy)}/${toPersian(pad(jm))}/${toPersian(pad(jd))}`;
    const timeStr = `${toPersian(pad(d.getHours()))}:${toPersian(pad(d.getMinutes()))}`;

    if (format === 'datetime') return `${dateStr} ${timeStr}`;
    if (format === 'long')     return `${toPersian(jd)} ${PERSIAN_MONTHS[jm - 1]} ${toPersian(jy)}`;
    return dateStr;
  } catch {
    return '—';
  }
}

export function jalaliYear() {
  const d = new Date();
  const { jy } = jalaali.toJalaali(d.getFullYear(), d.getMonth() + 1, d.getDate());
  return toPersian(jy);
}

export function jalaliAgo(input) {
  const d = parseDate(input);
  if (!d) return '—';

  const diff    = Math.floor((Date.now() - d.getTime()) / 1000);
  const minutes = Math.floor(diff / 60);
  const hours   = Math.floor(diff / 3600);
  const days    = Math.floor(diff / 86400);

  if (diff < 60)   return 'همین الان';
  if (hours < 1)   return `${toPersian(minutes)} دقیقه پیش`;
  if (hours < 24)  return `${toPersian(hours)} ساعت پیش`;
  if (days < 30)   return `${toPersian(days)} روز پیش`;
  return jalali(input);
}

export default { jalali, jalaliYear, jalaliAgo };
