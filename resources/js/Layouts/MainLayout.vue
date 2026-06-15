<template>
  <div class="min-h-screen bg-gray-50" dir="rtl">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <Link :href="route('home')" class="flex items-center gap-2 group">
            <div class="w-9 h-9 bg-gradient-to-br from-primary-600 to-primary-800 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow">
              <span class="text-white font-bold text-lg">N</span>
            </div>
            <span class="text-xl font-bold text-gray-900">نکسو <span class="text-primary-600">کورس</span></span>
          </Link>

          <!-- Desktop Nav -->
          <div class="hidden md:flex items-center gap-6">
            <Link :href="route('home')" class="text-gray-600 hover:text-primary-600 font-medium transition-colors text-sm">خانه</Link>
            <Link :href="route('courses.index')" class="text-gray-600 hover:text-primary-600 font-medium transition-colors text-sm">دوره‌ها</Link>
            <Link :href="route('about')" class="text-gray-600 hover:text-primary-600 font-medium transition-colors text-sm">درباره ما</Link>
            <Link :href="route('contact')" class="text-gray-600 hover:text-primary-600 font-medium transition-colors text-sm">تماس</Link>
          </div>

          <!-- Auth -->
          <div class="flex items-center gap-3">
            <template v-if="$page.props.auth?.user">
              <!-- User Menu -->
              <div class="relative" ref="userMenuRef">
                <button @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
                  <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 font-bold text-sm">{{ $page.props.auth.user.name[0] }}</span>
                  </div>
                  <span class="text-gray-700 font-medium text-sm hidden sm:block">{{ $page.props.auth.user.name }}</span>
                  <svg class="w-4 h-4 text-gray-400 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>

                <!-- Dropdown -->
                <Transition enter-active-class="transition ease-out duration-100" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                  <div v-if="userMenuOpen" class="absolute left-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-50 mb-1">
                      <p class="text-xs text-gray-400">خوش آمدید</p>
                      <p class="font-semibold text-gray-800 text-sm">{{ $page.props.auth.user.name }}</p>
                    </div>
                    <Link :href="route('dashboard.index')" @click="userMenuOpen = false"
                          class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                      داشبورد
                    </Link>
                    <Link :href="route('dashboard.my-courses')" @click="userMenuOpen = false"
                          class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                      دوره‌های من
                    </Link>
                    <Link :href="route('dashboard.orders')" @click="userMenuOpen = false"
                          class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                      سفارشات
                    </Link>
                    <Link :href="route('dashboard.profile')" @click="userMenuOpen = false"
                          class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      پروفایل
                    </Link>
                    <div class="border-t border-gray-100 mt-1 pt-1">
                      <Link :href="route('logout')" method="post" as="button" @click="userMenuOpen = false"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors w-full text-right">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        خروج
                      </Link>
                    </div>
                  </div>
                </Transition>
              </div>
            </template>
            <template v-else>
              <Link :href="route('login')" class="btn-primary text-sm px-5 py-2 rounded-xl">
                ورود / ثبت‌نام
              </Link>
            </template>

            <!-- Mobile Menu -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white">
          <div class="px-4 py-3 space-y-1">
            <Link :href="route('home')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700 font-medium">خانه</Link>
            <Link :href="route('courses.index')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700 font-medium">دوره‌ها</Link>
            <Link :href="route('about')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700 font-medium">درباره ما</Link>
            <Link :href="route('contact')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700 font-medium">تماس</Link>
            <template v-if="$page.props.auth?.user">
              <div class="border-t border-gray-100 pt-2 mt-2 space-y-1">
                <Link :href="route('dashboard.index')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700">داشبورد</Link>
                <Link :href="route('dashboard.my-courses')" @click="mobileMenuOpen=false" class="block py-2 text-gray-700">دوره‌های من</Link>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </nav>

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
          تمامی حقوق برای نکسو کورس محفوظ است © {{ new Date().getFullYear() }}
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const userMenuOpen = ref(false);
const mobileMenuOpen = ref(false);
const userMenuRef = ref(null);

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false;
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
