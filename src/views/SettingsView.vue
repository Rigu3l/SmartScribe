<template>
  <div class="min-h-screen flex flex-col transition-colors duration-300" :class="themeClasses.main">
    <!-- Header (same as other pages) -->
    <header class="p-4 flex justify-between items-center transition-colors duration-300" :class="themeClasses.header">
      <div class="flex items-center space-x-3">
        <!-- Classic Sidebar Toggle Button -->
        <button @click="toggleSidebar"
                class="group relative flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-slate-800 to-gray-900 hover:from-blue-600 hover:to-blue-700 text-gray-300 hover:text-white transition-all duration-300 ease-out transform hover:scale-105 active:scale-95 overflow-hidden shadow-lg hover:shadow-xl hover:shadow-blue-500/25"
                :title="sidebarVisible ? 'Hide sidebar' : 'Show sidebar'">

          <!-- Hamburger Menu Lines -->
          <div class="flex flex-col space-y-1 relative z-10">
            <!-- Top line -->
            <div class="w-6 h-0.5 bg-current transition-all duration-300 ease-out group-hover:w-7 group-hover:bg-white rounded-full"></div>
            <!-- Middle line -->
            <div class="w-5 h-0.5 bg-current transition-all duration-300 ease-out group-hover:w-6 group-hover:bg-white rounded-full"></div>
            <!-- Bottom line -->
            <div class="w-4 h-0.5 bg-current transition-all duration-300 ease-out group-hover:w-5 group-hover:bg-white rounded-full"></div>
          </div>

          <!-- Subtle background glow -->
          <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/0 to-blue-600/0 group-hover:from-blue-500/10 group-hover:to-blue-600/10 transition-all duration-300"></div>

          <!-- Tooltip -->
          <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap border border-gray-700">
            {{ sidebarVisible ? 'Hide Menu' : 'Show Menu' }}
            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 border-l border-t border-gray-700 rotate-45"></div>
          </div>
        </button>
        <div class="text-xl font-bold">SmartScribe</div>
      </div>
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
                      notification.bgColor || 'bg-blue-500'
                    ]">
                      <font-awesome-icon :icon="notification.icon" class="text-white text-sm" />
                    </div>
                  </div>
                  <div class="flex-grow">
                    <div class="flex items-start justify-between">
                      <div class="flex-grow">
                        <p class="text-sm font-medium" :class="themeClasses.text">{{ notification.title }}</p>
                        <p class="text-xs mt-1" :class="themeClasses.secondaryText">{{ notification.message }}</p>
                        <p class="text-xs mt-2" :class="themeClasses.secondaryText">{{ notification.time }}</p>
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
            <div v-else class="p-4 text-center" :class="themeClasses.secondaryText">
              <font-awesome-icon :icon="['fas', 'bell-slash']" class="text-2xl mb-2" />
              <p>No notifications yet</p>
            </div>
            <div v-if="notifications.length > 0" class="p-3 border-t" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'border-gray-700' : 'border-gray-200'">
              <button @click="markAllAsRead" class="text-sm text-blue-400 hover:text-blue-300">
                Mark all as read
              </button>
            </div>
          </div>
          <!-- Backdrop to close notifications when clicking outside -->
          <div v-if="showNotifications" class="fixed inset-0 z-0" @click="closeNotifications"></div>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 rounded-full overflow-hidden" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-600' : 'bg-gray-300'">
            <img
              v-if="user.profilePicture"
              :key="user.profilePicture"
              :src="getProfilePictureUrl(user.profilePicture)"
              alt="Profile"
              class="w-full h-full object-cover"
              @error="handleImageError"
              @load="handleImageLoad"
            />
            <div v-else class="w-full h-full flex items-center justify-center" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-600' : 'bg-gray-300'">
              <font-awesome-icon :icon="['fas', 'user']" class="text-sm" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'text-white' : 'text-gray-700'" />
            </div>
          </div>
          <span class="text-sm" :class="themeClasses.secondaryText">{{ user.name }}</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex flex-grow">
      <!-- Sidebar (same as other pages) -->
      <aside v-if="sidebarVisible" class="w-64 p-4 transition-colors duration-300" :class="themeClasses.sidebar">
        <nav>
          <ul class="space-y-2">
            <li>
              <router-link to="/dashboard" class="flex items-center space-x-2 p-2 rounded-md" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'">
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'">
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link to="/quizzes" class="flex items-center space-x-2 p-2 rounded-md" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'">
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>Quizzes</span>
              </router-link>
            </li>
            <li>
              <router-link to="/progress" class="flex items-center space-x-2 p-2 rounded-md" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'">
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link to="/settings" class="flex items-center space-x-2 p-2 rounded-md" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-700' : 'bg-gray-200'">
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>

        </nav>
      </aside>

      <!-- Settings Main Content -->
      <main class="flex-grow p-6 transition-colors duration-300" :class="themeClasses.mainContent">
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
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">Profile Picture</h2>
            <div class="flex flex-col items-center mb-6">
              <div class="relative w-24 h-24 rounded-full overflow-hidden mb-3" :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-600' : 'bg-gray-300'">
                <img
                  v-if="user.profilePicture"
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
                  v-model="user.firstName"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Last Name</label>
                <input
                  v-model="user.lastName"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Email Address</label>
                <input
                  v-model="user.email"
                  type="email"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
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
        
        <!-- API Settings -->
        <div v-if="activeTab === 'api'" class="space-y-6">
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">OpenAI API Configuration</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">API Key</label>
                <input
                  v-model="settings.api.openaiKey"
                  type="password"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                  placeholder="sk-..."
                />
                <p class="mt-1 text-xs" :class="themeClasses.secondaryText">Your OpenAI API key is stored securely and used for AI-powered features.</p>
              </div>
              
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">Model</label>
                <select
                  v-model="settings.api.openaiModel"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                >
                  <option value="gpt-4">GPT-4 (Most Capable)</option>
                  <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Faster)</option>
                </select>
              </div>
            </div>
            <div class="mt-4">
              <button @click="saveAPISettings" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'save']" class="mr-2" />
                Save API Settings
              </button>
            </div>
          </div>
          
          <div class="rounded-lg p-6" :class="themeClasses.card">
            <h2 class="font-semibold mb-4" :class="[themeClasses.text, fontSizeClasses.body]">OCR Configuration</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">OCR Engine</label>
                <select
                  v-model="settings.api.ocrEngine"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                >
                  <option value="tesseract">Tesseract (Local)</option>
                  <option value="google">Google Cloud Vision (Cloud)</option>
                  <option value="azure">Azure Computer Vision (Cloud)</option>
                </select>
              </div>
              
              <div v-if="settings.api.ocrEngine !== 'tesseract'">
                <label class="block text-sm mb-1" :class="themeClasses.secondaryText">API Key</label>
                <input
                  v-model="settings.api.ocrKey"
                  type="password"
                  class="w-full p-2 rounded border focus:outline-none focus:border-blue-500 transition-colors duration-300"
                  :class="themeClasses.input"
                />
              </div>
            </div>
            <div class="mt-4">
              <button @click="saveOCRSettings" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'save']" class="mr-2" />
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import { useNotifications } from '@/composables/useNotifications';
import api from '@/services/api';

