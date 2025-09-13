<template>
  <Header @open-profile-modal="openProfileModal">

      <!-- Goals Main Content -->
      <main class="flex-1 p-4 md:p-6 transition-all duration-300 ease-in-out" style="width: 100vw; max-width: 100vw;">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-3xl sm:text-4xl text-blue-500" />
        </div>

        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
          <font-awesome-icon :icon="['fas', 'times']" class="text-4xl text-red-400 mb-4" />
          <h2 class="text-xl font-medium mb-2">Error Loading Goals</h2>
          <p class="text-gray-400 mb-4">{{ error }}</p>
          <router-link to="/dashboard" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
            Back to Dashboard
          </router-link>
        </div>

        <div v-else>
          <!-- Goals Management Header -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Learning Goals</h1>
                <p class="text-gray-400">Set and track your learning objectives</p>
              </div>
              <div class="flex space-x-3 mt-4 sm:mt-0">
                <button @click="openCreateGoalModal" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                  <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" />
                  Create New Goal
                </button>
                <router-link to="/dashboard" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                  <font-awesome-icon :icon="['fas', 'angle-left']" class="mr-2" />
                  Back to Dashboard
                </router-link>
              </div>
            </div>
          </div>

          <!-- Goals Statistics -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6">
            <div :class="themeClasses.card" class="rounded-lg p-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-base font-medium">Active Goals</h3>
                <font-awesome-icon :icon="['fas', 'bullseye']" class="text-blue-400 text-lg" />
              </div>
              <div v-if="isLoadingStats" class="animate-pulse">
                <div class="h-6 bg-gray-700 rounded mb-2"></div>
                <div class="h-3 bg-gray-700 rounded w-3/4"></div>
              </div>
              <div v-else>
                <p class="text-2xl font-semibold">{{ goalStats.activeGoals }}</p>
                <p class="text-xs text-gray-500">Currently tracking</p>
              </div>
            </div>

            <div :class="themeClasses.card" class="rounded-lg p-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-base font-medium">Completed This Month</h3>
                <font-awesome-icon :icon="['fas', 'check-circle']" class="text-green-400 text-lg" />
              </div>
              <div v-if="isLoadingStats" class="animate-pulse">
                <div class="h-6 bg-gray-700 rounded mb-2"></div>
                <div class="h-3 bg-gray-700 rounded w-3/4"></div>
              </div>
              <div v-else>
                <p class="text-2xl font-semibold">{{ goalStats.completedGoals }}</p>
                <p class="text-xs text-gray-500">Goals achieved</p>
              </div>
            </div>

            <div :class="themeClasses.card" class="rounded-lg p-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-base font-medium">Success Rate</h3>
                <font-awesome-icon :icon="['fas', 'star']" class="text-yellow-400 text-lg" />
              </div>
              <div v-if="isLoadingStats" class="animate-pulse">
                <div class="h-6 bg-gray-700 rounded mb-2"></div>
                <div class="h-3 bg-gray-700 rounded w-3/4"></div>
              </div>
              <div v-else>
                <p class="text-2xl font-semibold">{{ goalStats.successRate }}%</p>
                <p class="text-xs text-gray-500">Completion rate</p>
              </div>
            </div>
          </div>

          <!-- Goals List -->
          <div :class="themeClasses.card" class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold">Your Goals</h3>
              <button @click="loadGoals" class="text-blue-400 hover:text-blue-300 text-sm">
                <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-1" />
                Refresh
              </button>
            </div>

            <!-- Loading State -->
            <div v-if="isLoadingGoals" class="flex justify-center py-8">
              <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-2xl text-blue-500" />
              <span class="ml-2 text-gray-400">Loading your goals...</span>
            </div>

            <!-- Empty State -->
            <div v-else-if="goals.length === 0" class="text-center py-12 text-gray-400">
              <font-awesome-icon :icon="['fas', 'bullseye']" class="text-6xl mb-6" />
              <h3 class="text-xl font-semibold mb-4">No Goals Yet</h3>
              <p class="text-gray-400 mb-6">Set your first learning goal to start tracking your progress!</p>
              <button @click="openCreateGoalModal" class="px-6 py-3 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" />
                Create Your First Goal
              </button>
            </div>

            <!-- Goals List -->
            <div v-else class="space-y-4">
              <div
                v-for="goal in goals"
                :key="goal.id"
                class="bg-gray-700 rounded-lg p-5 hover:bg-gray-600 transition-all duration-200"
              >
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                      <div :class="[
                        'w-3 h-3 rounded-full',
                        goal.status === 'completed' ? 'bg-green-500' :
                        goal.status === 'overdue' ? 'bg-red-500' : 'bg-blue-500'
                      ]"></div>
                      <h4 class="font-semibold text-white text-lg">{{ goal.title }}</h4>
                      <span :class="[
                        'px-2 py-1 text-xs rounded-full font-medium',
                        goal.status === 'completed' ? 'bg-green-600 text-white' :
                        goal.status === 'overdue' ? 'bg-red-600 text-white' : 'bg-blue-600 text-white'
                      ]">
                        {{ goal.status.charAt(0).toUpperCase() + goal.status.slice(1) }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-300 mb-3">{{ goal.description }}</p>

                    <!-- Goal Progress -->
                    <div class="mb-3">
                      <div class="flex justify-between text-sm text-gray-400 mb-1">
                        <span>Progress</span>
                        <span>{{ goal.current_value }} / {{ goal.target_value }}</span>
                      </div>
                      <div class="w-full bg-gray-600 rounded-full h-2">
                        <div
                          class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${Math.min((goal.current_value / goal.target_value) * 100, 100)}%` }"
                        ></div>
                      </div>
                    </div>

                    <div class="flex items-center space-x-4 text-sm text-gray-400">
                      <div class="flex items-center space-x-1">
                        <font-awesome-icon :icon="['fas', 'target']" />
                        <span>{{ goal.target_type.replace('_', ' ').toUpperCase() }}</span>
                      </div>
                      <div v-if="goal.deadline" class="flex items-center space-x-1">
                        <font-awesome-icon :icon="['fas', 'calendar']" />
                        <span>{{ new Date(goal.deadline).toLocaleDateString() }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center space-x-2 ml-4">
                    <button
                      @click="updateGoalProgress(goal)"
                      class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition"
                      title="Update Progress"
                    >
                      <font-awesome-icon :icon="['fas', 'plus']" />
                    </button>
                    <button
                      @click="editGoal(goal)"
                      class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition"
                      title="Edit Goal"
                    >
                      <font-awesome-icon :icon="['fas', 'edit']" />
                    </button>
                    <button
                      @click="confirmDeleteGoal(goal)"
                      class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition"
                      title="Delete Goal"
                    >
                      <font-awesome-icon :icon="['fas', 'trash']" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

    <!-- Create/Edit Goal Modal -->
    <div v-if="showGoalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold">{{ isEditing ? 'Edit Goal' : 'Create New Goal' }}</h2>
          <button @click="closeGoalModal" class="text-gray-400 hover:text-white">
            <font-awesome-icon :icon="['fas', 'times']" class="text-xl" />
          </button>
        </div>

        <form @submit.prevent="saveGoal" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Goal Title</label>
            <input
              v-model="goalForm.title"
              type="text"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter your goal title..."
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Description</label>
            <textarea
              v-model="goalForm.description"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              rows="3"
              placeholder="Describe your goal..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Target Type</label>
              <select
                v-model="goalForm.target_type"
                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="notes">Notes</option>
                <option value="quizzes">Quizzes</option>
                <option value="study_time">Study Time (hours)</option>
                <option value="accuracy">Accuracy (%)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2">Target Value</label>
              <input
                v-model.number="goalForm.target_value"
                type="number"
                min="1"
                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Deadline (Optional)</label>
            <input
              v-model="goalForm.deadline"
              type="date"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeGoalModal"
              class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition"
              :disabled="isSavingGoal"
            >
              <span v-if="isSavingGoal" class="flex items-center space-x-2">
                <font-awesome-icon :icon="['fas', 'spinner']" class="animate-spin" />
                <span>Saving...</span>
              </span>
              <span v-else>{{ isEditing ? 'Update Goal' : 'Create Goal' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-sm mx-4">
        <div class="flex items-center mb-4">
          <font-awesome-icon :icon="['fas', 'times']" class="text-red-400 text-xl mr-3" />
          <h3 class="text-lg font-medium">Delete Goal</h3>
        </div>
        <p class="text-gray-300 mb-6">
          Are you sure you want to delete "<strong>{{ goalToDelete?.title }}</strong>"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button @click="cancelDelete" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
            Cancel
          </button>
          <button @click="proceedDelete" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
            Delete Goal
          </button>
        </div>
      </div>
    </div>
  </Header>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { useNotifications } from '@/composables/useNotifications'
import { useUserProfile } from '@/composables/useUserProfile'
import api from '@/services/api'
import Header from '@/components/Header.vue'

export default {
  name: 'GoalsView',
  components: {
    Header
  },
  setup() {
    const store = useStore()

    // =====================================
    // NOTIFICATION SYSTEM
    // =====================================
    const {
      showSuccess,
      showInfo,
      showWarning
    } = useNotifications()

    // =====================================
    // USER PROFILE SYSTEM
    // =====================================
    const {
      user: userProfile,
      loadUserProfile,
      getProfilePictureUrl
    } = useUserProfile()

    // State
    const isLoading = ref(true)
    const error = ref(null)
    const goals = ref([])
    const isLoadingGoals = ref(false)
    const goalStats = ref({
      activeGoals: 0,
      completedGoals: 0,
      successRate: 0
    })
    const isLoadingStats = ref(false)

    // Modal state
    const showGoalModal = ref(false)
    const isEditing = ref(false)
    const currentGoal = ref(null)
    const isSavingGoal = ref(false)
    const showDeleteConfirmation = ref(false)
    const goalToDelete = ref(null)

    // Form state
    const goalForm = ref({
      title: '',
      description: '',
      target_type: 'notes',
      target_value: 1,
      deadline: ''
    })

    // Computed properties
    const user = userProfile

    const themeClasses = computed(() => {
      return store.getters['app/getThemeClasses']
    })

    // Methods
    const openProfileModal = () => {
      // For now, just close the menu
    }


    const handleImageError = () => {
      // Profile picture failed to load
    }

    const handleImageLoad = () => {
      // Profile picture loaded successfully
    }

    const loadGoals = async () => {
      try {
        isLoadingGoals.value = true
        // Force cache busting by adding timestamp
        const response = await api.get('?resource=goals&_t=' + Date.now())
        if (response.data.success) {
          goals.value = response.data.data
        } else {
          console.error('Failed to load goals:', response.data.error)
        }
      } catch (err) {
        console.error('Error loading goals:', err)
        showWarning('Failed to load goals', 'Please try again later.')
      } finally {
        isLoadingGoals.value = false
      }
    }

    const loadGoalStats = async () => {
      try {
        isLoadingStats.value = true
        // Force cache busting by adding timestamp
        const response = await api.get('?resource=goals&action=stats&_t=' + Date.now())
        if (response.data.success) {
          goalStats.value = response.data.data
        } else {
          console.error('Failed to load goal stats:', response.data.error)
        }
      } catch (err) {
        console.error('Error loading goal stats:', err)
      } finally {
        isLoadingStats.value = false
      }
    }

    const openCreateGoalModal = () => {
      isEditing.value = false
      currentGoal.value = null
      goalForm.value = {
        title: '',
        description: '',
        target_type: 'notes',
        target_value: 1,
        deadline: ''
      }
      showGoalModal.value = true
    }

    const editGoal = (goal) => {
      isEditing.value = true
      currentGoal.value = goal
      goalForm.value = {
        title: goal.title,
        description: goal.description,
        target_type: goal.target_type,
        target_value: goal.target_value,
        deadline: goal.deadline ? goal.deadline.split('T')[0] : ''
      }
      showGoalModal.value = true
    }

    const closeGoalModal = () => {
      showGoalModal.value = false
      goalForm.value = {
        title: '',
        description: '',
        target_type: 'notes',
        target_value: 1,
        deadline: ''
      }
    }

    const saveGoal = async () => {
      try {
        isSavingGoal.value = true

        const goalData = {
          ...goalForm.value,
          status: 'active'
        }

        let response
        if (isEditing.value) {
          response = await api.post(`?resource=goals&id=${currentGoal.value.id}`, goalData)
        } else {
          response = await api.post('?resource=goals', goalData)
        }

        if (response.data.success) {
          showSuccess(
            isEditing.value ? 'Goal updated!' : 'Goal created!',
            `Your goal "${goalForm.value.title}" has been ${isEditing.value ? 'updated' : 'created'}.`
          )
          closeGoalModal()
          await loadGoals()
          await loadGoalStats()
        } else {
          showWarning('Failed to save goal', response.data.error || 'Please try again.')
        }
      } catch (err) {
        console.error('Error saving goal:', err)
        showWarning('Failed to save goal', 'Please try again.')
      } finally {
        isSavingGoal.value = false
      }
    }

    const updateGoalProgress = async (goal) => {
      // Simple progress update - in a real app, you'd have a modal for this
      const newValue = prompt(`Update progress for "${goal.title}" (current: ${goal.current_value}/${goal.target_value}):`, goal.current_value)
      if (newValue !== null && !isNaN(newValue)) {
        try {
          const response = await api.post(`?resource=goals&action=update-progress&id=${goal.id}`, {
            progress_value: parseInt(newValue)
          })

          if (response.data.success) {
            showSuccess('Progress updated!', 'Your goal progress has been updated.')
            await loadGoals()
            await loadGoalStats()
          } else {
            showWarning('Failed to update progress', response.data.error || 'Please try again.')
          }
        } catch (err) {
          console.error('Error updating progress:', err)
          showWarning('Failed to update progress', 'Please try again.')
        }
      }
    }

    const confirmDeleteGoal = (goal) => {
      goalToDelete.value = goal
      showDeleteConfirmation.value = true
    }

    const cancelDelete = () => {
      showDeleteConfirmation.value = false
      goalToDelete.value = null
    }

    const proceedDelete = async () => {
      if (!goalToDelete.value) return

      try {
        const response = await api.post(`?resource=goals&id=${goalToDelete.value.id}`, {}, {
          headers: {
            'X-HTTP-Method-Override': 'DELETE'
          }
        })
        if (response.data.success) {
          showSuccess('Goal deleted!', 'Your goal has been deleted.')
          await loadGoals()
          await loadGoalStats()
        } else {
          showWarning('Failed to delete goal', response.data.error || 'Please try again.')
        }
      } catch (err) {
        console.error('Error deleting goal:', err)
        showWarning('Failed to delete goal', 'Please try again.')
      } finally {
        showDeleteConfirmation.value = false
        goalToDelete.value = null
      }
    }

    // Initialize
    onMounted(async () => {
      try {
        await loadUserProfile()
        await Promise.all([
          loadGoals(),
          loadGoalStats()
        ])
      } catch (err) {
        console.error('Error initializing goals view:', err)
        error.value = 'Failed to load goals'
      } finally {
        isLoading.value = false
      }
    })

    return {
      // UI State
      isLoading,
      error,
      showGoalModal,
      isEditing,
      isSavingGoal,
      showDeleteConfirmation,
      goalToDelete,

      // Data
      goals,
      isLoadingGoals,
      goalStats,
      isLoadingStats,
      goalForm,
      user,

      // Methods
      openProfileModal,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,
      loadGoals,
      loadGoalStats,
      openCreateGoalModal,
      editGoal,
      closeGoalModal,
      saveGoal,
      updateGoalProgress,
      confirmDeleteGoal,
      cancelDelete,
      proceedDelete,

      // Notification functions
      showSuccess,
      showInfo,
      showWarning,

      // Theme classes
      themeClasses,

      // Store
      store
    }
  }
}
</script>