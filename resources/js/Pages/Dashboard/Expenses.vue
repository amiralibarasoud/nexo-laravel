<template>
  <DashboardLayout>
    <Head title="ثبت هزینه‌ها" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-2xl font-black text-gray-900">حسابداری شخصی</h1>
          <p class="text-gray-500 text-sm mt-1">ثبت و تحلیل هزینه‌های روزانه</p>
        </div>
        <div class="text-sm text-gray-400">
          امروز: <span class="font-semibold text-gray-600">{{ today_jalali }}</span>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-5 text-white shadow-lg shadow-primary-200">
          <p class="text-primary-100 text-sm mb-1">جمع هفتگی</p>
          <p class="text-2xl font-black">{{ formatPrice(summary.week_total) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-gray-500 text-sm mb-1">جمع ماه جاری</p>
          <p class="text-2xl font-black text-gray-900">{{ formatPrice(summary.month_total) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
          <p class="text-gray-500 text-sm mb-1">جمع بازه انتخابی</p>
          <p class="text-2xl font-black text-rose-600">{{ formatPrice(summary.period_total) }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ toPersian(summary.expense_count) }} ثبت</p>
        </div>
      </div>

      <!-- Add Expense Form -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-sm">+</span>
          ثبت هزینه جدید
        </h2>
        <form @submit.prevent="submitExpense" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">عنوان هزینه</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="مثلاً خرید مواد غذایی"
              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
            />
            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">تاریخ (شمسی)</label>
            <input
              v-model="form.expense_date"
              type="text"
              dir="ltr"
              placeholder="۱۴۰۴/۰۵/۰۱"
              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-center focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
            />
            <p v-if="form.errors.expense_date" class="text-red-500 text-xs mt-1">{{ form.errors.expense_date }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">مبلغ (تومان)</label>
            <input
              v-model="form.amount"
              type="text"
              inputmode="numeric"
              placeholder="۵۰۰٬۰۰۰"
              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
            />
            <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</p>
          </div>
          <div class="md:col-span-4 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="btn-primary px-8 py-3 rounded-xl disabled:opacity-60"
            >
              {{ form.processing ? 'در حال ثبت...' : 'ثبت هزینه' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <button
            v-for="tab in periodTabs"
            :key="tab.value"
            type="button"
            class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
            :class="localFilters.period === tab.value
              ? 'bg-primary-600 text-white shadow-md'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            @click="setPeriod(tab.value)"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-if="localFilters.period === 'custom'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
          <div>
            <label class="block text-xs text-gray-500 mb-1">از تاریخ</label>
            <input v-model="localFilters.from" type="text" dir="ltr" placeholder="۱۴۰۴/۰۵/۰۱"
                   class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-center" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">تا تاریخ</label>
            <input v-model="localFilters.to" type="text" dir="ltr" placeholder="۱۴۰۴/۰۵/۳۰"
                   class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-center" />
          </div>
          <button type="button" class="btn-primary py-2 rounded-xl" @click="applyFilters">اعمال فیلتر</button>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 overflow-hidden">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-gray-900">نمودار هزینه‌ها</h2>
          <span class="text-xs text-gray-400">هاور روی نقاط برای جزئیات</span>
        </div>
        <div v-if="hasChartData" class="h-80 -mx-2">
          <VueApexCharts
            type="area"
            height="320"
            :options="chartOptions"
            :series="chartSeries"
          />
        </div>
        <div v-else class="h-64 flex flex-col items-center justify-center text-gray-400">
          <div class="text-5xl mb-3">📊</div>
          <p>در این بازه هنوز هزینه‌ای ثبت نشده</p>
        </div>
      </div>

      <!-- Expense List -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
          <h2 class="text-lg font-bold text-gray-900">لیست هزینه‌ها</h2>
          <span class="text-sm text-gray-400">{{ toPersian(expenses.length) }} مورد</span>
        </div>

        <div v-if="expenses.length" class="divide-y divide-gray-50">
          <div
            v-for="expense in expenses"
            :key="expense.id"
            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/80 transition-colors"
          >
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 truncate">{{ expense.title }}</p>
              <p class="text-sm text-gray-400">{{ expense.expense_date }}</p>
            </div>
            <div class="text-left shrink-0">
              <p class="font-black text-gray-900">{{ formatPrice(expense.amount) }}</p>
              <button
                type="button"
                class="text-xs text-red-500 hover:text-red-700 mt-1"
                @click="deleteExpense(expense.id)"
              >
                حذف
              </button>
            </div>
          </div>
        </div>

        <div v-else class="p-12 text-center text-gray-400">
          <p>هنوز هزینه‌ای ثبت نکرده‌اید</p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  filters: Object,
  summary: Object,
  chart: Array,
  expenses: Array,
  today_jalali: String,
});

const localFilters = reactive({
  period: props.filters?.period ?? 'month',
  from: props.filters?.from ?? '',
  to: props.filters?.to ?? '',
});

const periodTabs = [
  { value: 'week', label: '۷ روز اخیر' },
  { value: 'month', label: 'ماه جاری' },
  { value: 'custom', label: 'بازه دلخواه' },
];

const form = useForm({
  title: '',
  amount: '',
  expense_date: props.today_jalali ?? '',
});

const hasChartData = computed(() => (props.chart ?? []).some((p) => p.total > 0));

const chartSeries = computed(() => [{
  name: 'هزینه روزانه',
  data: (props.chart ?? []).map((p) => p.total),
}]);

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'Vazirmatn, Tahoma, sans-serif',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
    },
  },
  colors: ['#2563eb'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [0, 90, 100],
    },
  },
  stroke: {
    curve: 'smooth',
    width: 3,
  },
  dataLabels: { enabled: false },
  markers: {
    size: 5,
    strokeWidth: 2,
    strokeColors: '#fff',
    hover: { size: 8 },
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    padding: { left: 10, right: 10 },
  },
  xaxis: {
    categories: (props.chart ?? []).map((p) => p.jalali),
    labels: {
      style: { fontFamily: 'Vazirmatn, Tahoma, sans-serif', fontSize: '11px' },
      rotate: -45,
      rotateAlways: (props.chart ?? []).length > 10,
    },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      formatter: (v) => formatShort(v),
      style: { fontFamily: 'Vazirmatn, Tahoma, sans-serif' },
    },
  },
  tooltip: {
    custom: ({ dataPointIndex }) => {
      const point = props.chart?.[dataPointIndex];
      if (!point) return '';

      const itemsHtml = point.items?.length
        ? point.items.map((item) => `
            <div style="display:flex;justify-content:space-between;gap:12px;padding:4px 0;border-bottom:1px solid #f1f5f9">
              <span style="color:#334155">${item.title}</span>
              <span style="font-weight:700;color:#0f172a">${formatPrice(item.amount)}</span>
            </div>
          `).join('')
        : '<div style="color:#94a3b8;font-size:12px">بدون هزینه</div>';

      return `
        <div style="padding:12px 14px;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.12);min-width:200px;direction:rtl;text-align:right;font-family:Vazirmatn,Tahoma,sans-serif">
          <div style="font-weight:800;color:#0f172a;margin-bottom:6px">${point.jalali}</div>
          <div style="font-size:13px;color:#2563eb;font-weight:700;margin-bottom:8px">جمع: ${formatPrice(point.total)}</div>
          ${itemsHtml}
        </div>
      `;
    },
  },
}));

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function formatPrice(n) {
  return Number(n ?? 0).toLocaleString('fa-IR') + ' تومان';
}

