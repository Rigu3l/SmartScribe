<template>
  <div :class="themeClasses.main" class="min-h-screen flex flex-col">
    <!-- Header -->
    <header :class="themeClasses.header" class="p-4 flex justify-between items-center">
      <div class="text-lg md:text-xl font-bold">SmartScribe</div>
      <div class="flex items-center space-x-2 md:space-x-4">
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
            <span>{{ user?.name || 'User' }}</span>
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
      <!-- Sidebar -->
      <aside :class="themeClasses.sidebar" class="w-64 md:w-64 p-4">
        <nav>
          <ul class="space-y-2">
            <li>
              <router-link to="/dashboard" :class="themeClasses.button" class="flex items-center space-x-2 p-2 rounded-md">
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md" :class="themeClasses.button">
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link to="/progress" class="flex items-center space-x-2 p-2 rounded-md" :class="themeClasses.button">
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link to="/settings" class="flex items-center space-x-2 p-2 rounded-md" :class="themeClasses.button">
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>

      <div
        v-if="showSaveConfirmation"
        class="fixed top-5 right-5 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300"
      >
        ✅ Summary saved successfully!
      </div>

      <!-- Added title input modal -->
      <div v-if="showTitleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
          <h3 class="text-xl font-semibold mb-4">Add Note Title</h3>
          <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Title</label>
            <input 
              v-model="noteTitle" 
              type="text" 
              placeholder="Enter a title for your note..."
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="saveNoteWithTitle"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Preview</label>
            <div class="bg-gray-700 p-3 rounded-md max-h-32 overflow-y-auto text-sm text-gray-300">
              <div v-if="isProcessingFile" class="flex items-center space-x-2">
                <font-awesome-icon :icon="['fas', 'spinner']" class="animate-spin" />
                <span>Processing file...</span>
              </div>
              <div v-else>
                {{ ocrText ? ocrText.substring(0, 200) : 'No text extracted' }}{{ ocrText && ocrText.length > 200 ? '...' : '' }}
              </div>
            </div>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="cancelTitleInput"
              class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="isProcessingFile"
            >
              Cancel
            </button>
            <button
              @click="saveNoteWithTitle"
              class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="!noteTitle.trim() || isProcessingFile"
            >
              <span v-if="isProcessingFile" class="flex items-center space-x-2">
                <font-awesome-icon :icon="['fas', 'spinner']" class="animate-spin" />
                <span>Saving...</span>
              </span>
              <span v-else>Save Note</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-sm mx-4">
          <div class="flex items-center mb-4">
            <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-red-400 text-xl mr-3" />
            <h3 class="text-lg font-medium">Delete Note</h3>
          </div>
          <p class="text-gray-300 mb-6">
            Are you sure you want to delete "<strong>{{ noteToDelete.title }}</strong>"? This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <button @click="closeDeleteModal" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
              Cancel
            </button>
            <button @click="deleteNote" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Profile Modal -->
      <div v-if="showProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-sm mx-4">
          <div class="flex items-center mb-4">
            <font-awesome-icon :icon="['fas', 'user']" class="text-blue-400 text-xl mr-3" />
            <h3 class="text-lg font-medium">User Profile</h3>
          </div>

          <!-- Profile Picture Section -->
          <div class="flex flex-col items-center mb-6">
            <div class="w-24 h-24 rounded-full overflow-hidden mb-3" :class="themeClasses.input">
              <img
                v-if="user && user.profilePicture"
                :key="user.profilePicture"
                :src="getProfilePictureUrl(user.profilePicture)"
                alt="Profile"
                class="w-full h-full object-cover"
                @error="handleImageError"
                @load="handleImageLoad"
              />
              <div v-else class="w-full h-full flex items-center justify-center" :class="themeClasses.input">
                <font-awesome-icon :icon="['fas', 'user']" :class="themeClasses.text" class="text-2xl" />
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

      <!-- Main Dashboard -->
      <main :class="themeClasses.mainContent" class="flex-grow p-4 md:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Dashboard</h1>
          <div class="flex items-center space-x-4">
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
                {{ isConnected ? 'Live Updates' : 'Offline' }}
              </span>
              <span v-if="lastSync" class="text-xs text-gray-500">
                ({{ new Date(lastSync).toLocaleTimeString() }})
              </span>
            </div>

            <!-- Refresh Button -->
            <button
              @click="refreshNotes(); refreshStats(); refreshUser()"
              :disabled="loadingNotes || loadingStats || loadingUser"
              class="flex items-center space-x-1 px-3 py-1 bg-gray-700 rounded-md hover:bg-gray-600 transition disabled:opacity-50"
            >
              <font-awesome-icon
                :icon="['fas', loadingNotes || loadingStats || loadingUser ? 'spinner' : 'sync-alt']"
                :class="loadingNotes || loadingStats || loadingUser ? 'animate-spin' : ''"
                class="text-sm"
              />
              <span class="text-sm">Refresh</span>
            </button>
          </div>
        </div>

        <!-- Dashboard Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
          <div :class="themeClasses.card" class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-lg font-semibold">Total Notes</h3>
              <font-awesome-icon :icon="['fas', 'book']" class="text-blue-500 text-xl" />
            </div>
            <div v-if="loadingDashboard" class="animate-pulse">
              <div class="h-8 bg-gray-700 rounded mb-2"></div>
              <div class="h-4 bg-gray-700 rounded w-3/4"></div>
            </div>
            <div v-else>
              <p class="text-3xl font-bold">{{ stats.totalNotes }}</p>
              <p class="text-sm text-gray-400">{{ stats.notesThisWeek }} new this week</p>
            </div>
          </div>

          <div :class="themeClasses.card" class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-lg font-semibold">Study Time</h3>
              <font-awesome-icon :icon="['fas', 'clock']" class="text-green-500 text-xl" />
            </div>
            <div v-if="loadingDashboard" class="animate-pulse">
              <div :class="themeClasses.card" class="h-8 rounded mb-2"></div>
              <div :class="themeClasses.card" class="h-4 rounded w-3/4"></div>
            </div>
            <div v-else>
              <p class="text-3xl font-bold">{{ stats.studyHours }}h</p>
              <p :class="themeClasses.secondaryText" class="text-sm">{{ stats.studyHoursThisWeek }}h this week</p>
            </div>
          </div>

          <div :class="themeClasses.card" class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-lg font-semibold">Quiz Score</h3>
              <font-awesome-icon :icon="['fas', 'check-circle']" class="text-yellow-500 text-xl" />
            </div>
            <div v-if="loadingDashboard" class="animate-pulse">
              <div :class="themeClasses.card" class="h-8 rounded mb-2"></div>
              <div :class="themeClasses.card" class="h-4 rounded w-3/4"></div>
            </div>
            <div v-else>
              <p class="text-3xl font-bold">{{ stats.quizAverage }}%</p>
              <p :class="themeClasses.secondaryText" class="text-sm">{{ stats.quizzesCompleted }} quizzes completed</p>
            </div>
          </div>

          <div :class="themeClasses.card" class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-lg font-semibold">Goals</h3>
              <font-awesome-icon :icon="['fas', 'bullseye']" class="text-purple-500 text-xl" />
            </div>
            <div v-if="loadingDashboard" class="animate-pulse">
              <div :class="themeClasses.card" class="h-8 rounded mb-2"></div>
              <div :class="themeClasses.card" class="h-4 rounded w-3/4"></div>
            </div>
            <div v-else>
              <p class="text-3xl font-bold">{{ stats.activeGoals }}</p>
              <p :class="themeClasses.secondaryText" class="text-sm">{{ stats.completedGoals }} completed this month</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div :class="themeClasses.card" class="rounded-lg p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            <button @click="openCamera" :class="themeClasses.button" class="flex flex-col items-center p-4 rounded-lg transition">
              <font-awesome-icon :icon="['fas', 'camera']" class="text-2xl text-blue-500 mb-2" />
              <span class="text-sm font-medium">Scan Note</span>
            </button>

            <button @click="createNewNote" :class="themeClasses.button" class="flex flex-col items-center p-4 rounded-lg transition">
              <font-awesome-icon :icon="['fas', 'plus']" class="text-2xl text-green-500 mb-2" />
              <span class="text-sm font-medium">New Note</span>
            </button>

            <router-link to="/progress" :class="themeClasses.button" class="flex flex-col items-center p-4 rounded-lg transition">
              <font-awesome-icon :icon="['fas', 'chart-line']" class="text-2xl text-purple-500 mb-2" />
              <span class="text-sm font-medium">View Progress</span>
            </router-link>

            <button @click="openSettings" :class="themeClasses.button" class="flex flex-col items-center p-4 rounded-lg transition">
              <font-awesome-icon :icon="['fas', 'cog']" class="text-2xl text-yellow-500 mb-2" />
              <span class="text-sm font-medium">Settings</span>
            </button>
          </div>
        </div>

        <!-- Scan New Notes Section -->
        <div :class="themeClasses.card" class="rounded-lg p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">Scan New Notes</h2>
          <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center">
            <div class="mb-4">
              <font-awesome-icon :icon="['fas', 'camera']" class="text-4xl text-gray-400" />
            </div>
            <p class="mb-4 text-gray-400">Take a picture or upload your notes (images with OCR text extraction)</p>
            <div class="flex justify-center space-x-4">
              <button @click="openCamera" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'camera']" class="mr-2" /> Take Photo
              </button>
              <label :class="[
                'px-4 py-2 rounded-md transition cursor-pointer flex items-center space-x-2',
                isProcessingFile
                  ? 'bg-gray-600 cursor-not-allowed opacity-50'
                  : 'bg-gray-700 hover:bg-gray-600'
              ]">
                <font-awesome-icon v-if="isProcessingFile" :icon="['fas', 'spinner']" class="animate-spin" />
                <font-awesome-icon v-else :icon="['fas', 'upload']" />
                <span>{{ isProcessingFile ? 'Processing...' : 'Upload Image' }}</span>
                <input
                  type="file"
                  class="hidden"
                  @change="handleFileUpload"
                  accept="image/*"
                  :disabled="isProcessingFile"
                />
              </label>
            </div>

            <!-- Add this below the buttons -->
            <div v-if="showCamera" class="mt-6">
              <video ref="video" autoplay class="w-full rounded-md border border-gray-600"></video>
              <div class="flex justify-center space-x-4 mt-4">
                <button @click="capturePhoto" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">
                  <font-awesome-icon :icon="['fas', 'camera-retro']" class="mr-2" /> Capture
                </button>
                <button @click="closeCamera" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 transition">
                  <font-awesome-icon :icon="['fas', 'times']" class="mr-2" /> Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Notes Section -->
        <div :class="themeClasses.card" class="rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <font-awesome-icon :icon="['fas', 'clock']" class="text-white text-sm" />
              </div>
              <h2 class="text-xl font-semibold">Recent Notes</h2>
            </div>
            <router-link to="/notes" class="flex items-center space-x-2 text-blue-400 hover:text-blue-300 transition-colors duration-200">
              <span class="text-sm font-medium">View All</span>
              <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-xs" />
            </router-link>
          </div>

          <!-- Loading state -->
          <div v-if="loadingNotes" class="text-center py-12">
            <div class="inline-flex items-center space-x-3">
              <font-awesome-icon :icon="['fas', 'spinner']" class="animate-spin text-2xl text-blue-400" />
              <p class="text-gray-400">Loading your recent notes...</p>
            </div>
          </div>

          <!-- Notes list -->
          <div v-else-if="recentNotes.length > 0" class="space-y-4">
            <div v-for="(note, index) in recentNotes" :key="note.id || index"
                 class="group relative bg-gradient-to-r from-gray-800 to-gray-750 rounded-xl p-5 border border-gray-700 hover:border-gray-600 transition-all duration-300 hover:shadow-lg hover:shadow-gray-900/20 transform hover:-translate-y-0.5">
              <!-- Note header -->
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-start space-x-4 flex-grow cursor-pointer" @click="viewNote(note.id)">
                  <!-- Note icon -->
                  <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                    <font-awesome-icon :icon="['fas', 'file-alt']" class="text-white text-sm" />
                  </div>

                  <!-- Note content -->
                  <div class="flex-grow min-w-0">
                    <h3 class="font-semibold text-white group-hover:text-blue-400 transition-colors duration-200 truncate">
                      {{ note.title }}
                    </h3>
                    <div class="flex items-center space-x-3 mt-1">
                      <div class="flex items-center space-x-1 text-gray-400 text-xs">
                        <font-awesome-icon :icon="['fas', 'clock']" />
                        <span>{{ getTimeAgo(note.createdAt) }}</span>
                      </div>
                      <div class="flex items-center space-x-1 text-gray-400 text-xs">
                        <font-awesome-icon :icon="['fas', 'align-left']" />
                        <span>{{ note.wordCount }} words</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                  <button @click.stop="editNote(note.id)"
                          class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all duration-200"
                          title="Edit note">
                    <font-awesome-icon :icon="['fas', 'edit']" class="text-sm" />
                  </button>
                  <button @click.stop="confirmDelete(note.id, note.title)"
                          class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200"
                          title="Delete note">
                    <font-awesome-icon :icon="['fas', 'trash']" class="text-sm" />
                  </button>
                </div>
              </div>

              <!-- Note preview -->
              <div class="ml-14">
                <p class="text-sm text-gray-300 line-clamp-2 leading-relaxed">{{ note.summary }}</p>

                <!-- Tags and metadata -->
                <div class="flex items-center justify-between mt-4">
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(tag, tagIndex) in note.tags" :key="tagIndex"
                          class="px-3 py-1 bg-gradient-to-r from-gray-700 to-gray-600 text-gray-300 rounded-full text-xs font-medium border border-gray-600">
                      {{ tag }}
                    </span>
                    <span v-if="note.tags.length === 0"
                          class="px-3 py-1 bg-gray-700/50 text-gray-500 rounded-full text-xs">
                      No tags
                    </span>
                  </div>

                  <!-- Reading time indicator -->
                  <div class="flex items-center space-x-1 text-gray-500 text-xs">
                    <font-awesome-icon :icon="['fas', 'eye']" />
                    <span>{{ note.readingTime }} min read</span>
                  </div>
                </div>
              </div>

              <!-- Hover overlay effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </div>
          </div>

          <!-- Empty state -->
          <div v-else class="text-center py-12">
            <div class="max-w-md mx-auto">
              <div class="w-20 h-20 bg-gradient-to-br from-gray-700 to-gray-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <font-awesome-icon :icon="['fas', 'book-open']" class="text-3xl text-gray-400" />
              </div>
              <h3 class="text-lg font-semibold text-white mb-2">No notes yet</h3>
              <p class="text-gray-400 mb-6 leading-relaxed">
                Start building your knowledge base by scanning your first note, uploading images with OCR text extraction, or creating one from scratch.
              </p>
              <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button @click="openCamera"
                        class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all duration-200 transform hover:scale-105">
                  <font-awesome-icon :icon="['fas', 'camera']" />
                  <span>Scan Note</span>
                </button>
                <button @click="createNewNote"
                        class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-gray-700 to-gray-600 hover:from-gray-600 hover:to-gray-500 text-white rounded-lg transition-all duration-200">
                  <font-awesome-icon :icon="['fas', 'plus']" />
                  <span>Create Note</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import Tesseract from 'tesseract.js';
