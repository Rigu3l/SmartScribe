<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-4">
        <button class="text-gray-400 hover:text-white">
          <font-awesome-icon :icon="['fas', 'bell']" />
        </button>
        <div class="relative">
          <button @click="toggleUserMenu" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gray-600 rounded-full"></div>
            <span>{{ user.name }}</span>
            <font-awesome-icon :icon="['fas', 'chevron-down']" class="text-xs" />
          </button>
          <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-10">
            <a href="#" class="block px-4 py-2 hover:bg-gray-700">Profile</a>
            <a href="#" class="block px-4 py-2 hover:bg-gray-700">Settings</a>
            <a href="#" class="block px-4 py-2 hover:bg-gray-700" @click="logout">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex flex-grow">
      <!-- Sidebar -->
      <aside class="w-64 bg-gray-800 p-4">
        <nav>
          <ul class="space-y-2">
            <li>
              <router-link to="/dashboard" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link to="/progress" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link to="/settings" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Dashboard -->
      <main class="flex-grow p-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
        
        <!-- Scan New Notes Section -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">Scan New Notes</h2>
          <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center">
            <div class="mb-4">
              <font-awesome-icon :icon="['fas', 'camera']" class="text-4xl text-gray-400" />
            </div>
            <p class="mb-4 text-gray-400">Take a picture or upload your notes</p>
            <div class="flex justify-center space-x-4">
              <button @click="openCamera" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'camera']" class="mr-2" /> Take Photo
              </button>
              <label class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition cursor-pointer">
                <font-awesome-icon :icon="['fas', 'upload']" class="mr-2" /> Upload File
                <input type="file" class="hidden" @change="handleFileUpload" accept="image/*" />
              </label>
            </div>
          </div>
        </div>
        
        <!-- Recent Notes Section -->
        <div class="bg-gray-800 rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Notes</h2>
            <router-link to="/notes" class="text-blue-400 hover:underline">View All</router-link>
          </div>
          
          <div v-if="recentNotes.length > 0">
            <div v-for="(note, index) in recentNotes" :key="index" class="bg-gray-700 rounded-lg p-4 mb-4">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-medium">{{ note.title }}</h3>
                  <p class="text-sm text-gray-400">{{ note.date }}</p>
                </div>
                <div class="flex space-x-2">
                  <button class="text-gray-400 hover:text-white">
                    <font-awesome-icon :icon="['fas', 'edit']" />
                  </button>
                  <button class="text-gray-400 hover:text-white">
                    <font-awesome-icon :icon="['fas', 'trash']" />
                  </button>
                </div>
              </div>
              <p class="mt-2 text-sm text-gray-300 line-clamp-2">{{ note.summary }}</p>
              <div class="mt-3 flex space-x-2">
                <span v-for="(tag, tagIndex) in note.tags" :key="tagIndex" class="px-2 py-1 bg-gray-600 rounded-full text-xs">
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-400">
            <p>No notes yet. Start by scanning your first note!</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'DashboardView',
  setup() {
    const store = useStore();
    const router = useRouter();
    
    const showUserMenu = ref(false);
    const user = ref({
      name: 'User',
      email: 'user@example.com'
    });
    const recentNotes = ref([]);

    onMounted(async () => {
      try {
        // Get user info
        const userInfo = store.getters['auth/getUser'];
        if (userInfo) {
          user.value = userInfo;
        }
        
        // Get recent notes
        await store.dispatch('notes/fetchNotes');
        recentNotes.value = store.getters['notes/getRecentNotes'];
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      }
    });

    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value;
    };

    const logout = async () => {
      try {
        await store.dispatch('auth/logout');
        router.push('/login');
      } catch (error) {
        console.error('Error logging out:', error);
      }
    };

    const openCamera = () => {
      console.log('Opening camera...');
      // In a real app, you would implement camera access here
    };

    const handleFileUpload = async (event) => {
      const file = event.target.files[0];
      if (file) {
        try {
          await store.dispatch('notes/processImage', file);
          router.push('/notes/edit');
        } catch (error) {
          console.error('Error processing image:', error);
        }
      }
    };

    return {
      showUserMenu,
      user,
      recentNotes,
      toggleUserMenu,
      logout,
      openCamera,
      handleFileUpload
    };
  }
}
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>