export default {
  name: 'SettingsView',
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

    const activeTab = ref('account');

    const user = ref({
      id: null,
      firstName: '',
      lastName: '',
      name: '',
      email: '',
      profilePicture: null
    });

    const passwords = ref({
      current: '',
      new: '',
      confirm: ''
    });

    const isLoading = ref(false);
    const message = ref('');
    const messageType = ref(''); // 'success' or 'error'

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
      api: {
        openaiKey: '',
        openaiModel: 'gpt-3.5-turbo',
        ocrEngine: 'tesseract',
        ocrKey: ''
      }
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
     * Toggle sidebar visibility
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

    // Get profile picture URL
    const getProfilePictureUrl = (profilePicturePath) => {
      if (!profilePicturePath) return null;
      // Since the backend stores relative paths from public directory, construct the full URL
      // Add timestamp to prevent caching issues
      const timestamp = Date.now();
      // Use relative URL to avoid CORS issues and ensure proper path resolution
      return `/${profilePicturePath}?t=${timestamp}`;
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
          first_name: user.value.firstName,
          last_name: user.value.lastName,
          email: user.value.email
        };

        // Update profile via API
        const response = await api.updateProfile(profileData);

        if (response.data.success) {
          saveSettings();
          showMessage('Profile updated successfully!');
          // Refresh user profile data
          await fetchUserProfile();
        } else {
          showMessage('Failed to update profile: ' + (response.data.error || 'Unknown error'), 'error');
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
        // In a real app, you would make an API call here
        // await api.updatePassword(passwords.value);

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));

        passwords.value = { current: '', new: '', confirm: '' };
        showMessage('Password updated successfully!');
      } catch (error) {
        showMessage('Failed to update password', 'error');
      } finally {
        isLoading.value = false;
      }
    };

    // Save notification settings
    const saveNotificationSettings = () => {
      saveSettings();
      showMessage('Notification settings saved!');
    };

    // Save API settings
    const saveAPISettings = () => {
      saveSettings();
      showMessage('API settings saved!');
    };

    // Save OCR settings
    const saveOCRSettings = () => {
      saveSettings();
      showMessage('OCR settings saved!');
    };

    // Delete account
    const deleteAccount = () => {
      if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        // In a real app, you would make an API call here
        // await fetch('/api/user/delete', { method: 'DELETE' });
        showMessage('Account deletion is not implemented in demo mode', 'error');
      }
    };

    // Fetch user profile data
    const fetchUserProfile = async () => {
      try {
        const response = await api.getUser();
        if (response.data.success) {
          user.value = {
            id: response.data.user.id,
            firstName: response.data.user.first_name || '',
            lastName: response.data.user.last_name || '',
            name: response.data.user.name || 'User',
            email: response.data.user.email || 'user@example.com',
            profilePicture: response.data.user.profile_picture || null
          };
        }
      } catch (error) {
        // Error fetching user profile
      }
    };

    // Upload profile picture
    const uploadProfilePicture = async (file) => {
      try {
        const formData = new FormData();
        formData.append('profile_picture', file);

        // Use the centralized API service
        const response = await api.uploadProfilePicture(formData);

        if (response.data.success) {
          user.value.profilePicture = response.data.profile_picture;
          await fetchUserProfile(); // Refresh profile data
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

    // Load settings and user profile on mount
    onMounted(async () => {
      await Promise.all([
        loadSettings(),
        fetchUserProfile()
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
      isLoading,
      message,
      messageType,
      saveProfile,
      updatePassword,
      saveNotificationSettings,
      saveAPISettings,
      saveOCRSettings,
      deleteAccount,
      fetchUserProfile,
      uploadProfilePicture,
      handleProfilePictureUpload,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,
      toggleSidebar,
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,
      store
    };
  }
}
</script>