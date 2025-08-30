<!-- src/App.vue -->
<template>
  <div :class="globalThemeClasses">
    <router-view />
  </div>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'App',
  setup() {
    const store = useStore();

    // Global theme classes applied to entire app
    const globalThemeClasses = computed(() => {
      try {
        // Check if store and getters are available
        if (!store || !store.getters) {
          console.warn('Store not available, using default theme');
          return 'bg-gray-900 text-white';
        }

        const themeClasses = store.getters['app/getThemeClasses'];
        return themeClasses?.main || 'bg-gray-900 text-white';
      } catch (error) {
        console.error('Error getting global theme classes:', error);
        return 'bg-gray-900 text-white';
      }
    });

    onMounted(() => {
      // Ensure theme is applied on mount
      const currentTheme = store.getters['app/getTheme'];
      store.dispatch('app/applyTheme', currentTheme);
    });

    return {
      globalThemeClasses
    };
  }
}
</script>