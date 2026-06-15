<template>
  <MainLayout>
    <!-- Page Header -->
    <div class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">دوره‌های آموزشی</h1>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 items-center">
          <!-- Search -->
          <div class="relative flex-1 min-w-48">
            <input
              :value="filters.search"
              @input="updateFilter('search', $event.target.value)"
              type="text"
              placeholder="جستجو در دوره‌ها..."
              class="input-field pr-10 text-sm"
            />
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>

          <!-- Category Filter -->
          <select
            :value="filters.category"
            @change="updateFilter('category', $event.target.value)"
            class="input-field w-auto text-sm"
          >
            <option value="">همه دسته‌بندی‌ها</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
          </select>

          <!-- Level Filter -->
          <select
            :value="filters.level"
            @change="updateFilter('level', $event.target.value)"
            class="input-field w-auto text-sm"
          >
            <option value="">همه سطوح</option>
            <option value="beginner">مقدماتی</option>
            <option value="intermediate">متوسط</option>
            <option value="advanced">پیشرفته</option>
          </select>

          <button v-if="hasFilters" @click="clearFilters" class="text-gray-500 hover:text-red-500 text-sm flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            حذف فیلترها
          </button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Count -->
      <p class="text-gray-500 text-sm mb-6">
        {{ toPersian(courses.total) }} دوره پیدا شد
      </p>

      <!-- Grid -->
      <div v-if="courses.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <CourseCard v-for="course in courses.data" :key="course.id" :course="course" />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-20">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">دوره‌ای یافت نشد</h3>
        <p class="text-gray-500">فیلترهای خود را تغییر دهید</p>
        <button @click="clearFilters" class="btn-primary mt-6">نمایش همه دوره‌ها</button>
      </div>

      <!-- Pagination -->
      <div v-if="courses.last_page > 1" class="flex justify-center gap-2 mt-10">
        <button
          v-for="page in paginationPages"
          :key="page"
          @click="goToPage(page)"
          class="w-10 h-10 rounded-xl font-medium text-sm transition-all"
          :class="page === courses.current_page
            ? 'bg-primary-600 text-white shadow-md'
            : page === '...'
              ? 'cursor-default text-gray-400'
              : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
        >
          {{ page === '...' ? '...' : toPersian(page) }}
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import CourseCard from '@/Components/CourseCard.vue';

const props = defineProps({
  courses: Object,
  categories: Array,
  filters: Object,
});

const hasFilters = computed(() =>
  Object.values(props.filters).some(v => v)
);

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}

let searchTimeout = null;
function updateFilter(key, value) {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('courses.index'), {
      ...props.filters,
      [key]: value,
      page: 1,
    }, { preserveState: true, preserveScroll: true });
  }, key === 'search' ? 400 : 0);
}

function clearFilters() {
  router.get(route('courses.index'), {}, { preserveState: false });
}

function goToPage(page) {
  if (page === '...') return;
  router.get(route('courses.index'), { ...props.filters, page }, { preserveState: true });
}

const paginationPages = computed(() => {
  const total = props.courses.last_page;
  const current = props.courses.current_page;
  const pages = [];

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  pages.push(1);
  if (current > 3) pages.push('...');
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    pages.push(i);
  }
  if (current < total - 2) pages.push('...');
  pages.push(total);

  return pages;
});
</script>
