<template>
  <MainLayout>
    <Head :title="seoTitle">
      <meta name="description" :content="seoDesc">
      <meta property="og:title" :content="seoTitle">
      <meta property="og:description" :content="seoDesc">
      <meta property="og:type" content="product">
      <meta v-if="seoImage" property="og:image" :content="seoImage">
      <meta name="twitter:title" :content="seoTitle">
      <meta name="twitter:description" :content="seoDesc">
      <meta v-if="seoImage" name="twitter:image" :content="seoImage">
    </Head>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Header -->
          <div>
            <div v-if="course.category" class="mb-2">
              <Link :href="route('courses.index', { category: course.category.slug })" class="text-primary-600 text-sm font-semibold hover:underline">
                {{ course.category.name }}
              </Link>
            </div>
            <h1 class="text-3xl font-black text-gray-900 mb-4 leading-tight">{{ course.title }}</h1>
            <p v-if="course.short_description" class="text-gray-600 text-lg leading-relaxed">{{ course.short_description }}</p>

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-500">
              <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ toPersian(course.students_count) }} دانش‌آموز
              </span>
              <span v-if="course.rating > 0" class="flex items-center gap-1.5">
                <span class="text-yellow-500">★</span>
                {{ course.rating.toFixed(1) }} ({{ toPersian(course.ratings_count) }} نظر)
              </span>
              <span class="badge bg-gray-100 text-gray-600 px-3 py-1 rounded-full">{{ course.level_label }}</span>
              <span v-if="course.has_text" class="badge bg-blue-100 text-blue-700 px-3 py-1 rounded-full">📄 متنی</span>
              <span v-if="course.has_audio" class="badge bg-purple-100 text-purple-700 px-3 py-1 rounded-full">🎧 صوتی</span>
            </div>
          </div>

          <!-- Cover Image -->
          <div class="rounded-2xl overflow-hidden aspect-video bg-gray-100 shadow-lg">
            <img
              v-if="course.cover_image"
              :src="`/storage/${course.cover_image}`"
              :alt="course.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
              <svg class="w-24 h-24 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
          </div>

          <!-- Description -->
          <div v-if="course.description" class="card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">درباره این دوره</h2>
            <div class="prose max-w-none text-gray-600 leading-relaxed" v-html="course.description"></div>
          </div>

          <!-- Instructor -->
          <div v-if="course.instructor_name" class="card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">مدرس دوره</h2>
            <div class="flex items-start gap-4">
              <div class="w-16 h-16 rounded-2xl overflow-hidden bg-gray-100 flex-shrink-0">
                <img v-if="course.instructor_avatar" :src="`/storage/${course.instructor_avatar}`" :alt="course.instructor_name" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="font-bold text-gray-900">{{ course.instructor_name }}</h3>
                <p v-if="course.instructor_bio" class="text-gray-500 text-sm mt-1 leading-relaxed">{{ course.instructor_bio }}</p>
              </div>
            </div>
          </div>

          <!-- Curriculum -->
          <div v-if="course.sections.length" class="card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">سرفصل‌ها</h2>
            <div class="space-y-3">
              <div v-for="section in course.sections" :key="section.id" class="border border-gray-100 rounded-xl overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 font-semibold text-gray-800 flex items-center justify-between">
                  <span>{{ section.title }}</span>
                  <span class="text-gray-400 text-sm">{{ toPersian(section.lessons.length) }} جلسه</span>
                </div>
                <ul class="divide-y divide-gray-50">
                  <li v-for="lesson in section.lessons" :key="lesson.id"
                      class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                      <span v-if="lesson.is_preview" class="text-green-600">🔓</span>
                      <span v-else class="text-gray-400">🔒</span>
                      {{ lesson.title }}
                    </div>
                    <div class="flex items-center gap-2 text-gray-400 text-xs">
                      <span v-if="lesson.has_audio">🎧 {{ lesson.duration }}</span>
                      <span v-if="lesson.has_text">📄</span>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div v-if="course.reviews.length" class="card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">نظرات دانش‌آموزان</h2>
            <div class="space-y-4">
              <div v-for="review in course.reviews" :key="review.id" class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium text-gray-800">{{ review.user_name }}</span>
                  <div class="flex items-center gap-1">
                    <span v-for="i in 5" :key="i" class="text-sm" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'">★</span>
                  </div>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed">{{ review.comment }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ review.created_at }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar - Sticky -->
        <div class="lg:col-span-1">
          <div class="card p-6 sticky top-24 space-y-5">
            <!-- Price -->
            <div>
              <div v-if="course.is_discounted" class="flex items-center gap-3 mb-1">
                <span class="text-3xl font-black text-gray-900">{{ formatPrice(course.effective_price) }}</span>
                <span class="text-gray-400 line-through text-lg">{{ formatPrice(course.price) }}</span>
                <span class="badge bg-red-100 text-red-600 px-2 py-1 rounded-lg font-bold">{{ toPersian(course.discount_percent) }}٪</span>
              </div>
              <div v-else>
                <span v-if="course.price === 0" class="text-3xl font-black text-green-600">رایگان</span>
                <span v-else class="text-3xl font-black text-gray-900">{{ formatPrice(course.price) }}</span>
              </div>
            </div>

            <!-- CTA -->
            <template v-if="is_enrolled">
              <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center text-green-700 font-semibold text-sm">
                ✅ شما در این دوره ثبت‌نام کرده‌اید
              </div>
              <Link :href="route('courses.learn', course.slug)" class="btn-nexo w-full text-center py-4 rounded-xl text-base block">
                ادامه یادگیری ←
              </Link>
            </template>
            <template v-else>
              <Link :href="route('payment.checkout', course.slug)" class="btn-primary w-full text-center py-4 rounded-xl text-base block">
                {{ course.price === 0 ? 'دریافت رایگان' : 'خرید دوره' }}
              </Link>
            </template>

            <!-- Features -->
            <ul class="space-y-2.5 text-sm text-gray-600">
              <li v-if="course.has_text" class="flex items-center gap-2">
                <span class="text-primary-500">✓</span> محتوای متنی کامل
              </li>
              <li v-if="course.has_audio" class="flex items-center gap-2">
                <span class="text-primary-500">✓</span> پخش آنلاین صوتی (بدون دانلود)
              </li>
              <li class="flex items-center gap-2">
                <span class="text-primary-500">✓</span> دسترسی مادام‌العمر
              </li>
              <li class="flex items-center gap-2">
                <span class="text-primary-500">✓</span> پشتیبانی آنلاین
              </li>
            </ul>

            <!-- Stats -->
            <div class="border-t border-gray-100 pt-4 space-y-2.5 text-sm text-gray-500">
              <div class="flex justify-between">
                <span>دانش‌آموزان</span>
                <span class="font-semibold text-gray-700">{{ toPersian(course.students_count) }}</span>
              </div>
              <div v-if="course.duration_minutes" class="flex justify-between">
                <span>مدت صوت</span>
                <span class="font-semibold text-gray-700">{{ formatDur(course.duration_minutes) }}</span>
              </div>
              <div v-if="course.lessons_count" class="flex justify-between">
                <span>تعداد جلسات</span>
                <span class="font-semibold text-gray-700">{{ toPersian(course.lessons_count) }}</span>
              </div>
              <div class="flex justify-between">
                <span>سطح دوره</span>
                <span class="font-semibold text-gray-700">{{ course.level_label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  course: Object,
  is_enrolled: Boolean,
  enrollment: Object,
});

const seoTitle = computed(() => props.course.title);
const seoDesc = computed(() => props.course.short_description || `دوره ${props.course.title} - خرید و مشاهده دوره`);
const seoImage = computed(() => props.course.cover_image ? `/storage/${props.course.cover_image}` : null);

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

function formatPrice(amount) {
  return Number(amount).toLocaleString('fa-IR') + ' تومان';
}

function formatDur(minutes) {
  if (minutes < 60) return toPersian(minutes) + ' دقیقه';
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return toPersian(h) + ' ساعت' + (m ? ' و ' + toPersian(m) + ' دقیقه' : '');
}
</script>
