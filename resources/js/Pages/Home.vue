<template>
  <MainLayout>
    <!-- Hero Section -->
    <section
      v-if="home.hero?.enabled"
      class="relative overflow-hidden bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700"
    >
      <img
        v-if="home.hero.image"
        :src="home.hero.image"
        alt=""
        class="absolute inset-0 w-full h-full object-cover opacity-20"
      />

      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 right-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-primary-300 rounded-full blur-3xl"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <div
            v-if="home.hero.badge"
            class="inline-flex items-center gap-2 bg-white/10 text-white/90 rounded-full px-4 py-2 text-sm mb-6 backdrop-blur-sm border border-white/20"
          >
            {{ home.hero.badge }}
          </div>

          <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
            {{ home.hero.title_before }}
            <span class="text-yellow-300">{{ home.hero.highlight1 }}</span>
            {{ home.hero.title_middle }}
            <span class="text-green-300">{{ home.hero.highlight2 }}</span>؟
            <br class="hidden md:block">
            <span class="text-3xl md:text-5xl font-bold text-white/80">{{ home.hero.title_suffix }}</span>
          </h1>

          <p
            v-if="home.hero.description"
            class="text-primary-100 text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed"
          >
            {{ home.hero.description }}
          </p>

          <div v-if="home.hero.cta_text" class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              :href="resolveRoute(home.hero.cta_route)"
              class="btn-nexo text-base px-8 py-4 rounded-2xl shadow-xl hover:shadow-2xl"
            >
              {{ home.hero.cta_text }}
              <svg class="w-5 h-5 mr-2 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </Link>
          </div>

          <div
            v-if="home.stats?.enabled && home.stats.items?.length"
            class="grid gap-6 max-w-lg mx-auto mt-16"
            :class="statsGridClass"
          >
            <div
              v-for="(stat, index) in home.stats.items"
              :key="`stat-${index}`"
              class="text-center"
              :class="statBorderClass(index)"
            >
              <div class="text-3xl font-black text-white">
                {{ toPersian(resolveStatValue(stat)) }}{{ stat.suffix || '' }}
              </div>
              <div class="text-primary-200 text-sm mt-1">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section v-if="home.steps?.enabled" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="section-title">{{ home.steps.title }}</h2>
          <p v-if="home.steps.subtitle" class="section-subtitle">{{ home.steps.subtitle }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(step, index) in home.steps.items" :key="index" class="text-center group">
            <div
              class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-3xl shadow-lg transition-transform group-hover:-translate-y-1"
              :class="step.bg || 'bg-blue-50'"
            >
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
    <section v-if="home.categories?.enabled && categories.length" class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
          <h2 class="section-title">{{ home.categories.title }}</h2>
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
    <section v-if="home.featured?.enabled && featured_courses.length" class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="section-title">{{ home.featured.title }}</h2>
            <p v-if="home.featured.subtitle" class="section-subtitle">{{ home.featured.subtitle }}</p>
          </div>
          <Link :href="route('courses.index')" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-1 text-sm">
            {{ home.featured.link_text }}
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

    <!-- Latest Blog Posts -->
    <section v-if="home.blog?.enabled && latest_posts && latest_posts.length" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="section-title">{{ home.blog.title }}</h2>
            <p v-if="home.blog.subtitle" class="section-subtitle">{{ home.blog.subtitle }}</p>
          </div>
          <Link :href="route('blog.index')" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-1 text-sm">
            {{ home.blog.link_text }}
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link
            v-for="post in latest_posts"
            :key="post.id"
            :href="route('blog.show', post.slug)"
            class="card-hover group block"
          >
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img
                v-if="post.cover_image"
                :src="`/storage/${post.cover_image}`"
                :alt="post.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
              </div>
            </div>
            <div class="p-5">
              <div v-if="post.category" class="mb-2">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white" :style="`background: ${post.category.color}`">
                  {{ post.category.name }}
                </span>
              </div>
              <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors leading-relaxed">
                {{ post.title }}
              </h3>
              <p v-if="post.excerpt" class="text-gray-500 text-sm line-clamp-2 leading-relaxed mb-3">
                {{ post.excerpt }}
              </p>
              <p class="text-gray-400 text-xs">{{ post.published_at }}</p>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Content Types Section -->
    <section v-if="home.content_types?.enabled" class="py-20 bg-gradient-to-br from-gray-900 to-primary-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-black mb-3">{{ home.content_types.title }}</h2>
          <p v-if="home.content_types.subtitle" class="text-gray-300 text-lg">{{ home.content_types.subtitle }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <div
            v-for="(card, index) in home.content_types.cards"
            :key="`content-card-${index}`"
            class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20 hover:bg-white/15 transition-colors"
          >
            <div class="text-5xl mb-4">{{ card.emoji }}</div>
            <h3 class="text-2xl font-bold mb-3">{{ card.title }}</h3>
            <ul class="space-y-2 text-gray-300">
              <li v-for="(item, itemIndex) in card.items" :key="itemIndex" class="flex items-center gap-2">
                <span class="text-green-400">✓</span> {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import CourseCard from '@/Components/CourseCard.vue';

const page = usePage();
const home = computed(() => page.props.theme?.homepage ?? {});

const props = defineProps({
  featured_courses: Array,
  categories: Array,
  stats: Object,
  latest_posts: Array,
});

const statsGridClass = computed(() => {
  const count = home.value.stats?.items?.length || 3;
  if (count <= 2) return 'grid-cols-2';
  if (count === 4) return 'grid-cols-2 sm:grid-cols-4';
  return 'grid-cols-3';
});

function statBorderClass(index) {
  const count = home.value.stats?.items?.length || 3;
  if (count === 3 && index === 1) return 'border-x border-white/20';
  return '';
}

function resolveRoute(routeName) {
  try {
    return route(routeName || 'courses.index');
  } catch {
    return route('courses.index');
  }
}

function resolveStatValue(stat) {
  if (stat.type === 'dynamic_courses') return props.stats?.courses_count ?? 0;
  if (stat.type === 'dynamic_students') return props.stats?.students_count ?? 0;
  return stat.value ?? '';
}

function toPersian(n) {
  return String(n).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[d]);
}
</script>
