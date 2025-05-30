<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white" :style="{ fontSize: settings.fontSize + 'px' }">
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
              <router-link to="/progress" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link to="/settings" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Settings Main Content -->
      <main class="flex-grow p-6">
        <h1 class="text-2xl font-bold mb-6">Settings</h1>
        
        <!-- Settings Tabs -->
        <div class="flex border-b border-gray-700 mb-6">
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
          <button 
            @click="activeTab = 'api'" 
            :class="[
              'px-4 py-2 font-medium', 
              activeTab === 'api' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-400 hover:text-white'
            ]"
          >
            API Settings
          </button>
        </div>
        
        <!-- Account Settings -->
        <div v-if="activeTab === 'account'" class="space-y-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Profile Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-400 mb-1">Full Name</label>
                <input 
                  v-model="user.name" 
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-400 mb-1">Email Address</label>
                <input 
                  v-model="user.email" 
                  type="email"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>
            <div class="mt-4">
              <button class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                Save Changes
              </button>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Change Password</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm text-gray-400 mb-1">Current Password</label>
                <input 
                  v-model="passwords.current" 
                  type="password"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-400 mb-1">New Password</label>
                <input 
                  v-model="passwords.new" 
                  type="password"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-400 mb-1">Confirm New Password</label>
                <input 
                  v-model="passwords.confirm" 
                  type="password"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>
            <div class="mt-4">
              <button class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                Update Password
              </button>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Danger Zone</h2>
            <p class="text-gray-400 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
            <button class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
              Delete Account
            </button>
          </div>
        </div>
        
        <!-- Appearance Settings -->
        <div v-if="activeTab === 'appearance'" class="space-y-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Theme</h2>
            <div class="flex space-x-4">
              <div 
                @click="settings.theme = 'dark'" 
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2', 
                  settings.theme === 'dark' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full bg-gray-900 rounded-lg p-3">
                  <div class="h-4 w-full bg-gray-800 rounded mb-2"></div>
                  <div class="h-4 w-3/4 bg-gray-800 rounded"></div>
                  <div class="mt-4 text-xs text-center text-gray-400">Dark</div>
                </div>
              </div>
              <div 
                @click="settings.theme = 'light'" 
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2', 
                  settings.theme === 'light' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full bg-gray-100 rounded-lg p-3">
                  <div class="h-4 w-full bg-white rounded mb-2"></div>
                  <div class="h-4 w-3/4 bg-white rounded"></div>
                  <div class="mt-4 text-xs text-center text-gray-600">Light</div>
                </div>
              </div>
              <div 
                @click="settings.theme = 'system'" 
                :class="[
                  'w-32 h-24 rounded-lg cursor-pointer border-2', 
                  settings.theme === 'system' ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <div class="h-full bg-gradient-to-r from-gray-900 to-gray-100 rounded-lg p-3 flex items-center justify-center">
                  <div class="text-xs text-center">System</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Font Size</h2>
            <div class="flex items-center space-x-4">
              <span class="text-sm">A</span>
              <input 
                v-model="settings.fontSize" 
                type="range" 
                min="12" 
                max="20" 
                class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer"
              />
              <span class="text-lg">A</span>
            </div>
            <p class="mt-2 text-sm text-gray-400">Current font size: {{ settings.fontSize }}px</p>
          </div>
        </div>
        
        <!-- Notifications Settings -->
        <div v-if="activeTab === 'notifications'" class="space-y-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Email Notifications</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium">Weekly Summary</p>
                  <p class="text-sm text-gray-400">Receive a weekly summary of your study progress</p>
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
                  <p class="font-medium">Study Reminders</p>
                  <p class="text-sm text-gray-400">Receive reminders for your study goals</p>
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
                  <p class="font-medium">New Features</p>
                  <p class="text-sm text-gray-400">Receive updates about new features and improvements</p>
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
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">In-App Notifications</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium">Quiz Results</p>
                  <p class="text-sm text-gray-400">Show notifications for quiz results</p>
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
                  <p class="font-medium">Goal Progress</p>
                  <p class="text-sm text-gray-400">Show notifications for goal progress updates</p>
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
        
        <!-- API Settings -->
        <div v-if="activeTab === 'api'" class="space-y-6">
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">OpenAI API Configuration</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm text-gray-400 mb-1">API Key</label>
                <input 
                  v-model="settings.api.openaiKey" 
                  type="password"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                  placeholder="sk-..."
                />
                <p class="mt-1 text-xs text-gray-400">Your OpenAI API key is stored securely and used for AI-powered features.</p>
              </div>
              
              <div>
                <label class="block text-sm text-gray-400 mb-1">Model</label>
                <select 
                  v-model="settings.api.openaiModel" 
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                >
                  <option value="gpt-4">GPT-4 (Most Capable)</option>
                  <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Faster)</option>
                </select>
              </div>
            </div>
            <div class="mt-4">
              <button class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                Save API Settings
              </button>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">OCR Configuration</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm text-gray-400 mb-1">OCR Engine</label>
                <select 
                  v-model="settings.api.ocrEngine" 
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                >
                  <option value="tesseract">Tesseract (Local)</option>
                  <option value="google">Google Cloud Vision (Cloud)</option>
                  <option value="azure">Azure Computer Vision (Cloud)</option>
                </select>
              </div>
              
              <div v-if="settings.api.ocrEngine !== 'tesseract'">
                <label class="block text-sm text-gray-400 mb-1">API Key</label>
                <input 
                  v-model="settings.api.ocrKey" 
                  type="password"
                  class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>
            <div class="mt-4">
              <button class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                Save OCR Settings
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
// import { theme } from 'tailwind.config';
import { ref, reactive, watch } from 'vue';
// import { useStore } from 'vuex'; //

export default {
  name: 'SettingsView',
  setup() {
    // const store = useStore(); //
    
    const activeTab = ref('account');
    
    const user = ref({
      name: 'John Doe',
      email: 'john.doe@example.com'
    });
    
    const passwords = ref({
      current: '',
      new: '',
      confirm: ''
    });

    
    
    const settings = reactive({
      fontSize: 16,
      theme: 'dark',
      notifications: {
        weeklySummary: false,
        studyReminders: false,
        newFeatures:  false,
        quizResults: false,
        goalProgress: false
      },
      api: {
        openaiKey: '',
        openaiModel: 'gpt-3.5-turbo',
        ocrEngine: 'tesseract',
        ocrKey: ''
      }
    });

    // Watch for theme changes
    watch(() => settings.theme, (newTheme) => {
      const root = document.documentElement;

      if (newTheme === 'dark') {
        root.classList.add('dark');
      } else if (newTheme === 'light') {
        root.classList.remove('dark');
      } else if (newTheme === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
      }
    });
    
    return {
      activeTab,
      user,
      passwords,
      settings
    };
  }
}
</script>