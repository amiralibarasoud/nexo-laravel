import { Head } from '@inertiajs/vue3';
import { h } from 'vue';

/**
 * Generate SEO Head tags for a page.
 * Usage: <SeoHead v-bind="seo" /> where seo = useSeo({...})
 */
export function useSeo({ title, description, image, type = 'website', noIndex = false } = {}) {
  const siteName = 'نکسو کورس';
  const fullTitle = title ? `${title} | ${siteName}` : siteName;
  const defaultDesc = 'پلتفرم یادگیری آنلاین فارسی — دوره‌های متنی و صوتی با بهترین اساتید';
  const metaDesc = description || defaultDesc;

  return { fullTitle, metaDesc, image, type, noIndex };
}
