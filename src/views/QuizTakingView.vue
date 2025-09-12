<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Header -->
    <header class="bg-gray-800/50 backdrop-blur-sm border-b border-gray-700/50 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <router-link
              to="/quizzes"
              class="flex items-center space-x-2 text-gray-400 hover:text-white transition-colors"
            >
              <font-awesome-icon :icon="['fas', 'angle-left']" />
              <span>Back to Quizzes</span>
            </router-link>
          </div>

          <div class="text-center">
            <h1 class="text-xl font-bold text-white">{{ quizTitle }}</h1>
          </div>

          <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-400">
              Question {{ currentQuestionIndex + 1 }} of {{ quizQuestions.length }}
            </div>
            <div class="text-sm text-gray-400">
              • {{ correctAnswersCount }} ✓ • {{ incorrectAnswersCount }} ✗ • {{ currentAccuracy }}%
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-96">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
        <p class="text-gray-400">Loading quiz...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center min-h-96">
        <div class="text-red-400 mb-4">
          <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-4xl" />
        </div>
        <h2 class="text-xl font-semibold text-white mb-2">Error Loading Quiz</h2>
        <p class="text-gray-400 mb-6 text-center">{{ error }}</p>
        <router-link
          to="/quizzes"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
        >
          Return to Quizzes
        </router-link>
      </div>

      <!-- Quiz Content -->
      <div v-else>
        <!-- Progress Bar -->
        <div class="mb-8">
          <!-- Real-time Statistics -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center">
              <div class="text-xl font-bold text-green-400">{{ correctAnswersCount }}</div>
              <div class="text-xs text-gray-400">Correct</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-red-400">{{ incorrectAnswersCount }}</div>
              <div class="text-xs text-gray-400">Incorrect</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-blue-400">{{ currentAccuracy }}%</div>
              <div class="text-xs text-gray-400">Accuracy</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-yellow-400">{{ unansweredQuestionsCount }}</div>
              <div class="text-xs text-gray-400">Remaining</div>
            </div>
          </div>

          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-300">Progress</span>
            <span class="text-sm text-gray-400">{{ answeredQuestionsCount }}/{{ quizQuestions.length }} answered</span>
          </div>
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div
              class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500 ease-out"
              :style="{ width: `${(answeredQuestionsCount / quizQuestions.length) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- Question Card -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700/50 p-8 mb-8">
          <!-- Question Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-4">
                <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-full text-white font-bold text-sm">
                  {{ currentQuestionIndex + 1 }}
                </div>
                <div class="text-sm text-gray-400">
                  {{ getQuestionType(quizQuestions[currentQuestionIndex]) }}
                </div>
              </div>
              <h2 class="text-xl font-semibold text-white leading-relaxed">
                {{ quizQuestions[currentQuestionIndex].text }}
              </h2>
            </div>
          </div>

          <!-- Answer Options -->
          <div class="space-y-3 mb-8">
            <div
              v-for="(option, optionIndex) in quizQuestions[currentQuestionIndex].options"
              :key="`option-${currentQuestionIndex}-${optionIndex}`"
              class="relative"
            >
              <label
                :for="`q${currentQuestionIndex}-o${optionIndex}`"
                class="flex items-center space-x-4 p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer group"
                :class="{
                  'border-blue-500 bg-blue-500/10 text-blue-100': quizQuestions[currentQuestionIndex].selectedAnswer === optionIndex,
                  'border-gray-600 hover:border-gray-500 bg-gray-700/30 text-gray-300 hover:bg-gray-700/50': quizQuestions[currentQuestionIndex].selectedAnswer !== optionIndex
                }"
              >
                <input
                  type="radio"
                  :id="`q${currentQuestionIndex}-o${optionIndex}`"
                  :name="`question-${currentQuestionIndex}`"
                  :value="optionIndex"
                  v-model.number="quizQuestions[currentQuestionIndex].selectedAnswer"
                  class="text-blue-600 focus:ring-blue-500"
                  :disabled="quizCompleted"
                  @change="onAnswerSelected(currentQuestionIndex, optionIndex)"
                />

                <div class="flex items-center space-x-3 flex-1">
                  <div
                    class="flex items-center justify-center w-6 h-6 rounded-full border-2 text-xs font-bold"
                    :class="{
                      'border-blue-500 bg-blue-500 text-white': quizQuestions[currentQuestionIndex].selectedAnswer === optionIndex,
                      'border-gray-400 text-gray-400': quizQuestions[currentQuestionIndex].selectedAnswer !== optionIndex
                    }"
                  >
                    {{ String.fromCharCode(65 + optionIndex) }}
                  </div>
                  <span class="text-sm leading-relaxed">{{ option }}</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Navigation and Controls -->
        <div class="flex items-center justify-between">
          <!-- Previous Button -->
          <button
            @click="previousQuestion"
            :disabled="currentQuestionIndex === 0"
            class="flex items-center space-x-2 px-6 py-3 rounded-lg transition-colors"
            :class="currentQuestionIndex === 0
              ? 'bg-gray-700 text-gray-500 cursor-not-allowed'
              : 'bg-gray-700 hover:bg-gray-600 text-white'"
          >
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
            <span>Previous</span>
          </button>

          <!-- Question Navigation -->
          <div class="flex space-x-2">
            <button
              v-for="(question, index) in quizQuestions"
              :key="`nav-${index}`"
              @click="goToQuestion(index)"
              class="w-10 h-10 rounded-lg border-2 transition-all duration-200 flex items-center justify-center text-sm font-medium"
              :class="{
                'border-blue-500 bg-blue-500/20 text-blue-400': currentQuestionIndex === index,
                'border-gray-600 bg-gray-700/30 text-gray-400 hover:border-gray-500': currentQuestionIndex !== index && question.selectedAnswer === null,
                'bg-green-500/20 border-green-500 text-green-400': question.selectedAnswer !== null && question.selectedAnswer === question.correctAnswer,
                'bg-red-500/20 border-red-500 text-red-400': question.selectedAnswer !== null && question.selectedAnswer !== question.correctAnswer
              }"
            >
              {{ index + 1 }}
            </button>
          </div>

          <!-- Next/Submit Button -->
          <button
            v-if="currentQuestionIndex < quizQuestions.length - 1"
            @click="nextQuestion"
            class="flex items-center space-x-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors text-white"
          >
            <span>Next</span>
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
          </button>

          <button
            v-else-if="!quizCompleted"
            @click="checkAnswers"
            :disabled="!allQuestionsAnswered"
            class="flex items-center space-x-2 px-6 py-3 rounded-lg transition-colors text-white"
            :class="allQuestionsAnswered
              ? 'bg-green-600 hover:bg-green-700'
              : 'bg-gray-700 text-gray-500 cursor-not-allowed'"
          >
            <font-awesome-icon :icon="['fas', 'check']" />
            <span>Submit Quiz</span>
          </button>

          <button
            v-else
            @click="viewResults"
            class="flex items-center space-x-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors text-white"
          >
            <font-awesome-icon :icon="['fas', 'chart-bar']" />
            <span>View Results</span>
          </button>

          <!-- Debug button for testing reactivity -->
          <button
            @click="debugStats"
            class="flex items-center space-x-2 px-3 py-2 bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors text-white text-sm"
            title="Debug Statistics"
          >
            <font-awesome-icon :icon="['fas', 'info-circle']" />
          </button>
        </div>

        <!-- Results Modal -->
        <div v-if="showResults" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div class="bg-gray-800 rounded-xl max-w-2xl w-full max-h-90vh overflow-y-auto">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Quiz Results</h2>
                <button
                  @click="showResults = false"
                  class="text-gray-400 hover:text-white transition-colors"
                >
                  <font-awesome-icon :icon="['fas', 'times']" />
                </button>
              </div>

              <!-- Score Overview -->
              <div class="text-center mb-8">
                <div class="text-6xl font-bold mb-2" :class="quizScore === quizQuestions.length ? 'text-green-400' : quizScore >= quizQuestions.length * 0.7 ? 'text-yellow-400' : 'text-red-400'">
                  {{ Math.round((quizScore / quizQuestions.length) * 100) }}%
                </div>
                <p class="text-gray-300 text-lg">
                  {{ quizScore }} out of {{ quizQuestions.length }} correct
                </p>
              </div>

              <!-- Performance Stats -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-green-400 mb-1">{{ quizScore }}</div>
                  <div class="text-sm text-gray-400">Correct</div>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-red-400 mb-1">{{ quizQuestions.length - quizScore }}</div>
                  <div class="text-sm text-gray-400">Incorrect</div>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-blue-400 mb-1">{{ Math.round((quizScore / quizQuestions.length) * 100) }}%</div>
                  <div class="text-sm text-gray-400">Accuracy</div>
                </div>
                <div class="bg-gray-700/50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-purple-400 mb-1">{{ quizQuestions.length }}</div>
                  <div class="text-sm text-gray-400">Total Questions</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-4">
                <button
                  @click="retakeQuiz"
                  class="flex-1 flex items-center justify-center space-x-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors text-white"
                >
                  <font-awesome-icon :icon="['fas', 'redo']" />
                  <span>Retake Quiz</span>
                </button>

                <button
                  @click="saveQuiz"
                  :disabled="isSavingQuiz"
                  class="flex-1 flex items-center justify-center space-x-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors text-white disabled:opacity-50"
                >
                  <font-awesome-icon :icon="['fas', 'save']" :spin="isSavingQuiz" />
                  <span>{{ isSavingQuiz ? 'Saving...' : 'Save Results' }}</span>
                </button>

                <router-link
                  to="/quizzes"
                  class="flex-1 flex items-center justify-center space-x-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors text-white text-center"
                >
                  <font-awesome-icon :icon="['fas', 'angle-left']" />
                  <span>Back to Quizzes</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Completion Popup Modal -->
        <div v-if="showCompletionPopup" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-gray-700">
            <div class="p-8 text-center">
              <!-- Celebration Icon -->
              <div class="mb-6">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg">
                  <font-awesome-icon :icon="['fas', 'trophy']" class="text-white text-3xl" />
                </div>
              </div>

              <!-- Title -->
              <h2 class="text-2xl font-bold text-white mb-2">Quiz Completed!</h2>
              <p class="text-gray-300 mb-6">Great job! Here's your performance summary.</p>

              <!-- Score Display -->
              <div class="mb-6">
                <div class="text-5xl font-bold mb-2" :class="quizScore / quizQuestions.length >= 0.8 ? 'text-green-400' : quizScore / quizQuestions.length >= 0.6 ? 'text-yellow-400' : 'text-red-400'">
                  {{ Math.round((quizScore / quizQuestions.length) * 100) }}%
                </div>
                <p class="text-gray-300 text-lg">
                  {{ quizScore }} out of {{ quizQuestions.length }} correct
                </p>
              </div>

              <!-- Performance Message -->
              <div class="mb-6 p-4 rounded-lg" :class="quizScore / quizQuestions.length >= 0.8 ? 'bg-green-500/10 border border-green-500/20' : quizScore / quizQuestions.length >= 0.6 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <p class="text-sm" :class="quizScore / quizQuestions.length >= 0.8 ? 'text-green-300' : quizScore / quizQuestions.length >= 0.6 ? 'text-yellow-300' : 'text-red-300'">
                  {{ quizScore / quizQuestions.length >= 0.8 ? '🎉 Excellent work! You have a strong understanding of this material.' :
                     quizScore / quizQuestions.length >= 0.6 ? '👍 Good job! You\'re on the right track with some room for improvement.' :
                     '📚 Keep studying! Review the material and try again to improve your score.' }}
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-3">
                <button @click="showCompletionPopup = false; showResults = true"
                        class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors text-white font-medium">
                  <font-awesome-icon :icon="['fas', 'chart-bar']" class="mr-2" />
                  View Details
                </button>
                <button @click="showCompletionPopup = false"
                        class="flex-1 px-4 py-3 bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors text-white font-medium">
                  <font-awesome-icon :icon="['fas', 'times']" class="mr-2" />
                  Close
                </button>
              </div>

              <!-- Quick Stats -->
              <div class="mt-6 pt-4 border-t border-gray-700">
                <div class="grid grid-cols-3 gap-4 text-center">
                  <div>
                    <div class="text-lg font-bold text-green-400">{{ quizScore }}</div>
                    <div class="text-xs text-gray-400">Correct</div>
                  </div>
                  <div>
                    <div class="text-lg font-bold text-red-400">{{ quizQuestions.length - quizScore }}</div>
                    <div class="text-xs text-gray-400">Incorrect</div>
                  </div>
                  <div>
                    <div class="text-lg font-bold text-blue-400">{{ Math.round((quizScore / quizQuestions.length) * 100) }}%</div>
                    <div class="text-xs text-gray-400">Accuracy</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, nextTick } from 'vue'
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
    const quizQuestions = ref([])
    const quizCompleted = ref(false)
    const quizScore = ref(0)
    const showResults = ref(false)
    const showCompletionPopup = ref(false)
    const isSavingQuiz = ref(false)
    const currentQuestionIndex = ref(0)

    // Computed properties
    const allQuestionsAnswered = computed(() => {
      return quizQuestions.value.length > 0 &&
             quizQuestions.value.every(question => question.selectedAnswer !== null && question.selectedAnswer !== undefined)
    })

    const answeredQuestionsCount = computed(() => {
      return quizQuestions.value.filter(question => question.selectedAnswer !== null && question.selectedAnswer !== undefined).length
    })

    // Real-time statistics methods (called on demand for better reactivity)
    const getCorrectAnswersCount = () => {
      const count = quizQuestions.value.filter(question => {
        return question.selectedAnswer !== null &&
               question.selectedAnswer !== undefined &&
               question.selectedAnswer === question.correctAnswer
      }).length
      console.log('Correct answers count:', count)
      return count
    }

    const getIncorrectAnswersCount = () => {
      const count = quizQuestions.value.filter(question => {
        return question.selectedAnswer !== null &&
               question.selectedAnswer !== undefined &&
               question.selectedAnswer !== question.correctAnswer
      }).length
      console.log('Incorrect answers count:', count)
      return count
    }

    const getCurrentAccuracy = () => {
      const answered = answeredQuestionsCount.value
      const correct = getCorrectAnswersCount()
      const accuracy = answered === 0 ? 0 : Math.round((correct / answered) * 100)
      console.log('Current accuracy:', accuracy, 'Answered:', answered, 'Correct:', correct)
      return accuracy
    }

    const getUnansweredQuestionsCount = () => {
      const unanswered = quizQuestions.value.length - answeredQuestionsCount.value
      console.log('Unanswered questions count:', unanswered)
      return unanswered
    }

    // Computed properties that call the methods (for template use)
    const correctAnswersCount = computed(() => getCorrectAnswersCount())
    const incorrectAnswersCount = computed(() => getIncorrectAnswersCount())
    const currentAccuracy = computed(() => getCurrentAccuracy())
    const unansweredQuestionsCount = computed(() => getUnansweredQuestionsCount())

    // Helper functions
    const shuffleArray = (array) => {
      const shuffled = [...array]
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
      }
      return shuffled
    }

    const isQuestionCorrect = (question) => {
      if (question.selectedAnswer === null || question.selectedAnswer === undefined) {
        return false
      }
      if (!question.options || question.options.length === 0) {
        return false
      }
      if (question.correctAnswer < 0 || question.correctAnswer >= question.options.length) {
        return false
      }
      return Number(question.selectedAnswer) === Number(question.correctAnswer)
    }

    const getQuestionType = (question) => {
      // You can extend this to detect different question types based on question data
      if (question && question.options) {
        return `Multiple Choice (${question.options.length} options)`
      }
      return 'Multiple Choice'
    }

    // Navigation functions
    const nextQuestion = () => {
      if (currentQuestionIndex.value < quizQuestions.value.length - 1) {
        currentQuestionIndex.value++
      }
    }

    const previousQuestion = () => {
      if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--
      }
    }

    const goToQuestion = (index) => {
      if (index >= 0 && index < quizQuestions.value.length) {
        currentQuestionIndex.value = index
      }
    }

    const onAnswerSelected = async (questionIndex, optionIndex) => {
      console.log(`Answer selected for question ${questionIndex + 1}: option ${optionIndex}`)
      console.log('Question data:', quizQuestions.value[questionIndex])
      console.log('Correct answer:', quizQuestions.value[questionIndex].correctAnswer)
      console.log('Is correct:', optionIndex === quizQuestions.value[questionIndex].correctAnswer)

      // Force reactivity update using nextTick
      await nextTick()
      quizQuestions.value = [...quizQuestions.value]

      // Log updated statistics
      console.log('Updated statistics after answer selection:')
      console.log('- Correct answers:', getCorrectAnswersCount())
      console.log('- Incorrect answers:', getIncorrectAnswersCount())
      console.log('- Current accuracy:', getCurrentAccuracy() + '%')
    }

    const debugStats = () => {
      console.log('=== DEBUG STATISTICS ===')
      console.log('Total questions:', quizQuestions.value.length)
      console.log('Answered questions:', answeredQuestionsCount.value)
      console.log('Correct answers:', getCorrectAnswersCount())
      console.log('Incorrect answers:', getIncorrectAnswersCount())
      console.log('Current accuracy:', getCurrentAccuracy() + '%')
      console.log('Unanswered questions:', getUnansweredQuestionsCount())

      console.log('Question details:')
      quizQuestions.value.forEach((q, i) => {
        console.log(`Question ${i + 1}: Selected=${q.selectedAnswer}, Correct=${q.correctAnswer}, IsCorrect=${q.selectedAnswer === q.correctAnswer}`)
      })

      // Force reactivity update
      quizQuestions.value = [...quizQuestions.value]

      alert(`Debug Stats:
Total: ${quizQuestions.value.length}
Answered: ${answeredQuestionsCount.value}
Correct: ${getCorrectAnswersCount()}
Incorrect: ${getIncorrectAnswersCount()}
Accuracy: ${getCurrentAccuracy()}%
Unanswered: ${getUnansweredQuestionsCount()}`)
    }

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

          // Load the quiz questions
          const questions = quizData.questions || []
          if (!Array.isArray(questions) || questions.length === 0) {
            throw new Error('No questions found in quiz data')
          }

          quizQuestions.value = questions.map(q => {
            // Handle correct answer conversion from letter to index
            let correctAnswer = q.correct_answer || q.correctAnswer || 0

            // If correct answer is a letter (A, B, C, D), convert to index (0, 1, 2, 3)
            if (typeof correctAnswer === 'string' && /^[A-D]$/.test(correctAnswer.toUpperCase())) {
              correctAnswer = correctAnswer.toUpperCase().charCodeAt(0) - 65 // A=0, B=1, C=2, D=3
            } else {
              correctAnswer = Number(correctAnswer) || 0
            }

            // Ensure we have valid options array
            const originalOptions = Array.isArray(q.options) ? q.options : []

            // Validate that we have options
            if (originalOptions.length === 0) {
              throw new Error('Question has no options')
            }

            // Shuffle the options
            const shuffledOptions = shuffleArray(originalOptions)

            // Find the new index of the correct answer after shuffling
            const correctAnswerText = originalOptions[correctAnswer]
            const newCorrectAnswerIndex = shuffledOptions.indexOf(correctAnswerText)

            const processedQuestion = {
              text: q.question || q.text || 'Untitled Question',
              options: shuffledOptions,
              originalOptions: originalOptions, // Store original for reshuffling
              correctAnswer: Math.max(0, Math.min(newCorrectAnswerIndex, shuffledOptions.length - 1)), // Ensure valid index
              selectedAnswer: null
            }

            // Debug logging
            console.log('Processed question:', {
              text: processedQuestion.text,
              optionsCount: processedQuestion.options.length,
              correctAnswer: processedQuestion.correctAnswer,
              correctAnswerText: processedQuestion.options[processedQuestion.correctAnswer],
              originalCorrectAnswer: q.correct_answer || q.correctAnswer,
              shuffled: true,
              originalOptions: originalOptions,
              shuffledOptions: shuffledOptions
            })

            return processedQuestion
          })

          // Try to get note title if available and use it as quiz title
          if (quizData.note_id) {
            try {
              const noteResponse = await api.getNote(quizData.note_id)
              if (noteResponse.data.success && noteResponse.data.data) {
                const fetchedNoteTitle = noteResponse.data.data.title || 'Unknown Note'
                noteTitle.value = fetchedNoteTitle
                // Use note title as the quiz title
                quizTitle.value = fetchedNoteTitle
              } else {
                noteTitle.value = 'Quiz from Notes'
                quizTitle.value = quizData.title || `Quiz #${quizId.value}`
              }
            } catch (noteError) {
              console.warn('Could not load note title:', noteError)
              noteTitle.value = 'Quiz from Notes'
              quizTitle.value = quizData.title || `Quiz #${quizId.value}`
            }
          } else {
            noteTitle.value = 'Generated Quiz'
            quizTitle.value = quizData.title || `Quiz #${quizId.value}`
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
      console.log('checkAnswers called')
      if (!allQuestionsAnswered.value) {
        console.log('Not all questions answered')
        alert('Please answer all questions before submitting.')
        return
      }

      let correctCount = 0
      let totalValidQuestions = 0

      quizQuestions.value.forEach((question, index) => {
        // Only count questions that have valid data
        if (question.options && question.options.length > 0 &&
            question.correctAnswer >= 0 && question.correctAnswer < question.options.length) {
          totalValidQuestions++
          const isCorrect = isQuestionCorrect(question)
          console.log(`Question ${index + 1}: Correct=${isCorrect}, Selected=${question.selectedAnswer}, CorrectAnswer=${question.correctAnswer}`)
          if (isCorrect) {
            correctCount++
          }
        } else {
          console.warn(`Question ${index + 1} has invalid data and will be skipped`)
        }
      })

      // Ensure score is within valid range
      const validatedScore = Math.max(0, Math.min(totalValidQuestions, correctCount))

      quizScore.value = validatedScore
      quizCompleted.value = true
      console.log(`Quiz completed. Valid questions: ${totalValidQuestions}, Correct: ${validatedScore}, Score: ${validatedScore}/${totalValidQuestions}`)

      // Show completion popup immediately
      showCompletionPopup.value = true
    }

    const retakeQuiz = () => {
      quizQuestions.value.forEach(question => {
        question.selectedAnswer = null
        // Re-shuffle options for this attempt using original options
        const shuffledOptions = shuffleArray(question.originalOptions || question.options)
        const correctAnswerText = question.originalOptions ?
          question.originalOptions[question.correctAnswer] :
          question.options[question.correctAnswer]
        question.options = shuffledOptions
        question.correctAnswer = shuffledOptions.indexOf(correctAnswerText)
      })
      quizCompleted.value = false
      quizScore.value = 0
      showResults.value = false
      showCompletionPopup.value = false
      currentQuestionIndex.value = 0
      // Ensure all reactive state is properly reset
      quizQuestions.value = [...quizQuestions.value]
    }

    const viewResults = () => {
      console.log('View Results clicked')
      console.log('Quiz questions with answers:', quizQuestions.value.map((q, i) => ({
        question: i + 1,
        text: q.text,
        selected: q.selectedAnswer,
        correct: q.correctAnswer,
        isCorrect: isQuestionCorrect(q)
      })))
      showCompletionPopup.value = false
      showResults.value = true
    }

    const saveQuiz = async () => {
      if (!quizQuestions.value.length) {
        alert('No quiz to save. Please generate a quiz first.')
        return
      }

      // Validate quiz score before saving
      if (!quizCompleted.value) {
        alert('Please complete the quiz before saving results.')
        return
      }

      // Ensure score is valid
      const validatedScore = Math.max(0, Math.min(quizQuestions.value.length, quizScore.value || 0))

      try {
        isSavingQuiz.value = true

        // Find or create a note to associate with this quiz
        let noteIdToUse = null

        // First, try to use the original note if available
        if (quizId.value) {
          try {
            const quizResponse = await api.getQuiz(quizId.value)
            if (quizResponse.data.success && quizResponse.data.data.note_id) {
              noteIdToUse = quizResponse.data.data.note_id
              console.log('Using original note ID:', noteIdToUse)
            }
          } catch (error) {
            console.warn('Could not get original quiz data:', error)
          }
        }

        // If no original note, try to find user's first note
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

        // If still no note, we can't save the quiz
        if (!noteIdToUse) {
          alert('Unable to save quiz: No notes available. Please create a note first.')
          return
        }

        const quizData = {
          note_id: noteIdToUse,
          questions: quizQuestions.value,
          difficulty: 'medium',
          score: validatedScore,
          title: noteTitle.value || quizTitle.value,
          note_title: noteTitle.value
        }

        console.log('Saving quiz with accurate data:', {
          noteId: noteIdToUse,
          originalScore: quizScore.value,
          validatedScore: validatedScore,
          totalQuestions: quizQuestions.value.length,
          accuracy: quizQuestions.value.length > 0 ? Math.round((validatedScore / quizQuestions.value.length) * 100) : 0,
          questionsData: quizQuestions.value.map(q => ({
            selected: q.selectedAnswer,
            correct: q.correctAnswer,
            isCorrect: q.selectedAnswer === q.correctAnswer
          }))
        })

        const response = await api.saveQuiz(quizData)

        if (response.data.success) {
          alert(`Quiz saved successfully! Score: ${validatedScore}/${quizQuestions.value.length} (${Math.round((validatedScore / quizQuestions.value.length) * 100)}%)`)
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
      quizQuestions,
      quizCompleted,
      quizScore,
      showResults,
      showCompletionPopup,
      currentQuestionIndex,
      allQuestionsAnswered,
      answeredQuestionsCount,
      correctAnswersCount,
      incorrectAnswersCount,
      currentAccuracy,
      unansweredQuestionsCount,
      isSavingQuiz,
      isQuestionCorrect,
      getQuestionType,
      nextQuestion,
      previousQuestion,
      goToQuestion,
      onAnswerSelected,
      debugStats,
      getCorrectAnswersCount,
      getIncorrectAnswersCount,
      getCurrentAccuracy,
      getUnansweredQuestionsCount,
      checkAnswers,
      viewResults,
      retakeQuiz,
      saveQuiz,
      shuffleArray
    }
  }
}
</script>
