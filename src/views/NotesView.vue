<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header (same as other pages) -->
    <header class="p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <button @click="toggleNotifications" class="text-gray-400 hover:text-white relative">
            <font-awesome-icon :icon="['fas', 'bell']" />
            <span v-if="unreadNotifications > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
              {{ unreadNotifications }}
            </span>
          </button>
          <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-gray-800 rounded-md shadow-lg z-50 max-h-96 overflow-y-auto">
            <div class="p-4 border-b border-gray-700">
              <h3 class="text-lg font-semibold">Notifications</h3>
            </div>
            <div v-if="notifications.length > 0">
              <div v-for="(notification, index) in notifications" :key="notification.id || index"
                   class="p-4 border-b border-gray-700 hover:bg-gray-700 transition-colors"
                   :class="{
                     'bg-gray-700': !notification.read,
                     'cursor-pointer': !notification.read
                   }"
                   @click="markAsRead(notification)">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 mt-1">
                    <div :class="[
                      'w-8 h-8 rounded-full flex items-center justify-center',
                      notification.bgColor
                    ]">
                      <font-awesome-icon :icon="notification.icon" class="text-white text-sm" />
                    </div>
                  </div>
                  <div class="flex-grow">
                    <div class="flex items-start justify-between">
                      <div class="flex-grow">
                        <p class="text-sm font-medium text-white">{{ notification.title }}</p>
                        <p class="text-xs text-gray-300 mt-1">{{ notification.message }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ notification.time }}</p>
                      </div>
                      <div class="flex items-center space-x-2 ml-2">
                        <div v-if="!notification.read" class="flex-shrink-0">
                          <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                        <button v-if="notification.persistent"
                                @click.stop="removeNotification(notification.id)"
                                class="text-gray-400 hover:text-red-400 text-xs">
                          <font-awesome-icon :icon="['fas', 'times']" />
                        </button>
                      </div>
                    </div>

                    <!-- Notification Actions -->
                    <div v-if="notification.actions && notification.actions.length > 0" class="mt-3 flex space-x-2">
                      <button v-for="(action, actionIndex) in notification.actions" :key="actionIndex"
                              @click.stop="executeAction(notification, action)"
                              class="px-2 py-1 text-xs rounded transition-colors"
                              :class="{
                                'bg-blue-600 text-white hover:bg-blue-700': action.action === 'navigate',
                                'bg-gray-600 text-white hover:bg-gray-700': action.action === 'dismiss',
                                'bg-green-600 text-white hover:bg-green-700': action.action === 'callback'
                              }">
                        {{ action.label }}
                      </button>
                    </div>

                    <!-- Priority Indicator -->
                    <div v-if="notification.priority === 'urgent'" class="mt-2 flex items-center">
                      <div class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></div>
                      <span class="text-xs text-red-400 font-medium">URGENT</span>
                    </div>
                    <div v-else-if="notification.priority === 'high'" class="mt-2 flex items-center">
                      <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                      <span class="text-xs text-orange-400 font-medium">HIGH PRIORITY</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="p-4 text-center text-gray-400">
              <font-awesome-icon :icon="['fas', 'bell-slash']" class="text-2xl mb-2" />
              <p>No notifications yet</p>
            </div>
            <div v-if="notifications.length > 0" class="p-3 border-t border-gray-700">
              <button @click="markAllAsRead" class="text-sm text-blue-400 hover:text-blue-300">
                Mark all as read
              </button>
            </div>
          </div>
          <!-- Backdrop to close notifications when clicking outside -->
          <div v-if="showNotifications" class="fixed inset-0 z-0" @click="closeNotifications"></div>
        </div>
        <div class="relative">
          <button @click="toggleUserMenu" class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-600">
              <img
                v-if="user && user.profilePicture"
                :key="user.profilePicture"
                :src="getProfilePictureUrl(user.profilePicture)"
                alt="Profile"
                class="w-full h-full object-cover"
                @error="handleImageError"
                @load="handleImageLoad"
              />
              <div v-else class="w-full h-full bg-gray-600 flex items-center justify-center">
                <font-awesome-icon :icon="['fas', 'user']" class="text-white text-sm" />
              </div>
            </div>
            <span class="text-sm text-gray-300">{{ user?.name || 'User' }}</span>
            <font-awesome-icon :icon="['fas', 'chevron-down']" class="text-xs" />
          </button>
          <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-10">
            <button @click="openProfileModal" class="w-full text-left block px-4 py-2 hover:bg-gray-700">Profile</button>
            <router-link to="/settings" @click="closeUserMenu" class="block px-4 py-2 hover:bg-gray-700">Settings</router-link>
            <button @click="logout" class="w-full text-left block px-4 py-2 hover:bg-gray-700 text-red-400 hover:text-red-300">Logout</button>
          </div>
        </div>
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
          <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold">My Notes</h1>
            <!-- Connection Status Indicator -->
            <div class="flex items-center space-x-2">
              <div
                :class="[
                  'w-2 h-2 rounded-full transition-colors duration-300',
                  isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'
                ]"
              ></div>
              <span
                :class="[
                  'text-sm transition-colors duration-300',
                  isConnected ? 'text-green-400' : 'text-red-400'
                ]"
              >
                {{ isConnected ? 'Live' : 'Offline' }}
              </span>
            </div>
          </div>
          <div class="flex space-x-3">
            <div class="relative">
              <input
                type="text"
                placeholder="Search notes..."
                class="pl-10 pr-4 py-2 bg-gray-800 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <font-awesome-icon :icon="['fas', 'search']" class="absolute left-3 top-3 text-gray-400" />
            </div>
            <button
              @click="refreshNotes()"
              :disabled="loadingNotes"
              class="flex items-center space-x-1 px-3 py-1 bg-gray-700 rounded-md hover:bg-gray-600 transition disabled:opacity-50"
            >
              <font-awesome-icon
                :icon="['fas', loadingNotes ? 'spinner' : 'sync-alt']"
                :class="loadingNotes ? 'animate-spin' : ''"
                class="text-sm"
              />
            </button>
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
              'px-3 py-1 rounded-md transition-colors',
              activeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300'
            ]"
          >
            All Notes
          </button>
          <button
            @click="activeFilter = 'recent'"
            :class="[
              'px-3 py-1 rounded-md transition-colors flex items-center space-x-1',
              activeFilter === 'recent' ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300'
            ]"
          >
            <font-awesome-icon :icon="['fas', 'clock']" class="text-xs" />
            <span>Recent</span>
          </button>
          <button
            @click="activeFilter = 'favorites'"
            :class="[
              'px-3 py-1 rounded-md transition-colors flex items-center space-x-1',
              activeFilter === 'favorites' ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300'
            ]"
          >
            <font-awesome-icon :icon="['fas', 'star']" class="text-xs" />
            <span>Favorites</span>
          </button>
        </div>

        <!-- Error State -->
        <div v-if="notesError" class="bg-red-800 rounded-lg p-6 text-center">
          <div class="mb-4">
            <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-4xl text-red-400" />
          </div>
          <h3 class="text-xl font-medium mb-2">Unable to Load Notes</h3>
          <p class="text-red-300 mb-4">{{ notesError.message || 'An error occurred while loading your notes.' }}</p>
          <div class="flex justify-center space-x-3">
            <button @click="refreshNotes()" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
              <font-awesome-icon :icon="['fas', 'redo']" class="mr-2" />
              Try Again
            </button>
            <button @click="clearError()" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
              Dismiss
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="loadingNotes && notes.length === 0" class="text-center py-8 text-gray-400">
          <font-awesome-icon :icon="['fas', 'spinner']" class="animate-spin text-3xl mb-3" />
          <p class="text-lg">Loading your notes...</p>
        </div>

        <!-- Notes Grid -->
        <div v-else-if="notes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(note, index) in filteredNotes"
            :key="index"
            class="bg-gray-800 rounded-lg overflow-hidden hover:shadow-lg transition cursor-pointer relative"
            @click="viewNote(note.id)"
          >
            <div class="p-4">
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-medium">{{ note.title }}</h3>
                <div class="flex space-x-2">
                  <button @click.stop="toggleFavorite(note.id)" class="text-gray-400 hover:text-yellow-500">
                    <font-awesome-icon :icon="['fas', note.isFavorite ? 'star' : 'star']" :class="note.isFavorite ? 'text-yellow-500' : ''" />
                  </button>
                  <div class="relative">
                    <button @click.stop="showNoteMenu(note.id)" class="text-gray-400 hover:text-white">
                      <font-awesome-icon :icon="['fas', 'ellipsis-v']" />
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div
                      v-if="activeMenu === note.id"
                      class="absolute right-0 top-full mt-1 w-32 bg-gray-700 rounded-md shadow-lg z-10"
                      @click.stop
                    >
                      <button
                        @click="editNote(note.id)"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-600 rounded-t-md flex items-center space-x-2"
                      >
                        <font-awesome-icon :icon="['fas', 'edit']" class="text-xs" />
                        <span>Edit</span>
                      </button>
                      <button
                        @click="duplicateNote(note.id)"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-600 flex items-center space-x-2"
                      >
                        <font-awesome-icon :icon="['fas', 'copy']" class="text-xs" />
                        <span>Duplicate</span>
                      </button>
                      <button
                        @click="confirmDelete(note.id)"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-red-600 text-red-400 hover:text-white rounded-b-md flex items-center space-x-2"
                      >
                        <font-awesome-icon :icon="['fas', 'trash']" class="text-xs" />
                        <span>Delete</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-sm text-gray-400 mb-2">
                {{ activeFilter === 'recent' && (Date.now() - note.timestamp) < 604800000 ? getTimeAgo(note.createdAt) : note.date }}
              </p>
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

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="closeDeleteModal"
    >
      <div
        class="bg-gray-800 rounded-lg p-6 w-96 max-w-90vw"
        @click.stop
      >
        <div class="flex items-center mb-4">
          <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-red-400 text-xl mr-3" />
          <h3 class="text-lg font-medium">Delete Note</h3>
        </div>
        <p class="text-gray-300 mb-6">
          Are you sure you want to delete this note? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="closeDeleteModal"
            class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition"
          >
            Cancel
          </button>
          <button
            @click="deleteNote"
            class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Profile Modal -->
    <div v-if="showProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-96 max-w-90vw">
        <div class="flex items-center mb-4">
          <font-awesome-icon :icon="['fas', 'user']" class="text-blue-400 text-xl mr-3" />
          <h3 class="text-lg font-medium">User Profile</h3>
        </div>

        <!-- Profile Picture Section -->
        <div class="flex flex-col items-center mb-6">
          <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-600 mb-3">
            <img
              v-if="user && user.profilePicture"
              :key="user.profilePicture"
              :src="getProfilePictureUrl(user.profilePicture)"
              alt="Profile"
              class="w-full h-full object-cover"
              @error="handleImageError"
              @load="handleImageLoad"
            />
            <div v-else class="w-full h-full bg-gray-600 flex items-center justify-center">
              <font-awesome-icon :icon="['fas', 'user']" class="text-white text-2xl" />
            </div>
          </div>
          <p class="text-sm text-gray-400 mb-2">Profile picture can be changed in Settings</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Full Name</label>
            <div class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-300">
              {{ user?.firstName && user?.lastName ? `${user.firstName} ${user.lastName}` : (user?.name || 'User') }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <div class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-300">
              {{ user?.email || 'user@example.com' }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Member Since</label>
            <div class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-300">
              {{ user?.memberSince || new Date().toLocaleDateString() }}
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button @click="closeProfileModal" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Backdrop to close menu when clicking outside -->
    <div
      v-if="activeMenu"
      class="fixed inset-0 z-0"
      @click="closeMenu"
    ></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';
import { useUserProfile } from '@/composables/useUserProfile';
import api from '@/services/api';

export default {
  name: 'NotesView',
  setup() {
    // const store = useStore(); //
    const router = useRouter();
    const route = useRoute();

    // Use the shared notifications composable
    const {
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,
      showSuccess,
      showWarning
    } = useNotifications();

    // =====================================
    // SIMPLE DATA MANAGEMENT
    // =====================================

    // Use the user profile composable
    const {
      user: userProfile,
      loading: loadingUser,
      loadUserProfile
    } = useUserProfile();

    // Simple reactive data
    const notesResponse = ref(null);
    const loadingNotes = ref(false);
    const isConnected = ref(true);
    const connectionStatus = ref('connected');
    const lastSync = ref(new Date());

    // =====================================
    // UI STATE
    // =====================================
    const activeFilter = ref('all');
    const activeMenu = ref(null);
    const showDeleteModal = ref(false);
    const noteToDelete = ref(null);
    const ocrText = ref('');
    const notesError = ref('');
    const showUserMenu = ref(false);
    const showProfileModal = ref(false);

    // =====================================
    // COMPUTED PROPERTIES
    // =====================================

    // Process notes data
    const notes = computed(() => {
      if (!notesResponse.value?.data) {
        return [];
      }

      return notesResponse.value.data.map(note => ({
        id: note.id,
        title: note.title || 'Untitled Note',
        original_text: note.original_text || '',
        date: new Date(note.created_at).toLocaleDateString(),
        createdAt: new Date(note.created_at), // Keep original Date object for sorting
        timestamp: new Date(note.created_at).getTime(), // Timestamp for precise sorting
        isFavorite: false, // You can add favorite logic here
        tags: [] // You can add tags logic here
      }));
    });

    // Use user from composable
    const user = userProfile;

    // Get profile picture URL
    const getProfilePictureUrl = (profilePicturePath) => {
      if (!profilePicturePath) return null;
      // Since the backend stores relative paths from public directory, construct the full URL
      // Add timestamp to prevent caching issues
      const timestamp = Date.now();
      // Use relative URL to avoid CORS issues and ensure proper path resolution
      return `/${profilePicturePath}?t=${timestamp}`;
    };

    // Format date as "time ago" for recent notes
    const getTimeAgo = (date) => {
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);

      if (diffInSeconds < 60) return 'Just now';
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
      if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;

      return date.toLocaleDateString();
    };

    // Handle image loading errors
    const handleImageError = (event) => {
      const imgSrc = event.target.src;
      console.error('Profile picture failed to load:', imgSrc);
      console.error('Image naturalWidth:', event.target.naturalWidth);
      console.error('Image naturalHeight:', event.target.naturalHeight);
      console.error('User profile picture path:', user?.profilePicture || 'null');
    };

    // Handle successful image loading
    const handleImageLoad = (event) => {
      const imgSrc = event.target.src;
      console.log('Profile picture loaded successfully:', imgSrc);
      console.log('Image dimensions:', event.target.naturalWidth, 'x', event.target.naturalHeight);
    };

    const filteredNotes = computed(() => {
      if (activeFilter.value === 'all') {
        return notes.value;
      } else if (activeFilter.value === 'recent') {
        // Sort by timestamp (most recent first) for better precision
        return [...notes.value].sort((a, b) => b.timestamp - a.timestamp);
      } else if (activeFilter.value === 'favorites') {
        return notes.value.filter(note => note.isFavorite);
      }
      return notes.value;
    });

    // =====================================
    // SIMPLE API FUNCTIONS
    // =====================================

    // Fetch notes from API
    const fetchNotes = async () => {
      try {
        loadingNotes.value = true;

        // Ensure we have user authentication
        const userData = localStorage.getItem('user');
        if (!userData) {
          console.error('❌ No user data found in localStorage');
          notesError.value = 'Please log in to view your notes.';
          loadingNotes.value = false;
          return;
        }

        console.log('🔍 Fetching notes for user:', userData);

        // Parse user data to get user ID
        let user;
        try {
          user = JSON.parse(userData);
          console.log('✅ Parsed user data:', user);
          console.log('✅ User ID:', user.id);
        } catch (parseError) {
          console.error('❌ Error parsing user data:', parseError);
          notesError.value = 'Invalid user session. Please log in again.';
          loadingNotes.value = false;
          return;
        }

        const response = await api.getNotes();
        console.log('📡 Notes API response:', response.data);

        if (response.data && response.data.success) {
          console.log('✅ Notes loaded successfully:', response.data.data?.length || 0, 'notes');
        } else {
          console.log('❌ Notes API returned error:', response.data?.error);
        }

        if (response.data && response.data.success) {
          notesResponse.value = response.data;
          notesError.value = ''; // Clear any previous errors
          console.log('Notes loaded successfully:', response.data.data?.length || 0, 'notes');
        } else {
          notesError.value = response.data?.error || 'Failed to load notes.';
          console.error('Notes API error:', response.data?.error);
        }
      } catch (error) {
        console.error('Error fetching notes:', error);
        notesError.value = 'Failed to load notes. Please check your connection and try again.';
      } finally {
        loadingNotes.value = false;
      }
    };

    // Fetch user profile using composable
    const fetchUser = async () => {
      await loadUserProfile();
    };

    // Refresh notes
    const refreshNotes = async () => {
      await fetchNotes();
    };

    // Refresh user profile using composable
    const refreshUser = async () => {
      await loadUserProfile();
    };

    // Clear error
    const clearError = () => {
      notesError.value = '';
    };

    // =====================================
    // LIFECYCLE HOOKS
    // =====================================

    onMounted(async () => {
      // Fetch initial data
      await Promise.all([
        fetchNotes(),
        fetchUser()
      ]);
    });

    // Watch for route changes to refresh notes when navigating back to notes page
    watch(() => route.fullPath, async (newPath) => {
      if (newPath.startsWith('/notes')) {
        console.log('Navigated to notes page, checking if refresh needed...');

        // Check if we need to refresh (either from query param or just navigating to notes)
        const shouldRefresh = route.query.refresh === 'true' || newPath === '/notes';

        if (shouldRefresh) {
          console.log('Refreshing notes...');
          await fetchNotes();

          // Show success notification if this was a refresh after saving
          if (route.query.refresh) {
            showSuccess('Notes updated', 'Your notes have been refreshed.');
          }

          // Clean up the refresh query parameter
          if (route.query.refresh) {
            router.replace('/notes');
          }
        }
      }
    });

    const viewNote = (noteId) => {
      closeMenu();
      router.push(`/notes/${noteId}`);
    };

    const toggleFavorite = (noteId) => {
      const note = notes.value.find(n => n.id === noteId);
      if (note) {
        note.isFavorite = !note.isFavorite;
        // In a real app, you would also update this on the server
        updateNoteOnServer(note);
      }
    };

    const showNoteMenu = (noteId) => {
      activeMenu.value = activeMenu.value === noteId ? null : noteId;
    };

    const closeMenu = () => {
      activeMenu.value = null;
    };

    const editNote = (noteId) => {
      closeMenu();
      router.push(`/notes/edit/${noteId}`);
    };

    const duplicateNote = async (noteId) => {
      closeMenu();
      const originalNote = notes.value.find(n => n.id === noteId);
      if (originalNote) {
        const duplicatedNote = {
          title: `${originalNote.title} (Copy)`,
          text: originalNote.original_text
        };

        try {
          await api.createNote(duplicatedNote);
          await fetchNotes(); // Refresh notes list
          showSuccess('Note duplicated', 'The note has been successfully duplicated.');
        } catch (error) {
          console.error('Error duplicating note:', error);
          showWarning('Duplicate failed', 'Failed to duplicate the note. Please try again.');
        }
      }
    };

    const confirmDelete = (noteId) => {
      closeMenu();
      noteToDelete.value = noteId;
      showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      noteToDelete.value = null;
    };

    const deleteNote = async () => {
      if (noteToDelete.value) {
        try {
          await api.deleteNote(noteToDelete.value);
          await fetchNotes(); // Refresh notes list
          showSuccess('Note deleted', 'The note has been successfully deleted.');
        } catch (error) {
          console.error('Error deleting note:', error);
          showWarning('Delete failed', 'Failed to delete the note. Please try again.');
        }
      }
      closeDeleteModal();
    };

    const openCamera = () => {
      console.log('Opening camera...');
      // In a real app, this would open the camera
      router.push('/notes/edit');
    };

    const createNewNote = () => {
      router.push('/notes/edit');
    };

    // Notes are now handled by the real-time system
    // The getNotes function is replaced by the useRealtime composable

    const updateNoteOnServer = async (note) => {
      try {
        await api.updateNote(note.id, note);
      } catch (error) {
        console.error('Error updating note:', error);
      }
    };

    // =====================================
    // USER MENU FUNCTIONS
    // =====================================

    /**
     * Toggle user menu dropdown
     */
    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value;
    };

    /**
     * Close user menu dropdown
     */
    const closeUserMenu = () => {
      showUserMenu.value = false;
    };

    /**
     * Open user profile modal
     */
    const openProfileModal = () => {
      showProfileModal.value = true;
      showUserMenu.value = false; // Close the dropdown menu
    };

    /**
     * Close user profile modal
     */
    const closeProfileModal = () => {
      showProfileModal.value = false;
    };

    /**
     * Logout user and redirect to login
     */
    const logout = async () => {
      try {
        router.push('/login');
      } catch (error) {
        console.error('Error logging out:', error);
      }
    };

    return {
      // Data
      notes,
      user,

      // UI State
      activeFilter,
      activeMenu,
      showDeleteModal,
      noteToDelete,
      filteredNotes,
      notesError,

      // Loading states
      loadingNotes,
      loadingUser,

      // Connection status
      isConnected,
      connectionStatus,
      lastSync,

      // Functions
      viewNote,
      toggleFavorite,
      showNoteMenu,
      closeMenu,
      editNote,
      duplicateNote,
      confirmDelete,
      closeDeleteModal,
      deleteNote,
      openCamera,
      createNewNote,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,
      getTimeAgo,

      // User menu functions
      toggleUserMenu,
      closeUserMenu,
      openProfileModal,
      closeProfileModal,
      logout,

      // Real-time functions
      refreshNotes,
      refreshUser,
      clearError,

      // Notification functions
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,

      // User profile composable functions
      loadUserProfile,

      // OCR data
      ocrText
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