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
              <router-link to="/notes" class="flex items-center space-x-2 p-2 rounded-md bg-gray-700">
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
              <router-link to="/settings" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-700">
                <font-awesome-icon :icon="['fas', 'cog']" />
                <span>Settings</span>
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Note Detail Main Content -->
      <main class="flex-grow p-6">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-4xl text-blue-500" />
        </div>
        
        <div v-else-if="note && !error">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h1 class="text-2xl font-bold">{{ note.title }}</h1>
              <p class="text-gray-400 text-sm">Last edited: {{ note.lastEdited }}</p>
            </div>
            <div class="flex space-x-3">
              <button @click="editNote" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'edit']" class="mr-2" /> Edit
              </button>
              <button @click="showExportOptions = !showExportOptions" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition relative">
                <font-awesome-icon :icon="['fas', 'file-export']" class="mr-2" /> Export
                
                <!-- Export Options Dropdown -->
                <div v-if="showExportOptions" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-10">
                  <button @click="exportNote('pdf')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-pdf']" class="mr-2" /> Export as PDF
                  </button>
                  <button @click="exportNote('word')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-word']" class="mr-2" /> Export as Word
                  </button>
                  <button @click="exportNote('text')" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
                    <font-awesome-icon :icon="['fas', 'file-alt']" class="mr-2" /> Export as Text
                  </button>
                </div>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Original Text -->
            <div class="bg-gray-800 rounded-lg p-6">
              <h2 class="text-lg font-semibold mb-4">Original Text</h2>
              <div class="bg-gray-700 rounded-lg p-4 text-gray-200 h-96 overflow-y-auto">
                {{ note.originalText }}
              </div>
            </div>

            <!-- AI Summary -->
            <div class="bg-gray-800 rounded-lg p-6">
              <h2 class="text-lg font-semibold mb-4">AI Summary</h2>
              <div class="bg-gray-700 rounded-lg p-4 text-gray-200 h-96 overflow-y-auto">
                {{ note.summary }}
              </div>
            </div>
          </div>

          <!-- Keywords and Tags -->
          <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Keywords & Tags</h2>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="(keyword, index) in note.keywords" 
                :key="`keyword-${index}`"
                class="px-3 py-1 bg-blue-600 rounded-full text-sm"
              >
                {{ keyword }}
              </span>
            </div>
          </div>

          <!-- Quiz Section -->
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold">Study Quiz</h2>
              <button @click="generateQuiz" class="px-3 py-1 bg-blue-600 rounded text-sm hover:bg-blue-700 transition">
                <font-awesome-icon :icon="['fas', 'sync-alt']" class="mr-1" /> Generate New Quiz
              </button>
            </div>
            
            <div v-if="quizQuestions.length > 0" class="space-y-4">
              <div v-for="(question, qIndex) in quizQuestions" :key="`question-${qIndex}`" class="bg-gray-700 rounded-lg p-4">
                <p class="font-medium mb-2">{{ qIndex + 1 }}. {{ question.text }}</p>
                <div class="space-y-2 ml-4">
                  <div 
                    v-for="(option, oIndex) in question.options" 
                    :key="`option-${qIndex}-${oIndex}`"
                    class="flex items-center space-x-2"
                  >
                    <input 
                      type="radio" 
                      :id="`q${qIndex}-o${oIndex}`" 
                      :name="`question-${qIndex}`" 
                      :value="oIndex"
                      v-model="question.selectedAnswer"
                      class="text-blue-600"
                    />
                    <label :for="`q${qIndex}-o${oIndex}`">{{ option }}</label>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between">
                <button @click="checkQuizAnswers" class="px-4 py-2 bg-green-600 rounded-md hover:bg-green-700 transition">
                  Check Answers
                </button>
                <button @click="resetQuiz" class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
                  Reset
                </button>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-400">
              <p>No quiz questions generated yet. Click "Generate New Quiz" to create questions based on your notes.</p>
            </div>
          </div>
        </div>
        
        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
           <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="text-4xl text-red-400 mb-4" />
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
import { ref, onMounted } from 'vue';
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

    const exportNote = (format) => {
      console.log(`Exporting note as ${format}...`);
      showExportOptions.value = false;
      
      // In a real app, this would call the export API
      // For now, we'll just show an alert
      setTimeout(() => {
        alert(`Note exported as ${format.toUpperCase()} successfully!`);
      }, 1000);
    };

    const generateQuiz = () => {
      console.log('Generating quiz...');
      
      // In a real app, this would call the GPT API
      // For now, we'll use mock data
      setTimeout(() => {
        quizQuestions.value = [
          {
            text: 'Who discovered cells in 1665?',
            options: ['Robert Hooke', 'Anton van Leeuwenhoek', 'Matthias Schleiden', 'Theodor Schwann'],
            correctAnswer: 0,
            selectedAnswer: null
          },
          {
            text: 'What is the study of cells called?',
            options: ['Microbiology', 'Histology', 'Cytology', 'Physiology'],
            correctAnswer: 2,
            selectedAnswer: null
          },
          {
            text: 'What is the typical size range of most plant and animal cells?',
            options: ['0.1-1 micrometers', '1-100 micrometers', '100-1000 micrometers', '1-10 millimeters'],
            correctAnswer: 1,
            selectedAnswer: null
          },
          {
            text: 'What encloses the cytoplasm in a cell?',
            options: ['Cell wall', 'Nucleus', 'Membrane', 'Ribosome'],
            correctAnswer: 2,
            selectedAnswer: null
          },
          {
            text: 'Cells are often referred to as the:',
            options: ['Essence of life', 'Building blocks of life', 'Foundation of organisms', 'Microscopic life'],
            correctAnswer: 1,
            selectedAnswer: null
          }
        ];
      }, 1500);
    };

    const checkQuizAnswers = () => {
      let correctCount = 0;
      quizQuestions.value.forEach(question => {
        if (question.selectedAnswer === question.correctAnswer) {
          correctCount++;
        }
      });

      alert(`You got ${correctCount} out of ${quizQuestions.value.length} questions correct!`);
    };

    const resetQuiz = () => {
      quizQuestions.value.forEach(question => {
        question.selectedAnswer = null;
      });
    };

    return {
      note,
      isLoading,
      error,
      showExportOptions,
      quizQuestions,
      editNote,
      exportNote,
      generateQuiz,
      checkQuizAnswers,
      resetQuiz
    };
  }
}
</script>