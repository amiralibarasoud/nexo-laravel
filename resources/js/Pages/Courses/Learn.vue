<template>
  <div class="min-h-screen bg-gray-900 text-white" dir="rtl">
    <!-- Top Bar -->
    <header class="bg-gray-800 border-b border-gray-700 h-14 flex items-center px-3 sm:px-4 gap-2 sm:gap-3 relative z-30">
      <Link :href="route('courses.show', course.slug)" class="text-gray-400 hover:text-white transition-colors flex-shrink-0 p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </Link>

      <!-- Mobile hamburger -->
      <button
        type="button"
        class="md:hidden flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-all"
        @click="openSyllabus"
        aria-label="باز کردن سرفصل‌ها"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
        </svg>
      </button>

      <h1 class="font-bold text-sm flex-1 truncate min-w-0">{{ course.title }}</h1>

      <div v-if="enrollment.can_access_text && enrollment.can_access_audio" class="flex bg-gray-700 rounded-lg p-1 gap-1 flex-shrink-0">
        <button
          type="button"
          @click="activeType = 'text'"
          class="px-2.5 sm:px-3 py-1 rounded-md text-xs font-medium transition-all"
          :class="activeType === 'text' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'"
        >
          متن
        </button>
        <button
          type="button"
          @click="activeType = 'audio'"
          class="px-2.5 sm:px-3 py-1 rounded-md text-xs font-medium transition-all"
          :class="activeType === 'audio' ? 'bg-white text-gray-900' : 'text-gray-400 hover:text-white'"
        >
          صوت
        </button>
      </div>
    </header>

    <div class="flex h-[calc(100vh-3.5rem)]">
      <!-- Desktop sidebar -->
      <aside class="hidden md:block w-80 bg-gray-800 border-l border-gray-700 overflow-y-auto flex-shrink-0">
        <div class="p-4">
          <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wide">سرفصل دوره</h3>
          <div class="space-y-3">
            <div v-for="section in course.sections" :key="'desk-' + section.id">
              <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1.5 px-2">{{ section.title }}</div>
              <ul class="space-y-0.5">
                <li v-for="lesson in section.lessons" :key="'desk-l-' + lesson.id">
                  <button
                    type="button"
                    @click="selectLesson(lesson)"
                    class="w-full text-right px-3 py-2.5 rounded-lg text-sm transition-all flex items-center justify-between gap-2"
                    :class="activeLesson?.id === lesson.id
                      ? 'bg-primary-600 text-white'
                      : 'text-gray-400 hover:bg-gray-700 hover:text-white'"
                  >
                    <span class="truncate flex-1">{{ lesson.title }}</span>
                    <div class="flex items-center gap-1 flex-shrink-0">
                      <span v-if="isCompleted(lesson)" class="text-green-400 text-xs">✓</span>
                      <span v-if="lesson.has_audio && (activeType === 'audio' || !enrollment.can_access_text)" class="text-gray-500 text-xs">{{ lesson.duration }}</span>
                    </div>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main -->
      <main class="flex-1 overflow-y-auto min-w-0 w-full">
        <div v-if="!activeLesson" class="flex items-center justify-center h-full text-center px-6">
          <div>
            <div class="text-6xl mb-4">📚</div>
            <h2 class="text-xl font-bold mb-2">یک جلسه انتخاب کنید</h2>
            <p class="text-gray-400 text-sm md:hidden mb-5">با دکمه منو بالا، سرفصل را باز کنید و جلسه را انتخاب کنید.</p>
            <p class="text-gray-400 text-sm hidden md:block">از فهرست سمت راست، جلسه‌ای را برای شروع انتخاب کنید.</p>
            <button
              type="button"
              class="md:hidden inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-sm font-medium transition-all"
              @click="openSyllabus"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
              </svg>
              باز کردن سرفصل‌ها
            </button>
          </div>
        </div>

        <div v-else class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <button
                type="button"
                class="md:hidden mb-3 inline-flex items-center gap-1.5 text-xs text-primary-300 hover:text-white transition-colors"
                @click="openSyllabus"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
                </svg>
                تغییر سرفصل
              </button>
              <h2 class="text-lg sm:text-xl font-bold leading-snug">{{ activeLesson.title }}</h2>
            </div>
            <button
              type="button"
              @click="markCompleted"
              class="flex-shrink-0 flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all"
              :class="isCompleted(activeLesson)
                ? 'bg-green-600/20 text-green-400 border border-green-600/30'
                : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              {{ isCompleted(activeLesson) ? 'تکمیل شده' : 'علامت تکمیل' }}
            </button>
          </div>

          <div v-if="activeType === 'audio' && activeLesson.has_audio" class="bg-gray-800 rounded-2xl p-4 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="min-w-0">
                <p class="font-semibold truncate">{{ activeLesson.title }}</p>
                <p class="text-gray-400 text-sm">{{ activeLesson.duration }}</p>
              </div>
            </div>
            <audio
              ref="audioEl"
              class="w-full"
              controlsList="nodownload nofullscreen noremoteplayback"
              controls
              preload="metadata"
              @timeupdate="onTimeUpdate"
              @ended="onAudioEnded"
              @error="onAudioError"
            >
              <source v-if="audioStreamUrl" :src="audioStreamUrl" :type="audioMimeType || undefined" />
            </audio>
            <p v-if="audioError" class="text-xs text-red-400 mt-2 text-center">{{ audioError }}</p>
            <p v-else class="text-xs text-gray-500 mt-2 text-center">🔒 دانلود فایل صوتی امکان‌پذیر نیست</p>
          </div>

          <div v-if="activeType === 'text' && activeLesson.has_text && textContent" class="bg-white text-gray-900 rounded-2xl p-5 sm:p-8">
            <div class="prose max-w-none leading-loose text-base" v-html="textContent"></div>
          </div>

          <div v-if="contentLoading" class="flex items-center justify-center py-16">
            <div class="text-center">
              <svg class="animate-spin w-8 h-8 text-primary-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <p class="text-gray-400 text-sm">در حال بارگذاری محتوا...</p>
            </div>
          </div>

          <div class="flex justify-between items-center pt-4 border-t border-gray-700 gap-2">
            <button
              type="button"
              @click="prevLesson"
              :disabled="!hasPrevLesson"
              class="flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl text-sm bg-gray-700 hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              جلسه قبل
            </button>
            <button
              type="button"
              @click="nextLesson"
              :disabled="!hasNextLesson"
              class="flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl text-sm bg-primary-600 hover:bg-primary-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
            >
              جلسه بعد
              <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Mobile drawer -->
    <Teleport to="body">
      <div v-show="sidebarOpen" class="md:hidden fixed inset-0 z-[100]" dir="rtl">
        <div class="absolute inset-0 bg-black/70" @click="closeSyllabus" />
        <aside
          class="absolute top-0 bottom-0 right-0 w-[min(20rem,88vw)] bg-gray-800 shadow-2xl flex flex-col text-white"
          role="dialog"
          aria-modal="true"
          aria-label="سرفصل دوره"
        >
          <div class="h-14 flex items-center justify-between px-4 border-b border-gray-700 flex-shrink-0">
            <h3 class="text-sm font-semibold">سرفصل دوره</h3>
            <button type="button" class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-700" @click="closeSyllabus" aria-label="بستن">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="flex-1 overflow-y-auto p-4 overscroll-contain">
            <div class="space-y-3">
              <div v-for="section in course.sections" :key="'mob-' + section.id">
                <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1.5 px-2">{{ section.title }}</div>
                <ul class="space-y-0.5">
                  <li v-for="lesson in section.lessons" :key="'mob-l-' + lesson.id">
                    <button
                      type="button"
                      @click="selectLesson(lesson)"
                      class="w-full text-right px-3 py-2.5 rounded-lg text-sm transition-all flex items-center justify-between gap-2"
                      :class="activeLesson?.id === lesson.id
                        ? 'bg-primary-600 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
                    >
                      <span class="truncate flex-1">{{ lesson.title }}</span>
                      <div class="flex items-center gap-1 flex-shrink-0">
                        <span v-if="isCompleted(lesson)" class="text-green-400 text-xs">✓</span>
                        <span v-if="lesson.has_audio && (activeType === 'audio' || !enrollment.can_access_text)" class="text-gray-500 text-xs">{{ lesson.duration }}</span>
                      </div>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  course: Object,
  enrollment: Object,
});

