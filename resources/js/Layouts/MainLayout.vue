<template>
  <div class="min-h-screen bg-gray-50" dir="rtl">
    <SiteHeader />

    <!-- Flash Messages -->
    <div v-if="$page.props.flash?.success || $page.props.flash?.error"
         class="max-w-7xl mx-auto px-4 pt-4">
      <div v-if="$page.props.flash?.success"
           class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error"
           class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-4 flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.error }}
      </div>
    </div>

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <SiteFooter />
  </div>
</template>

<script setup>
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
</script>
