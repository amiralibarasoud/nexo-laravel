<template>
  <DashboardLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-black text-gray-900">دوره‌های من</h1>

      <div v-if="courses.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <div v-for="course in courses" :key="course.id"
             class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
          <!-- Cover -->
          <div class="relative aspect-video bg-gray-100">
            <img v-if="course.cover_image" :src="`/storage/${course.cover_image}`" :alt="course.title" class="w-full h-full object-cover"/>
            <div v-else class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
              <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>

            <!-- Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-2 bg-black/20">
              <div class="h-full bg-green-500 transition-all duration-500" :style="`width: ${course.progress_percent}%`"></div>
            </div>

            <!-- Badge -->
            <div class="absolute top-3 right-3">
              <span class="badge text-xs px-2 py-1 rounded-lg font-medium"
                    :class="course.content_type === 'audio' ? 'bg-purple-600/90 text-white' : course.content_type === 'both' ? 'bg-blue-600/90 text-white' : 'bg-blue-500/90 text-white'">
                {{ course.content_type_label }}
              </span>
            </div>
          </div>

          <!-- Body -->
          <div class="p-5">
            <h3 class="font-bold text-gray-900 mb-1 line-clamp-2 leading-relaxed">{{ course.title }}</h3>
            <p v-if="course.instructor_name" class="text-gray-500 text-xs mb-3">{{ course.instructor_name }}</p>

            <!-- Progress -->
            <div class="mb-4">
              <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                <span>پیشرفت</span>
                <span class="font-semibold" :class="course.progress_percent === 100 ? 'text-green-600' : 'text-gray-700'">
                  {{ toPersian(course.progress_percent) }}٪
                  <span v-if="course.progress_percent === 100" class="mr-1">✅</span>
                </span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     :class="course.progress_percent === 100 ? 'bg-green-500' : 'bg-primary-500'"
                     :style="`width: ${course.progress_percent}%`"></div>
              </div>
            </div>

            <!-- Info -->
            <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
              <span>{{ toPersian(course.lessons_count) }} جلسه</span>
              <span>ثبت‌نام: {{ course.enrolled_at }}</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <Link :href="route('courses.learn', course.slug)"
                    class="btn-primary flex-1 text-center text-sm py-2.5 rounded-xl block">
                {{ course.progress_percent > 0 ? 'ادامه یادگیری' : 'شروع یادگیری' }}
              </Link>
              <Link :href="route('courses.show', course.slug)"
                    class="px-3 py-2.5 border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="text-7xl mb-4">📚</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">هنوز دوره‌ای نخریدی</h3>
        <p class="text-gray-500 mb-6">از بین دوره‌های موجود، اولین دوره‌ات رو انتخاب کن.</p>
        <Link :href="route('courses.index')" class="btn-primary">مشاهده دوره‌ها</Link>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ courses: Array });

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}
</script>
