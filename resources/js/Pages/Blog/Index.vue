<template>
  <MainLayout>
    <Head title="بلاگ">
      <meta name="description" content="آخرین مقالات و آموزش‌های نکسو کورس">
      <meta property="og:title" content="بلاگ | نکسو کورس">
      <meta property="og:description" content="آخرین مقالات و آموزش‌های نکسو کورس">
    </Head>

    <!-- Header -->
    <div class="bg-gradient-to-br from-gray-900 to-primary-900 py-16 text-white text-center">
      <div class="max-w-3xl mx-auto px-4">
        <h1 class="text-4xl font-black mb-3">بلاگ نکسو کورس</h1>
        <p class="text-gray-300 text-lg">آخرین مقالات، آموزش‌ها و اخبار</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- Sidebar -->
        <aside class="lg:col-span-1 order-2 lg:order-1">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-24">
            <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wide">دسته‌بندی‌ها</h3>
            <ul class="space-y-1">
              <li>
                <Link :href="route('blog.index')"
                      class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all"
                      :class="!active_category ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'">
                  <span>همه مقالات</span>
                  <span class="text-xs text-gray-400">{{ toPersian(posts.total) }}</span>
                </Link>
              </li>
              <li v-for="cat in categories" :key="cat.id">
                <Link :href="route('blog.category', cat.slug)"
                      class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all"
                      :class="active_category?.id === cat.id ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'">
                  <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" :style="`background: ${cat.color}`"></span>
                    {{ cat.name }}
                  </span>
                  <span class="text-xs text-gray-400">{{ toPersian(cat.published_posts_count) }}</span>
                </Link>
              </li>
            </ul>
          </div>
        </aside>

        <!-- Posts Grid -->
        <main class="lg:col-span-3 order-1 lg:order-2">
          <!-- Active Category Badge -->
          <div v-if="active_category" class="flex items-center gap-2 mb-6">
            <span class="text-gray-500 text-sm">دسته‌بندی:</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold text-white"
                  :style="`background: ${active_category.color}`">
              {{ active_category.name }}
            </span>
          </div>

          <div v-if="posts.data.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <Link v-for="post in posts.data" :key="post.id"
                  :href="route('blog.show', post.slug)"
                  class="card-hover group block">
              <!-- Cover -->
              <div class="aspect-video bg-gray-100 overflow-hidden">
                <img v-if="post.cover_image" :src="`/storage/${post.cover_image}`"
                     :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"/>
                <div v-else class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                  <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                  </svg>
                </div>
              </div>

              <!-- Body -->
              <div class="p-5">
                <!-- Category -->
                <div v-if="post.category" class="mb-2">
                  <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                        :style="`background: ${post.category.color}`">
                    {{ post.category.name }}
                  </span>
                </div>

                <h2 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors leading-relaxed">
                  {{ post.title }}
                </h2>
                <p v-if="post.excerpt" class="text-gray-500 text-sm line-clamp-2 mb-4 leading-relaxed">
                  {{ post.excerpt }}
                </p>

                <div class="flex items-center justify-between text-xs text-gray-400 pt-3 border-t border-gray-100">
                  <span>{{ post.author_name }}</span>
                  <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      {{ toPersian(post.views) }}
                    </span>
                    <span>{{ post.published_at }}</span>
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <!-- Empty -->
          <div v-else class="text-center py-20">
            <div class="text-6xl mb-4">📝</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">مقاله‌ای یافت نشد</h3>
            <p class="text-gray-500">به زودی مقالات جدید اضافه می‌شوند.</p>
          </div>

          <!-- Pagination -->
          <div v-if="posts.last_page > 1" class="flex justify-center gap-2 mt-10">
            <Link v-for="page in posts.links" :key="page.label"
                  :href="page.url || '#'"
                  class="px-4 py-2 rounded-xl text-sm transition-all"
                  :class="page.active ? 'bg-primary-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                  v-html="page.label">
            </Link>
          </div>
        </main>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, Head } from '@inertiajs/vue3';

defineProps({
  posts: Object,
  categories: Array,
  active_category: Object,
});

function toPersian(n) {
  return String(n ?? 0).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
}
</script>
