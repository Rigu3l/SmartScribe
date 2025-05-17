<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header (same as other pages) -->
    <header class="p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-4">
        <button class="text-gray-400 hover:text-white">
          <i class="fas fa-bell"></i>
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
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
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
              <a href="#" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
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

      <!-- Progress Tracker Main Content -->
      <main class="flex-grow p-6">
        <h1 class="text-2xl font-bold mb-6">Study Progress</h1>
        
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Total Notes</h2>
              <i class="fas fa-book text-blue-500 text-xl"></i>
            </div>
            <p class="text-3xl font-bold">{{ stats.totalNotes }}</p>
            <p class="text-sm text-gray-400">{{ stats.notesThisWeek }} new this week</p>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Study Time</h2>
              <i class="fas fa-clock text-green-500 text-xl"></i>
            </div>
            <p class="text-3xl font-bold">{{ stats.studyHours }}h</p>
            <p class="text-sm text-gray-400">{{ stats.studyHoursThisWeek }}h this week</p>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold">Quiz Score</h2>
              <i class="fas fa-check-circle text-yellow-500 text-xl"></i>
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
                  <i :class="`fas ${activity.icon}`"></i>
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
            <button class="px-3 py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition">
              <i class="fas fa-plus mr-1"></i> New Goal
            </button>
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
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="text-gray-400 hover:text-white">
                    <i class="fas fa-trash"></i>
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

<script setup>
import { ref } from 'vue';

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
    icon: 'fa-camera', 
    iconColor: 'bg-blue-600' 
  },
  { 
    title: 'Completed History Quiz', 
    time: 'Yesterday at 4:30 PM', 
    icon: 'fa-check-circle', 
    iconColor: 'bg-green-600' 
  },
  { 
    title: 'Generated Summary for Physics', 
    time: 'Yesterday at 2:15 PM', 
    icon: 'fa-file-alt', 
    iconColor: 'bg-yellow-600' 
  },
  { 
    title: 'Created Study Goal', 
    time: '3 days ago', 
    icon: 'fa-bullseye', 
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
</script>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
</style>