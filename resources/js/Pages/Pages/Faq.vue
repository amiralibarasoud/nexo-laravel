<template>
  <MainLayout>
    <Head :title="pageFaq.seo_title || 'سوالات متداول'">
      <meta v-if="pageFaq.subtitle" name="description" :content="pageFaq.subtitle">
    </Head>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-3">
          {{ pageFaq.title || 'سوالات متداول' }}
        </h1>
        <p v-if="pageFaq.subtitle" class="text-gray-500 leading-relaxed">
          {{ pageFaq.subtitle }}
        </p>
      </div>

      <div v-if="items.length" class="space-y-3">
        <div
          v-for="(item, index) in items"
          :key="`faq-${index}`"
          class="card overflow-hidden"
        >
          <button
            type="button"
            class="w-full flex items-center justify-between gap-4 px-5 py-4 text-right hover:bg-gray-50 transition-colors"
            :aria-expanded="openIndex === index"
            @click="toggle(index)"
          >
            <span class="font-bold text-gray-900 leading-relaxed">{{ item.question }}</span>
            <span
              class="shrink-0 w-8 h-8 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center transition-transform"
              :class="openIndex === index ? 'rotate-180' : ''"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </span>
          </button>
          <div
            v-show="openIndex === index"
            class="px-5 pb-5 text-gray-600 text-sm leading-loose border-t border-gray-50 pt-4 whitespace-pre-line"
          >
            {{ item.answer }}
          </div>
        </div>
      </div>

      <div v-else class="card p-10 text-center text-gray-500">
        هنوز سوالی ثبت نشده است.
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  faq: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const pageFaq = computed(() => ({
  ...(page.props.theme?.faq ?? {}),
  ...(props.faq ?? {}),
}));

const items = computed(() => {
  const raw = pageFaq.value.items;
  const list = Array.isArray(raw) ? raw : Object.values(raw ?? {});

  return list.filter((item) => item && String(item.question ?? '').trim());
});

const openIndex = ref(items.value.length ? 0 : -1);

function toggle(index) {
  openIndex.value = openIndex.value === index ? -1 : index;
}
</script>
