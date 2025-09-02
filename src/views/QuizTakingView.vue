<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white overflow-x-hidden">
    <!-- Header -->
    <header class="p-3 sm:p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-lg sm:text-xl font-bold">SmartScribe - Quiz</div>
      <div class="flex items-center space-x-2 sm:space-x-4">
        <button class="text-gray-400 hover:text-white p-2">
          <font-awesome-icon :icon="['fas', 'bell']" class="text-sm sm:text-base" />
        </button>
        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-600 rounded-full"></div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex flex-grow">
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
      <aside
        :class="[
          'bg-gray-800 p-4 transition-all duration-300 ease-in-out',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
          'fixed md:relative z-50 md:z-auto',
          'w-64 md:w-64 max-w-full',
          'min-h-screen md:min-h-0',
          'top-0 left-0 md:top-auto md:left-auto',
          'overflow-hidden'
        ]"
      >
        <nav class="mt-16 md:mt-0">
          <ul class="space-y-2">
            <li>
              <router-link
                to="/dashboard"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700 transition"
              >
                <font-awesome-icon :icon="['fas', 'home']" />
                <span>Dashboard</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/notes"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700 transition"
              >
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/quizzes"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md bg-gray-700"
              >
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>Quizzes</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/progress"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700 transition"
              >
                <font-awesome-icon :icon="['fas', 'chart-line']" />
                <span>Progress</span>
              </router-link>
            </li>
            <li>
              <router-link
                to="/settings"
                @click="sidebarOpen = false"
                class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700 transition"
              >
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Quiz Taking Content -->
      <main class="flex-grow p-4 sm:p-6 ml-0 md:ml-0" style="width: 100vw; max-width: 100vw;">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-3xl sm:text-4xl text-blue-500" />
        </div>

        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
          <font-awesome-icon :icon="['fas', 'times']" class="text-4xl text-red-400 mb-4" />
          <h2 class="text-xl font-medium mb-2">Error Loading Quiz</h2>
          <p class="text-gray-400 mb-4">{{ error }}</p>
          <router-link to="/quizzes" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
            Back to Quizzes
          </router-link>
        </div>

        <div v-else>
          <!-- Quiz Header -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold mb-2">{{ quizTitle }}</h1>
                <p class="text-gray-400">{{ noteTitle }}</p>
              </div>
              <div class="flex space-x-3 mt-4 sm:mt-0">
                <router-link to="/quizzes" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                  <font-awesome-icon :icon="['fas', 'arrow-left']" class="mr-2" />
                  Back to Quizzes
                </router-link>
              </div>
            </div>
          </div>

          <!-- Quiz Results -->
          <div v-if="showResults" class="mb-8 bg-gray-800 rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-xl font-semibold mb-4">Quiz Results</h3>

              <!-- Score Display -->
              <div class="mb-6">
                <div class="text-5xl font-bold mb-2" :class="quizScore === quizQuestions.length ? 'text-green-400' : quizScore >= quizQuestions.length * 0.7 ? 'text-yellow-400' : 'text-red-400'">
                  {{ quizScore }}/{{ quizQuestions.length }}
                </div>
                <p class="text-gray-300 text-lg">
                  {{ Math.round((quizScore / quizQuestions.length) * 100) }}% Correct
                </p>
                <p class="text-sm text-gray-400 mt-2">
                  You got {{ quizScore }} out of {{ quizQuestions.length }} questions correct
                </p>
              </div>

              <!-- Performance Analysis -->
              <div class="bg-gray-700 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold mb-3">Performance Analysis</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                  <div>
                    <div class="text-2xl font-bold text-blue-400">{{ quizScore }}</div>
                    <div class="text-sm text-gray-400">Correct Answers</div>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-red-400">{{ quizQuestions.length - quizScore }}</div>
                    <div class="text-sm text-gray-400">Incorrect Answers</div>
                  </div>
                  <div>
                    <div class="text-2xl font-bold text-yellow-400">{{ Math.round((quizScore / quizQuestions.length) * 100) }}%</div>
                    <div class="text-sm text-gray-400">Accuracy Rate</div>
                  </div>
                </div>
              </div>

              <!-- Improvement Advice -->
              <div class="bg-gray-700 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold mb-3">📚 Study Recommendations</h4>
                <div class="text-left space-y-2">
                  <div v-for="advice in improvementAdvice" :key="advice.id" class="flex items-start space-x-3">
                    <font-awesome-icon :icon="advice.icon" class="text-blue-400 mt-1 flex-shrink-0" />
                    <div>
                      <p class="font-medium">{{ advice.title }}</p>
                      <p class="text-sm text-gray-300">{{ advice.description }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-center space-x-4">
                <button @click="saveQuiz" class="px-6 py-3 bg-purple-600 rounded-md hover:bg-purple-700 transition" :disabled="isSavingQuiz">
                  <font-awesome-icon :icon="['fas', 'save']" class="mr-2" />
                  {{ isSavingQuiz ? 'Saving...' : 'Save Quiz' }}
                </button>
                <button @click="retakeQuiz" class="px-6 py-3 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                  <font-awesome-icon :icon="['fas', 'redo-alt']" class="mr-2" />
                  Retake Quiz
                </button>
                <router-link to="/quizzes" class="px-6 py-3 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                  <font-awesome-icon :icon="['fas', 'arrow-left']" class="mr-2" />
                  Back to Quizzes
                </router-link>
              </div>
            </div>
          </div>

          <!-- Quiz Questions -->
          <div v-if="quizQuestions.length > 0 && !showResults" class="space-y-6">
            <div v-for="(question, qIndex) in quizQuestions" :key="`question-${qIndex}`" class="bg-gray-800 rounded-lg p-6">
              <div class="mb-4">
                <h3 class="text-lg font-semibold mb-3">{{ qIndex + 1 }}. {{ question.text }}</h3>
                <div class="space-y-3">
                  <div
                    v-for="(option, oIndex) in question.options"
                    :key="`option-${qIndex}-${oIndex}`"
                    class="flex items-center space-x-3 p-3 rounded-lg transition-colors"
                    :class="{
                      'bg-green-900 border border-green-500': quizCompleted && oIndex === question.correctAnswer,
                      'bg-red-900 border border-red-500': quizCompleted && question.selectedAnswer === oIndex && oIndex !== question.correctAnswer,
                      'bg-gray-700 hover:bg-gray-600 border border-gray-600': !quizCompleted && question.selectedAnswer === oIndex,
                      'bg-gray-700 hover:bg-gray-600 border border-transparent': !quizCompleted && question.selectedAnswer !== oIndex
                    }"
                  >
                    <input
                      type="radio"
                      :id="`q${qIndex}-o${oIndex}`"
                      :name="`question-${qIndex}`"
                      :value="oIndex"
                      v-model="question.selectedAnswer"
                      class="text-blue-600 focus:ring-blue-500"
                      :disabled="quizCompleted"
                    />
                    <label :for="`q${qIndex}-o${oIndex}`" class="cursor-pointer flex-1 text-gray-200">
                      <span class="font-medium mr-2">{{ String.fromCharCode(65 + oIndex) }}.</span>
                      {{ option }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Answer Explanation (shown after completion) -->
              <div v-if="quizCompleted" class="mt-4 p-4 bg-gray-700 rounded-lg">
                <div class="flex items-start space-x-3">
                  <font-awesome-icon
                    :icon="question.selectedAnswer === question.correctAnswer ? ['fas', 'check-circle'] : ['fas', 'times-circle']"
                    :class="question.selectedAnswer === question.correctAnswer ? 'text-green-400' : 'text-red-400'"
                    class="mt-1"
                  />
                  <div class="flex-1">
                    <p class="font-medium mb-2">
                      <span class="text-green-400">Correct Answer:</span>
                      {{ question.originalOptions ? question.originalOptions[question.originalCorrectIndex] : question.options[question.correctAnswer] }}
                    </p>
                    <div v-if="question.selectedAnswer !== null && question.selectedAnswer !== question.correctAnswer">
                      <p class="text-red-400 mb-2">
                        <span class="font-medium">Your Answer:</span>
                        {{ question.options[question.selectedAnswer] }}
                      </p>
                      <p class="text-sm text-gray-300">
                        💡 <strong>Tip:</strong> Review the material related to this topic for better understanding.
                      </p>
                    </div>
                    <div v-if="question.selectedAnswer !== null && question.selectedAnswer === question.correctAnswer">
                      <p class="text-green-400">
                        <span class="font-medium">Your Answer:</span>
                        {{ question.options[question.selectedAnswer] }} ✓
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quiz Controls -->
            <div class="flex justify-between items-center bg-gray-800 rounded-lg p-6">
              <div class="text-sm text-gray-400">
                {{ answeredQuestionsCount }}/{{ quizQuestions.length }} questions answered
              </div>
              <div class="flex space-x-4">
                <button
                  v-if="!quizCompleted"
                  @click="checkAnswers"
                  class="px-6 py-3 bg-green-600 rounded-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!allQuestionsAnswered"
                >
                  <font-awesome-icon :icon="['fas', 'check']" class="mr-2" />
                  Check Answers
                </button>
                <button
                  v-if="quizCompleted"
                  @click="showResults = true"
                  class="px-6 py-3 bg-blue-600 rounded-md hover:bg-blue-700 transition"
                >
                  <font-awesome-icon :icon="['fas', 'chart-bar']" class="mr-2" />
                  View Results
                </button>
                <button @click="resetQuiz" class="px-6 py-3 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                  <font-awesome-icon :icon="['fas', 'redo']" class="mr-2" />
                  Reset
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
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

export default {
  name: 'QuizTakingView',
  setup() {
    const route = useRoute()

    // State
    const quizId = ref(parseInt(route.params.id) || null)
    const quizTitle = ref('Loading Quiz...')
    const noteTitle = ref('')
    const isLoading = ref(true)
    const error = ref(null)
    const sidebarOpen = ref(false)

    // Quiz state
    const quizQuestions = ref([])
    const quizCompleted = ref(false)
    const quizScore = ref(0)
    const showResults = ref(false)
    const isSavingQuiz = ref(false)

    // Computed properties
    const allQuestionsAnswered = computed(() => {
      return quizQuestions.value.length > 0 &&
            quizQuestions.value.every(question => question.selectedAnswer !== null)
    })

    const answeredQuestionsCount = computed(() => {
      return quizQuestions.value.filter(question => question.selectedAnswer !== null).length
    })

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

    // Methods
    const loadQuiz = async () => {
      if (!quizId.value) {
        error.value = 'No quiz ID provided'
        isLoading.value = false
        return
      }

      try {
        const response = await api.getQuiz(quizId.value)
        if (response.data.success && response.data.data) {
          const quizData = response.data.data

          // Set quiz title
          quizTitle.value = quizData.title || `Quiz #${quizId.value}`

          // Load the quiz questions
          const questions = quizData.questions || []
          quizQuestions.value = questions.map(q => ({
            text: q.question || q.text,
            options: q.options || [],
            correctAnswer: q.correct_answer || q.correctAnswer || 0,
            selectedAnswer: null,
            originalOptions: q.options || [],
            originalCorrectIndex: q.correct_answer || q.correctAnswer || 0
          }))

          // Try to get note title if available
          if (quizData.note_id) {
            try {
              const noteResponse = await api.getNote(quizData.note_id)
              if (noteResponse.data.success && noteResponse.data.data) {
                noteTitle.value = noteResponse.data.data.title || 'Unknown Note'
              }
            } catch (noteError) {
              console.warn('Could not load note title:', noteError)
              noteTitle.value = 'Quiz from Notes'
            }
          } else {
            noteTitle.value = 'Generated Quiz'
          }
        } else {
          error.value = response.data?.error || 'Quiz not found'
        }
      } catch (err) {
        console.error('Error loading quiz:', err)
        error.value = 'Failed to load quiz'
      } finally {
        isLoading.value = false
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

    const saveQuiz = async () => {
      if (!quizQuestions.value.length) {
        alert('No quiz to save. Please generate a quiz first.')
        return
      }

      try {
        isSavingQuiz.value = true

        const quizData = {
          note_id: null, // This is a standalone quiz
          questions: quizQuestions.value,
          difficulty: 'medium',
          score: quizScore.value,
          title: quizTitle.value
        }

        console.log('Saving quiz with data:', quizData)

        const response = await api.saveQuiz(quizData)

        if (response.data.success) {
          alert('Quiz progress saved successfully!')
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

    // Initialize
    onMounted(async () => {
      await loadQuiz()
    })

    return {
      quizId,
      quizTitle,
      noteTitle,
      isLoading,
      error,
      sidebarOpen,
      quizQuestions,
      quizCompleted,
      quizScore,
      showResults,
      allQuestionsAnswered,
      answeredQuestionsCount,
      improvementAdvice,
      isSavingQuiz,
      checkAnswers,
      resetQuiz,
      retakeQuiz,
      saveQuiz
    }
  }
}
</script>