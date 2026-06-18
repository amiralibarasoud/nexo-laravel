<template>
  <MainLayout>
    <Head :title="contact.seo_title || 'تماس با ما'" />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="text-center mb-12">
        <h1 class="text-4xl font-black text-gray-900 mb-3">{{ contact.page_title }}</h1>
        <p v-if="contact.subtitle" class="text-gray-500 text-lg">{{ contact.subtitle }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div v-if="visibleInfoItems.length" class="space-y-4">
          <div
            v-for="(item, index) in visibleInfoItems"
            :key="`contact-info-${index}`"
            class="card p-5 flex items-start gap-4"
          >
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 text-xl" :class="item.bg || 'bg-blue-50'">
              {{ item.icon }}
            </div>
            <div>
              <h3 class="font-bold text-gray-900 text-sm">{{ item.title }}</h3>
              <p class="text-gray-500 text-sm mt-0.5">{{ item.value }}</p>
            </div>
          </div>
        </div>

        <div v-if="contact.form?.enabled" :class="visibleInfoItems.length ? 'lg:col-span-2' : 'lg:col-span-3'">
          <div class="card p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">{{ contact.form.title }}</h2>

            <div
              v-if="$page.props.flash?.success"
              class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2"
            >
              <span>✅</span> {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">{{ contact.form.name_label }}</label>
                  <input v-model="form.name" type="text" class="input-field" :placeholder="contact.form.name_placeholder"/>
                  <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">{{ contact.form.mobile_label }}</label>
                  <input v-model="form.mobile" type="tel" class="input-field" :placeholder="contact.form.mobile_placeholder" dir="ltr"/>
                  <p v-if="errors.mobile" class="text-red-500 text-xs mt-1">{{ errors.mobile }}</p>
                </div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ contact.form.subject_label }}</label>
                <input v-model="form.subject" type="text" class="input-field" :placeholder="contact.form.subject_placeholder"/>
                <p v-if="errors.subject" class="text-red-500 text-xs mt-1">{{ errors.subject }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ contact.form.message_label }}</label>
                <textarea v-model="form.message" rows="5" class="input-field resize-none" :placeholder="contact.form.message_placeholder"></textarea>
                <p v-if="errors.message" class="text-red-500 text-xs mt-1">{{ errors.message }}</p>
              </div>
              <button type="submit" :disabled="loading" class="btn-primary w-full py-3 rounded-xl disabled:opacity-50">
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ contact.form.loading_text }}
                </span>
                <span v-else>{{ contact.form.submit_text }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const contact = computed(() => page.props.theme?.contact ?? {});

const visibleInfoItems = computed(() =>
  (contact.value.info_items ?? []).filter((item) => item.visible !== false)
);

const loading = ref(false);
const errors = ref({});
const form = reactive({ name: '', mobile: '', subject: '', message: '' });

function submit() {
  loading.value = true;
  errors.value = {};
  router.post(route('contact.submit'), form, {
    onError: (e) => { errors.value = e; loading.value = false; },
    onSuccess: () => { Object.assign(form, { name: '', mobile: '', subject: '', message: '' }); loading.value = false; },
  });
}
</script>
