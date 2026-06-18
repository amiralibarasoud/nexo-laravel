<template>
  <MainLayout>
    <Head :title="about.seo_title || 'درباره ما'" />

    <section
      v-if="about.hero?.enabled"
      class="relative overflow-hidden bg-gradient-to-br from-primary-900 to-primary-700 py-20 text-white text-center"
    >
      <img
        v-if="about.hero.image"
        :src="about.hero.image"
        alt=""
        class="absolute inset-0 w-full h-full object-cover opacity-20"
      />
      <div class="relative max-w-3xl mx-auto px-4">
        <h1 class="text-4xl font-black mb-4">{{ about.hero.title }}</h1>
        <p v-if="about.hero.description" class="text-primary-200 text-lg leading-relaxed">
          {{ about.hero.description }}
        </p>
      </div>
    </section>

    <section v-if="about.mission?.enabled" class="py-20">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-black text-gray-900 mb-5">{{ about.mission.title }}</h2>
            <p v-if="about.mission.paragraph1" class="text-gray-600 leading-loose mb-4">
              {{ about.mission.paragraph1 }}
            </p>
            <p v-if="about.mission.paragraph2" class="text-gray-600 leading-loose">
              {{ about.mission.paragraph2 }}
            </p>
          </div>
          <div v-if="about.mission.image" class="rounded-2xl overflow-hidden shadow-lg">
            <img :src="about.mission.image" alt="" class="w-full h-full object-cover"/>
          </div>
          <div v-else-if="about.mission.stats?.length" class="grid grid-cols-2 gap-4">
            <div v-for="(stat, index) in about.mission.stats" :key="`about-stat-${index}`" class="card p-6 text-center">
              <div class="text-4xl font-black text-primary-600 mb-1">{{ stat.value }}</div>
              <div class="text-gray-500 text-sm">{{ stat.label }}</div>
            </div>
          </div>
        </div>
        <div v-if="about.mission.image && about.mission.stats?.length" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-12">
          <div v-for="(stat, index) in about.mission.stats" :key="`about-stat-below-${index}`" class="card p-6 text-center">
            <div class="text-4xl font-black text-primary-600 mb-1">{{ stat.value }}</div>
            <div class="text-gray-500 text-sm">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="about.values?.enabled" class="py-16 bg-gray-50">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-black text-gray-900">{{ about.values.title }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="(val, index) in about.values.items"
            :key="`about-value-${index}`"
            class="card p-6 text-center hover:shadow-lg transition-shadow"
          >
            <div class="text-4xl mb-3">{{ val.icon }}</div>
            <h3 class="font-bold text-gray-900 mb-2">{{ val.title }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">{{ val.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section v-if="about.cta?.enabled" class="py-20 bg-primary-900 text-white text-center">
      <div class="max-w-2xl mx-auto px-4">
        <h2 class="text-3xl font-black mb-4">{{ about.cta.title }}</h2>
        <p v-if="about.cta.subtitle" class="text-primary-200 mb-8">{{ about.cta.subtitle }}</p>
        <Link :href="resolveRoute(about.cta.button_route)" class="btn-nexo text-base px-8 py-4 rounded-2xl">
          {{ about.cta.button_text }}
        </Link>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const about = computed(() => page.props.theme?.about ?? {});

function resolveRoute(routeName) {
  try {
    return route(routeName || 'courses.index');
  } catch {
    return route('courses.index');
  }
}
</script>
