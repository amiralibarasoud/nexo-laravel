<template>
  <MainLayout>
    <Head :title="terms.seo_title || 'قوانین و مقررات'">
      <meta
        v-if="terms.subtitle"
        name="description"
        :content="terms.subtitle"
      >
    </Head>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h1 class="text-3xl font-black text-gray-900 mb-2">
        {{ terms.title || 'قوانین و مقررات' }}
      </h1>
      <p v-if="terms.subtitle" class="text-gray-400 text-sm mb-10">
        {{ terms.subtitle }}
      </p>

      <div
        v-if="terms.content"
        class="card p-8 prose prose-sm max-w-none text-gray-600 leading-loose
               prose-headings:text-gray-900 prose-headings:font-bold
               prose-h2:text-xl prose-h2:mt-8 prose-h2:mb-3
               prose-h3:text-lg prose-p:leading-loose"
        v-html="terms.content"
      />
      <div v-else class="card p-10 text-center text-gray-500">
        هنوز متنی برای قوانین و مقررات ثبت نشده است.
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const page = usePage();
const terms = computed(() => page.props.theme?.terms ?? {});
</script>
