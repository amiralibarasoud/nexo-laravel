<template>
  <MainLayout>
    <Head :title="post.meta_title">
      <meta name="description" :content="post.meta_description">
      <meta v-if="post.meta_keywords" name="keywords" :content="post.meta_keywords">
      <meta property="og:title" :content="post.meta_title">
      <meta property="og:description" :content="post.meta_description">
      <meta property="og:type" content="article">
      <meta v-if="post.cover_image" property="og:image" :content="`/storage/${post.cover_image}`">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" :content="post.meta_title">
      <meta name="twitter:description" :content="post.meta_description">
      <link rel="canonical" :href="`${$page.props.ziggy?.url}/blog/${post.slug}`">
    </Head>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <Link :href="route('home')" class="hover:text-primary-600 transition-colors">خانه</Link>
        <span>/</span>
        <Link :href="route('blog.index')" class="hover:text-primary-600 transition-colors">بلاگ</Link>
        <span v-if="post.category">/</span>
        <Link v-if="post.category" :href="route('blog.category', post.category.slug)"
              class="hover:text-primary-600 transition-colors">{{ post.category.name }}</Link>
        <span>/</span>
        <span class="text-gray-700 line-clamp-1">{{ post.title }}</span>
      </nav>

      <!-- Article -->
      <article>
        <!-- Category Badge -->
        <div v-if="post.category" class="mb-4">
          <Link :href="route('blog.category', post.category.slug)"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold text-white"
                :style="`background: ${post.category.color}`">
            {{ post.category.name }}
          </Link>
        </div>

        <!-- Title -->
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-5 leading-tight">{{ post.title }}</h1>

        <!-- Meta -->
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-8 pb-6 border-b border-gray-100">
          <span class="flex items-center gap-1.5">
            <div class="w-7 h-7 bg-primary-100 rounded-full flex items-center justify-center">
              <span class="text-primary-700 font-bold text-xs">{{ post.author_name[0] }}</span>
            </div>
            {{ post.author_name }}
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ post.published_at }}
          </span>
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            {{ toPersian(post.views) }} بازدید
          </span>
        </div>

        <!-- Cover Image -->
        <div v-if="post.cover_image" class="mb-8 rounded-2xl overflow-hidden aspect-video shadow-lg">
          <img :src="`/storage/${post.cover_image}`" :alt="post.title" class="w-full h-full object-cover"/>
        </div>

        <!-- Body Content -->
        <div class="prose prose-lg max-w-none text-gray-700 leading-loose" v-html="post.body"></div>

        <!-- Share -->
        <div class="mt-12 pt-6 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
          <p class="text-gray-500 text-sm">این مقاله مفید بود؟ با دوستانت به اشتراک بذار.</p>
          <div class="flex gap-2">
            <a :href="`https://t.me/share/url?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(post.title)}`"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
              <span>📤</span> تلگرام
            </a>
          </div>
        </div>
      </article>

      <!-- Related Posts -->
      <section v-if="related.length" class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">مقالات مرتبط</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <Link v-for="rpost in related" :key="rpost.id"
                :href="route('blog.show', rpost.slug)"
                class="card-hover group block">
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img v-if="rpost.cover_image" :src="`/storage/${rpost.cover_image}`"
                   :alt="rpost.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
              <div v-else class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
              </div>
            </div>
            <div class="p-4">
              <h3 class="font-bold text-gray-900 text-sm line-clamp-2 group-hover:text-primary-600 transition-colors leading-relaxed mb-1">
                {{ rpost.title }}
              </h3>
              <p class="text-xs text-gray-400">{{ rpost.published_at }}</p>
            </div>
          </Link>
        </div>
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  post: Object,
  related: Array,
});

const currentUrl = computed(() => {
  if (typeof window !== 'undefined') return window.location.href;
  return '';
});

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}
</script>
