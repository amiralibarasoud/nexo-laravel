<template>
  <MainLayout>
    <Head title="درباره ما" />

    <!-- Hero -->
    <section class="bg-gradient-to-br from-primary-900 to-primary-700 py-20 text-white text-center">
      <div class="max-w-3xl mx-auto px-4">
        <h1 class="text-4xl font-black mb-4">درباره نکسو کورس</h1>
        <p class="text-primary-200 text-lg leading-relaxed">
          ما یک پلتفرم آموزشی ایرانی هستیم که هدفمان ارائه آموزش‌های کاربردی به زبان فارسی است.
        </p>
      </div>
    </section>

    <!-- Mission -->
    <section class="py-20">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-black text-gray-900 mb-5">مأموریت ما</h2>
            <p class="text-gray-600 leading-loose mb-4">
              نکسو کورس با هدف دسترسی آسان‌تر به آموزش‌های باکیفیت فارسی تأسیس شد. ما بر این باوریم که هر فردی باید بتواند با هر بودجه و در هر شرایطی، به بهترین آموزش‌ها دسترسی داشته باشد.
            </p>
            <p class="text-gray-600 leading-loose">
              نوآوری اصلی ما در ارائه محتوا به دو فرمت متنی و صوتی است — شما انتخاب می‌کنید که چطور یاد بگیرید.
            </p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div v-for="stat in statsItems" :key="stat.label" class="card p-6 text-center">
              <div class="text-4xl font-black text-primary-600 mb-1">{{ stat.value }}</div>
              <div class="text-gray-500 text-sm">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-black text-gray-900">ارزش‌های ما</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="val in values" :key="val.title" class="card p-6 text-center hover:shadow-lg transition-shadow">
            <div class="text-4xl mb-3">{{ val.icon }}</div>
            <h3 class="font-bold text-gray-900 mb-2">{{ val.title }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">{{ val.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-primary-900 text-white text-center">
      <div class="max-w-2xl mx-auto px-4">
        <h2 class="text-3xl font-black mb-4">آماده شروع هستی؟</h2>
        <p class="text-primary-200 mb-8">همین الان اولین دوره‌ات رو شروع کن.</p>
        <Link :href="route('courses.index')" class="btn-nexo text-base px-8 py-4 rounded-2xl">
          مشاهده دوره‌ها
        </Link>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Link, Head } from '@inertiajs/vue3';

const statsItems = [
  { value: '۱۰+', label: 'دوره آموزشی' },
  { value: '۵۰۰+', label: 'دانش‌آموز' },
  { value: '۲', label: 'فرمت محتوا' },
  { value: '۱۰۰٪', label: 'فارسی' },
];

const values = [
  { icon: '🎯', title: 'کاربردی', desc: 'محتوای ما بر اساس نیاز واقعی بازار کار طراحی شده.' },
  { icon: '🌍', title: 'فارسی‌زبان', desc: 'تمام محتوا به زبان فارسی و متناسب با فرهنگ ایران.' },
  { icon: '💡', title: 'نوآورانه', desc: 'اولین پلتفرم با محتوای همزمان متنی و صوتی.' },
  { icon: '🤝', title: 'حمایت‌گر', desc: 'پشتیبانی کامل در طول مسیر یادگیری شما.' },
];
</script>
