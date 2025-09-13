<template>
  <Header @open-profile-modal="openProfileModal">

      <!-- Progress Tracker Main Content -->
      <main class="flex-1 p-4 md:p-6 transition-all duration-300 ease-in-out">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold">Study Progress</h1>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="refreshProgress()"
              :disabled="loadingProgress"
              class="flex items-center space-x-1 px-3 py-1 bg-gray-700 rounded-md hover:bg-gray-600 transition disabled:opacity-50"
            >
              <font-awesome-icon
                :icon="['fas', loadingProgress ? 'spinner' : 'sync-alt']"
                :class="loadingProgress ? 'animate-spin' : ''"
                class="text-sm"
              />
              <span class="text-sm">Refresh</span>
            </button>
            <button
              @click="debugStats()"
              class="px-3 py-1 bg-blue-600 rounded-md hover:bg-blue-700 transition text-sm"
            >
              Debug Stats
            </button>
          </div>
        </div>
        
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Total Notes</h2>
              <font-awesome-icon :icon="['fas', 'book']" class="text-blue-500 text-xl" />
            </div>
            <p class="text-3xl font-bold">{{ stats.totalNotes }}</p>
            <p class="text-sm text-gray-400">{{ stats.notesThisWeek }} new this week</p>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Study Time</h2>
              <font-awesome-icon :icon="['fas', 'clock']" class="text-green-500 text-xl" />
            </div>
            <p class="text-3xl font-bold">{{ stats.studyHours }}h</p>
            <p class="text-sm text-gray-400">{{ stats.studyHoursThisWeek }}h this week</p>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Quiz Score</h2>
              <font-awesome-icon :icon="['fas', 'check-circle']" class="text-yellow-500 text-xl" />
            </div>
            <p class="text-3xl font-bold">{{ stats.quizAverage }}%</p>
            <p class="text-sm text-gray-400">{{ stats.quizzesCompleted }} quizzes completed</p>
          </div>
        </div>
        
        <!-- Weekly Activity Chart -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Weekly Activity</h2>
          <div class="h-64 flex items-end justify-between">
            <div v-for="(day, index) in weeklyActivity" :key="index" class="flex flex-col items-center w-full">
              <div class="w-full px-1">
                <div 
                  class="bg-blue-600 rounded-t"
                  :style="{ height: `${day.activity * 200}px` }"
                ></div>
              </div>
              <p class="text-xs mt-2 text-gray-400">{{ day.name }}</p>
            </div>
          </div>
        </div>
        
        <!-- Subject Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Subject Distribution</h2>
            <div class="space-y-4">
              <div v-for="(subject, index) in subjects" :key="index">
                <div class="flex justify-between mb-1">
                  <span>{{ subject.name }}</span>
                  <span>{{ subject.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                  <div 
                    class="h-2.5 rounded-full" 
                    :class="subject.color"
                    :style="{ width: `${subject.percentage}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Recent Activity -->
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>
            <div class="space-y-4">
              <div v-for="(activity, index) in recentActivity" :key="index" class="flex items-start">
                <div :class="`${activity.iconColor} p-2 rounded-full mr-3`">
                  <font-awesome-icon :icon="activity.icon" />
                </div>
                <div>
                  <p class="font-medium">{{ activity.title }}</p>
                  <p class="text-sm text-gray-400">{{ activity.time }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Study Goals -->
        <div class="bg-gray-800 rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Study Goals</h2>
            <button @click="showAddGoalForm = true" class="px-3 py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition">
              <font-awesome-icon :icon="['fas', 'plus']" class="mr-1" /> New Goal
            </button>
          </div>
          
          <!-- Add Goal Form -->
          <div v-if="showAddGoalForm" class="bg-gray-700 rounded-lg p-4 mb-4">
            <h3 class="font-medium mb-3">Add New Goal</h3>
            <div class="mb-3">
              <label class="block text-sm text-gray-400 mb-1">Goal Title</label>
              <input 
                v-model="newGoal.title" 
                class="w-full p-2 bg-gray-600 rounded border border-gray-500 focus:outline-none focus:border-blue-500"
                placeholder="E.g., Complete Biology Chapter 5-8"
              />
            </div>
            <div class="mb-3">
              <label class="block text-sm text-gray-400 mb-1">Due Date</label>
              <input 
                v-model="newGoal.dueDate" 
                type="date" 
                class="w-full p-2 bg-gray-600 rounded border border-gray-500 focus:outline-none focus:border-blue-500"
              />
            </div>
            <div class="flex justify-end space-x-2">
              <button @click="showAddGoalForm = false" class="px-3 py-1 bg-gray-600 rounded text-sm hover:bg-gray-500 transition">
                Cancel
              </button>
              <button @click="addGoal" class="px-3 py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition">
                Add Goal
              </button>
            </div>
          </div>
          
          <div class="space-y-4">
            <div v-for="(goal, index) in goals" :key="index" class="bg-gray-700 rounded-lg p-4">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-medium">{{ goal.title }}</h3>
                  <p class="text-sm text-gray-400">{{ goal.dueDate }}</p>
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
              
              <div class="mt-3">
                <div class="flex justify-between mb-1 text-sm">
                  <span>Progress</span>
                  <span>{{ goal.progress }}%</span>
                </div>
                <div class="w-full bg-gray-600 rounded-full h-2">
                  <div 
                    class="h-2 rounded-full bg-green-500" 
                    :style="{ width: `${goal.progress}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </main>

  <!-- Profile Modal -->
    <div v-if="showProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-sm mx-4">
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
</Header>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { useNotifications } from '@/composables/useNotifications';
import { useUserProfile } from '@/composables/useUserProfile';
import api from '@/services/api';
import Header from '@/components/Header.vue';

export default {
  name: 'ProgressView',
  components: {
    Header
  },
  setup() {
    const router = useRouter();
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

    // Use the user profile composable
    const {
      user: userProfile,
      loading: loadingUser,
      loadUserProfile,
      getProfilePictureUrl
    } = useUserProfile();

    // =====================================
    // SIMPLE DATA MANAGEMENT
    // =====================================

    // Simple reactive data
    const progressResponse = ref(null);
    const loadingProgress = ref(false);
    const isConnected = ref(true);
    const connectionStatus = ref('connected');
    const lastSync = ref(new Date());

    // =====================================
    // COMPUTED PROPERTIES
    // =====================================

    // Process progress data
    const stats = computed(() => {
      if (!progressResponse.value?.data?.data) {
        console.log('ProgressView: No progress data available');
        return {
          totalNotes: 0,
          notesThisWeek: 0,
          studyHours: 0,
          studyHoursThisWeek: 0,
          quizAverage: 0,
          quizzesCompleted: 0
        };
      }

      const progressData = progressResponse.value.data.data;
      const result = {
        totalNotes: progressData.totalNotes || 0,
        notesThisWeek: progressData.notesThisWeek || 0,
        studyHours: progressData.studyHours || 0,
        studyHoursThisWeek: progressData.studyHoursThisWeek || 0,
        quizAverage: progressData.quizAverage || 0,
        quizzesCompleted: progressData.quizzesCompleted || 0
      };

      console.log('ProgressView stats:', result);
      return result;
    });

    const weeklyActivity = computed(() => {
      return progressResponse.value?.data?.data?.weeklyActivity || [];
    });

    const subjects = computed(() => {
      return progressResponse.value?.data?.data?.subjects || [];
    });

    const recentActivity = computed(() => {
      return progressResponse.value?.data?.data?.recentActivity || [];
    });

    // Use user from composable
    const user = userProfile;

    // Sidebar visibility from store
    const sidebarVisible = computed(() => store.getters['app/getSidebarVisible']);

    // Use global theme classes from store
    const themeClasses = computed(() => {
      return store.getters['app/getThemeClasses'];
    });

    const goals = ref([
      {
        title: 'Complete Biology Chapter 5-8',
        dueDate: 'Due in 5 days',
        progress: 65
      },
      {
        title: 'Prepare for History Midterm',
        dueDate: 'Due in 2 weeks',
        progress: 30
      },
      {
        title: 'Review Math Formulas',
        dueDate: 'Due tomorrow',
        progress: 90
      }
    ]);


    const showAddGoalForm = ref(false);
    const newGoal = ref({
      title: '',
      dueDate: ''
    });

    // User menu state
    const showUserMenu = ref(false);
    const showProfileModal = ref(false);


    // =====================================
    // SIMPLE API FUNCTIONS
    // =====================================

    // Fetch progress data from API
    const fetchProgress = async () => {
      try {
        loadingProgress.value = true;
        const response = await api.getDashboardStats();
        progressResponse.value = response.data;
        console.log('Progress data fetched successfully:', response.data);
      } catch (error) {
        console.error('Error fetching progress data:', error);
      } finally {
        loadingProgress.value = false;
      }
    };


    // Refresh functions
    const refreshProgress = async () => {
      await fetchProgress();
    };

    const refreshUser = async () => {
      await loadUserProfile();
    };

    // =====================================
    // LIFECYCLE HOOKS
    // =====================================

    onMounted(async () => {
      // Fetch initial data
      await Promise.all([
        fetchProgress(),
        loadUserProfile()
      ]);

      // Achievements are now automatically reactive - no manual initialization needed
      console.log('ProgressView mounted and data loaded');
    });
    

    // Handle image loading errors
    const handleImageError = (event) => {
      const imgSrc = event.target.src;
      console.error('Profile picture failed to load:', imgSrc);
      console.error('Image naturalWidth:', event.target.naturalWidth);
      console.error('Image naturalHeight:', event.target.naturalHeight);
      const userData = user.value || {};
      console.error('User profile picture path:', userData.profilePicture || 'null');
    };

    // Handle successful image loading
    const handleImageLoad = (event) => {
      const imgSrc = event.target.src;
      console.log('Profile picture loaded successfully:', imgSrc);
      console.log('Image dimensions:', event.target.naturalWidth, 'x', event.target.naturalHeight);
    };

    const debugStats = () => {
      console.log('=== DEBUG STATS ===');
      console.log('Raw progress response:', progressResponse.value);
      console.log('Computed stats:', stats.value);
      console.log('Loading state:', loadingProgress.value);
      console.log('Connection status:', isConnected.value);
      console.log('===================');
    };

    const addGoal = async () => {
      try {
        if (!newGoal.value.title.trim() || !newGoal.value.dueDate) {
          alert('Please fill in all fields');
          return;
        }

        // Format the due date
        const dueDate = new Date(newGoal.value.dueDate);
        const now = new Date();
        const diffTime = dueDate - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        let dueDateText;
        if (diffDays === 0) {
          dueDateText = 'Due today';
        } else if (diffDays === 1) {
          dueDateText = 'Due tomorrow';
        } else if (diffDays < 7) {
          dueDateText = `Due in ${diffDays} days`;
        } else if (diffDays < 14) {
          dueDateText = 'Due in 1 week';
        } else {
          dueDateText = `Due in ${Math.floor(diffDays / 7)} weeks`;
        }

        // In a real app, we would dispatch to the store
        // await store.dispatch('progress/addGoal', {
        //   title: newGoal.value.title,
        //   dueDate: dueDateText,
        //   progress: 0
        // });

        // For now, we'll just add it to our local array
        goals.value.push({
          title: newGoal.value.title,
          dueDate: dueDateText,
          progress: 0
        });

        // Reset form
        newGoal.value = {
          title: '',
          dueDate: ''
        };
        showAddGoalForm.value = false;
      } catch (error) {
        console.error('Error adding goal:', error);
        alert('Failed to add goal. Please try again.');
      }
    };

    // =====================================
    // SIDEBAR FUNCTIONS
    // =====================================

    /**
     * Toggle sidebar visibility
     */
    const toggleSidebar = () => {
      store.dispatch('app/toggleSidebar');
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
      stats,
      weeklyActivity,
      subjects,
      recentActivity,
      goals,
      user,
      sidebarVisible,

      // UI State
      showAddGoalForm,
      newGoal,
      showUserMenu,
      showProfileModal,

      // Loading states
      loadingProgress,
      loadingUser,

      // Connection status
      isConnected,
      connectionStatus,
      lastSync,

      // Functions
      toggleSidebar,
      addGoal,
      debugStats,
      refreshProgress,
      refreshUser,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,

      // User menu functions
      toggleUserMenu,
      closeUserMenu,
      openProfileModal,
      closeProfileModal,
      logout,

      // Notification functions
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,

      // Theme classes
      themeClasses,

      // Store for theme access
      store,
    };
  }
}
</script>