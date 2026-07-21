<template>
  <footer class="bg-gray-900 text-gray-300 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
          <div class="flex items-center gap-2 mb-4">
            <div
              v-if="footer.logo"
              class="bg-white rounded-lg px-2.5 py-1.5 shrink-0"
            >
              <img
                :src="footer.logo"
                :alt="footer.site_name"
                class="h-9 w-auto max-w-[140px] object-contain"
              />
            </div>
            <div
              v-else
              class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center"
            >
              <span class="text-white font-bold text-lg">{{ footer.logo_letter || 'N' }}</span>
            </div>
            <span class="text-xl font-bold text-white">{{ footer.site_name }}</span>
          </div>
          <p v-if="footer.description" class="text-gray-400 leading-relaxed text-sm max-w-xs">
            {{ footer.description }}
          </p>
        </div>

        <div v-if="visibleLinks.length">
          <h4 class="text-white font-semibold mb-4">{{ footer.links_title }}</h4>
          <ul class="space-y-2 text-sm">
            <li v-for="(item, index) in visibleLinks" :key="`footer-link-${index}`">
              <Link :href="resolveHref(item)" class="hover:text-white transition-colors">
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-semibold mb-4">{{ footer.contact_title }}</h4>
          <ul class="space-y-2 text-sm text-gray-400">
            <li v-if="footer.email">{{ footer.email }}</li>
            <li v-if="footer.phone">{{ footer.phone }}</li>
            <li v-if="footer.address" class="leading-relaxed">
              <span class="text-gray-500">آدرس:</span>
              {{ footer.address }}
            </li>
            <li v-if="footer.show_contact_link">
              <Link :href="route('contact')" class="hover:text-white transition-colors">
                {{ footer.contact_link_text || 'فرم تماس' }}
              </Link>
            </li>
          </ul>

          <div
            v-if="showEnamad"
            class="mt-6 pt-4 border-t border-gray-800"
          >
            <div class="enamad-badge inline-block" v-html="footer.enamad_html" />
          </div>
        </div>
      </div>

      <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm text-gray-500">
        {{ copyrightText }}
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { jalaliYear } from '@/Composables/useJalali';

const page = usePage();
const footer = computed(() => page.props.theme?.footer ?? {});

const visibleLinks = computed(() =>
  (footer.value.links ?? []).filter((item) => item.visible !== false)
);

const showEnamad = computed(() =>
  footer.value.enamad_enabled !== false && (footer.value.enamad_html ?? '').trim() !== ''
);

const copyrightText = computed(() => {
  const template = footer.value.copyright || 'تمامی حقوق محفوظ است © {year}';
  return template.replace('{year}', jalaliYear());
});

function resolveHref(item) {
  if (item.route_name) {
    try {
      return route(item.route_name);
    } catch {
      return item.url || '#';
    }
  }
  return item.url || '#';
}
</script>
