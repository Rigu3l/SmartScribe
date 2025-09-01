<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white overflow-x-hidden">
    <!-- Header (same as other pages) -->
    <header class="p-3 sm:p-4 bg-gray-800 flex justify-between items-center">
      <div class="text-lg sm:text-xl font-bold">SmartScribe</div>
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
        <font-awesome-icon :icon="['fas', sidebarOpen ? 'times' : 'bars']" />
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
                class="flex items-center space-x-2 p-2 rounded-md bg-gray-700"
              >
                <font-awesome-icon :icon="['fas', 'book']" />
                <span>My Notes</span>
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

      <!-- Note Detail Main Content -->
      <main class="flex-grow p-4 sm:p-6 ml-0 md:ml-0" style="width: 100vw; max-width: 100vw;">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-3xl sm:text-4xl text-blue-500" />
        </div>
        
        <div v-else-if="note && !error">
          <div class="flex flex-col justify-between items-start mb-6 space-y-4" style="flex-direction: column !important;">
            <div>
              <h1 class="text-xl sm:text-2xl font-bold">{{ note.title }}</h1>
              <p class="text-gray-400 text-xs sm:text-sm">Last edited: {{ note.lastEdited }}</p>
            </div>
            <div class="flex space-x-2 sm:space-x-3 w-full sm:w-auto">
              <button @click="editNote" class="flex-1 sm:flex-none px-3 py-2 sm:px-4 sm:py-2 text-sm sm:text-base bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'edit']" class="mr-2" /> Edit
              </button>
              <button @click="showExportOptions = !showExportOptions" class="flex-1 sm:flex-none px-3 py-2 sm:px-4 sm:py-2 text-sm sm:text-base bg-gray-700 rounded-md hover:bg-gray-600 transition relative">
                <font-awesome-icon :icon="['fas', 'file-export']" class="mr-2" /> Export
                
                <!-- Export Options Dropdown -->
                <div v-if="showExportOptions" class="absolute right-0 mt-2 w-48 sm:w-56 bg-gray-800 rounded-md shadow-lg py-1 z-10 max-w-full">
                  <button @click="exportNote('pdf')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-code']" class="mr-2" /> HTML for PDF conversion
                  </button>
                  <button @click="exportNote('word')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-word']" class="mr-2" /> Word Document (.doc)
                  </button>
                  <button @click="exportNote('text')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-alt']" class="mr-2" /> Plain Text (.txt)
                  </button>
                </div>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:gap-6 mb-6" style="grid-template-columns: 1fr;">
            <!-- Original Text -->
            <div class="bg-gray-800 rounded-lg p-4 sm:p-6">
              <h2 class="text-base sm:text-lg font-semibold mb-4">Original Text</h2>
              <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-gray-200 h-64 sm:h-96 overflow-y-auto overflow-x-hidden text-sm sm:text-base break-words">
                {{ note.originalText }}
              </div>
            </div>

            <!-- AI Summary -->
            <div class="bg-gray-800 rounded-lg p-4 sm:p-6">
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-2 sm:space-y-0">
                <h2 class="text-base sm:text-lg font-semibold">AI Summary</h2>
                <button @click="generateSummary" class="w-full sm:w-auto px-3 py-2 sm:px-3 sm:py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition" :disabled="generatingSummary">
                  <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-1" :spin="generatingSummary" /> {{ generatingSummary ? 'Generating...' : 'Generate Summary' }}
                </button>
              </div>
              <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-gray-200 h-64 sm:h-96 overflow-y-auto overflow-x-hidden text-sm sm:text-base break-words">
                {{ note.summary || 'No summary available. Click "Generate Summary" to create one.' }}
              </div>
            </div>
          </div>

          <!-- Keywords and Tags -->
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6 mb-6">
            <h2 class="text-base sm:text-lg font-semibold mb-4">Keywords & Tags</h2>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(keyword, index) in note.keywords"
                :key="`keyword-${index}`"
                class="px-2 py-1 sm:px-3 sm:py-1 bg-blue-600 rounded-full text-xs sm:text-sm"
              >
                {{ keyword }}
              </span>
            </div>
          </div>

          <!-- Quiz Section -->
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col justify-between items-start mb-4 space-y-2" style="flex-direction: column !important;">
              <h2 class="text-base sm:text-lg font-semibold">Study Quiz</h2>
              <button @click="generateQuiz" class="w-full sm:w-auto px-3 py-2 sm:px-3 sm:py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition" :disabled="isGeneratingQuiz">
                <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-1" :spin="isGeneratingQuiz" /> {{ isGeneratingQuiz ? 'Generating Quiz...' : 'Generate New Quiz' }}
              </button>
            </div>

            <!-- Quiz Results -->
            <div v-if="showResults !== undefined && showResults" class="mb-6 bg-gray-700 rounded-lg p-4">
              <div class="text-center">
                <h3 class="text-lg font-semibold mb-2">Quiz Results</h3>
                <div class="text-3xl font-bold mb-2" :class="quizScore === quizQuestions.length ? 'text-green-400' : quizScore >= quizQuestions.length * 0.7 ? 'text-yellow-400' : 'text-red-400'">
                  {{ quizScore }}/{{ quizQuestions.length }}
                </div>
                <p class="text-gray-300 mb-2">
                  {{ Math.round((quizScore / quizQuestions.length) * 100) }}% Correct
                </p>
                <p class="text-sm text-gray-400 mb-4">
                  You got {{ quizScore }} out of {{ quizQuestions.length }} questions correct
                </p>
                <div class="flex justify-center space-x-4">
                  <button @click="showResults = false; resetQuiz()" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                    Retake Quiz
                  </button>
                  <button @click="generateQuiz()" class="px-4 py-2 bg-gray-600 rounded-md hover:bg-gray-700 transition">
                    New Quiz
                  </button>
                </div>
              </div>
            </div>

            <!-- Quiz Questions -->
            <div v-if="quizQuestions.length > 0 && showResults !== undefined && !showResults" class="space-y-4">
              <div v-for="(question, qIndex) in quizQuestions" :key="`question-${qIndex}`" class="bg-gray-700 rounded-lg p-4">
                <p class="font-medium mb-2">{{ qIndex + 1 }}. {{ question.text }}</p>
                <div class="space-y-2 ml-4">
                  <div
                    v-for="(option, oIndex) in question.options"
                    :key="`option-${qIndex}-${oIndex}`"
                    class="flex items-center space-x-2"
                    :class="{
                      'text-green-400 font-semibold': quizCompleted && oIndex === question.correctAnswer,
                      'text-red-400': quizCompleted && question.selectedAnswer === oIndex && oIndex !== question.correctAnswer,
                      'text-gray-400': quizCompleted && question.selectedAnswer !== oIndex && oIndex !== question.correctAnswer
                    }"
                  >
                    <input
                      type="radio"
                      :id="`q${qIndex}-o${oIndex}`"
                      :name="`question-${qIndex}`"
                      :value="oIndex"
                      v-model="question.selectedAnswer"
                      class="text-blue-600"
                      :disabled="quizCompleted"
                    />
                    <label :for="`q${qIndex}-o${oIndex}`" class="cursor-pointer block p-2 rounded transition-colors" :class="{
                      'cursor-not-allowed': quizCompleted,
                      'bg-green-100 text-green-800 border border-green-300': quizCompleted && oIndex === question.correctAnswer,
                      'bg-red-100 text-red-800 border border-red-300': quizCompleted && question.selectedAnswer === oIndex && oIndex !== question.correctAnswer,
                      'border border-gray-200': !quizCompleted && question.selectedAnswer === oIndex,
                      'border border-transparent': !quizCompleted && question.selectedAnswer !== oIndex
                    }">
                      {{ option }}
                    </label>
                  </div>
                </div>

                <!-- Show explanation after quiz completion -->
                <div v-if="quizCompleted" class="mt-3 p-3 bg-gray-600 rounded text-sm">
                  <p class="text-gray-300">
                    <strong>Correct Answer:</strong> {{ question.originalOptions ? question.originalOptions[question.originalCorrectIndex] : question.options[question.correctAnswer] }}
                  </p>
                  <p v-if="question.selectedAnswer !== null && question.selectedAnswer !== question.correctAnswer" class="text-red-300 mt-1">
                    <strong>Your Answer:</strong> {{ question.options[question.selectedAnswer] }}
                  </p>
                  <p v-if="question.selectedAnswer !== null && question.selectedAnswer === question.correctAnswer" class="text-green-300 mt-1">
                    <strong>Your Answer:</strong> {{ question.options[question.selectedAnswer] }} ✓
                  </p>
                </div>
              </div>

              <div class="flex justify-between">
                <button
                  v-if="!quizCompleted"
                  @click="checkQuizAnswers"
                  class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition"
                  :disabled="!allQuestionsAnswered"
                >
                  Check Answers
                </button>
                <button
                  v-if="quizCompleted"
                  @click="showResults = true"
                  class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition"
                >
                  View Results
                </button>
                <button @click="resetQuiz" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
                  Reset
                </button>
              </div>

              <!-- Progress indicator -->
              <div v-if="!quizCompleted" class="text-center text-sm text-gray-400">
                {{ answeredQuestionsCount }}/{{ quizQuestions.length }} questions answered
              </div>
            </div>

            <div v-else-if="showResults !== undefined && !showResults" class="text-center py-8 text-gray-400">
              <p>No quiz questions generated yet. Click "Generate New Quiz" to create questions based on your notes.</p>
            </div>
          </div>
        </div>
        
        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
           <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-4xl text-red-400 mb-4" />
           <h2 class="text-xl font-medium mb-2">Error Loading Note</h2>
           <p class="text-gray-400 mb-4">{{ error }}</p>
           <router-link to="/notes" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
             Back to Notes
           </router-link>
         </div>

         <div v-else class="flex flex-col items-center justify-center h-full">
           <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="text-4xl text-gray-400 mb-4" />
           <h2 class="text-xl font-medium mb-2">Note Not Found</h2>
           <p class="text-gray-400 mb-4">The note you're looking for doesn't exist or has been deleted.</p>
           <router-link to="/notes" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
             Back to Notes
           </router-link>
         </div>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
