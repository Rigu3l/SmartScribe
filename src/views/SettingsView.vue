<template>
  <Header @open-profile-modal="openProfileModal">

      <!-- Settings Main Content -->
      <main class="flex-1 p-6 transition-all duration-300 ease-in-out" :class="themeClasses.mainContent">
        <h1 class="font-bold mb-6 transition-all duration-300" :class="[themeClasses.text, fontSizeClasses.heading]">Settings</h1>

        <!-- Message Display -->
        <div v-if="message" :class="[
          'mb-6 p-4 rounded-lg',
          messageType === 'success' ? 'bg-green-800 text-green-200' : 'bg-red-800 text-red-200'
        ]">
          <font-awesome-icon :icon="messageType === 'success' ? ['fas', 'check-circle'] : ['fas', 'triangle-exclamation']" class="mr-2" />
          {{ message }}
        </div>
        
        <!-- Settings Tabs -->
        <div class="flex mb-6 transition-colors duration-300" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'border-gray-700' : 'border-gray-200'">
          <button
            @click="activeTab = 'account'"
            :class="[
              'px-4 py-2 font-medium',
              activeTab === 'account' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-400 hover:text-white'
            ]"
          >
            Account
          </button>
          <button
            @click="activeTab = 'appearance'"
            :class="[
              'px-4 py-2 font-medium',
              activeTab === 'appearance' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-400 hover:text-white'
            ]"
          >
            Appearance
          </button>
          <button
            @click="activeTab = 'notifications'"
            :class="[
              'px-4 py-2 font-medium',
              activeTab === 'notifications' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-400 hover:text-white'
            ]"
          >
            Notifications
          </button>
        </div>
        
        <!-- Account Settings -->
        <div v-if="activeTab === 'account'" class="space-y-6">
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Profile Picture</h2>
            <div class="flex flex-col items-center mb-6">
              <div class="relative w-24 h-24 rounded-full overflow-hidden mb-3" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-600' : 'bg-gray-300'">
                <img
                  v-if="user && user.profilePicture"
                  :key="user.profilePicture"
                  :src="getProfilePictureUrl(user.profilePicture)"
                  alt="Profile"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  @load="handleImageLoad"
                />
                <div v-else class="w-full h-full flex items-center justify-center" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-600' : 'bg-gray-300'">
                  <font-awesome-icon :icon="['fas', 'user']" class="text-2xl" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'text-white' : 'text-gray-700'" />
                </div>
                <!-- Upload overlay -->
                <label class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 hover:opacity-100 transition cursor-pointer">
                  <font-awesome-icon :icon="['fas', 'camera']" class="text-white" />
                  <input
                    type="file"
                    accept="image/*,.pdf,application/pdf"
                    @change="handleProfilePictureUpload"
                    class="hidden"
                  />
                </label>
              </div>
              <p class="text-sm mb-2" :class="themeClasses.secondaryText">Click to change profile picture</p>
              <p class="text-xs" :class="themeClasses.secondaryText">JPEG, PNG, GIF, WebP, PDF (max 5MB)</p>
            </div>
          </div>

          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Profile Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">First Name</label>
                <input
                  :value="user?.firstName || ''"
                  @input="user ? (user.firstName = $event.target.value) : null"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                  :disabled="!user"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Last Name</label>
                <input
                  :value="user?.lastName || ''"
                  @input="user ? (user.lastName = $event.target.value) : null"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                  :disabled="!user"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Email Address</label>
                <input
                  :value="user?.email || ''"
                  @input="user ? (user.email = $event.target.value) : null"
                  type="email"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                  :disabled="!user"
                />
              </div>
            </div>
            <div class="mt-4">
              <button @click="saveProfile" :disabled="isLoading" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-50">
                <span v-if="isLoading">
                  <font-awesome-icon :icon="['fas', 'spinner']" spin class="mr-2" />
                  Saving...
                </span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </div>
          
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Change Password</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Current Password</label>
                <input
                  v-model="passwords.current"
                  type="password"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">New Password</label>
                <input
                  v-model="passwords.new"
                  type="password"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Confirm New Password</label>
                <input
                  v-model="passwords.confirm"
                  type="password"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
            </div>
            <div class="mt-4">
              <button @click="updatePassword" :disabled="isLoading" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-50">
                <span v-if="isLoading">
                  <font-awesome-icon :icon="['fas', 'spinner']" spin class="mr-2" />
                  Updating...
                </span>
                <span v-else>Update Password</span>
              </button>
            </div>
          </div>
          
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Danger Zone</h2>
            <p class="mb-4" :class="themeClasses.secondaryText">Once you delete your account, there is no going back. Please be certain.</p>
            <button @click="deleteAccount" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
              <font-awesome-icon :icon="['fas', 'times']" class="mr-2" />
              Delete Account
            </button>
          </div>
        </div>
        
        <!-- Appearance Settings -->
        <div v-if="activeTab === 'appearance'" class="space-y-6">
          <div class="rounded-lg p-6 transition-colors duration-300" :class="themeClasses.card">
            <h2 class="font-semibold mb-4 transition-all duration-300" :class="[themeClasses.text, fontSizeClasses.body]">Theme</h2>
            <div class="flex space-x-4">
              <div
                @click="store.dispatch('app/setTheme', 'dark')"
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2',
                  store.getters['app/getTheme'] === 'dark' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full rounded-lg p-3" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-900' : 'bg-gray-700'">
                    <div class="h-4 w-full rounded mb-2" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-800' : 'bg-gray-700'"></div>
                    <div class="h-4 w-3/4 rounded" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-800' : 'bg-gray-700'"></div>
                    <div class="mt-4 text-xs text-center" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'text-gray-400' : 'text-gray-600'">Dark</div>
                  </div>
              </div>
              <div
                @click="store.dispatch('app/setTheme', 'light')"
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2',
                  store.getters['app/getTheme'] === 'light' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full bg-gray-100 rounded-lg p-3">
                  <div class="h-4 w-full bg-white rounded mb-2"></div>
                  <div class="h-4 w-3/4 bg-white rounded"></div>
                  <div class="mt-4 text-xs text-center text-gray-600">Light</div>
                </div>
              </div>
              <div
                @click="store.dispatch('app/setTheme', 'system')"
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2',
                  store.getters['app/getTheme'] === 'system' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full bg-gradient-to-r from-gray-900 to-gray-100 rounded-lg p-3 flex items-center justify-center">
                  <div class="text-xs text-center">System</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4 transition-all duration-300" :class="[themeClasses.text, fontSizeClasses.body]">Font Size</h2>
            <div class="flex items-center space-x-4">
              <span class="transition-all duration-300" :class="[themeClasses.secondaryText, fontSizeClasses.small]">A</span>
              <input
                :value="store.getters['app/getFontSize']"
                @input="store.dispatch('app/setFontSize', parseInt($event.target.value))"
                type="range"
                min="12"
                max="20"
                class="w-full h-2 rounded-lg appearance-none cursor-pointer transition-colors duration-300"
                :class="themeClasses.button"
              />
              <span class="transition-all duration-300" :class="[themeClasses.text, fontSizeClasses.body]">A</span>
            </div>
            <p class="mt-2 transition-all duration-300" :class="[themeClasses.secondaryText, fontSizeClasses.label]">Current font size: {{ store.getters['app/getFontSize'] }}px</p>
          </div>

        </div>
        
        <!-- Notifications Settings -->
        <div v-if="activeTab === 'notifications'" class="space-y-6">
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Email Notifications</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium" :class="themeClasses.text">Weekly Summary</p>
                  <p class="text-sm" :class="themeClasses.secondaryText">Receive a weekly summary of your study progress</p>
                </div>
                <button
                  @click="settings.notifications.weeklySummary = !settings.notifications.weeklySummary"
                  :class="settings.notifications.weeklySummary
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-600 text-white'"
                  class="px-4 py-2 rounded transition"
                  >
                  {{ settings.notifications.weeklySummary ? 'ON' : 'OFF' }}
                </button>
              </div>
              
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium" :class="themeClasses.text">Study Reminders</p>
                  <p class="text-sm" :class="themeClasses.secondaryText">Receive reminders for your study goals</p>
                </div>
                <button
                  @click="settings.notifications.studyReminders = !settings.notifications.studyReminders"
                  :class="settings.notifications.studyReminders
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-600 text-white'"
                  class="px-4 py-2 rounded transition"
                  >
                  {{ settings.notifications.studyReminders ? 'ON' : 'OFF' }}
                </button>
              </div>
              
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium" :class="themeClasses.text">New Features</p>
                  <p class="text-sm" :class="themeClasses.secondaryText">Receive updates about new features and improvements</p>
                </div>
                <button
                  @click="settings.notifications.newFeatures = !settings.notifications.newFeatures"
                  :class="settings.notifications.newFeatures
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-600 text-white'"
                  class="px-4 py-2 rounded transition"
                  >
                  {{ settings.notifications.newFeatures ? 'ON' : 'OFF' }}
                </button>
              </div>
            </div>
            <div class="mt-6">
              <button @click="saveNotificationSettings" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'save']" class="mr-2" />
                Save Notification Settings
              </button>
            </div>
          </div>
          
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">In-App Notifications</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium" :class="themeClasses.text">Quiz Results</p>
                  <p class="text-sm" :class="themeClasses.secondaryText">Show notifications for quiz results</p>
                </div>
                <button
                  @click="settings.notifications.quizResults = !settings.notifications.quizResults"
                  :class="settings.notifications.quizResults
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-600 text-white'"
                  class="px-4 py-2 rounded transition"
                  >
                  {{ settings.notifications.quizResults ? 'ON' : 'OFF' }}
                </button>
              </div>
              
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium" :class="themeClasses.text">Goal Progress</p>
                  <p class="text-sm" :class="themeClasses.secondaryText">Show notifications for goal progress updates</p>
                </div>
                <button
                  @click="settings.notifications.goalProgress = !settings.notifications.goalProgress"
                  :class="settings.notifications.goalProgress
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-600 text-white'"
                  class="px-4 py-2 rounded transition"
                  >
                  {{ settings.notifications.goalProgress ? 'ON' : 'OFF' }}
                </button>
              </div>
            </div>
          </div>
        </div>
        
      </main>
