<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-4">
        <button class="text-gray-400 hover:text-white">
          <i class="fas fa-bell"></i>
        </button>
        <div class="relative">
          <button @click="toggleUserMenu" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gray-600 rounded-full"></div>
            <span>{{ user.name }}</span>
            <i class="fas fa-chevron-down text-xs"></i>
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
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <i class="fas fa-book"></i>
                <span>My Notes</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
              </a>
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
              <i class="fas fa-camera text-4xl text-gray-400"></i>
            </div>
            <p class="mb-4 text-gray-400">Take a picture or upload your notes</p>
            <div class="flex justify-center space-x-4">
              <button @click="openCamera" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <i class="fas fa-camera mr-2"></i> Take Photo
              </button>
              <label class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition cursor-pointer">
                <i class="fas fa-upload mr-2"></i> Upload File
                <input type="file" class="hidden" @change="handleFileUpload" accept="image/*" />
              </label>
            </div>
          </div>
        </div>
        
        <!-- Recent Notes Section -->
        <div class="bg-gray-800 rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Notes</h2>
            <a href="#" class="text-blue-400 hover:underline">View All</a>
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
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-gray-400 hover:text-white">
                    <i class="fas fa-trash"></i>
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

<script setup>
import { ref } from 'vue';

const showUserMenu = ref(false);
const user = ref({
  name: 'Marc Angelo',
  email: 'marc@example.com'
});

const recentNotes = ref([
  {
    title: 'Biology Notes - Chapter 5',
    date: 'May 14, 2025',
    summary: 'Cell structure and function. The cell is the basic unit of life. All living organisms are composed of cells, and all cells come from pre-existing cells.',
    tags: ['Biology', 'Chapter 5', 'Cells']
  },
  {
    title: 'History - World War II',
    date: 'May 12, 2025',
    summary: 'World War II was a global war that lasted from 1939 to 1945. It involved the vast majority of the world\'s countries forming two opposing military alliances.',
    tags: ['History', 'WWII']
  }
]);

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
};

const logout = () => {
  console.log('Logging out...');
  // In a real app, you would implement logout logic here
  // Then redirect to login page
};

const openCamera = () => {
  console.log('Opening camera...');
  // In a real app, you would implement camera access here
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    console.log('File uploaded:', file.name);
    // In a real app, you would process the image with OCR here
    processImage(file);
  }
};

const processImage = (file) => {
  // This is where you would implement OCR processing
  // For now, we'll just simulate it
  console.log('Processing image with OCR...');
  setTimeout(() => {
    // Navigate to the note editor with the extracted text
    console.log('OCR processing complete, navigating to editor...');
  }, 2000);
};
</script>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>