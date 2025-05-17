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
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link to="/progress" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
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

      <!-- Progress Tracker Main Content -->
      <main class="flex-grow p-6">
        <h1 class="text-2xl font-bold mb-6">Study Progress</h1>
        
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
// import { useStore } from 'vuex'; //

export default {
  name: 'ProgressView',
  setup() {
    // const store = useStore(); //
    
    const stats = ref({
      totalNotes: 15,
      notesThisWeek: 3,
      studyHours: 24,
      studyHoursThisWeek: 6,
      quizAverage: 85,
      quizzesCompleted: 8
    });

    const weeklyActivity = ref([
      { name: 'Mon', activity: 0.3 },
      { name: 'Tue', activity: 0.5 },
      { name: 'Wed', activity: 0.2 },
      { name: 'Thu', activity: 0.8 },
      { name: 'Fri', activity: 0.6 },
      { name: 'Sat', activity: 0.4 },
      { name: 'Sun', activity: 0.1 }
    ]);

    const subjects = ref([
      { name: 'Biology', percentage: 35, color: 'bg-green-500' },
      { name: 'History', percentage: 25, color: 'bg-blue-500' },
      { name: 'Mathematics', percentage: 20, color: 'bg-yellow-500' },
      { name: 'Physics', percentage: 15, color: 'bg-purple-500' },
      { name: 'Literature', percentage: 5, color: 'bg-red-500' }
    ]);

    const recentActivity = ref([
      { 
        title: 'Scanned Biology Notes', 
        time: '2 hours ago', 
        icon: ['fas', 'camera'], 
        iconColor: 'bg-blue-600' 
      },
      { 
        title: 'Completed History Quiz', 
        time: 'Yesterday at 4:30 PM', 
        icon: ['fas', 'check-circle'], 
        iconColor: 'bg-green-600' 
      },
      { 
        title: 'Generated Summary for Physics', 
        time: 'Yesterday at 2:15 PM', 
        icon: ['fas', 'file-alt'], 
        iconColor: 'bg-yellow-600' 
      },
      { 
        title: 'Created Study Goal', 
        time: '3 days ago', 
        icon: ['fas', 'bullseye'], 
        iconColor: 'bg-purple-600' 
      }
    ]);

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

    onMounted(async () => {
      try {
        // In a real app, we would fetch progress data from the store
        // await store.dispatch('progress/fetchStats');
        // await store.dispatch('progress/fetchActivity');
        // stats.value = store.getters['progress/getStats'];
        // weeklyActivity.value = store.getters['progress/getWeeklyActivity'];
        // subjects.value = store.getters['progress/getSubjects'];
        // recentActivity.value = store.getters['progress/getRecentActivity'];
        // goals.value = store.getters['progress/getGoals'];
      } catch (error) {
        console.error('Error loading progress data:', error);
      }
    });
    
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

    return {
      stats,
      weeklyActivity,
      subjects,
      recentActivity,
      goals,
      showAddGoalForm,
      newGoal,
      addGoal
    };
  }
}
</script>