// import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';

export default {
  name: 'NoteDetailView',
  setup() {
    // const store = useStore();
    const router = useRouter();
    const route = useRoute();

    const note = ref(null);
    const isLoading = ref(true);
    const showExportOptions = ref(false);
    const quizQuestions = ref([]);
    const error = ref(null);
    const generatingSummary = ref(false);
    const sidebarOpen = ref(false);

    // Initialize generatingQuiz with proper reactive reference
    const generatingQuiz = ref(false);

    // Quiz state management - Initialize all reactive variables at once
    const quizCompleted = ref(false);
    const quizScore = ref(0);
    const showResults = ref(false);

    // Computed property to safely access generatingQuiz state
    const isGeneratingQuiz = computed(() => generatingQuiz.value);

    // Computed properties for quiz tracking
    const allQuestionsAnswered = computed(() => {
      return quizQuestions.value.length > 0 &&
             quizQuestions.value.every(question => question.selectedAnswer !== null);
    });

    const answeredQuestionsCount = computed(() => {
      return quizQuestions.value.filter(question => question.selectedAnswer !== null).length;
    });

    onMounted(async () => {
      try {
        const noteId = parseInt(route.params.id);

        if (!noteId || isNaN(noteId)) {
          error.value = 'Invalid note ID';
          isLoading.value = false;
          return;
        }

        // Fetch the actual note data from API
        const response = await api.getNote(noteId);

        if (response.data.success && response.data.data) {
          const noteData = response.data.data;

          note.value = {
            id: noteData.id,
            title: noteData.title || 'Untitled Note',
            lastEdited: noteData.last_edited || 'Unknown',
            originalText: noteData.original_text || 'No content available',
            summary: noteData.summary || 'No summary available',
            keywords: noteData.keywords ? noteData.keywords.split(',').map(k => k.trim()) : []
          };
        } else {
          error.value = response.data?.error || 'Note not found';
        }
      } catch (error) {
        console.error('Error loading note:', error);
        error.value = 'Failed to load note. Please try again.';
      } finally {
        isLoading.value = false;
      }
    });

    const editNote = () => {
      if (note.value) {
        router.push(`/notes/edit?id=${note.value.id}`);
      }
    };

    const exportNote = async (format) => {
      if (!note.value || !note.value.id) {
        alert('No note to export');
        return;
      }

      try {
        console.log(`Exporting note ${note.value.id} as ${format}...`);
        showExportOptions.value = false;

        // Map frontend format names to backend format names
        const backendFormat = format === 'word' ? 'docx' : format === 'text' ? 'txt' : format;
        const fileExtension = format === 'pdf' ? 'html' : format === 'word' ? 'doc' : format === 'text' ? 'txt' : format;

        console.log(`Making API call to export with backendFormat: ${backendFormat}`);
        const response = await api.exportNote(note.value.id, backendFormat);
        console.log('API response received:', response);
        console.log('Response data type:', typeof response.data);
        console.log('Response data length:', response.data ? response.data.length : 'N/A');

        // Create blob and download
        const mimeType = backendFormat === 'pdf' ? 'text/html' :
                        backendFormat === 'docx' ? 'application/msword' :
                        'text/plain';

        console.log(`Creating blob with MIME type: ${mimeType}`);
        const blob = new Blob([response.data], { type: mimeType });
        console.log('Blob created:', blob);
        console.log('Blob size:', blob.size);

        const url = window.URL.createObjectURL(blob);
        console.log('Blob URL created:', url);

        const link = document.createElement('a');
        link.href = url;
        const baseFilename = note.value.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        const filename = format === 'pdf' ? `${baseFilename}_for_pdf.${fileExtension}` : `${baseFilename}_export.${fileExtension}`;
        link.download = filename;
        console.log('Download filename:', filename);

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        console.log(`Note exported as ${format} successfully`);
        alert(`File exported successfully! Check your downloads folder for "${filename}"`);
      } catch (error) {
        console.error('Export error:', error);
        console.error('Error details:', error.response || error.message);
        alert(`Failed to export note as ${format.toUpperCase()}. Please try again. Error: ${error.message}`);
      }
    };

    const generateSummary = async () => {
      if (!note.value || generatingSummary.value) return;

      generatingSummary.value = true;
      try {
        console.log('Starting summary generation for note:', note.value.id);
        const response = await api.createSummary(note.value.id, { length: 'auto' });
        console.log('Summary API response:', response.data);

        if (response.data.success) {
          console.log('Summary created successfully, refreshing note data...');
          // Refresh the note data to get the updated summary
          const noteResponse = await api.getNote(note.value.id);
          console.log('Note refresh response:', noteResponse.data);

          if (noteResponse.data.success && noteResponse.data.data) {
            const newSummary = noteResponse.data.data.summary;
            console.log('New summary from server:', newSummary);
            note.value.summary = newSummary;
            console.log('Updated note summary in UI:', note.value.summary);
          } else {
            console.error('Failed to refresh note data:', noteResponse.data);
          }
          alert('Summary generated successfully!');
        } else {
          console.error('Summary generation failed:', response.data.error);
          alert('Failed to generate summary: ' + (response.data.error || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error generating summary:', error);
        alert('Failed to generate summary. Please try again.');
      } finally {
        generatingSummary.value = false;
      }
    };

    const generateQuiz = async () => {
      if (!note.value || generatingQuiz.value) return;

      generatingQuiz.value = true;

      try {
        // Use the AI summary for better context, fallback to original text if summary not available
        const usingSummary = note.value.summary && note.value.summary !== 'No summary available. Click "Generate Summary" to create one.';
        const textForQuiz = usingSummary ? note.value.summary : note.value.originalText;

        // Ensure we have enough content for quiz generation
        if (!textForQuiz || textForQuiz.length < 50) {
          alert('Not enough content available for quiz generation. Please generate a summary first or ensure the note has sufficient text.');
          return;
        }

        // Inform user about content source
        const contentSource = usingSummary ? 'AI summary' : 'original text';
        console.log(`Generating quiz for note "${note.value.title}" using ${contentSource} (${textForQuiz.length} characters)`);

        console.log('Generating quiz using:', textForQuiz.length < 100 ? textForQuiz : textForQuiz.substring(0, 100) + '...');
        console.log('Text source:', note.value.summary && note.value.summary !== 'No summary available. Click "Generate Summary" to create one.' ? 'AI Summary' : 'Original Text');
        console.log('Text length:', textForQuiz.length, 'characters');

        // First, generate quiz questions using GPT
        const gptResponse = await api.gpt.generateQuiz(textForQuiz, {
          difficulty: 'medium',
          questionCount: 5,
          noteTitle: note.value.title || 'Untitled Note'
        });

        if (gptResponse.data.success && gptResponse.data.data) {
          const generatedQuestions = gptResponse.data.data.questions || [];

          if (generatedQuestions.length === 0) {
            alert('No quiz questions were generated. The content may be too short or unclear.');
            return;
          }

          // Then, save the quiz to the database
          const quizResponse = await api.createQuiz(note.value.id, {
            questions: generatedQuestions,
            difficulty: 'medium'
          });

          if (quizResponse.data.success) {
            // Display the generated questions
            quizQuestions.value = generatedQuestions.map((q) => {
              // Handle different correct answer formats (letter vs index)
              let correctAnswer = q.correct_answer || q.correctAnswer || 0;

              // Convert letter answers (A, B, C, D) to numeric indices (0, 1, 2, 3)
              if (typeof correctAnswer === 'string') {
                const letterToIndex = { 'A': 0, 'B': 1, 'C': 2, 'D': 3 };
                correctAnswer = letterToIndex[correctAnswer.toUpperCase()] ?? 0;
              }

              // Shuffle the answer options while keeping A-B-C-D labels in order
              const shuffledOptions = [...q.options];
              const originalCorrectIndex = correctAnswer;

              // Fisher-Yates shuffle algorithm to randomize option positions
              for (let i = shuffledOptions.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffledOptions[i], shuffledOptions[j]] = [shuffledOptions[j], shuffledOptions[i]];
              }

              // Find the new index of the correct answer after shuffling
              const correctAnswerText = q.options[originalCorrectIndex];
              const newCorrectIndex = shuffledOptions.findIndex(option => option === correctAnswerText);

              const processedQuestion = {
                text: q.question,
                options: shuffledOptions,
                correctAnswer: newCorrectIndex,
                selectedAnswer: null,
                originalOptions: q.options, // Keep original for results display
                originalCorrectIndex: originalCorrectIndex
              };

              // Debug logging to verify correct answers are set
              console.log('Processed quiz question:', {
                question: processedQuestion.text,
                correctAnswer: processedQuestion.correctAnswer,
                correctAnswerText: processedQuestion.options[processedQuestion.correctAnswer],
                shuffled: true
              });

              return processedQuestion;
            });
            alert(`Quiz generated successfully! Created ${generatedQuestions.length} questions based on the note content.`);
          } else {
            alert('Failed to save quiz: ' + (quizResponse.data.error || 'Unknown error'));
          }
        } else {
          alert('Failed to generate quiz questions: ' + (gptResponse.data.error || 'Unknown error'));
        }
      } catch (error) {
        console.error('Error generating quiz:', error);
        alert('Failed to generate quiz. Please try again.');
      } finally {
        generatingQuiz.value = false;
      }
    };

    const checkQuizAnswers = () => {
      if (!allQuestionsAnswered.value) {
        alert('Please answer all questions before checking your results.');
        return;
      }

      let correctCount = 0;
      quizQuestions.value.forEach(question => {
        if (question.selectedAnswer === question.correctAnswer) {
          correctCount++;
        }
      });

      quizScore.value = correctCount;
      quizCompleted.value = true;
    };

    const resetQuiz = () => {
      quizQuestions.value.forEach(question => {
        question.selectedAnswer = null;
      });
      quizCompleted.value = false;
      quizScore.value = 0;
      showResults.value = false;
    };

    return {
      note,
      isLoading,
      error,
      showExportOptions,
      quizQuestions,
      generatingSummary,
      generatingQuiz,
      isGeneratingQuiz,
      sidebarOpen,
      quizCompleted,
      quizScore,
      showResults,
      allQuestionsAnswered,
      answeredQuestionsCount,
      editNote,
      exportNote,
      generateSummary,
      generateQuiz,
      checkQuizAnswers,
      resetQuiz
    };
  }
}
</script>