</Header>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import { useNotifications } from '@/composables/useNotifications';
import { useUserProfile } from '@/composables/useUserProfile';
import api from '@/services/api';
import Header from '@/components/Header.vue';

export default {
  name: 'SettingsView',
  components: {
    Header
  },
  setup() {
    const store = useStore();

    // Use the shared notifications composable
    const {
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead
    } = useNotifications();

    // Use the shared user profile composable
    const {
      user,
      loadUserProfile,
      updateUserProfile,
      getProfilePictureUrl
    } = useUserProfile();

    const activeTab = ref('account');

    const passwords = ref({
      current: '',
      new: '',
      confirm: ''
    });

    const isLoading = ref(false);
    const message = ref('');
    const messageType = ref(''); // 'success' or 'error'

    // User menu state
    const showUserMenu = ref(false);

    // Use global theme and font size from store
    const settings = reactive({
      get theme() {
        return store.getters['app/getTheme'];
      },
      set theme(value) {
        store.dispatch('app/setTheme', value);
      },
      get fontSize() {
        return store.getters['app/getFontSize'];
      },
      set fontSize(value) {
        store.dispatch('app/setFontSize', value);
      },
      notifications: {
        weeklySummary: false,
        studyReminders: false,
        newFeatures:  false,
        quizResults: false,
        goalProgress: false
      },
    });

    // Use global theme classes from store
    const themeClasses = computed(() => {
      return store.getters['app/getThemeClasses'];
    });

    // Use global font size classes from store
    const fontSizeClasses = computed(() => {
      return store.getters['app/getFontSizeClasses'];
    });

    // Sidebar visibility from store
    const sidebarVisible = computed(() => store.getters['app/getSidebarVisible']);

    // =====================================
    // SIDEBAR FUNCTIONS
    // =====================================

    /**
     * Handle sidebar toggle from AppHeader component
     */
    const handleSidebarToggle = () => {
      store.dispatch('app/toggleSidebar');
    };

    /**
     * Toggle sidebar visibility (legacy function for backward compatibility)
     */
    const toggleSidebar = () => {
      store.dispatch('app/toggleSidebar');
    };

    // Theme is now handled globally by the store
    // No need for local theme watching or application

    // Load settings from backend
    const loadSettings = async () => {
      try {
        const response = await api.getSettings();
        if (response.data.success) {
          Object.assign(settings, response.data.data);
        }
      } catch (error) {
        // Fallback to localStorage
        const savedSettings = localStorage.getItem('smartscribe_settings');
        if (savedSettings) {
          const parsed = JSON.parse(savedSettings);
          Object.assign(settings, parsed);
        }
      }
    };

    // Save settings to backend and localStorage
    const saveSettings = async () => {
      try {
        const response = await api.updateSettings(settings);
        if (response.data.success) {
          // Settings saved successfully
        }
      } catch (error) {
        // Error saving settings to backend
      }

      // Always save to localStorage as fallback
      localStorage.setItem('smartscribe_settings', JSON.stringify(settings));
    };

    // Show message function
    const showMessage = (text, type = 'success') => {
      message.value = text;
      messageType.value = type;
      setTimeout(() => {
        message.value = '';
        messageType.value = '';
      }, 3000);
    };


    // Handle image loading errors
    const handleImageError = () => {
      // Profile picture failed to load
    };

    // Handle successful image loading
    const handleImageLoad = () => {
      // Profile picture loaded successfully
    };

    // Save profile changes
    const saveProfile = async () => {
      try {
        isLoading.value = true;

        // Create profile data to update
        const profileData = {
          first_name: user.value?.firstName || '',
          last_name: user.value?.lastName || '',
          email: user.value?.email || ''
        };

        // Update profile using the composable
        const result = await updateUserProfile(profileData);

        if (result.success) {
          saveSettings();
          showMessage('Profile updated successfully!');
        } else {
          showMessage('Failed to update profile: ' + (result.error || 'Unknown error'), 'error');
        }
      } catch (error) {
        showMessage('Failed to update profile', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    // Update password
    const updatePassword = async () => {
      if (passwords.value.new !== passwords.value.confirm) {
        showMessage('New passwords do not match', 'error');
        return;
      }

      if (passwords.value.new.length < 6) {
        showMessage('Password must be at least 6 characters', 'error');
        return;
      }

      try {
        isLoading.value = true;

        // Make API call to update password
        const response = await api.updatePassword({
          current_password: passwords.value.current,
          new_password: passwords.value.new
        });

        if (response.data.success) {
          passwords.value = { current: '', new: '', confirm: '' };
          showMessage('Password updated successfully!');
        } else {
          showMessage(response.data.message || 'Failed to update password', 'error');
        }
      } catch (error) {
        console.error('Password update error:', error);
        const errorMessage = error.response?.data?.message ||
                            error.response?.data?.error ||
                            'Failed to update password';
        showMessage(errorMessage, 'error');
      } finally {
        isLoading.value = false;
      }
    };

    // Save notification settings
    const saveNotificationSettings = () => {
      saveSettings();
      showMessage('Notification settings saved!');
    };


    // Delete account
    const deleteAccount = () => {
      if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        // In a real app, you would make an API call here
        // await fetch('/api/user/delete', { method: 'DELETE' });
        showMessage('Account deletion is not implemented in demo mode', 'error');
      }
    };

    // Fetch user profile data using the composable
    const fetchUserProfile = async () => {
      await loadUserProfile();
    };

    // Upload profile picture
    const uploadProfilePicture = async (file) => {
      try {
        const formData = new FormData();
        formData.append('profile_picture', file);

        // Use the centralized API service
        const response = await api.uploadProfilePicture(formData);

        if (response.data.success) {
          // Refresh profile data - this will update the global state
          await fetchUserProfile();
          return { success: true, message: response.data.message };
        } else {
          return { success: false, message: response.data.error };
        }
      } catch (error) {
        return { success: false, message: 'Network error' };
      }
    };

    // Handle profile picture file selection
    const handleProfilePictureUpload = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
      if (!allowedTypes.includes(file.type)) {
        showMessage('Please select a valid image file (JPEG, PNG, GIF, WebP) or PDF file', 'error');
        return;
      }

      // Validate file size (max 5MB)
      const maxSize = 5 * 1024 * 1024;
      if (file.size > maxSize) {
        showMessage('File size must be less than 5MB', 'error');
        return;
      }

      try {
        const result = await uploadProfilePicture(file);

        if (result.success) {
          showMessage(result.message);
          // Refresh user profile data
          await fetchUserProfile();
        } else {
          showMessage('Upload failed: ' + result.message, 'error');
        }
      } catch (error) {
        showMessage('Upload failed: Network error', 'error');
      }

      // Reset file input
      event.target.value = '';
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
      // For now, just close the menu
      // In a full implementation, this would open a profile modal
      showUserMenu.value = false;
    };

    /**
     * Logout user and redirect to login
     */
    const logout = async () => {
      try {
        // Use the composable's logout function
        const { logout: logoutUser } = useUserProfile();
        logoutUser();

        // Close menu
        showUserMenu.value = false;

        // In a real app, you would redirect to login page
        // For now, just show a message
        showMessage('Logged out successfully!');
      } catch (error) {
        showMessage('Error logging out', 'error');
      }
    };

    // Load settings and user profile on mount
    onMounted(async () => {
      await Promise.all([
        loadSettings(),
        loadUserProfile()
      ]);

      // Theme is now handled globally by the store
      // No need to apply theme locally
    });

    return {
      activeTab,
      user,
      passwords,
      settings,
      themeClasses,
      fontSizeClasses,
      sidebarVisible,
      showUserMenu,
      isLoading,
      message,
      messageType,
      saveProfile,
      updatePassword,
      saveNotificationSettings,
      deleteAccount,
      fetchUserProfile,
      uploadProfilePicture,
      handleProfilePictureUpload,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,
      handleSidebarToggle,
      toggleSidebar,
      toggleUserMenu,
      closeUserMenu,
      openProfileModal,
      logout,
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,
      store,
      // Add composable functions
      loadUserProfile,
      updateUserProfile
    };
  }
}
</script>