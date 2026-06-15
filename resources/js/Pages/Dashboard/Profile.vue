<template>
  <DashboardLayout>
    <div class="max-w-xl space-y-6">
      <h1 class="text-2xl font-black text-gray-900">پروفایل من</h1>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.success }}
      </div>

      <!-- Profile Card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-primary-600 to-primary-800 px-6 py-8 text-center">
          <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <span class="text-white font-black text-3xl">{{ user.name?.[0] }}</span>
          </div>
          <h2 class="text-white font-bold text-xl">{{ user.name }}</h2>
          <p class="text-primary-200 text-sm mt-1">{{ user.mobile }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="p-6 space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">نام و نام خانوادگی</label>
            <input v-model="form.name" type="text" class="input-field"
                   placeholder="نام کامل خود را وارد کنید"/>
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">شماره موبایل</label>
            <input :value="user.mobile" type="text" disabled
                   class="input-field bg-gray-50 text-gray-400 cursor-not-allowed" dir="ltr"/>
            <p class="text-gray-400 text-xs mt-1">شماره موبایل قابل تغییر نیست.</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">ایمیل (اختیاری)</label>
            <input v-model="form.email" type="email" class="input-field" dir="ltr"
                   placeholder="example@email.com"/>
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
          </div>

          <button type="submit" :disabled="loading"
                  class="btn-primary w-full py-3 rounded-xl disabled:opacity-50">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              در حال ذخیره...
            </span>
            <span v-else>ذخیره تغییرات</span>
          </button>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({ user: Object });

const loading = ref(false);
const errors = ref({});

const form = reactive({
  name: props.user.name ?? '',
  email: props.user.email ?? '',
});

function submit() {
  loading.value = true;
  errors.value = {};
  router.post(route('dashboard.profile.update'), form, {
    onError: (e) => { errors.value = e; loading.value = false; },
    onSuccess: () => { loading.value = false; },
  });
}
</script>
