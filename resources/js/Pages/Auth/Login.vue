<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-gray-100 flex items-center justify-center p-4" dir="rtl">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-primary-700 to-primary-900 p-8 text-center">
          <Link :href="route('home')" class="inline-flex items-center gap-2 mb-6 group">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
              <span class="text-white font-black text-xl">N</span>
            </div>
            <span class="text-white text-xl font-bold">نکسو کورس</span>
          </Link>
          <h1 class="text-2xl font-black text-white">ورود / ثبت‌نام</h1>
          <p class="text-primary-200 text-sm mt-2">با شماره موبایل وارد شو</p>
        </div>

        <!-- Form -->
        <div class="p-8">
          <!-- Step 1: Mobile Input -->
          <div v-if="step === 'mobile'" class="space-y-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">شماره موبایل</label>
              <input
                v-model="mobile"
                type="tel"
                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                class="input-field text-center text-xl tracking-widest"
                @keyup.enter="sendOtp"
                :disabled="loading"
                dir="ltr"
              />
              <p v-if="errors.mobile" class="text-red-500 text-sm mt-1">{{ errors.mobile }}</p>
            </div>

            <button
              @click="sendOtp"
              :disabled="loading || !mobile"
              class="btn-primary w-full py-4 text-base rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                در حال ارسال...
              </span>
              <span v-else>دریافت کد تأیید</span>
            </button>
          </div>

          <!-- Step 2: OTP Input -->
          <div v-else-if="step === 'otp'" class="space-y-6">
            <div class="text-center">
              <p class="text-gray-600">کد تأیید به شماره</p>
              <p class="text-primary-600 font-bold text-lg">{{ mobile }}</p>
              <p class="text-gray-600">ارسال شد</p>
            </div>

            <!-- Sandbox Notice -->
            <div v-if="sandboxCode" class="bg-amber-50 border-2 border-amber-300 rounded-xl px-4 py-3 text-center">
              <p class="text-amber-700 font-bold text-sm mb-1">⚙️ حالت تست فعال است</p>
              <p class="text-amber-800 text-2xl font-black tracking-widest">{{ sandboxCode }}</p>
              <p class="text-amber-600 text-xs mt-1">این کد برای همه شماره‌ها کار می‌کند</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">کد تأیید</label>
              <input
                v-model="code"
                type="text"
                inputmode="numeric"
                placeholder="• • • • •"
                maxlength="6"
                class="input-field text-center text-3xl tracking-[0.5em] font-bold"
                @keyup.enter="verifyOtp"
                :disabled="loading"
                dir="ltr"
                ref="codeInput"
              />
              <p v-if="errors.code" class="text-red-500 text-sm mt-1">{{ errors.code }}</p>
            </div>

            <button
              @click="verifyOtp"
              :disabled="loading || code.length < 4"
              class="btn-primary w-full py-4 text-base rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                در حال بررسی...
              </span>
              <span v-else>تأیید و ورود</span>
            </button>

            <!-- Resend -->
            <div class="text-center">
              <button
                v-if="canResend"
                @click="step = 'mobile'; code = ''"
                class="text-primary-600 hover:text-primary-700 text-sm font-semibold"
              >
                ارسال مجدد کد
              </button>
              <p v-else class="text-gray-400 text-sm">
                ارسال مجدد تا {{ toPersian(countdown) }} ثانیه دیگر
              </p>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ errorMessage }}
          </div>
        </div>
      </div>

      <p class="text-center text-gray-400 text-sm mt-6">
        با ورود، <a href="#" class="text-primary-600 hover:underline">قوانین و مقررات</a> را می‌پذیرید.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const step = ref('mobile');
const mobile = ref('');
const code = ref('');
const loading = ref(false);
const errors = ref({});
const errorMessage = ref('');
const countdown = ref(90);
const canResend = ref(false);
const codeInput = ref(null);
const sandboxCode = ref('');

let countdownTimer = null;

function toPersian(n) {
  return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function startCountdown(seconds = 60) {
  canResend.value = false;
  countdown.value = seconds;
  clearInterval(countdownTimer);
  countdownTimer = setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      clearInterval(countdownTimer);
      canResend.value = true;
    }
  }, 1000);
}

async function sendOtp() {
  if (!mobile.value || loading.value) return;
  loading.value = true;
  errors.value = {};
  errorMessage.value = '';

  try {
    const response = await axios.post(route('auth.send-otp'), { mobile: mobile.value });
    if (response.data.success) {
      step.value = 'otp';
      sandboxCode.value = response.data.sandbox ? response.data.code : '';
      startCountdown(90);
      await nextTick();
      codeInput.value?.focus();
    } else {
      errorMessage.value = response.data.message;
      if (response.data.retry_after) {
        startCountdown(response.data.retry_after);
      }
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(err.response.data.errors).map(([k, v]) => [k, v[0]])
      );
    } else {
      errorMessage.value = err.response?.data?.message || 'خطایی رخ داد.';
    }
  } finally {
    loading.value = false;
  }
}

async function verifyOtp() {
  if (!code.value || loading.value) return;
  loading.value = true;
  errors.value = {};
  errorMessage.value = '';

  try {
    const response = await axios.post(route('auth.verify-otp'), {
      mobile: mobile.value,
      code: code.value,
    });

    if (response.data.success) {
      router.visit(response.data.redirect || route('dashboard'));
    } else {
      errorMessage.value = response.data.message;
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = Object.fromEntries(
        Object.entries(err.response.data.errors).map(([k, v]) => [k, v[0]])
      );
    } else {
      errorMessage.value = err.response?.data?.message || 'کد اشتباه است.';
    }
  } finally {
    loading.value = false;
  }
}

onUnmounted(() => clearInterval(countdownTimer));
</script>
