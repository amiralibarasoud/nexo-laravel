<template>
  <div
    ref="container"
    class="footer-trust-seal inline-flex items-center justify-center"
  />
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
  html: {
    type: String,
    default: '',
  },
});

const container = ref(null);

function renderTrustSeal() {
  const el = container.value;
  const html = (props.html ?? '').trim();

  if (!el) {
    return;
  }

  el.innerHTML = '';

  if (!html) {
    return;
  }

  el.innerHTML = html;

  el.querySelectorAll('script').forEach((oldScript) => {
    const newScript = document.createElement('script');
    Array.from(oldScript.attributes).forEach((attr) => {
      newScript.setAttribute(attr.name, attr.value);
    });
    newScript.textContent = oldScript.textContent;
    oldScript.replaceWith(newScript);
  });
}

onMounted(renderTrustSeal);
watch(() => props.html, renderTrustSeal);
</script>

<style scoped>
.footer-trust-seal :deep(img) {
  display: block;
  max-height: 5.5rem;
  width: auto;
}
</style>
