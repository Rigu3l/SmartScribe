<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white overflow-x-hidden">
    <!-- Header -->
    <header :class="themeClasses.header" class="p-4 flex justify-between items-center">
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
        <div class="text-lg md:text-xl font-bold">SmartScribe</div>
      </div>
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
    <div class="flex flex-grow transition-all duration-300">
      <!-- Mobile Menu Button -->
      <button
        @click="sidebarOpen = !sidebarOpen"
        class="md:hidden fixed top-20 left-4 z-50 bg-gray-800 p-2 rounded-md shadow-lg"
      >
        <span v-if="!sidebarOpen" class="text-lg font-bold">☰</span>
        <font-awesome-icon v-else :icon="['fas', 'times']" />
      </button>

      <!-- Sidebar Overlay for Mobile -->
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
      ></div>

      <!-- Sidebar -->
      <aside v-if="sidebarVisible" class="w-64 p-4 transition-all duration-300 ease-in-out" :class="themeClasses.sidebar">
        <nav>
          <ul class="space-y-2">
            <li>
              <router-link
                to="/dashboard"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md"
                :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'"
              >
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/notes"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md"
                :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'"
              >
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/quizzes"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md"
                :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'bg-gray-700' : 'bg-gray-200'"
              >
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>Quizzes</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/progress"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md"
                :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'"
              >
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/settings"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md"
                :class="store.getters['app/getCurrentTheme'] === 'dark' ? 'hover:bg-gray-700' : 'hover:bg-gray-200'"
              >
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>

        </nav>
      </aside>
      <!-- Quiz Main Content -->
      <main class="flex-1 p-4 sm:p-6 ml-0 md:ml-0 transition-all duration-300 ease-in-out" style="width: 100vw; max-width: 100vw;">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-3xl sm:text-4xl text-blue-500" />
        </div>

        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
          <font-awesome-icon :icon="['fas', 'times']" class="text-4xl text-red-400 mb-4" />
          <h2 class="text-xl font-medium mb-2">Error Loading Quiz</h2>
          <p class="text-gray-400 mb-4">{{ error }}</p>
          <router-link to="/notes" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
            Back to Notes
          </router-link>
        </div>

        <div v-else>
          <!-- Quiz Management Header -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">Quizzes</h1>
                <p class="text-gray-400">Create and manage your study quizzes</p>
              </div>
              <div class="flex space-x-3 mt-4 sm:mt-0">
                <button @click="openNoteSelector" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition" :disabled="isGeneratingQuiz">
                  <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" />
                  {{ isGeneratingQuiz ? 'Generating...' : 'Create New Quiz' }}
                </button>
                <router-link to="/notes" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                  <font-awesome-icon :icon="['fas', 'angle-left']" class="mr-2" />
                  Back to Notes
                </router-link>
              </div>
            </div>
          </div>

          <!-- Active Quiz Section -->
          <div v-if="quizQuestions.length > 0" class="bg-gray-800 rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold">Active Quiz</h3>
              <div class="flex space-x-2">
                <button @click="resetQuiz" class="px-3 py-1 bg-gray-600 rounded-md hover:bg-gray-700 transition text-sm">
                  <font-awesome-icon :icon="['fas', 'undo']" class="mr-1" />
                  Reset
                </button>
                <button @click="saveQuiz" :disabled="isSavingQuiz" class="px-3 py-1 bg-blue-600 rounded-md hover:bg-blue-700 transition text-sm disabled:opacity-50">
                  <font-awesome-icon :icon="['fas', 'save']" class="mr-1" :spin="isSavingQuiz" />
                  {{ isSavingQuiz ? 'Saving...' : 'Save Quiz' }}
                </button>
              </div>
            </div>

            <!-- Quiz Info -->
            <div class="mb-6">
              <div class="mb-4">
                <h4 class="font-semibold text-white text-lg mb-2">{{ quizTitle || 'Active Quiz' }}</h4>
                <div class="text-sm text-gray-400">
                  <span class="text-xs">Active Quiz Session</span>
                </div>
              </div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-400">Progress</span>
                <span class="text-sm text-gray-400">{{ answeredQuestionsCount }} / {{ quizQuestions.length }} answered</span>
              </div>
              <div class="w-full bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                     :style="{ width: `${(answeredQuestionsCount / quizQuestions.length) * 100}%` }"></div>
              </div>
            </div>

            <!-- Quiz Questions -->
            <div class="space-y-6">
              <div v-for="(question, index) in quizQuestions" :key="index" class="bg-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-white mb-4">{{ index + 1 }}. {{ question.text }}</h4>

                <div class="space-y-2">
                  <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                       class="flex items-center space-x-3 p-3 rounded-md cursor-pointer transition-all"
                       :class="{
                         'bg-blue-600 text-white': question.selectedAnswer === optionIndex,
                         'bg-gray-600 hover:bg-gray-500': question.selectedAnswer !== optionIndex
                       }"
                       @click="question.selectedAnswer = optionIndex">
                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                         :class="{
                           'border-white bg-blue-600': question.selectedAnswer === optionIndex,
                           'border-gray-400': question.selectedAnswer !== optionIndex
                         }">
                      <div v-if="question.selectedAnswer === optionIndex" class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="text-sm">{{ option }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quiz Actions -->
            <div class="mt-6 flex justify-center space-x-4">
              <button @click="toggleShowAnswers" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'eye']" class="mr-2" />
                {{ showCorrectAnswers ? 'Hide Answers' : 'Show Answers' }}
              </button>
              <button @click="checkAnswers" :disabled="!allQuestionsAnswered"
                      class="px-6 py-3 bg-green-600 rounded-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                <font-awesome-icon :icon="['fas', 'check']" class="mr-2" />
                Check Answers
              </button>
            </div>

            <!-- Immediate Answer Feedback (when showCorrectAnswers is true) -->
            <div v-if="showCorrectAnswers" class="mt-6 bg-gray-700 rounded-lg p-4">
              <h4 class="font-semibold mb-4 text-white">📚 Answer Key</h4>
              <div class="space-y-3">
                <div v-for="(question, index) in quizQuestions" :key="index"
                     class="bg-gray-600 rounded-lg p-3">
                  <div class="flex items-start space-x-3 mb-2">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-gray-500 text-white">
                      {{ index + 1 }}
                    </div>
                    <div class="flex-1">
                      <p class="text-sm text-white font-medium mb-2">{{ question.text }}</p>

                      <div class="space-y-1">
                        <div v-for="(option, optionIndex) in question.originalOptions" :key="optionIndex"
                             class="flex items-center space-x-2 text-xs">
                          <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                               :class="{
                                 'border-green-400 bg-green-400': optionIndex === question.originalCorrectIndex,
                                 'border-gray-400': optionIndex !== question.originalCorrectIndex
                               }">
                            <div v-if="optionIndex === question.originalCorrectIndex"
                                 class="w-2 h-2 rounded-full bg-white"></div>
                          </div>
                          <span :class="{
                            'text-green-300 font-medium': optionIndex === question.originalCorrectIndex,
                            'text-gray-300': optionIndex !== question.originalCorrectIndex
                          }">
                            {{ option }}
                            <span v-if="optionIndex === question.originalCorrectIndex" class="text-green-400 font-bold ml-1">(Correct Answer)</span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quiz Results Section -->
          <div v-if="quizCompleted" class="bg-gray-800 rounded-lg p-6 mb-6">
            <div class="text-center mb-6">
              <h3 class="text-2xl font-bold mb-2">Quiz Results</h3>
              <div class="text-6xl font-bold mb-4" :class="quizScore / quizQuestions.length >= 0.8 ? 'text-green-400' : quizScore / quizQuestions.length >= 0.6 ? 'text-yellow-400' : 'text-red-400'">
                {{ Math.round((quizScore / quizQuestions.length) * 100) }}%
              </div>
              <p class="text-gray-400">{{ quizScore }} out of {{ quizQuestions.length }} correct</p>
            </div>

            <!-- Performance Analysis -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-green-400">{{ quizScore }}</div>
                <div class="text-sm text-gray-400">Correct</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-red-400">{{ quizQuestions.length - quizScore }}</div>
                <div class="text-sm text-gray-400">Incorrect</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-400">{{ Math.round((quizScore / quizQuestions.length) * 100) }}%</div>
                <div class="text-sm text-gray-400">Accuracy</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-400">{{ quizQuestions.length }}</div>
                <div class="text-sm text-gray-400">Total Questions</div>
              </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="bg-gray-700 rounded-lg p-4 mb-6">
              <h4 class="font-semibold mb-3 text-white">📊 Detailed Performance</h4>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-400">Questions Answered:</span>
                  <span class="text-white ml-2">{{ quizQuestions.length }}</span>
                </div>
                <div>
                  <span class="text-gray-400">Time Spent:</span>
                  <span class="text-white ml-2">N/A</span>
                </div>
                <div>
                  <span class="text-gray-400">Average per Question:</span>
                  <span class="text-white ml-2">{{ Math.round((quizScore / quizQuestions.length) * 100) }}%</span>
                </div>
                <div>
                  <span class="text-gray-400">Performance Level:</span>
                  <span class="text-white ml-2" :class="quizScore / quizQuestions.length >= 0.8 ? 'text-green-400' : quizScore / quizQuestions.length >= 0.6 ? 'text-yellow-400' : 'text-red-400'">
                    {{ quizScore / quizQuestions.length >= 0.8 ? 'Excellent' : quizScore / quizQuestions.length >= 0.6 ? 'Good' : 'Needs Improvement' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Question Review -->
            <div class="bg-gray-700 rounded-lg p-4 mb-6">
              <h4 class="font-semibold mb-4 text-white">📝 Question Review</h4>
              <div class="space-y-4 max-h-96 overflow-y-auto">
                <div v-for="(question, index) in quizQuestions" :key="index"
                     class="bg-gray-600 rounded-lg p-3">
                  <div class="flex items-start space-x-3 mb-2">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                         :class="question.selectedAnswer === question.correctAnswer ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                      {{ index + 1 }}
                    </div>
                    <div class="flex-1">
                      <p class="text-sm text-white font-medium mb-2">{{ question.text }}</p>

                      <div class="space-y-1">
                        <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                             class="flex items-center space-x-2 text-xs">
                          <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                               :class="{
                                 'border-green-400 bg-green-400': optionIndex === question.correctAnswer,
                                 'border-red-400 bg-red-400': optionIndex === question.selectedAnswer && optionIndex !== question.correctAnswer,
                                 'border-gray-400': optionIndex !== question.correctAnswer && optionIndex !== question.selectedAnswer
                               }">
                            <div v-if="optionIndex === question.correctAnswer || optionIndex === question.selectedAnswer"
                                 class="w-2 h-2 rounded-full bg-white"></div>
                          </div>
                          <span :class="{
                            'text-green-300 font-medium': optionIndex === question.correctAnswer,
                            'text-red-300': optionIndex === question.selectedAnswer && optionIndex !== question.correctAnswer,
                            'text-gray-300': optionIndex !== question.correctAnswer && optionIndex !== question.selectedAnswer
                          }">
                            {{ option }}
                            <span v-if="optionIndex === question.correctAnswer" class="text-green-400 font-bold ml-1">(Correct)</span>
                            <span v-if="optionIndex === question.selectedAnswer && optionIndex !== question.correctAnswer" class="text-red-400 font-bold ml-1">(Your Answer)</span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Improvement Advice -->
            <div class="bg-gray-700 rounded-lg p-4 mb-6">
              <h4 class="font-semibold mb-3 text-white">📚 Study Recommendations</h4>
              <div class="space-y-2">
                <div v-for="advice in improvementAdvice" :key="advice.id" class="flex items-start space-x-3">
                  <font-awesome-icon :icon="advice.icon" class="text-blue-400 mt-1" />
                  <div>
                    <div class="font-medium text-white">{{ advice.title }}</div>
                    <div class="text-sm text-gray-300">{{ advice.description }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Results Actions -->
            <div class="flex justify-center space-x-4">
              <button @click="retakeQuiz" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'redo']" class="mr-2" />
                Retake Quiz
              </button>
              <button @click="resetQuiz" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                <font-awesome-icon :icon="['fas', 'times']" class="mr-2" />
                Close Results
              </button>
            </div>
          </div>


          <!-- Saved Quizzes Section -->
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold">Your Quizzes</h3>
              <div class="flex space-x-2">
                <button @click="debugQuizStatus" class="text-gray-400 hover:text-gray-300 text-sm">
                  <font-awesome-icon :icon="['fas', 'info-circle']" class="mr-1" />
                  Debug
                </button>
                <button @click="loadSavedQuizzes" class="text-blue-400 hover:text-blue-300 text-sm">
                  <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-1" />
                  Refresh
                </button>
              </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoadingSavedQuizzes" class="flex justify-center py-8">
              <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-2xl text-blue-500" />
              <span class="ml-2 text-gray-400">Loading your quizzes...</span>
            </div>

            <!-- Empty State -->
            <div v-else-if="savedQuizzes.length === 0" class="text-center py-12 text-gray-400">
              <font-awesome-icon :icon="['fas', 'book-open']" class="text-6xl mb-6" />
              <h3 class="text-xl font-semibold mb-4">No Quizzes Yet</h3>
              <p class="text-gray-400 mb-6">Create your first quiz to start studying!</p>
              <button @click="openNoteSelector" class="px-6 py-3 bg-blue-600 rounded-md hover:bg-blue-700 transition" :disabled="isGeneratingQuiz">
                <font-awesome-icon :icon="['fas', 'plus']" class="mr-2" />
                {{ isGeneratingQuiz ? 'Generating...' : 'Create Your First Quiz' }}
              </button>
            </div>

            <!-- Saved Quizzes List -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="quiz in savedQuizzes"
                :key="quiz.id"
                class="bg-gray-700 rounded-lg p-5 hover:bg-gray-600 transition-all duration-200 cursor-pointer transform hover:scale-105 relative"
                @click="startQuiz(quiz.id)"
              >
                <!-- Completion Status Indicator -->
                <div v-if="quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0" class="absolute top-2 right-2 w-3 h-3 bg-green-500 rounded-full" title="Completed"></div>
                <div v-else class="absolute top-2 right-2 w-3 h-3 bg-gray-500 rounded-full" title="Not taken"></div>
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h4 class="font-semibold text-white text-lg mb-2">{{ quiz.title }}</h4>
                    <div class="text-sm text-gray-400 mb-2">
                      <span class="text-xs">ID: {{ quiz.id }}</span>
                    </div>
                    <p class="text-sm text-gray-400 mb-2">
                      Created: {{ new Date(quiz.created_at).toLocaleDateString() }}
                    </p>
                    <div class="flex items-center text-sm text-gray-300">
                      <font-awesome-icon :icon="['fas', 'question-circle']" class="mr-1" />
                      {{ quiz.questionCount }} questions
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-bold mb-1" :class="quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0 ? (quiz.accuracy >= 80 ? 'text-green-400' : quiz.accuracy >= 60 ? 'text-yellow-400' : 'text-red-400') : 'text-gray-500'">
                      {{ quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0 ? quiz.accuracy + '%' : 'Not taken' }}
                    </div>
                    <div class="text-xs text-gray-400 mb-2">{{ quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0 ? 'Accuracy' : 'Status' }}</div>
                    <div class="text-sm">
                      <span class="text-green-400 font-medium">{{ (quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0) ? quiz.score : 0 }}</span>
                      <span class="text-gray-500 mx-1">/</span>
                      <span class="text-red-400 font-medium">{{ (quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0) ? (quiz.questionCount - quiz.score) : 0 }}</span>
                    </div>
                    <div class="text-xs text-gray-400">Correct/Incorrect</div>
                  </div>
                </div>

                <!-- Performance Analysis -->
                <div class="bg-gray-600 rounded-lg p-3 mb-4">
                  <h5 class="text-sm font-semibold mb-2 text-gray-200">Performance Statistics</h5>
                  <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                      <div class="text-lg font-bold text-green-400">{{ (quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0) ? quiz.score : 0 }}</div>
                      <div class="text-xs text-gray-400">Correct</div>
                    </div>
                    <div>
                      <div class="text-lg font-bold text-red-400">{{ (quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0) ? (quiz.questionCount - quiz.score) : 0 }}</div>
                      <div class="text-xs text-gray-400">Incorrect</div>
                    </div>
                    <div>
                      <div class="text-lg font-bold text-blue-400">{{ quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0 ? (quiz.accuracy || 0) + '%' : 'N/A' }}</div>
                      <div class="text-xs text-gray-400">Accuracy</div>
                    </div>
                  </div>
                  <div class="mt-2 pt-2 border-t border-gray-500">
                    <div class="text-xs text-gray-300 text-center">
                      <span class="font-medium">Difficulty:</span> {{ quiz.difficulty || 'Medium' }}
                      <span class="mx-2">•</span>
                      <span class="font-medium">Questions:</span> {{ quiz.questionCount || 0 }}
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-400">
                    Difficulty: {{ quiz.difficulty || 'Medium' }}
                  </span>
                  <div class="flex space-x-2">
                    <button
                      @click.stop="startQuiz(quiz.id)"
                      class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition"
                    >
                      <font-awesome-icon :icon="['fas', 'play']" class="mr-1" />
                      Take Quiz
                    </button>
                    <button
                      @click.stop="confirmDeleteQuiz(quiz.id)"
                      class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition"
                      title="Delete Quiz"
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
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex items-center mb-4">
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-white">Delete Quiz</h3>
            <p class="text-sm text-gray-300 mt-1">Are you sure you want to delete this quiz? This action cannot be undone.</p>
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button @click="cancelDelete" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
            Cancel
          </button>
          <button @click="proceedDelete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
            Delete Quiz
          </button>
        </div>
      </div>
    </div>

    <!-- Note Selector Modal -->
    <div v-if="showNoteSelector" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold">Select Notes for Quiz Generation</h2>
          <button @click="closeNoteSelector" class="text-gray-400 hover:text-white">
            <font-awesome-icon :icon="['fas', 'times']" class="text-xl" />
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoadingNotes" class="flex justify-center py-8">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-2xl text-blue-500" />
          <span class="ml-2 text-gray-400">Loading your notes...</span>
        </div>

        <!-- Notes List -->
        <div v-else-if="availableNotes.length > 0" class="space-y-4">
          <!-- Selection Controls -->
          <div class="flex justify-between items-center mb-4">
            <div class="text-sm text-gray-400">
              {{ selectedNotes.length }} of {{ availableNotes.length }} notes selected
            </div>
            <div class="flex space-x-2">
              <button @click="selectAllNotes" class="px-3 py-1 text-sm bg-blue-600 rounded-md hover:bg-blue-700 transition">
                Select All
              </button>
              <button @click="deselectAllNotes" class="px-3 py-1 text-sm bg-gray-600 rounded-md hover:bg-gray-700 transition">
                Deselect All
              </button>
            </div>
          </div>

          <!-- Notes Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
            <div
              v-for="note in availableNotes"
              :key="note.id"
              class="bg-gray-700 rounded-lg p-4 cursor-pointer transition-all"
              :class="note.selected ? 'ring-2 ring-blue-500 bg-gray-600' : 'hover:bg-gray-600'"
              @click="toggleNoteSelection(note.id)"
            >
              <div class="flex items-start space-x-3">
                <input
                  type="checkbox"
                  :checked="note.selected"
                  class="mt-1 text-blue-600 focus:ring-blue-500"
                  readonly
                />
                <div class="flex-1">
                  <h3 class="font-medium text-white mb-2">{{ note.title || 'Untitled Note' }}</h3>
                  <p class="text-sm text-gray-300 mb-2 line-clamp-2">
                    {{ note.original_text ? note.original_text.substring(0, 100) + '...' : 'No content available' }}
                  </p>
                  <div class="text-xs text-gray-400">
                    Created: {{ new Date(note.created_at).toLocaleDateString() }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 text-gray-400">
          <font-awesome-icon :icon="['fas', 'book']" class="text-4xl mb-4" />
          <p class="text-lg mb-2">No notes available</p>
          <p class="text-sm">Create some notes first to generate quizzes</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4 mt-6">
          <button @click="closeNoteSelector" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
            Cancel
          </button>
          <button
            @click="generateQuizFromSelectedNotes"
            :disabled="selectedNotes.length === 0 || isGeneratingQuiz"
            class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-2" :spin="isGeneratingQuiz" />
            {{ isGeneratingQuiz ? 'Generating...' : `Generate Quiz (${selectedNotes.length})` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { useNotifications } from '@/composables/useNotifications'
import { useUserProfile } from '@/composables/useUserProfile'
import api from '@/services/api'

export default {
  name: 'QuizView',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const store = useStore()

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
    } = useNotifications()

    // =====================================
    // USER PROFILE SYSTEM
    // =====================================
    const {
      user: userProfile,
      loadUserProfile,
      getProfilePictureUrl
    } = useUserProfile()

    // Route debugging removed for production

    // State - Add error handling for route params
    let parsedNoteId = null
    try {
      const paramId = route.params.id
      if (paramId && paramId !== 'undefined' && paramId !== 'null') {
        parsedNoteId = parseInt(paramId)
        if (isNaN(parsedNoteId)) {
          parsedNoteId = null
        }
      }
    } catch (paramError) {
      console.error('Error parsing route params:', paramError)
      parsedNoteId = null
    }

    const noteId = ref(parsedNoteId)
    const noteTitle = ref('Loading...')
    const quizTitle = ref('')
    const isLoading = ref(true)
    const error = ref(null)
    const sidebarOpen = ref(false)
    const isGeneratingQuiz = ref(false)

    // Initial state set

    // Note selection state
    const showNoteSelector = ref(false)
    const availableNotes = ref([])
    const selectedNotes = ref([])
    const isLoadingNotes = ref(false)

    // Quiz state
    const quizQuestions = ref([])
    const quizCompleted = ref(false)
    const quizScore = ref(0)
    const showResults = ref(false)
    const isSavingQuiz = ref(false)
    const savedQuizId = ref(null)
    const showCorrectAnswers = ref(false)

    // Saved quizzes state
    const savedQuizzes = ref([])
    const isLoadingSavedQuizzes = ref(false)
    const selectedSavedQuiz = ref(null)

    // Delete confirmation state
    const showDeleteConfirmation = ref(false)
    const quizToDelete = ref(null)

    // User menu state
    const showUserMenu = ref(false)

    // Computed properties
    const allQuestionsAnswered = computed(() => {
      return quizQuestions.value.length > 0 &&
             quizQuestions.value.every(question => question.selectedAnswer !== null)
    })

    const answeredQuestionsCount = computed(() => {
      return quizQuestions.value.filter(question => question.selectedAnswer !== null).length
    })


    // Sidebar visibility from store
    const sidebarVisible = computed(() => store.getters['app/getSidebarVisible'])

    // Use user from composable
    const user = userProfile

    // Use global theme classes from store
    const themeClasses = computed(() => {
      return store.getters['app/getThemeClasses'];
    });

    const improvementAdvice = computed(() => {
      const score = quizScore.value
      const total = quizQuestions.value.length
      const percentage = (score / total) * 100

      const advice = []

      if (percentage >= 90) {
        advice.push({
          id: 1,
          icon: ['fas', 'trophy'],
          title: 'Excellent Performance!',
          description: 'You have a strong grasp of this material. Consider moving to more advanced topics.'
        })
      } else if (percentage >= 80) {
        advice.push({
          id: 1,
          icon: ['fas', 'thumbs-up'],
          title: 'Great Job!',
          description: 'You\'re doing well. Focus on the few areas where you made mistakes for even better results.'
        })
      } else if (percentage >= 70) {
        advice.push({
          id: 1,
          icon: ['fas', 'book-open'],
          title: 'Good Progress',
          description: 'You\'re on the right track. Review the incorrect answers and try again.'
        })
      } else if (percentage >= 60) {
        advice.push({
          id: 1,
          icon: ['fas', 'lightbulb'],
          title: 'Keep Studying',
          description: 'Focus on understanding the core concepts. Consider breaking down complex topics into smaller parts.'
        })
      } else {
        advice.push({
          id: 1,
          icon: ['fas', 'graduation-cap'],
          title: 'Time for Review',
          description: 'Take time to thoroughly review the material. Consider creating flashcards or mind maps for key concepts.'
        })
      }

      // Add specific advice based on wrong answers
      const wrongAnswers = total - score
      if (wrongAnswers > 0) {
        advice.push({
          id: 2,
          icon: ['fas', 'search'],
          title: 'Review Incorrect Answers',
          description: `Focus on understanding why ${wrongAnswers} answer${wrongAnswers > 1 ? 's were' : ' was'} incorrect.`
        })
      }

      // Add general study tips
      advice.push({
        id: 3,
        icon: ['fas', 'clock'],
        title: 'Study Tip',
        description: 'Spaced repetition helps retain information better. Review this material again in a few days.'
      })

      return advice
    })

    // =====================================
    // SIDEBAR FUNCTIONS
    // =====================================

    /**
     * Toggle sidebar visibility
     */
    const toggleSidebar = () => {
      store.dispatch('app/toggleSidebar')
    }

    // =====================================
    // USER MENU FUNCTIONS
    // =====================================

    /**
     * Toggle user menu dropdown
     */
    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value
    }

    /**
     * Close user menu dropdown
     */
    const closeUserMenu = () => {
      showUserMenu.value = false
    }

    /**
     * Open user profile modal
     */
    const openProfileModal = () => {
      showUserMenu.value = false
      // For now, just close the menu
      // In a full implementation, this would open a profile modal
    }

    /**
     * Logout user and redirect to login
     */
    const logout = async () => {
      try {
        router.push('/login')
      } catch (error) {
        // Error logging out
      }
    }


    // Handle image loading errors
    const handleImageError = () => {
      // Profile picture failed to load
    }

    // Handle successful image loading
    const handleImageLoad = () => {
      // Profile picture loaded successfully
    }

    // Methods
    const loadNoteData = async () => {
      console.log('=== LOAD NOTE DATA START ===')
      console.log('noteId.value:', noteId.value, 'Type:', typeof noteId.value)

      // If no note ID is provided, skip loading specific note data
      // and show the note selector instead
      if (!noteId.value) {
        console.log('No note ID provided, will show note selector')
        isLoading.value = false
        console.log('=== LOAD NOTE DATA END (no noteId) ===')
        return
      }

      try {
        console.log('Making API call to getNote with ID:', noteId.value)
        const response = await api.getNote(noteId.value)
        console.log('API response received:', response)
        console.log('Response data:', response.data)

        if (response.data.success && response.data.data) {
          const noteData = response.data.data
          console.log('Note data loaded successfully:', noteData)
          noteTitle.value = noteData.title || 'Untitled Note'
          console.log('Note title set to:', noteTitle.value)
        } else {
          console.warn('API response indicates failure:', response.data)
          error.value = response.data?.error || 'Note not found'
          console.log('Error set to:', error.value)
        }
      } catch (err) {
        console.error('Error loading note:', err)
        console.error('Error details:', {
          message: err.message,
          stack: err.stack,
          response: err.response
        })
        error.value = 'Failed to load note data'
      } finally {
        isLoading.value = false
        console.log('=== LOAD NOTE DATA END ===')
      }
    }

    const loadAvailableNotes = async () => {
      isLoadingNotes.value = true
      try {
        const response = await api.getNotes()
        if (response.data.success && response.data.data) {
          availableNotes.value = response.data.data.map(note => ({
            id: note.id,
            title: note.title || 'Untitled Note',
            original_text: note.original_text || '',
            summary: note.summary || '',
            created_at: note.created_at,
            selected: false
          }))
        } else {
          console.error('Failed to load notes:', response.data?.error)
        }
      } catch (error) {
        console.error('Error loading notes:', error)
      } finally {
        isLoadingNotes.value = false
      }
    }

    const toggleNoteSelection = (noteId) => {
      const note = availableNotes.value.find(n => n.id === noteId)
      if (note) {
        note.selected = !note.selected
        updateSelectedNotes()
      }
    }

    const updateSelectedNotes = () => {
      selectedNotes.value = availableNotes.value.filter(note => note.selected)
    }

    const selectAllNotes = () => {
      availableNotes.value.forEach(note => {
        note.selected = true
      })
      updateSelectedNotes()
    }

    const deselectAllNotes = () => {
      availableNotes.value.forEach(note => {
        note.selected = false
      })
      updateSelectedNotes()
    }

    const openNoteSelector = async () => {
      console.log('Opening note selector modal')
      showNoteSelector.value = true
      console.log('showNoteSelector set to:', showNoteSelector.value)
      if (availableNotes.value.length === 0) {
        console.log('Loading available notes...')
        await loadAvailableNotes()
        console.log('Available notes loaded:', availableNotes.value.length)
      } else {
        console.log('Notes already loaded:', availableNotes.value.length)
      }
    }

    const closeNoteSelector = () => {
      showNoteSelector.value = false
    }

    const generateQuizFromSelectedNotes = async () => {
      if (selectedNotes.value.length === 0) {
        alert('Please select at least one note to generate a quiz from.')
        return
      }

      closeNoteSelector()
      await generateQuizFromNotes(selectedNotes.value)
    }

    const generateNewQuiz = async () => {
      console.log('=== GENERATE NEW QUIZ BUTTON CLICKED ===')
      console.log('isGeneratingQuiz:', isGeneratingQuiz.value)
      console.log('noteId:', noteId.value)
      console.log('Current route params:', route.params)
      console.log('showNoteSelector:', showNoteSelector.value)
      console.log('availableNotes length:', availableNotes.value.length)

      if (isGeneratingQuiz.value) {
        console.log('Quiz generation already in progress, returning')
        return
      }

      // Load available notes if not already loaded
      if (availableNotes.value.length === 0) {
        console.log('Loading available notes...')
        await loadAvailableNotes()
        console.log('Available notes loaded:', availableNotes.value.length)
      }

      // Determine which notes to use for quiz generation
      let notesToUse = []

      if (noteId.value) {
        // Use the specific note if provided
        console.log('Using specific note ID:', noteId.value)
        const specificNote = availableNotes.value.find(n => n.id === noteId.value)
        if (specificNote) {
          notesToUse = [specificNote]
          console.log('Found specific note:', specificNote.title)
        } else {
          console.error('Specific note not found, using all available notes')
          notesToUse = availableNotes.value
        }
      } else {
        // Use all available notes if no specific note is provided
        console.log('No specific note ID, using all available notes')
        notesToUse = availableNotes.value
      }

      if (notesToUse.length === 0) {
        alert('No notes available for quiz generation. Please create some notes first.')
        return
      }

      // Generate quiz from the selected notes
      console.log('Generating quiz from', notesToUse.length, 'note(s)')
      await generateQuizFromNotes(notesToUse)
    }

    const generateQuizFromNotes = async (notes) => {
      if (isGeneratingQuiz.value) return

      isGeneratingQuiz.value = true

      try {
        console.log('=== FRONTEND QUIZ GENERATION START ===')
        console.log('Generating quiz from', notes.length, 'note(s)')

        // Collect content from all selected notes
        let combinedContent = ''
        const noteTitles = []

        for (const note of notes) {
          try {
            let noteData
            if (note.original_text) {
              // Note data already available
              noteData = note
            } else {
              // Fetch note data
              const response = await api.getNote(note.id)
              if (response.data.success && response.data.data) {
                noteData = response.data.data
              } else {
                console.warn('Failed to load note:', note.id)
                continue
              }
            }

            const usingSummary = noteData.summary && noteData.summary !== 'No summary available. Click "Generate Summary" to create one.'
            const content = usingSummary ? noteData.summary : noteData.original_text

            if (content && content.length > 10) {
              combinedContent += `\n\n--- ${noteData.title || 'Untitled Note'} ---\n${content}`
              noteTitles.push(noteData.title || 'Untitled Note')
            }
          } catch (error) {
            console.warn('Error loading note content:', note.id, error)
          }
        }

        if (!combinedContent || combinedContent.length < 50) {
          alert('Not enough content available for quiz generation. Please ensure your selected notes have sufficient text content.')
          return
        }

        console.log('Combined content length:', combinedContent.length)
        console.log('Note titles:', noteTitles.join(', '))

        // Generate quiz title using note title(s)
        let generatedQuizTitle = 'General Knowledge Quiz'

        if (notes.length === 1) {
          // For single note, use the note title as the quiz title
          generatedQuizTitle = notes[0].title || 'Untitled Note'
        } else if (notes.length === 2) {
          // For two notes, combine both titles
          const title1 = notes[0].title || 'Note 1'
          const title2 = notes[1].title || 'Note 2'
          generatedQuizTitle = `${title1} & ${title2}`
        } else if (notes.length > 2) {
          // For multiple notes, show first note title and count
          const firstTitle = notes[0].title || 'First Note'
          const remainingCount = notes.length - 1
          generatedQuizTitle = `${firstTitle} + ${remainingCount} more notes`
        } else {
          // Fallback for edge cases
          const titleResponse = await api.gpt.extractKeywords(combinedContent, 3)
          if (titleResponse.data && titleResponse.data.success && titleResponse.data.data) {
            const keywords = titleResponse.data.data
            if (Array.isArray(keywords) && keywords.length > 0) {
              // Create a meaningful title from the top keywords
              const mainTopic = keywords[0].toLowerCase()
              const secondaryTopic = keywords.length > 1 ? keywords[1].toLowerCase() : ''
              generatedQuizTitle = `${mainTopic.charAt(0).toUpperCase() + mainTopic.slice(1)}${secondaryTopic ? ' & ' + secondaryTopic.charAt(0).toUpperCase() + secondaryTopic.slice(1) : ''} Quiz`
            }
          }
        }

        // Generate quiz questions from combined content
        const gptResponse = await api.gpt.generateQuiz(combinedContent, {
          difficulty: 'medium',
          questionCount: Math.min(10, Math.max(5, notes.length * 2)), // More questions for multiple notes
          noteTitle: noteTitles.length === 1 ? noteTitles[0] : `${noteTitles.length} Selected Notes`,
          quizTitle: generatedQuizTitle
        })

        if (gptResponse.data && gptResponse.data.success && gptResponse.data.data) {
          const generatedQuestions = gptResponse.data.data.questions || gptResponse.data.data || []

          if (!Array.isArray(generatedQuestions) || generatedQuestions.length === 0) {
            alert('No quiz questions were generated. The content may be too short or unclear.')
            return
          }

          // For multiple notes, we'll create a quiz without saving to a specific note
          // Save the quiz to the database (using first note as reference or create a general quiz)
          const referenceNoteId = notes.length === 1 ? notes[0].id : (noteId.value || notes[0].id)

          const quizResponse = await api.createQuiz(referenceNoteId, {
            questions: generatedQuestions,
            difficulty: 'medium',
            source: notes.length === 1 ? 'single_note' : 'multiple_notes',
            note_count: notes.length,
            title: generatedQuizTitle,
            note_title: generatedQuizTitle
          })

          if (quizResponse.data.success) {
            // Process and display the questions
            quizQuestions.value = generatedQuestions.map((q) => {
              let correctAnswer = q.correct_answer || q.correctAnswer || 0

              if (typeof correctAnswer === 'string') {
                const letterToIndex = { 'A': 0, 'B': 1, 'C': 2, 'D': 3 }
                correctAnswer = letterToIndex[correctAnswer.toUpperCase()] ?? 0
              }

              const shuffledOptions = [...q.options]
              const originalCorrectIndex = correctAnswer

              for (let i = shuffledOptions.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1))
                ;[shuffledOptions[i], shuffledOptions[j]] = [shuffledOptions[j], shuffledOptions[i]]
              }

              const correctAnswerText = q.options[originalCorrectIndex]
              const newCorrectIndex = shuffledOptions.findIndex(option => option === correctAnswerText)

              return {
                text: q.question,
                options: shuffledOptions,
                correctAnswer: newCorrectIndex,
                selectedAnswer: null,
                originalOptions: q.options,
                originalCorrectIndex: originalCorrectIndex
              }
            })

            // Set quiz title for display - use the generated title (which is now just the note title)
            quizTitle.value = generatedQuizTitle

            // Reset quiz state
            quizCompleted.value = false
            quizScore.value = 0
            showResults.value = false

            console.log('Quiz generated successfully from', notes.length, 'note(s)')

            // Show success message and scroll to quiz
            alert(`Quiz "${generatedQuizTitle}" generated successfully! Created ${generatedQuestions.length} questions from ${notes.length} selected note${notes.length > 1 ? 's' : ''}.`)

            // Scroll to the quiz section
            setTimeout(() => {
              const quizSection = document.querySelector('.bg-gray-800.rounded-lg.p-6.mb-6')
              if (quizSection) {
                quizSection.scrollIntoView({ behavior: 'smooth', block: 'start' })
              }
            }, 100)
          } else {
            alert('Failed to save quiz: ' + (quizResponse.data.error || 'Unknown error'))
          }
        } else {
          const errorMessage = gptResponse.data?.error || 'Failed to generate quiz questions'
          if (errorMessage.includes('API key') || errorMessage.includes('authentication') || errorMessage.includes('Gemini')) {
            alert('AI service is not configured properly. Please check your Google Gemini API key in the .env file.\n\nUsing basic fallback quiz generation instead.')
            // The backend should handle fallback, so this should still work
          } else {
            alert('Failed to generate quiz questions: ' + errorMessage)
          }
        }
      } catch (error) {
        console.error('Error generating quiz:', error)
        alert('Failed to generate quiz. Please try again.')
      } finally {
        isGeneratingQuiz.value = false
        console.log('=== FRONTEND QUIZ GENERATION END ===')
      }
    }

    const checkAnswers = () => {
      if (!allQuestionsAnswered.value) {
        alert('Please answer all questions before checking your results.')
        return
      }

      let correctCount = 0
      quizQuestions.value.forEach(question => {
        if (question.selectedAnswer === question.correctAnswer) {
          correctCount++
        }
      })

      quizScore.value = correctCount
      quizCompleted.value = true
    }

    const resetQuiz = () => {
      quizQuestions.value.forEach(question => {
        question.selectedAnswer = null
      })
      quizCompleted.value = false
      quizScore.value = 0
      showResults.value = false
    }

    const retakeQuiz = () => {
      resetQuiz()
    }

    const toggleShowAnswers = () => {
      showCorrectAnswers.value = !showCorrectAnswers.value
    }

    const saveQuiz = async () => {
      if (!quizQuestions.value.length) {
        alert('No quiz to save. Please generate a quiz first.')
        return
      }

      try {
        isSavingQuiz.value = true

        // Determine which note_id to use
        let noteIdToUse = noteId.value

        // If no specific note ID, try to get user's first note
        if (!noteIdToUse) {
          try {
            const notesResponse = await api.getNotes()
            if (notesResponse.data.success && notesResponse.data.data && notesResponse.data.data.length > 0) {
              noteIdToUse = notesResponse.data.data[0].id
              console.log('Using first available note ID:', noteIdToUse)
            }
          } catch (error) {
            console.error('Error fetching notes for quiz save:', error)
          }
        }

        if (!noteIdToUse) {
          alert('Unable to save quiz: No notes available. Please create a note first.')
          return
        }

        const quizData = {
          note_id: noteIdToUse,
          questions: quizQuestions.value,
          difficulty: 'medium',
          score: quizScore.value,
          title: noteTitle.value,
          note_title: noteTitle.value
        }

        console.log('Saving quiz with data:', quizData)

        const response = await api.saveQuiz(quizData)

        if (response.data.success) {
          savedQuizId.value = response.data.quiz_id
          alert('Quiz saved successfully!')
          // Refresh saved quizzes list
          loadSavedQuizzes()
        } else {
          alert('Failed to save quiz: ' + (response.data.error || 'Unknown error'))
        }
      } catch (error) {
        console.error('Error saving quiz:', error)
        alert('Failed to save quiz. Please try again.')
      } finally {
        isSavingQuiz.value = false
      }
    }

    const debugQuizStatus = () => {
      console.log('=== QUIZ STATUS DEBUG ===')
      console.log('Total quizzes:', savedQuizzes.value.length)
      savedQuizzes.value.forEach((quiz, index) => {
        console.log(`Quiz ${index + 1} (ID: ${quiz.id}):`, {
          score: quiz.score,
          scoreType: typeof quiz.score,
          questionCount: quiz.questionCount,
          accuracy: quiz.accuracy,
          isCompleted: quiz.score !== null && quiz.score !== undefined && quiz.questionCount > 0,
          title: quiz.title
        })
      })

      const completedQuizzes = savedQuizzes.value.filter(q => q.score !== null && q.score !== undefined && q.questionCount > 0)
      const notTakenQuizzes = savedQuizzes.value.filter(q => !(q.score !== null && q.score !== undefined && q.questionCount > 0))

      console.log('Completed quizzes:', completedQuizzes.length)
      console.log('Not taken quizzes:', notTakenQuizzes.length)

      alert(`Debug Info:
Total Quizzes: ${savedQuizzes.value.length}
Completed: ${completedQuizzes.length}
Not Taken: ${notTakenQuizzes.length}

Check console for detailed quiz data.`)
    }

    const loadSavedQuizzes = async () => {
      console.log('=== LOAD SAVED QUIZZES START ===')
      try {
        isLoadingSavedQuizzes.value = true
        console.log('Making API call to getQuizzes')
        const response = await api.getQuizzes()
        console.log('API response received:', response)
        console.log('Response data:', response.data)

        if (response.data.success && response.data.data) {
          console.log('Processing', response.data.data.length, 'quizzes')
          savedQuizzes.value = response.data.data.map(quiz => {
            console.log('Processing quiz:', quiz.id, 'Questions type:', typeof quiz.questions, 'Questions value:', quiz.questions)
            let questions = []
            let questionCount = 0
            let accuracy = 0

            try {
              // Parse questions JSON if it's a string (from index method)
              if (typeof quiz.questions === 'string') {
                questions = JSON.parse(quiz.questions) || []
              } else {
                questions = quiz.questions || []
              }
              questionCount = Array.isArray(questions) ? questions.length : 0

              // Ensure score is a valid number (including 0)
              const validScore = (quiz.score !== null && quiz.score !== undefined && !isNaN(quiz.score)) ? Number(quiz.score) : null

              if (validScore !== null && questionCount > 0) {
                accuracy = Math.round((validScore / questionCount) * 100)
                // Ensure accuracy is within valid range
                accuracy = Math.max(0, Math.min(100, accuracy))
              } else {
                accuracy = 0
              }

              console.log(`Quiz ${quiz.id} stats: score=${validScore}, questionCount=${questionCount}, accuracy=${accuracy}%, isCompleted=${validScore !== null && questionCount > 0}`)
            } catch (parseError) {
              console.warn('Error parsing questions for quiz', quiz.id, parseError)
              console.warn('Quiz data:', quiz)
              questionCount = 0
              accuracy = 0
            }

            // Use note title as the primary display title
            let displayTitle = quiz.note_title || quiz.title
            if (!displayTitle) {
              displayTitle = `Quiz #${quiz.id}`
            }

            const processedQuiz = {
              id: quiz.id,
              note_id: quiz.note_id,
              questions: questions,
              difficulty: quiz.difficulty,
              score: quiz.score,
              created_at: quiz.created_at,
              updated_at: quiz.updated_at,
              questionCount: questionCount,
              accuracy: accuracy,
              title: displayTitle,
              note_title: quiz.note_title
            }
            console.log('Processed quiz:', processedQuiz)
            return processedQuiz
          })
          console.log('Successfully processed', savedQuizzes.value.length, 'quizzes')
        } else {
          console.error('Failed to load saved quizzes:', response.data?.error)
          console.error('Response data:', response.data)
        }
      } catch (error) {
        console.error('Error loading saved quizzes:', error)
        console.error('Error details:', {
          message: error.message,
          stack: error.stack,
          response: error.response
        })
      } finally {
        isLoadingSavedQuizzes.value = false
        console.log('=== LOAD SAVED QUIZZES END ===')
      }
    }

    const startQuiz = (quizId) => {
      // Navigate to the quiz taking page with the quiz ID
      router.push(`/quizzes/${quizId}`)
    }

    const confirmDeleteQuiz = (quizId) => {
      quizToDelete.value = quizId
      showDeleteConfirmation.value = true
    }

    const cancelDelete = () => {
      showDeleteConfirmation.value = false
      quizToDelete.value = null
    }

    const proceedDelete = async () => {
      if (quizToDelete.value) {
        await deleteQuiz(quizToDelete.value)
        showDeleteConfirmation.value = false
        quizToDelete.value = null
      }
    }

    const deleteQuiz = async (quizId) => {
      try {
        const response = await api.deleteQuiz(quizId)
        if (response.data.success) {
          // Remove the quiz from the local list
          savedQuizzes.value = savedQuizzes.value.filter(quiz => quiz.id !== quizId)
          alert('Quiz deleted successfully!')
        } else {
          alert('Failed to delete quiz: ' + (response.data.error || 'Unknown error'))
        }
      } catch (error) {
        console.error('Error deleting quiz:', error)
        alert('Failed to delete quiz. Please try again.')
      }
    }

    const loadSavedQuiz = async (quizId) => {
      try {
        const response = await api.getQuiz(quizId)

        if (response.data.success && response.data.data) {
          const quizData = response.data.data
          // Backend already returns parsed questions, no need to JSON.parse
          const questions = quizData.questions || []

          // Load the quiz questions
          quizQuestions.value = questions.map(q => ({
            text: q.question || q.text,
            options: q.options || [],
            correctAnswer: q.correct_answer || q.correctAnswer || 0,
            selectedAnswer: null,
            originalOptions: q.options || [],
            originalCorrectIndex: q.correct_answer || q.correctAnswer || 0
          }))

          // Set quiz title for display - use note title as the quiz title
          quizTitle.value = quizData.note_title || quizData.title || `Quiz #${quizId}`

          // Reset quiz state
          quizCompleted.value = false
          quizScore.value = 0
          showResults.value = false
          selectedSavedQuiz.value = quizData

          alert(`Loaded saved quiz with ${questions.length} questions!`)
        } else {
          alert('Failed to load quiz: ' + (response.data?.error || 'Unknown error'))
        }
      } catch (error) {
        console.error('Error loading saved quiz:', error)
        alert('Failed to load quiz. Please try again.')
      }
    }


    // Initialize
    onMounted(async () => {
      console.log('=== ONMOUNTED START ===')
      try {
        console.log('Calling loadNoteData...')
        await loadNoteData()
        console.log('loadNoteData completed successfully')

        console.log('Calling loadSavedQuizzes...')
        await loadSavedQuizzes()
        console.log('loadSavedQuizzes completed successfully')

        console.log('Calling loadUserProfile...')
        await loadUserProfile()
        console.log('loadUserProfile completed successfully')

        console.log('=== ONMOUNTED END ===')
      } catch (error) {
        console.error('=== ONMOUNTED ERROR ===')
        console.error('Error during component initialization:', error)
        console.error('Error details:', {
          message: error.message,
          stack: error.stack,
          name: error.name
        })
        // Set error state to show error UI
        error.value = 'Failed to initialize component: ' + error.message
        isLoading.value = false
        console.log('=== ONMOUNTED ERROR HANDLED ===')
      }

      // Note: Removed automatic note selector popup when navigating to /quizzes
      // The popup will only show when user clicks "Generate Quiz" button
    })

    return {
      noteId,
      noteTitle,
      quizTitle,
      isLoading,
      error,
      sidebarOpen,
      sidebarVisible,
      isGeneratingQuiz,
      quizQuestions,
      quizCompleted,
      quizScore,
      showResults,
      showCorrectAnswers,
      toggleShowAnswers,
      allQuestionsAnswered,
      answeredQuestionsCount,
      improvementAdvice,
      showNoteSelector,
      availableNotes,
      selectedNotes,
      isLoadingNotes,
      isSavingQuiz,
      savedQuizId,
      savedQuizzes,
      isLoadingSavedQuizzes,
      selectedSavedQuiz,
      showUserMenu,
      user,
      toggleSidebar,
      generateNewQuiz,
      checkAnswers,
      resetQuiz,
      retakeQuiz,
      saveQuiz,
      loadAvailableNotes,
      toggleNoteSelection,
      selectAllNotes,
      deselectAllNotes,
      openNoteSelector,
      closeNoteSelector,
      generateQuizFromSelectedNotes,
      generateQuizFromNotes,
      loadSavedQuizzes,
      loadSavedQuiz,
      startQuiz,
      confirmDeleteQuiz,
      deleteQuiz,
      cancelDelete,
      proceedDelete,
      showDeleteConfirmation,
      quizToDelete,
      toggleUserMenu,
      closeUserMenu,
      openProfileModal,
      logout,
      getProfilePictureUrl,
      handleImageError,
      handleImageLoad,
      showNotifications,
      notifications,
      unreadNotifications,
      toggleNotifications,
      closeNotifications,
      markAsRead,
      markAllAsRead,
      showSuccess,
      showInfo,
      showWarning,
      loadUserProfile,
      debugQuizStatus,
      themeClasses,
      store
    }
  }
}
</script>
