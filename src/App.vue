<template>
  <div id="app">
    <!-- Global loading indicator -->
    <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 p-6 rounded-lg shadow-lg flex items-center">
        <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-blue-500 text-2xl mr-3" />
        <span class="text-white">Loading...</span>
      </div>
    </div>
    
    <!-- Global error alert -->
    <div v-if="globalError" class="fixed top-4 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-md">
      <div class="flex items-start">
        <div class="flex-grow">
          <h3 class="font-bold mb-1">Error</h3>
          <p>{{ globalError }}</p>
        </div>
        <button @click="clearError" class="ml-4 text-white">
          <font-awesome-icon :icon="['fas', 'times']" />
        </button>
      </div>
    </div>
    
    <!-- Main content -->
    <router-view />
  </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'App',
  setup() {
    const store = useStore();
    
    const isLoading = computed(() => store.getters.isAppLoading);
    const globalError = computed(() => store.getters.getGlobalError);
    
    const clearError = () => {
      store.dispatch('clearGlobalError');
    };
    
    return {
      isLoading,
      globalError,
      clearError
    };
  }
}
</script>