function formatShort(n) {
  const v = Number(n ?? 0);
  if (v >= 1_000_000) return (v / 1_000_000).toLocaleString('fa-IR', { maximumFractionDigits: 1 }) + 'M';
  if (v >= 1_000) return (v / 1_000).toLocaleString('fa-IR', { maximumFractionDigits: 0 }) + 'K';
  return v.toLocaleString('fa-IR');
}

function normalizeAmount(value) {
  const persian = '۰۱۲۳۴۵۶۷۸۹';
  const arabic = '٠١٢٣٤٥٦٧٨٩';
  return String(value ?? '')
    .replace(/[۰-۹]/g, (d) => String(persian.indexOf(d)))
    .replace(/[٠-٩]/g, (d) => String(arabic.indexOf(d)))
    .replace(/[^\d]/g, '');
}

function submitExpense() {
  form.transform((data) => ({
    ...data,
    amount: normalizeAmount(data.amount),
  })).post(route('dashboard.expenses.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('title', 'amount');
      form.expense_date = props.today_jalali;
    },
  });
}

function setPeriod(period) {
  localFilters.period = period;
  if (period !== 'custom') {
    applyFilters();
  }
}

function applyFilters() {
  router.get(route('dashboard.expenses'), {
    period: localFilters.period,
    from: localFilters.period === 'custom' ? localFilters.from : undefined,
    to: localFilters.period === 'custom' ? localFilters.to : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}

function deleteExpense(id) {
  if (!confirm('این هزینه حذف شود؟')) return;
  router.delete(route('dashboard.expenses.destroy', id), { preserveScroll: true });
}
</script>
