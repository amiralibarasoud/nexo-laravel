<template>
  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Order Summary -->
        <div class="card p-6 space-y-5">
          <h2 class="text-xl font-bold text-gray-900">خلاصه سفارش</h2>

          <!-- Course Info -->
          <div class="flex items-center gap-4 pb-5 border-b border-gray-100">
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
              <img v-if="course.cover_image" :src="`/storage/${course.cover_image}`" :alt="course.title" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full bg-primary-100 flex items-center justify-center text-2xl">📚</div>
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ course.title }}</h3>
              <div class="flex items-center gap-2 mt-1">
                <span v-if="course.has_text" class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">📄 متنی</span>
                <span v-if="course.has_audio" class="text-xs text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">🎧 صوتی</span>
              </div>
            </div>
          </div>

          <!-- Content Type -->
          <div v-if="course.has_text || course.has_audio">
            <label class="block text-sm font-semibold text-gray-700 mb-3">نوع محتوا:</label>
            <div class="space-y-2">
              <label v-if="course.has_text" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'text' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="text" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div class="flex-1 flex items-center justify-between gap-3">
                  <div class="font-semibold text-gray-800 text-sm">📄 فقط متنی</div>
                  <div class="text-left">
                    <span class="font-bold text-primary-700 text-sm">{{ formatPrice(priceForType('text').effective_price) }}</span>
                    <span v-if="priceForType('text').is_discounted" class="block text-xs text-gray-400 line-through">{{ formatPrice(priceForType('text').price) }}</span>
                  </div>
                </div>
              </label>
              <label v-if="course.has_audio" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'audio' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="audio" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div class="flex-1 flex items-center justify-between gap-3">
                  <div class="font-semibold text-gray-800 text-sm">🎧 فقط صوتی</div>
                  <div class="text-left">
                    <span class="font-bold text-primary-700 text-sm">{{ formatPrice(priceForType('audio').effective_price) }}</span>
                    <span v-if="priceForType('audio').is_discounted" class="block text-xs text-gray-400 line-through">{{ formatPrice(priceForType('audio').price) }}</span>
                  </div>
                </div>
              </label>
              <label v-if="course.has_text && course.has_audio" class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                     :class="form.content_type === 'both' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                <input type="radio" value="both" v-model="form.content_type" class="mt-0.5 text-primary-600" />
                <div class="flex-1 flex items-center justify-between gap-3">
                  <div class="font-semibold text-gray-800 text-sm">📄🎧 هر دو</div>
                  <div class="text-left">
                    <span class="font-bold text-primary-700 text-sm">{{ formatPrice(priceForType('both').effective_price) }}</span>
                    <span v-if="priceForType('both').is_discounted" class="block text-xs text-gray-400 line-through">{{ formatPrice(priceForType('both').price) }}</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Coupon Code -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">کد تخفیف</label>
            <div class="flex gap-2">
              <input
                v-model="couponInput"
                type="text"
                placeholder="کد تخفیف دارید؟"
                class="input-field flex-1 uppercase tracking-widest text-center"
                :disabled="!!appliedCoupon || couponLoading"
                @keyup.enter="applyCoupon"
                dir="ltr"
              />
              <button
                v-if="!appliedCoupon"
                @click="applyCoupon"
                :disabled="!couponInput || couponLoading"
                class="px-4 py-3 bg-gray-800 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 disabled:opacity-40 transition-all flex-shrink-0"
              >
                <span v-if="couponLoading">
                  <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                </span>
                <span v-else>اعمال</span>
              </button>
              <button
                v-else
                @click="removeCoupon"
                class="px-4 py-3 bg-red-50 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-100 transition-all flex-shrink-0"
              >
                حذف
              </button>
            </div>

            <!-- Coupon feedback -->
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0">
              <div v-if="couponMessage" class="mt-2 text-sm rounded-lg px-3 py-2 flex items-center gap-2"
                   :class="appliedCoupon ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-200'">
                <span>{{ appliedCoupon ? '✅' : '❌' }}</span>
                {{ couponMessage }}
              </div>
            </Transition>
          </div>

          <!-- Price Summary -->
          <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>قیمت دوره</span>
              <span>{{ formatPrice(selectedPricing.price) }}</span>
            </div>
            <div v-if="selectedPricing.is_discounted" class="flex justify-between text-orange-500">
              <span>تخفیف دوره</span>
              <span>- {{ formatPrice(selectedPricing.price - selectedPricing.effective_price) }}</span>
            </div>
            <div v-if="appliedCoupon" class="flex justify-between text-green-600 font-semibold">
              <span>کد تخفیف ({{ appliedCoupon.code }})</span>
              <span>- {{ formatPrice(appliedCoupon.discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-200 text-base">
              <span>مبلغ قابل پرداخت</span>
              <span class="text-primary-600 text-lg">{{ formatPrice(finalAmount) }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Gateway -->
        <div class="card p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-5">درگاه پرداخت</h2>

          <div class="space-y-3 mb-6">
            <label v-if="gateways.zarinpal" class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                   :class="form.gateway === 'zarinpal' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" value="zarinpal" v-model="form.gateway" class="text-primary-600" />
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center font-black text-yellow-500 text-xl">Z</div>
                <div><div class="font-bold text-gray-800">زرین‌پال</div><div class="text-xs text-gray-500">امن‌ترین درگاه پرداخت</div></div>
              </div>
            </label>

            <label v-if="gateways.zibal" class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                   :class="form.gateway === 'zibal' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" value="zibal" v-model="form.gateway" class="text-primary-600" />
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center font-black text-blue-500 text-xl">Z</div>
                <div><div class="font-bold text-gray-800">زیبال</div><div class="text-xs text-gray-500">پرداخت سریع و مطمئن</div></div>
              </div>
            </label>
          </div>

          <!-- Security -->
          <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-5 text-sm text-green-700 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            پرداخت از طریق درگاه امن بانکی
          </div>

          <!-- Final amount highlight -->
          <div v-if="appliedCoupon" class="bg-primary-50 border border-primary-200 rounded-xl p-4 mb-5 text-center">
            <p class="text-sm text-primary-600 mb-1">با کد تخفیف می‌پردازی:</p>
            <p class="text-2xl font-black text-primary-700">{{ formatPrice(finalAmount) }}</p>
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
            <span v-else>پرداخت {{ formatPrice(finalAmount) }}</span>
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  course: Object,
  gateways: {
    type: Object,
    default: () => ({ zarinpal: false, zibal: true }),
  },
});

const loading       = ref(false);
const couponInput   = ref('');
const couponLoading = ref(false);
const couponMessage = ref('');
const appliedCoupon = ref(null);

const form = reactive({
  content_type: props.course.has_text ? 'text' : (props.course.has_audio ? 'audio' : 'text'),
  gateway: props.gateways.zibal ? 'zibal' : (props.gateways.zarinpal ? 'zarinpal' : 'zibal'),
  coupon_code: '',
});

const selectedPricing = computed(() => {
  return props.course.content_type_prices?.[form.content_type] ?? {
    price: props.course.price,
    effective_price: props.course.effective_price,
    is_discounted: props.course.is_discounted,
  };
});

const finalAmount = computed(() => {
  const base = selectedPricing.value.effective_price;
  if (appliedCoupon.value) return base - appliedCoupon.value.discount;
  return base;
});

function priceForType(type) {
  return props.course.content_type_prices?.[type] ?? {
    price: props.course.price,
    effective_price: props.course.effective_price,
    is_discounted: props.course.is_discounted,
  };
}

function formatPrice(amount) {
  return Number(amount).toLocaleString('fa-IR') + ' تومان';
}

async function applyCoupon() {
  if (!couponInput.value || couponLoading.value) return;
  couponLoading.value = true;
  couponMessage.value = '';
  appliedCoupon.value = null;

  try {
    const res = await axios.post(route('coupon.validate'), {
      code:      couponInput.value.toUpperCase(),
      course_id: props.course.id,
      amount:    selectedPricing.value.effective_price,
    });
    appliedCoupon.value = res.data;
    form.coupon_code    = couponInput.value.toUpperCase();
    couponMessage.value = res.data.message;
  } catch (err) {
    couponMessage.value = err.response?.data?.message || 'کد تخفیف معتبر نیست.';
  } finally {
    couponLoading.value = false;
  }
}

function removeCoupon() {
  appliedCoupon.value = null;
  couponMessage.value = '';
  couponInput.value   = '';
  form.coupon_code    = '';
}

watch(() => form.content_type, () => {
  if (appliedCoupon.value) {
    removeCoupon();
  }
});

function submitPayment() {
  if (loading.value || !form.gateway) return;
  loading.value = true;

  const htmlForm = document.createElement('form');
  htmlForm.method = 'POST';
  htmlForm.action = route('payment.initiate');

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (csrfToken) {
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = csrfToken;
    htmlForm.appendChild(tokenInput);
  }

  const fields = {
    course_id: props.course.id,
    content_type: form.content_type,
    gateway: form.gateway,
    coupon_code: form.coupon_code || '',
  };

  for (const [name, value] of Object.entries(fields)) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    htmlForm.appendChild(input);
  }

  document.body.appendChild(htmlForm);
  htmlForm.submit();
}
</script>
