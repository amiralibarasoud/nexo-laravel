<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-black text-gray-900">داشبورد من</h1>
        <p class="text-gray-500 text-sm mt-1">خوش آمدید، {{ $page.props.auth?.user?.name }}</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
          </div>
          <div class="text-3xl font-black text-gray-900">{{ toPersian(stats.enrolled_courses) }}</div>
          <div class="text-gray-500 text-sm mt-1">دوره خریداری‌شده</div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
          <div class="text-3xl font-black text-gray-900">{{ toPersian(stats.completed_lessons) }}</div>
          <div class="text-gray-500 text-sm mt-1">جلسه تکمیل‌شده</div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
          </div>
          <div class="text-3xl font-black text-gray-900">{{ toPersian(stats.total_orders) }}</div>
          <div class="text-gray-500 text-sm mt-1">خرید موفق</div>
        </div>
      </div>

      <!-- Recent Courses -->
      <div v-if="recent_courses.length">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-gray-900">دوره‌های اخیر</h2>
          <Link :href="route('dashboard.my-courses')" class="text-primary-600 text-sm hover:underline">مشاهده همه</Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="course in recent_courses" :key="course.id"
               class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="relative aspect-video bg-gray-100">
              <img v-if="course.cover_image" :src="`/storage/${course.cover_image}`" :alt="course.title" class="w-full h-full object-cover"/>
              <div v-else class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <!-- Progress bar -->
              <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20">
                <div class="h-full bg-primary-500 transition-all" :style="`width: ${course.progress_percent}%`"></div>
              </div>
            </div>
            <div class="p-4">
              <h3 class="font-bold text-gray-900 text-sm mb-2 line-clamp-2">{{ course.title }}</h3>
              <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                <span>{{ toPersian(course.progress_percent) }}٪ تکمیل</span>
                <span class="badge bg-primary-50 text-primary-700 px-2 py-0.5 rounded-lg">{{ course.content_type_label }}</span>
              </div>
              <Link :href="route('courses.learn', course.slug)" class="btn-primary w-full text-center text-sm py-2.5 rounded-xl block">
                ادامه یادگیری
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="text-6xl mb-4">🎓</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">هنوز دوره‌ای نداری</h3>
        <p class="text-gray-500 text-sm mb-6">اولین دوره‌ات رو همین الان بخر و یادگیری رو شروع کن!</p>
        <Link :href="route('courses.index')" class="btn-primary">
          مشاهده دوره‌ها
        </Link>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
  stats: Object,
  recent_courses: Array,
});

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}
</script>
