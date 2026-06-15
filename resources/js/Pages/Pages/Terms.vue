<template>
  <MainLayout>
    <Head title="قوانین و مقررات" />

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h1 class="text-3xl font-black text-gray-900 mb-2">قوانین و مقررات</h1>
      <p class="text-gray-400 text-sm mb-10">آخرین بروزرسانی: خرداد ۱۴۰۴</p>

      <div class="card p-8 space-y-8 prose max-w-none">
        <section v-for="section in sections" :key="section.title">
          <h2 class="text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
            <span>{{ section.icon }}</span> {{ section.title }}
          </h2>
          <p class="text-gray-600 leading-loose text-sm">{{ section.content }}</p>
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';

const sections = [
  {
    icon: '📋',
    title: 'پذیرش قوانین',
    content: 'با استفاده از خدمات نکسو کورس، شما قوانین و مقررات این صفحه را می‌پذیرید. لطفاً این قوانین را به دقت مطالعه کنید.',
  },
  {
    icon: '🔐',
    title: 'حساب کاربری',
    content: 'هر کاربر مجاز به داشتن یک حساب کاربری است. اطلاعات حساب کاربری شما محرمانه است و مسئولیت حفاظت از آن با شماست.',
  },
  {
    icon: '💳',
    title: 'خرید و پرداخت',
    content: 'تمام پرداخت‌ها از طریق درگاه‌های معتبر بانکی انجام می‌شود. قیمت‌ها به تومان بوده و پس از تأیید پرداخت، دسترسی به دوره فوری است.',
  },
  {
    icon: '🎧',
    title: 'محتوای صوتی',
    content: 'محتوای صوتی دوره‌ها فقط به صورت آنلاین پخش می‌شود و امکان دانلود آن وجود ندارد. این محتوا دارای حق مالکیت معنوی است.',
  },
  {
    icon: '🔄',
    title: 'سیاست بازگشت وجه',
    content: 'در صورت بروز مشکل فنی از سمت ما، امکان بازگشت وجه وجود دارد. درخواست بازگشت وجه باید حداکثر ۷۲ ساعت پس از خرید ارسال شود.',
  },
  {
    icon: '📝',
    title: 'مالکیت محتوا',
    content: 'تمام محتوای آموزشی ارائه‌شده در این پلتفرم دارای حق مالکیت معنوی است. کپی‌برداری، توزیع یا فروش مجدد محتوا بدون اجازه کتبی ممنوع است.',
  },
  {
    icon: '🔒',
    title: 'حریم خصوصی',
    content: 'اطلاعات شخصی شما (شماره موبایل، نام) نزد ما محفوظ است و هرگز به اشخاص ثالث فروخته یا منتقل نمی‌شود.',
  },
];
</script>
