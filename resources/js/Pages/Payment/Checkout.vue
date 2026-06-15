<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Order Summary -->
        <div class="card p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-5">خلاصه سفارش</h2>

          <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
              <img v-if="course.cover_image" :src="`/storage/${course.cover_image}`" :alt="course.title" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full bg-primary-100 flex items-center justify-center">
                <span class="text-2xl">📚</span>
              </div>
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ course.title }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span v-if="course.has_text" class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">📄 متنی</span>
                <span v-if="course.has_audio" class="text-xs text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">🎧 صوتی</span>
              </div>
            </div>
          </div>

          <!-- Content Type Selection -->
          <div v-if="course.has_text || course.has_audio" class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">نوع محتوا را انتخاب کنید:</label>
            <div class="space-y-2.5">
              <label v-if="course.has_text" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'text' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="text" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div>
                  <div class="font-semibold text-gray-800">📄 فقط متنی</div>
                  <div class="text-sm text-gray-500">مطالعه دوره به صورت متن</div>
                </div>
              </label>

              <label v-if="course.has_audio" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'audio' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="audio" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div>
                  <div class="font-semibold text-gray-800">🎧 فقط صوتی</div>
                  <div class="text-sm text-gray-500">گوش دادن به صدای استاد (بدون قابلیت دانلود)</div>
                </div>
              </label>

              <label v-if="course.has_text && course.has_audio" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'both' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="both" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div>
                  <div class="font-semibold text-gray-800">📄🎧 هر دو</div>
                  <div class="text-sm text-gray-500">دسترسی به محتوای متنی و صوتی</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Price -->
          <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>قیمت دوره</span>
              <span>{{ formatPrice(course.price) }}</span>
            </div>
            <div v-if="course.is_discounted" class="flex justify-between text-red-500">
              <span>تخفیف</span>
              <span>- {{ formatPrice(course.price - course.effective_price) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-200 text-base">
              <span>مبلغ قابل پرداخت</span>
              <span class="text-primary-600">{{ formatPrice(course.effective_price) }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Gateway -->
        <div class="card p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-5">درگاه پرداخت</h2>

          <div class="space-y-3 mb-6">
            <label class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                   :class="form.gateway === 'zarinpal' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" value="zarinpal" v-model="form.gateway" class="text-primary-600" />
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                  <span class="text-yellow-500 font-black text-xl">Z</span>
                </div>
                <div>
                  <div class="font-bold text-gray-800">زرین‌پال</div>
                  <div class="text-xs text-gray-500">امن‌ترین درگاه پرداخت</div>
                </div>
              </div>
            </label>

            <label class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                   :class="form.gateway === 'zibal' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" value="zibal" v-model="form.gateway" class="text-primary-600" />
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                  <span class="text-blue-500 font-black text-xl">Z</span>
                </div>
                <div>
                  <div class="font-bold text-gray-800">زیبال</div>
                  <div class="text-xs text-gray-500">پرداخت سریع و مطمئن</div>
                </div>
              </div>
            </label>
          </div>

          <!-- Security Notice -->
          <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-6 text-sm text-green-700 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            پرداخت شما از طریق درگاه امن بانکی انجام می‌شود.
          </div>

          <button
            @click="submitPayment"
            :disabled="loading || !form.gateway"
            class="btn-primary w-full py-4 text-base rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              در حال اتصال به درگاه...
            </span>
            <span v-else>پرداخت {{ formatPrice(course.effective_price) }}</span>
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  course: Object,
});

const loading = ref(false);
const form = reactive({
  content_type: props.course.has_text ? 'text' : (props.course.has_audio ? 'audio' : 'text'),
  gateway: 'zarinpal',
});

function formatPrice(amount) {
  return Number(amount).toLocaleString('fa-IR') + ' تومان';
}

function submitPayment() {
  loading.value = true;
  router.post(route('payment.initiate'), {
    course_id: props.course.id,
    content_type: form.content_type,
    gateway: form.gateway,
  }, {
    onError: () => { loading.value = false; },
  });
}
</script>