const activeLesson = ref(null);
const activeType = ref(props.enrollment.can_access_text ? 'text' : 'audio');
const textContent = ref('');
const audioStreamUrl = ref('');
const audioMimeType = ref('');
const audioError = ref('');
const contentLoading = ref(false);
const completedLessons = ref({});
const audioEl = ref(null);
const sidebarOpen = ref(false);
let saveProgressTimer = null;

const allLessons = computed(() =>
  props.course.sections.flatMap(s => s.lessons)
);

function openSyllabus() {
  sidebarOpen.value = true;
  document.body.style.overflow = 'hidden';
}

function closeSyllabus() {
  sidebarOpen.value = false;
  document.body.style.overflow = '';
}

onBeforeUnmount(() => {
  document.body.style.overflow = '';
});

function isCompleted(lesson) {
  const key = `${lesson.id}_${activeType.value}`;
  return completedLessons.value[key] ?? (
    activeType.value === 'text' ? lesson.text_completed : lesson.audio_completed
  );
}

async function selectLesson(lesson) {
  activeLesson.value = lesson;
  closeSyllabus();
  textContent.value = '';
  audioStreamUrl.value = '';
  audioMimeType.value = '';
  audioError.value = '';
  contentLoading.value = true;

  try {
    if (activeType.value === 'text' && lesson.has_text) {
      const res = await axios.get(route('lessons.content', { course: props.course.id, lessonId: lesson.id }), {
        params: { type: 'text' }
      });
      textContent.value = res.data.content;
    } else if (activeType.value === 'audio' && lesson.has_audio) {
      const res = await axios.get(route('lessons.content', { course: props.course.id, lessonId: lesson.id }), {
        params: { type: 'audio' }
      });
      audioStreamUrl.value = res.data.stream_url
        || route('lessons.audio.stream', { course: props.course.id, lessonId: lesson.id });
      audioMimeType.value = res.data.mime_type || '';
      await nextTick();
      if (audioEl.value) {
        audioEl.value.load();
        if (lesson.audio_position > 0) {
          const resume = () => {
            try { audioEl.value.currentTime = lesson.audio_position; } catch (_) {}
            audioEl.value.removeEventListener('loadedmetadata', resume);
          };
          audioEl.value.addEventListener('loadedmetadata', resume);
        }
      }
    }
  } catch (e) {
    console.error(e);
    if (activeType.value === 'audio') {
      audioError.value = 'بارگذاری فایل صوتی انجام نشد. دوباره تلاش کنید.';
    }
  } finally {
    contentLoading.value = false;
  }
}

