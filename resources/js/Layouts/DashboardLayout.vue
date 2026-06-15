<template>
  <div class="min-h-screen bg-gray-50" dir="rtl">

    <!-- Top Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <Link :href="route('home')" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-600 to-primary-800 rounded-xl flex items-center justify-center">
              <span class="text-white font-bold">N</span>
            </div>
            <span class="font-bold text-gray-900 text-lg">نکسو کورس</span>
          </Link>
          <div class="flex items-center gap-3">
            <span class="text-gray-500 text-sm hidden sm:block">{{ $page.props.auth?.user?.name }}</span>
            <Link :href="route('logout')" method="post" as="button"
                  class="text-sm text-red-500 hover:text-red-700 transition-colors flex items-center gap-1 font-medium">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              خروج
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile Nav -->
    <div class="md:hidden bg-white border-b border-gray-100 px-4 py-3">
      <div class="flex gap-2 overflow-x-auto pb-1">
        <Link v-for="item in navItems" :key="item.route"
              :href="route(item.route)"
              class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-all whitespace-nowrap"
              :class="isActive(item.route) ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-100 text-gray-600'">
          {{ item.label }}
        </Link>
      </div>
    </div>

    <!-- Layout Body -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">

        <!-- Sidebar — right column in RTL -->
        <aside class="hidden md:block md:col-span-1">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
            <!-- User Info -->
            <div class="bg-gradient-to-br from-primary-600 to-primary-800 p-5 text-white text-right">
              <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-3">
                <span class="text-white font-black text-xl">{{ $page.props.auth?.user?.name?.[0] }}</span>
              </div>
              <p class="font-bold text-base leading-tight">{{ $page.props.auth?.user?.name }}</p>
              <p class="text-primary-200 text-sm mt-0.5 font-mono" dir="ltr" style="text-align: right">
                {{ $page.props.auth?.user?.mobile }}
              </p>
            </div>

            <!-- Nav Links -->
            <nav class="p-3 space-y-0.5">
              <Link v-for="item in navItems" :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all w-full text-right"
                    :class="isActive(item.route)
                      ? 'bg-primary-50 text-primary-700 font-semibold'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                <span class="text-base">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
              </Link>

              <div class="border-t border-gray-100 mt-2 pt-2">
                <Link :href="route('courses.index')"
                      class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all w-full text-right text-primary-600 hover:bg-primary-50">
                  <span class="text-base">🔍</span>
                  <span>مشاهده دوره‌ها</span>
                </Link>
              </div>
            </nav>
          </div>
        </aside>

        <!-- Main Content — left 3 columns in RTL -->
        <main class="md:col-span-3 min-w-0">
          <slot />
        </main>

      </div>
    </div>

  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const navItems = [
  { route: 'dashboard.index',      label: 'داشبورد',       icon: '🏠' },
  { route: 'dashboard.my-courses', label: 'دوره‌های من',   icon: '📚' },
  { route: 'dashboard.orders',     label: 'سفارشات',       icon: '🧾' },
  { route: 'dashboard.profile',    label: 'پروفایل',       icon: '👤' },
];

function isActive(routeName) {
  try {
    const current = page.url;
    const target  = route(routeName);
    const targetPath = new URL(target, window.location.origin).pathname;
    return current === targetPath || current.startsWith(targetPath + '/');
  } catch {
    return false;
  }
}
</script>
