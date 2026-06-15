<template>
  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h1 class="text-2xl font-bold text-gray-900 mb-8">داشبورد من</h1>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="card p-6">
          <div class="text-3xl mb-3">🎓</div>
          <h3 class="font-bold text-gray-900">دوره‌های من</h3>
          <p class="text-gray-500 text-sm mt-1">دوره‌هایی که خریداری کرده‌اید</p>
          <Link :href="route('courses.index')" class="text-primary-600 text-sm font-semibold mt-3 inline-block hover:underline">
            مشاهده دوره‌ها ←
          </Link>
        </div>
        <div class="card p-6">
          <div class="text-3xl mb-3">👤</div>
          <h3 class="font-bold text-gray-900">پروفایل</h3>
          <p class="text-gray-500 text-sm mt-1">{{ $page.props.auth?.user?.name }}</p>
          <p class="text-gray-400 text-sm">{{ $page.props.auth?.user?.mobile }}</p>
        </div>
        <div class="card p-6">
          <div class="text-3xl mb-3">💳</div>
          <h3 class="font-bold text-gray-900">سفارشات</h3>
          <p class="text-gray-500 text-sm mt-1">تاریخچه خریدهای شما</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>