function onAudioError() {
  audioError.value = 'پخش این فایل در مرورگر ممکن نیست. فرمت‌هایی مثل MP3، M4A، AAC، WAV، OGG و FLAC معمولاً بهتر کار می‌کنند.';
}

async function markCompleted() {
  if (!activeLesson.value) return;
  const newState = !isCompleted(activeLesson.value);
  completedLessons.value[`${activeLesson.value.id}_${activeType.value}`] = newState;

  await axios.post(route('courses.progress', props.course.id), {
    lesson_id: activeLesson.value.id,
    type: activeType.value,
    is_completed: newState,
  });
}

function onTimeUpdate() {
  if (!audioEl.value || !activeLesson.value) return;
  clearTimeout(saveProgressTimer);
  saveProgressTimer = setTimeout(() => {
    axios.post(route('courses.progress', props.course.id), {
      lesson_id: activeLesson.value.id,
      type: 'audio',
      is_completed: false,
      audio_position: Math.floor(audioEl.value.currentTime),
    });
  }, 5000);
}

function onAudioEnded() {
  markCompleted();
}

const currentIndex = computed(() =>
  activeLesson.value ? allLessons.value.findIndex(l => l.id === activeLesson.value.id) : -1
);
const hasPrevLesson = computed(() => currentIndex.value > 0);
const hasNextLesson = computed(() => currentIndex.value < allLessons.value.length - 1);

function prevLesson() {
  if (hasPrevLesson.value) selectLesson(allLessons.value[currentIndex.value - 1]);
}
function nextLesson() {
  if (hasNextLesson.value) selectLesson(allLessons.value[currentIndex.value + 1]);
}

watch(activeType, () => {
  if (activeLesson.value) selectLesson(activeLesson.value);
});
</script>