import { nextTick } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useUserProfile } from '@/composables/useUserProfile';
import api from '@/services/api';

export default {
  name: 'DashboardView',
  setup() {
    const router = useRouter();
    const store = useStore();

    // =====================================
    // SIMPLE DATA MANAGEMENT
    // =====================================

    // Simple reactive data
    const notesResponse = ref(null);
    const statsResponse = ref(null);
    const loadingNotes = ref(false);
    const loadingStats = ref(false);
    const loadingDashboard = ref(false);
    const isConnected = ref(true);
    const connectionStatus = ref('connected');
    const lastSync = ref(new Date());

    // =====================================
    // NOTIFICATION SYSTEM
    // =====================================
    const {
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,
      showSuccess,
      showInfo,
      showWarning
    } = useNotifications();

    // =====================================
    // USER PROFILE SYSTEM
    // =====================================
    const {
      user: userProfile,
      loading: loadingUser,
      loadUserProfile
    } = useUserProfile();

    // =====================================
    // COMPUTED PROPERTIES
    // =====================================

    // Process notes data
    const recentNotes = computed(() => {
      if (!notesResponse.value?.data) return [];

      return notesResponse.value.data.slice(0, 5).map(note => {
        const createdDate = new Date(note.created_at);
        const summary = note.original_text ? note.original_text.substring(0, 100) + (note.original_text.length > 100 ? '...' : '') : 'No content';

        return {
          id: note.id,
          title: note.title || 'Untitled Note',
          summary: summary,
          date: createdDate.toLocaleDateString(),
          createdAt: createdDate, // Keep original Date object for time calculations
          timestamp: createdDate.getTime(), // For precise sorting
          tags: [],
          wordCount: getWordCount(summary),
          readingTime: getReadingTime(summary)
        };
      });
    });

    // Process stats data
    const stats = computed(() => {
      if (!statsResponse.value?.data) {
        return {
          totalNotes: 0,
          notesThisWeek: 0,
          studyHours: 0,
          studyHoursThisWeek: 0,
          quizAverage: 0,
          quizzesCompleted: 0,
          activeGoals: 0,
          completedGoals: 0
        };
      }

      const statsData = statsResponse.value.data;
      const result = {
        totalNotes: statsData.totalNotes || 0,
        notesThisWeek: statsData.notesThisWeek || 0,
        studyHours: statsData.studyHours || 0,
        studyHoursThisWeek: statsData.studyHoursThisWeek || 0,
        quizAverage: statsData.quizAverage || 0,
        quizzesCompleted: statsData.quizzesCompleted || 0,
        activeGoals: 0,
        completedGoals: 0
      };

      return result;
    });

    // Use user from composable
    const user = userProfile;

    // Use global theme classes from store with computed property
    const themeClasses = computed(() => {
      try {
        // Check if store and getters are available
        if (!store || !store.getters) {
          return {
            main: 'bg-gray-900 text-white',
            header: 'bg-gray-800',
            sidebar: 'bg-gray-800',
            mainContent: '',
            card: 'bg-gray-800',
            text: 'text-white',
            secondaryText: 'text-gray-400',
            input: 'bg-gray-700 border-gray-600 text-white',
            button: 'bg-gray-700 hover:bg-gray-600'
          };
        }

        const classes = store.getters['app/getThemeClasses'];
        return classes && typeof classes === 'object' ? classes : {
          main: 'bg-gray-900 text-white',
          header: 'bg-gray-800',
          sidebar: 'bg-gray-800',
          mainContent: '',
          card: 'bg-gray-800',
          text: 'text-white',
          secondaryText: 'text-gray-400',
          input: 'bg-gray-700 border-gray-600 text-white',
          button: 'bg-gray-700 hover:bg-gray-600'
        };
      } catch (error) {
        return {
          main: 'bg-gray-900 text-white',
          header: 'bg-gray-800',
          sidebar: 'bg-gray-800',
          mainContent: '',
          card: 'bg-gray-800',
          text: 'text-white',
          secondaryText: 'text-gray-400',
          input: 'bg-gray-700 border-gray-600 text-white',
          button: 'bg-gray-700 hover:bg-gray-600'
        };
      }
    });

    // =====================================
    // UI STATE
    // =====================================
    const showUserMenu = ref(false);
    const showProfileModal = ref(false);
    const showCamera = ref(false);
    const video = ref(null);

    // Note creation state
    const ocrText = ref('');
    const isProcessingFile = ref(false);
    const showSaveConfirmation = ref(false);
    const showTitleModal = ref(false);
    const noteTitle = ref('');
    const pendingImageData = ref(null);

    // Note deletion state
    const showDeleteModal = ref(false);
    const noteToDelete = ref(null);

    // =====================================
    // API FUNCTIONS
    // =====================================

    /**
     * Upload profile picture to server
     */
    const uploadProfilePicture = async (file) => {
      try {
        const formData = new FormData();
        formData.append('profile_picture', file);

        // Use the centralized API service
        const response = await api.uploadProfilePicture(formData);

        if (response.data.success) {
          // Refresh user profile after successful upload
          await refreshUser();
          return { success: true, message: response.data.message };
        } else {
          return { success: false, message: response.data.error };
        }
      } catch (error) {
        return { success: false, message: 'Network error' };
      }
    };

    /**
     * Handle profile picture file selection and upload
     */
    const handleProfilePictureUpload = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        showWarning('Invalid file type', 'Please select a valid image file (JPEG, PNG, GIF, or WebP)');
        return;
      }

      // Validate file size (max 5MB)
      const maxSize = 5 * 1024 * 1024;
      if (file.size > maxSize) {
        showWarning('File too large', 'File size must be less than 5MB');
        return;
      }

      try {
        const result = await uploadProfilePicture(file);
        if (result.success) {
          showSuccess('Profile picture updated', result.message);
        } else {
          showWarning('Upload failed', result.message);
        }
      } catch (error) {
        showWarning('Upload error', 'Network error occurred');
      }

      // Reset file input
      event.target.value = '';
    };

    // =====================================
    // USER INTERACTION FUNCTIONS
    // =====================================

    /**
     * Quick action: Create new note
     */
    const createNewNote = () => {
      router.push('/notes/edit');
    };

    /**
     * Quick action: Open settings
     */
    const openSettings = () => {
      router.push('/settings');
    };

    // =====================================
    // SIMPLE API FUNCTIONS
    // =====================================

    // Fetch notes from API
    const fetchNotes = async () => {
      try {
        loadingNotes.value = true;
        const response = await api.getNotes();
        notesResponse.value = response.data;
      } catch (error) {
        // Error fetching notes
      } finally {
        loadingNotes.value = false;
      }
    };

    // Fetch dashboard stats from API
    const fetchStats = async () => {
      try {
        loadingDashboard.value = true;
        const response = await api.getDashboardStats();
        statsResponse.value = response.data;
      } catch (error) {
        // Error fetching dashboard stats
      } finally {
        loadingDashboard.value = false;
      }
    };


    // Refresh functions
    const refreshNotes = async () => {
      await fetchNotes();
    };

    const refreshStats = async () => {
      await fetchStats();
    };

    const refreshUser = async () => {
      await loadUserProfile();
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

    // Format date as "time ago" for recent notes
    const getTimeAgo = (date) => {
      try {
        // Ensure date is a valid Date object
        const dateObj = date instanceof Date ? date : new Date(date);
        if (isNaN(dateObj.getTime())) {
          return 'Invalid date';
        }

        const now = new Date();
        const diffInSeconds = Math.floor((now - dateObj) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;

        return dateObj.toLocaleDateString();
      } catch (error) {
        return 'Date error';
      }
    };

    // Utility functions for note metadata
    const getWordCount = (text) => {
      if (!text || typeof text !== 'string') return 0;
      return text.trim().split(/\s+/).filter(word => word.length > 0).length;
    };

    const getReadingTime = (text) => {
      const wordsPerMinute = 200; // Average reading speed
      const wordCount = getWordCount(text);
      const minutes = Math.ceil(wordCount / wordsPerMinute);
      return Math.max(1, minutes); // Minimum 1 minute
    };

    // =====================================
    // LIFECYCLE HOOKS
    // =====================================

    onMounted(async () => {
      // Fetch initial data
      await Promise.all([
        fetchNotes(),
        fetchStats(),
        loadUserProfile()
      ]);

      // Add welcome notification
      setTimeout(() => {
        showSuccess('Welcome to SmartScribe!', 'Your dashboard is ready.');
      }, 1000);
    });

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
        // Error logging out
      }
    };

    // =====================================
    // CAMERA & OCR FUNCTIONS
    // =====================================

    /**
     * Open camera for note scanning
     */
    const openCamera = async () => {
      showCamera.value = true;

      await nextTick(); // Wait for the video element to exist

      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        if (video.value) {
          video.value.srcObject = stream;
        }
      } catch (err) {
        // Camera access denied or error
      }
    };

    /**
     * Close camera and stop video stream
     */
    const closeCamera = () => {
      showCamera.value = false;
      const stream = video.value.srcObject;
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
      }
      video.value.srcObject = null;
    };

    /**
     * Capture photo from camera and perform OCR
     */
    const capturePhoto = () => {
      const canvas = document.createElement('canvas');
      canvas.width = video.value.videoWidth;
      canvas.height = video.value.videoHeight;
      canvas.getContext('2d').drawImage(video.value, 0, 0);

      canvas.toBlob(async (blob) => {
        try {
          const { data: { text } } = await Tesseract.recognize(blob, 'eng', {
            logger: () => {} // Disable logging
          });

          ocrText.value = text;
          pendingImageData.value = { type: 'capture', data: text };
          closeCamera();

          showTitleModal.value = true;
          noteTitle.value = ''; // Reset title input

        } catch (error) {
          // OCR error
        }
      }, 'image/jpeg');
    };

    /**
     * Handle image file upload and perform OCR
     */
    const handleFileUpload = async (event) => {
      const file = event.target.files[0];
      if (!file) {
        return;
      }

      const MAX_FILE_SIZE = 5 * 1024 * 1024;
      if (file.size > MAX_FILE_SIZE) {
        alert('File is too large!');
        return;
      }

      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, PNG, GIF, and WebP images are allowed!');
        return;
      }

      isProcessingFile.value = true;

      try {
        // Process images with OCR
        const { data: { text } } = await Tesseract.recognize(file, 'eng', {
          logger: () => {} // Disable logging
        });

        ocrText.value = text;
        pendingImageData.value = { type: 'upload', file: file, text: text };

        showTitleModal.value = true;
        noteTitle.value = ''; // Reset title input

      } catch (error) {
        // Error processing file
        alert('Error processing file. Please try again.');
      } finally {
        isProcessingFile.value = false;
      }

      event.target.value = '';
    };

    // =====================================
    // NOTE MANAGEMENT FUNCTIONS
    // =====================================

    /**
     * Save note with title after OCR processing
     */
    const saveNoteWithTitle = async () => {
      if (!noteTitle.value.trim()) {
        return;
      }

      try {
        const noteData = {
          title: noteTitle.value.trim(),
          text: pendingImageData.value.type === 'upload' ? pendingImageData.value.text : pendingImageData.value.data,
          ...(pendingImageData.value.type === 'upload' && pendingImageData.value.file && { image: pendingImageData.value.file })
        };

        // Check if file is valid
        if (noteData.image && !(noteData.image instanceof File)) {
          alert('File upload error: Invalid file object');
          return;
        }

        try {
          // Create note using simple API call
          await api.createNote(noteData);

          showSuccess('Note saved', 'Your note has been created successfully.');

          // Refresh notes and stats after successful creation
          await Promise.all([
            refreshNotes(),
            refreshStats()
          ]);
        } catch (error) {
          showWarning('Save failed', 'Failed to save the note. Please try again.');
        }

        showTitleModal.value = false;
        noteTitle.value = '';
        pendingImageData.value = null;
        ocrText.value = '';

      } catch (error) {
        // Error saving note
      }
    };

    /**
     * Cancel note title input
     */
    const cancelTitleInput = () => {
      showTitleModal.value = false;
      noteTitle.value = '';
      pendingImageData.value = null;
      ocrText.value = '';
    };

    /**
     * Show save confirmation message
     */
    const showConfirmation = () => {
      showSaveConfirmation.value = true;
      setTimeout(() => {
        showSaveConfirmation.value = false;
      }, 3000);
    };

    /**
     * Navigate to view note details
     */
    const viewNote = (noteId) => {
      router.push(`/notes/${noteId}`);
    };

    /**
     * Navigate to edit note
     */
    const editNote = (noteId) => {
      router.push(`/notes/edit?id=${noteId}`);
    };

    /**
     * Confirm note deletion
     */
    const confirmDelete = (noteId, noteTitle) => {
      noteToDelete.value = { id: noteId, title: noteTitle };
      showDeleteModal.value = true;
    };

    /**
     * Close delete confirmation modal
     */
    const closeDeleteModal = () => {
      showDeleteModal.value = false;
      noteToDelete.value = null;
    };

    /**
     * Delete note from server and update UI
     */
    const deleteNote = async () => {
      if (!noteToDelete.value) return;

      try {
        await api.deleteNote(noteToDelete.value.id);
        // Remove the note from the recent notes list
        recentNotes.value = recentNotes.value.filter(note => note.id !== noteToDelete.value.id);
        // Refresh the notes list
        await refreshNotes();
      } catch (error) {
        // Error deleting note
      }

      closeDeleteModal();
    };

    return {
      // UI State
      showUserMenu,
      showProfileModal,
      showCamera,
      video,
      showSaveConfirmation,
      showTitleModal,
      noteTitle,
      pendingImageData,
      showDeleteModal,
      noteToDelete,
      isProcessingFile,

      // Data
      user,
      recentNotes,
      stats,

      // Loading states
      loadingNotes,
      loadingStats,
      loadingDashboard,
      loadingUser,

      // Connection status
      isConnected,
      connectionStatus,
      lastSync,

      // User interaction functions
      toggleUserMenu,
      closeUserMenu,
      openProfileModal,
      closeProfileModal,
      logout,
      openCamera,
      closeCamera,
      capturePhoto,
      handleFileUpload,
      handleProfilePictureUpload,
      saveNoteWithTitle,
      cancelTitleInput,
      showConfirmation,
      viewNote,
      editNote,
      confirmDelete,
      closeDeleteModal,
      deleteNote,
      createNewNote,
      openSettings,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,

      // Notification functions
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      markAsRead,
      markAllAsRead,
      closeNotifications,
      showSuccess,
      showInfo,
      showWarning,

      // Refresh functions
      refreshNotes,
      refreshStats,
      refreshUser,

      // User profile functions
      loadUserProfile,

      // Utility functions
      getTimeAgo,
      getWordCount,
      getReadingTime,

      // OCR data
      ocrText,
  
      // Theme classes
      themeClasses,
  
      // Store for theme access
      store
    };
  }
}
</script>

