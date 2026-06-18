<template>
  <div class="min-h-screen bg-gray-50" dir="rtl">
    <SiteHeader />

    <!-- Flash Messages -->
    <div v-if="$page.props.flash?.success || $page.props.flash?.error"
         class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error"
           class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.error }}
      </div>
    </div>

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="md:col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center">
                <span class="text-white font-bold text-lg">N</span>
              </div>
              <span class="text-xl font-bold text-white">نکسو کورس</span>
            </div>
            <p class="text-gray-400 leading-relaxed text-sm max-w-xs">
              پلتفرم یادگیری آنلاین با بهترین دوره‌های متنی و صوتی. یادگیری را به شیوه‌ای جدید تجربه کنید.
            </p>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">دسترسی سریع</h4>
            <ul class="space-y-2 text-sm">
              <li><Link :href="route('home')" class="hover:text-white transition-colors">خانه</Link></li>
              <li><Link :href="route('courses.index')" class="hover:text-white transition-colors">دوره‌ها</Link></li>
              <li><Link :href="route('about')" class="hover:text-white transition-colors">درباره ما</Link></li>
              <li><Link :href="route('terms')" class="hover:text-white transition-colors">قوانین و مقررات</Link></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">تماس با ما</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>info@nexocourse.ir</li>
              <li><Link :href="route('contact')" class="hover:text-white transition-colors">فرم تماس</Link></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm text-gray-500">
          تمامی حقوق برای نکسو کورس محفوظ است © {{ currentJalaliYear }}
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { jalaliYear } from '@/Composables/useJalali';
import SiteHeader from '@/Components/SiteHeader.vue';

const currentJalaliYear = jalaliYear();
</script>
