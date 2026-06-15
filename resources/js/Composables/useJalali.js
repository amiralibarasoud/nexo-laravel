/**
 * تبدیل تاریخ میلادی به شمسی (جلالی)
 * بدون نیاز به پکیج خارجی
 */

const PERSIAN_MONTHS = [
  'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
  'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
];

function toFarsiNum(n) {
  return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function padZero(n) {
  return String(n).padStart(2, '0');
}

/**
 * تبدیل تاریخ میلادی به جلالی
 * @param {Date|string|null} date
 * @returns {{ jy: number, jm: number, jd: number, jh: number, ji: number }}
 */
function gregorianToJalali(gy, gm, gd) {
  const g_d_no = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  let jy, jm, jd, j_day_no, i, gy2;

  gy2 = (gm > 2) ? (gy + 1) : gy;
  const g_day_no =
    365 * gy +
    Math.floor((gy2 + 3) / 4) -
    Math.floor((gy2 + 99) / 100) +
    Math.floor((gy2 + 399) / 400) +
    g_d_no[gm - 1] +
    gd;

  j_day_no = g_day_no - 79;

  const j_np = Math.floor(j_day_no / 12053);
  j_day_no = j_day_no % 12053;
  jy = 979 + 33 * j_np + 4 * Math.floor(j_day_no / 1461);
  j_day_no %= 1461;

  if (j_day_no >= 366) {
    jy += Math.floor((j_day_no - 1) / 365);
    j_day_no = (j_day_no - 1) % 365;
  }

  for (i = 0; i < 11 && j_day_no >= (j_day_no = j_day_no - (i < 6 ? 31 : 30)); i++) {
    // empty
  }
  jm = i + 1;
  jd = j_day_no + 1;

  return { jy, jm, jd };
}

/**
 * فرمت تاریخ شمسی
 * @param {string|Date|null} input
 * @param {'date'|'datetime'|'long'} format
 */
export function jalali(input, format = 'date') {
  if (!input) return '—';

  try {
    const d = typeof input === 'string' ? new Date(input) : input;
    if (isNaN(d.getTime())) return '—';

    const { jy, jm, jd } = gregorianToJalali(d.getFullYear(), d.getMonth() + 1, d.getDate());

    const farsiDate = `${toFarsiNum(jy)}/${toFarsiNum(padZero(jm))}/${toFarsiNum(padZero(jd))}`;
    const farsiTime = `${toFarsiNum(padZero(d.getHours()))}:${toFarsiNum(padZero(d.getMinutes()))}`;

    if (format === 'datetime') return `${farsiDate} - ${farsiTime}`;
    if (format === 'long') return `${toFarsiNum(jd)} ${PERSIAN_MONTHS[jm - 1]} ${toFarsiNum(jy)}`;
    return farsiDate;
  } catch {
    return '—';
  }
}

/**
 * زمان نسبی شمسی (مثل: ۳ روز پیش)
 */
export function jalaliAgo(input) {
  if (!input) return '—';
  try {
    const d = typeof input === 'string' ? new Date(input) : input;
    const diff = Date.now() - d.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours   = Math.floor(diff / 3600000);
    const days    = Math.floor(diff / 86400000);

    if (minutes < 1)  return 'همین الان';
    if (minutes < 60) return `${toFarsiNum(minutes)} دقیقه پیش`;
    if (hours < 24)   return `${toFarsiNum(hours)} ساعت پیش`;
    if (days < 30)    return `${toFarsiNum(days)} روز پیش`;

    return jalali(input);
  } catch {
    return '—';
  }
}

export default { jalali, jalaliAgo };
