<template>
  <DashboardLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-black text-gray-900">تاریخچه سفارشات</h1>

      <div v-if="orders.length" class="space-y-4">
        <div v-for="order in orders" :key="order.id"
             class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4">
            <!-- Course Cover -->
            <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
              <img v-if="order.course_cover" :src="`/storage/${order.course_cover}`" class="w-full h-full object-cover"/>
              <div v-else class="w-full h-full bg-primary-100 flex items-center justify-center">
                <span class="text-2xl">📚</span>
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-gray-900 mb-1">{{ order.course_title }}</h3>
              <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  {{ order.order_number }}
                </span>
                <span>{{ order.content_type }}</span>
                <span>{{ order.created_at }}</span>
              </div>
            </div>

            <!-- Price & Action -->
            <div class="text-left flex-shrink-0">
              <div class="text-lg font-black text-gray-900 mb-2">{{ formatPrice(order.amount) }}</div>
              <span class="badge bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">پرداخت شده ✓</span>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-50 flex justify-end">
            <Link :href="route('courses.learn', order.course_slug)"
                  class="text-primary-600 hover:text-primary-700 text-sm font-semibold flex items-center gap-1">
              رفتن به دوره
              <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="text-7xl mb-4">🧾</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">هنوز سفارشی نداری</h3>
        <p class="text-gray-500 mb-6">بعد از اولین خرید، سفارشاتت اینجا نمایش داده می‌شه.</p>
        <Link :href="route('courses.index')" class="btn-primary">مشاهده دوره‌ها</Link>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ orders: Array });

function formatPrice(n) {
  return Number(n).toLocaleString('fa-IR') + ' تومان';
}
</script>
