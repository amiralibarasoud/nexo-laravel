<template>
  <Link :href="route('courses.show', course.slug)" class="card-hover block group">
    <!-- Cover Image -->
    <div class="relative aspect-video overflow-hidden bg-gray-100">
      <img
        v-if="course.cover_image"
        :src="`/storage/${course.cover_image}`"
        :alt="course.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
      />
      <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
        <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>

      <!-- Badges -->
      <div class="absolute top-3 right-3 flex flex-col gap-1.5">
        <span v-if="course.is_discounted" class="badge bg-red-500 text-white text-xs px-2 py-1 rounded-lg font-bold shadow-sm">
          {{ course.discount_percent }}٪ تخفیف
        </span>
        <span v-if="course.is_featured" class="badge bg-yellow-500 text-white text-xs px-2 py-1 rounded-lg font-bold shadow-sm">
          ✨ ویژه
        </span>
      </div>

      <!-- Content Type Badges -->
      <div class="absolute bottom-3 left-3 flex gap-1.5">
        <span v-if="course.has_text" class="bg-blue-600/90 text-white text-xs px-2 py-0.5 rounded-md font-medium backdrop-blur-sm">
          📄 متنی
        </span>
        <span v-if="course.has_audio" class="bg-purple-600/90 text-white text-xs px-2 py-0.5 rounded-md font-medium backdrop-blur-sm">
          🎧 صوتی
        </span>
      </div>
    </div>

    <!-- Card Body -->
    <div class="p-5">
      <!-- Title -->
      <h3 class="font-bold text-gray-900 text-base mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors leading-relaxed">
        {{ course.title }}
      </h3>

      <!-- Description -->
      <p v-if="course.short_description" class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed">
        {{ course.short_description }}
      </p>

      <!-- Instructor -->
      <div v-if="course.instructor_name" class="flex items-center gap-2 mb-3">
        <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
          <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
        </div>
        <span class="text-gray-600 text-sm">{{ course.instructor_name }}</span>
      </div>

      <!-- Stats Row -->
      <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
        <span v-if="course.rating > 0" class="flex items-center gap-1">
          <span class="text-yellow-500">★</span>
          <span>{{ course.rating.toFixed(1) }}</span>
        </span>
        <span class="flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ toPersian(course.students_count) }} دانش‌آموز
        </span>
        <span v-if="course.duration_minutes" class="flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ formatDur(course.duration_minutes) }}
        </span>
      </div>

      <!-- Price -->
      <div class="flex items-center justify-between border-t border-gray-100 pt-3">
        <div>
          <div v-if="course.is_discounted" class="flex items-center gap-2">
            <span class="text-xl font-bold text-gray-900">{{ formatPrice(course.effective_price) }}</span>
            <span class="text-sm text-gray-400 line-through">{{ formatPrice(course.price) }}</span>
          </div>
          <div v-else>
            <span v-if="course.price === 0" class="text-xl font-bold text-green-600">رایگان</span>
            <span v-else class="text-xl font-bold text-gray-900">{{ formatPrice(course.price) }}</span>
          </div>
        </div>
        <span class="badge bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-lg">{{ course.level_label }}</span>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  course: {
    type: Object,
    required: true,
  },
});

function toPersian(n) {
  return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function formatPrice(amount) {
  const formatted = Number(amount).toLocaleString('fa-IR');
  return formatted + ' تومان';
}

function formatDur(minutes) {
  if (minutes < 60) return toPersian(minutes) + ' دقیقه';
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return toPersian(h) + ' ساعت' + (m ? ' و ' + toPersian(m) + ' دقیقه' : '');
}
</script>
