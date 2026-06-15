<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-primary-300 rounded-full blur-3xl"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <div class="inline-flex items-center gap-2 bg-white/10 text-white/90 rounded-full px-4 py-2 text-sm mb-6 backdrop-blur-sm border border-white/20">
            ✨ بهترین پلتفرم یادگیری آنلاین فارسی
          </div>

          <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
            یادگیری با
            <span class="text-yellow-300">صدای</span> استاد<br>
            یا <span class="text-green-300">متن</span>؟
            <br class="hidden md:block">
            <span class="text-3xl md:text-5xl font-bold text-white/80">انتخاب با توست.</span>
          </h1>

          <p class="text-primary-100 text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            دوره‌های کاربردی به دو فرمت متنی و صوتی. بعد از خرید، هر طور که راحت‌تری یاد بگیر.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link :href="route('courses.index')" class="btn-nexo text-base px-8 py-4 rounded-2xl shadow-xl hover:shadow-2xl">
              مشاهده دوره‌ها
              <svg class="w-5 h-5 mr-2 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </Link>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-6 max-w-lg mx-auto mt-16">
            <div class="text-center">
              <div class="text-3xl font-black text-white">{{ toPersian(stats.courses_count) }}+</div>
              <div class="text-primary-200 text-sm mt-1">دوره آموزشی</div>
            </div>
            <div class="text-center border-x border-white/20">
              <div class="text-3xl font-black text-white">{{ toPersian(stats.students_count) }}+</div>
              <div class="text-primary-200 text-sm mt-1">دانش‌آموز</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-black text-white">۲</div>
              <div class="text-primary-200 text-sm mt-1">فرمت محتوا</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="section-title">چطور کار می‌کنه؟</h2>
          <p class="section-subtitle">در چند قدم ساده شروع کن</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(step, index) in steps" :key="index" class="text-center group">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-3xl shadow-lg transition-transform group-hover:-translate-y-1"
                 :class="step.bg">
              {{ step.emoji }}
            </div>
            <div class="text-primary-600 font-bold text-sm mb-2">مرحله {{ toPersian(index + 1) }}</div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ step.title }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section v-if="categories.length" class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
          <h2 class="section-title">دسته‌بندی‌ها</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <Link
            v-for="cat in categories"
            :key="cat.id"
            :href="route('courses.index', { category: cat.slug })"
            class="card-hover p-4 text-center group cursor-pointer"
          >
            <div class="text-3xl mb-2">{{ cat.icon || '📚' }}</div>
            <div class="font-semibold text-gray-800 text-sm group-hover:text-primary-600 transition-colors">{{ cat.name }}</div>
            <div class="text-gray-400 text-xs mt-1">{{ toPersian(cat.courses_count) }} دوره</div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Featured Courses -->
    <section v-if="featured_courses.length" class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="section-title">دوره‌های ویژه</h2>
            <p class="section-subtitle">بهترین دوره‌ها برای شما</p>
          </div>
          <Link :href="route('courses.index')" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-1 text-sm">
            مشاهده همه
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <CourseCard v-for="course in featured_courses" :key="course.id" :course="course" />
        </div>
      </div>
    </section>

    <!-- Content Types Section -->
    <section class="py-20 bg-gradient-to-br from-gray-900 to-primary-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-black mb-3">دو راه برای یادگیری</h2>
          <p class="text-gray-300 text-lg">بعد از خرید، خودت انتخاب می‌کنی</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <!-- Text -->
          <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20 hover:bg-white/15 transition-colors">
            <div class="text-5xl mb-4">📄</div>
            <h3 class="text-2xl font-bold mb-3">محتوای متنی</h3>
            <ul class="space-y-2 text-gray-300">
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> خواندن راحت و سریع</li>
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> امکان جستجو و مرور مجدد</li>
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> مناسب برای محیط‌های ساکت</li>
            </ul>
          </div>

          <!-- Audio -->
          <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20 hover:bg-white/15 transition-colors">
            <div class="text-5xl mb-4">🎧</div>
            <h3 class="text-2xl font-bold mb-3">محتوای صوتی</h3>
            <ul class="space-y-2 text-gray-300">
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> یادگیری در حین رانندگی</li>
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> پخش آنلاین بدون دانلود</li>
              <li class="flex items-center gap-2"><span class="text-green-400">✓</span> صدای واضح و حرفه‌ای استاد</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import CourseCard from '@/Components/CourseCard.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  featured_courses: Array,
  categories: Array,
  stats: Object,
});

function toPersian(n) {
  return String(n).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

const steps = [
  {
    emoji: '🔍',
    title: 'دوره مورد نظر را انتخاب کن',
    desc: 'از میان دوره‌های متنوع، دوره‌ای که به آن نیاز داری را پیدا کن.',
    bg: 'bg-blue-50',
  },
  {
    emoji: '💳',
    title: 'فرمت و پرداخت',
    desc: 'نوع محتوا (متنی یا صوتی) را انتخاب کن و با درگاه امن پرداخت کن.',
    bg: 'bg-green-50',
  },
  {
    emoji: '🚀',
    title: 'شروع یادگیری',
    desc: 'فوری دسترسی بگیر و با هر دستگاهی یاد بگیر.',
    bg: 'bg-purple-50',
  },
];
</script>
