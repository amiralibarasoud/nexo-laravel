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
                  class="text-sm text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              خروج
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex gap-8">
        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 hidden md:block">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- User Info -->
            <div class="bg-gradient-to-br from-primary-600 to-primary-800 p-6 text-white">
              <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-3">
                <span class="text-white font-black text-xl">{{ $page.props.auth?.user?.name?.[0] }}</span>
              </div>
              <p class="font-bold text-lg leading-tight">{{ $page.props.auth?.user?.name }}</p>
              <p class="text-primary-200 text-sm mt-0.5">{{ $page.props.auth?.user?.mobile }}</p>
            </div>

            <!-- Nav Items -->
            <nav class="p-3">
              <NavItem :href="route('dashboard.index')" icon="home">
                داشبورد
              </NavItem>
              <NavItem :href="route('dashboard.my-courses')" icon="book">
                دوره‌های من
              </NavItem>
              <NavItem :href="route('dashboard.orders')" icon="receipt">
                سفارشات
              </NavItem>
              <NavItem :href="route('dashboard.profile')" icon="user">
                پروفایل
              </NavItem>
              <div class="border-t border-gray-100 mt-2 pt-2">
                <NavItem :href="route('courses.index')" icon="search">
                  مشاهده دوره‌ها
                </NavItem>
              </div>
            </nav>
          </div>
        </aside>

        <!-- Mobile Nav -->
        <div class="md:hidden w-full mb-4 overflow-x-auto">
          <div class="flex gap-2 pb-2">
            <MobileNavItem :href="route('dashboard.index')">داشبورد</MobileNavItem>
            <MobileNavItem :href="route('dashboard.my-courses')">دوره‌های من</MobileNavItem>
            <MobileNavItem :href="route('dashboard.orders')">سفارشات</MobileNavItem>
            <MobileNavItem :href="route('dashboard.profile')">پروفایل</MobileNavItem>
          </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
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

// NavItem component
const NavItem = {
  props: ['href', 'icon'],
  template: `
    <Link :href="href"
      class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all mb-0.5"
      :class="isActive ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
      <slot />
    </Link>
  `,
  setup(props) {
    const isActive = computed(() => {
      if (typeof window === 'undefined') return false;
      return window.location.pathname === new URL(props.href, window.location.origin).pathname;
    });
    return { isActive };
  },
  components: { Link },
};

const MobileNavItem = {
  props: ['href'],
  template: `
    <Link :href="href"
      class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-all whitespace-nowrap"
      :class="isActive ? 'bg-primary-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200'">
      <slot />
    </Link>
  `,
  setup(props) {
    const isActive = computed(() => {
      if (typeof window === 'undefined') return false;
      return window.location.pathname === new URL(props.href, window.location.origin).pathname;
    });
    return { isActive };
  },
  components: { Link },
};
</script>
