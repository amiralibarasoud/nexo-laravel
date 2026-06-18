<template>
  <header>
    <!-- Announcement Bar -->
    <div
      v-if="header.announcement?.enabled && header.announcement.text"
      class="bg-primary-600 text-white text-center text-sm py-2 px-4"
    >
      <a
        v-if="header.announcement.link"
        :href="header.announcement.link"
        class="hover:underline"
      >
        {{ header.announcement.text }}
      </a>
      <span v-else>{{ header.announcement.text }}</span>
    </div>

    <nav
      class="bg-white shadow-sm border-b border-gray-100 z-50"
      :class="header.sticky ? 'sticky top-0' : ''"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="flex items-center h-16 gap-4"
          :class="navRowClass"
        >
          <!-- Logo -->
          <div :class="logoWrapperClass">
            <Link :href="route('home')" class="flex items-center gap-2 group">
              <img
                v-if="header.logo"
                :src="header.logo"
                :alt="header.site_name"
                class="h-9 w-auto max-w-[140px] object-contain"
              />
              <div
                v-else
                class="w-9 h-9 bg-gradient-to-br from-primary-600 to-primary-800 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow"
              >
                <span class="text-white font-bold text-lg">{{ header.logo_letter || 'N' }}</span>
              </div>
              <span
                v-if="header.show_text_logo"
                class="text-xl font-bold text-gray-900"
              >
                {{ header.site_name }}
                <span v-if="header.site_name_highlight" class="text-primary-600">
                  {{ header.site_name_highlight }}
                </span>
              </span>
            </Link>
          </div>

          <!-- Desktop Nav -->
          <div
            v-if="visibleNavLinks.length"
            class="hidden md:flex items-center gap-6"
            :class="navLinksClass"
          >
            <Link
              v-for="(item, index) in visibleNavLinks"
              :key="`${item.label}-${index}`"
              :href="resolveHref(item)"
              class="text-gray-600 hover:text-primary-600 font-medium transition-colors text-sm"
            >
              {{ item.label }}
            </Link>
          </div>

          <!-- Widgets after nav (desktop) -->
          <div
            v-if="widgetsAfterNav.length"
            class="hidden md:flex items-center gap-3"
          >
            <template v-for="(widget, index) in widgetsAfterNav" :key="`nav-widget-${index}`">
              <component :is="widgetTag(widget)" v-bind="widgetProps(widget)">
                {{ widget.content }}
              </component>
            </template>
          </div>

          <!-- Auth + widgets before auth -->
          <div class="flex items-center gap-3" :class="authWrapperClass">
            <div
              v-if="widgetsBeforeAuth.length"
              class="hidden sm:flex items-center gap-3"
            >
              <template v-for="(widget, index) in widgetsBeforeAuth" :key="`auth-widget-${index}`">
                <component :is="widgetTag(widget)" v-bind="widgetProps(widget)">
                  {{ widget.content }}
                </component>
              </template>
            </div>

            <template v-if="$page.props.auth?.user">
              <div class="relative" ref="userMenuRef">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors"
                >
                  <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 font-bold text-sm">{{ $page.props.auth.user.name[0] }}</span>
                  </div>
                  <span class="text-gray-700 font-medium text-sm hidden sm:block">{{ $page.props.auth.user.name }}</span>
                  <svg class="w-4 h-4 text-gray-400 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>

                <Transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="opacity-0 scale-95"
                  enter-to-class="opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="opacity-100 scale-100"
                  leave-to-class="opacity-0 scale-95"
                >
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
                {{ header.login_text || 'ورود / ثبت‌نام' }}
              </Link>
            </template>

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
      <Transition
        enter-active-class="transition ease-out duration-150"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white">
          <div class="px-4 py-3 space-y-1">
            <Link
              v-for="(item, index) in visibleNavLinks"
              :key="`mobile-${item.label}-${index}`"
              :href="resolveHref(item)"
              @click="mobileMenuOpen = false"
              class="block py-2 text-gray-700 font-medium"
            >
              {{ item.label }}
            </Link>
            <template v-if="$page.props.auth?.user">
              <div class="border-t border-gray-100 pt-2 mt-2 space-y-1">
                <Link :href="route('dashboard.index')" @click="mobileMenuOpen = false" class="block py-2 text-gray-700">داشبورد</Link>
                <Link :href="route('dashboard.my-courses')" @click="mobileMenuOpen = false" class="block py-2 text-gray-700">دوره‌های من</Link>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const header = computed(() => page.props.theme?.header ?? {});

const userMenuOpen = ref(false);
const mobileMenuOpen = ref(false);
const userMenuRef = ref(null);

const visibleNavLinks = computed(() =>
  (header.value.nav_links ?? []).filter((item) => item.visible !== false)
);

const visibleWidgets = computed(() =>
  (header.value.widgets ?? []).filter((item) => item.visible !== false)
);

const widgetsBeforeAuth = computed(() =>
  visibleWidgets.value.filter((item) => (item.position ?? 'before_auth') === 'before_auth')
);

const widgetsAfterNav = computed(() =>
  visibleWidgets.value.filter((item) => item.position === 'after_nav')
);

const navRowClass = computed(() => {
  if (header.value.logo_position === 'center') {
    return 'justify-between md:grid md:grid-cols-3';
  }
  return 'justify-between';
});

const logoWrapperClass = computed(() => {
  if (header.value.logo_position === 'center') {
    return 'md:col-start-2 md:flex md:justify-center';
  }
  if (header.value.logo_position === 'end') {
    return 'order-last ms-auto';
  }
  return '';
});

const navLinksClass = computed(() => {
  if (header.value.logo_position === 'center') {
    return 'md:col-start-1 md:justify-start';
  }
  return '';
});

const authWrapperClass = computed(() => {
  if (header.value.logo_position === 'center') {
    return 'md:col-start-3 md:justify-end';
  }
  return '';
});

function resolveHref(item) {
  if (item.route_name) {
    try {
      return route(item.route_name);
    } catch {
      return item.url || '#';
    }
  }
  return item.url || '#';
}

function widgetTag(widget) {
  if (widget.type === 'link' || (widget.type === 'badge' && widget.link)) {
    return 'a';
  }
  return 'span';
}

function widgetProps(widget) {
  const baseClass =
    widget.type === 'badge'
      ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700'
      : widget.type === 'link'
        ? 'text-sm text-primary-600 hover:text-primary-800 font-medium'
        : 'text-sm text-gray-600';

  if (widget.type === 'link' || (widget.type === 'badge' && widget.link)) {
    return {
      href: widget.link,
      class: baseClass,
    };
  }

  return { class: baseClass };
}

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false;
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
