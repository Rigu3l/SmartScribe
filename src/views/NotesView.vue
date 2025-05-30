<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header (same as other pages) -->
    <header class="p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-4">
        <button class="text-gray-400 hover:text-white">
          <font-awesome-icon :icon="['fas', 'bell']" />
        </button>
        <div class="w-8 h-8 bg-gray-600 rounded-full"></div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex flex-grow">
      <!-- Sidebar (same as other pages) -->
      <aside class="w-64 bg-gray-800 p-4">
        <nav>
          <ul class="space-y-2">
            <li>
              <router-link to="/dashboard" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
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

      <!-- Notes Main Content -->
      <main class="flex-grow p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">My Notes</h1>
          <div class="flex space-x-3">
            <div class="relative">
              <input
                type="text"
                placeholder="Search notes..."
                class="pl-10 pr-4 py-2 bg-gray-800 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <font-awesome-icon :icon="['fas', 'search']" class="absolute left-3 top-3 text-gray-400" />
            </div>
            <button @click="openCamera" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
              <font-awesome-icon :icon="['fas', 'camera']" class="mr-2" /> Scan New
            </button>
          </div>
        </div>

        <!-- Notes Filter -->
        <div class="flex mb-6 space-x-2">
          <button
            @click="activeFilter = 'all'"
            :class="[
              'px-3 py-1 rounded-md',
              activeFilter === 'all' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'
            ]"
          >
            All Notes
          </button>
          <button
            @click="activeFilter = 'recent'"
            :class="[
              'px-3 py-1 rounded-md',
              activeFilter === 'recent' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'
            ]"
          >
            Recent
          </button>
          <button
            @click="activeFilter = 'favorites'"
            :class="[
              'px-3 py-1 rounded-md',
              activeFilter === 'favorites' ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'
            ]"
          >
            Favorites
          </button>
        </div>

        <!-- Notes Grid -->
        <div v-if="notes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(note, index) in filteredNotes"
            :key="index"
            class="bg-gray-800 rounded-lg overflow-hidden hover:shadow-lg transition cursor-pointer"
            @click="viewNote(note.id)"
          >
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-medium">{{ note.title }}</h3>
                <div class="flex space-x-2">
                  <button @click.stop="toggleFavorite(note.id)" class="text-gray-400 hover:text-yellow-500">
                    <font-awesome-icon :icon="['fas', note.isFavorite ? 'star' : 'star']" :class="note.isFavorite ? 'text-yellow-500' : ''" />
                  </button>
                  <button @click.stop="showNoteMenu(note.id)" class="text-gray-400 hover:text-white">
                    <font-awesome-icon :icon="['fas', 'ellipsis-v']" />
                  </button>
                </div>
              </div>
              <p class="text-sm text-gray-400 mb-2">{{ note.date }}</p>
              <p class="text-sm text-gray-300 line-clamp-3 mb-3">{{ note.original_text }}</p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="(tag, tagIndex) in note.tags"
                  :key="tagIndex"
                  class="px-2 py-1 bg-gray-700 rounded-full text-xs"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-gray-800 rounded-lg p-8 text-center">
          <div class="mb-4">
            <font-awesome-icon :icon="['fas', 'book']" class="text-4xl text-gray-400" />
          </div>
          <h3 class="text-xl font-medium mb-2">No Notes Yet</h3>
          <p class="text-gray-400 mb-4">Start by scanning your study materials or creating a new note.</p>
          <div class="flex justify-center space-x-4">
            <button @click="openCamera" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
              <font-awesome-icon :icon="['fas', 'camera']" class="mr-2" /> Scan Notes
            </button>
            <button @click="createNewNote" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
              <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" /> Create New
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
// import { useStore } from 'vuex'; //
import { useRouter } from 'vue-router';

export default {
  name: 'NotesView',
  setup() {
    // const store = useStore(); //
    const router = useRouter();

    const notes = ref([
      {
        id: 1,
        title: 'Biology Notes - Chapter 5',
        date: 'May 14, 2025',
        original_text: 'Cells are the fundamental units of life, discovered by Robert Hooke in 1665. They are microscopic structures containing cytoplasm, proteins, and nucleic acids enclosed within a membrane.',
        tags: ['Biology', 'Cell', 'Chapter 5'],
        isFavorite: true
      },
      {
        id: 2,
        title: 'History - World War II',
        date: 'May 10, 2025',
        original_text: 'World War II was a global conflict that lasted from 1939 to 1945, involving many nations including all great powers, organized into two opposing alliances: the Allies and the Axis.',
        tags: ['History', 'WWII'],
        isFavorite: false
      },
      {
        id: 3,
        title: 'Mathematics - Calculus',
        date: 'May 5, 2025',
        original_text: 'Calculus is the mathematical study of continuous change. The two major branches of calculus are differential calculus and integral calculus.',
        tags: ['Math', 'Calculus'],
        isFavorite: false
      }
    ]);

    const activeFilter = ref('all');

    const filteredNotes = computed(() => {
      if (activeFilter.value === 'all') {
        return notes.value;
      } else if (activeFilter.value === 'recent') {
        return [...notes.value].sort((a, b) => new Date(b.date) - new Date(a.date));
      } else if (activeFilter.value === 'favorites') {
        return notes.value.filter(note => note.isFavorite);
      }
      return notes.value;
    });

    onMounted(async () => {
      try {
        // In a real app, we would fetch notes from the store
        // await store.dispatch('notes/fetchNotes');
        // notes.value = store.getters['notes/getNotes'];
        getNotes();
      } catch (error) {
        console.error('Error loading notes:', error);
      }
    });

    const viewNote = (noteId) => {
      router.push(`/notes/${noteId}`);
    };

    const toggleFavorite = (noteId) => {
      const note = notes.value.find(n => n.id === noteId);
      if (note) {
        note.isFavorite = !note.isFavorite;
      }
    };

    const showNoteMenu = (noteId) => {
      console.log('Show menu for note:', noteId);
      // In a real app, this would show a dropdown menu
    };

    const openCamera = () => {
      console.log('Opening camera...');
      // In a real app, this would open the camera
      router.push('/notes/edit');
    };

    const createNewNote = () => {
      router.push('/notes/edit');
    };

    const getNotes = async () => {
      const response = await fetch('http://localhost:3000/api/notes');

      if (response.ok) {
        let { data } = await response.json();
        notes.value = data;
      }
    }

    return {
      notes,
      activeFilter,
      filteredNotes,
      viewNote,
      toggleFavorite,
      showNoteMenu,
      openCamera,
      createNewNote
    };
  }
}
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  display: box;
  display: -moz-box;
  display: -ms-box;
  display: -o-box;
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Standard property */
  -webkit-box-orient: vertical;
  box-orient: vertical;
  overflow: hidden;
}
</style>