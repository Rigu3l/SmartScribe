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

      <!-- Note Editor Main Content -->
      <main class="flex-grow p-4 sm:p-6 ml-0 md:ml-0" style="width: 100vw; max-width: 100vw;">
        <div v-if="isLoading" class="flex justify-center items-center h-full">
          <font-awesome-icon :icon="['fas', 'spinner']" spin class="text-4xl text-blue-500" />
        </div>

        <div v-else-if="error" class="flex flex-col items-center justify-center h-full">
          <font-awesome-icon :icon="['fas', 'times']" class="text-4xl text-red-400 mb-4" />
          <h2 class="text-xl font-medium mb-2">Error Loading Note</h2>
          <p class="text-gray-400 mb-4">{{ error }}</p>
          <router-link to="/notes" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
            Back to Notes
          </router-link>
        </div>

        <div v-else>
          <div class="flex flex-col justify-between items-start mb-6 space-y-4" style="flex-direction: column !important;">
            <div class="w-full sm:w-auto flex-1">
              <h1 class="text-xl sm:text-2xl font-bold">
                <input
                  v-model="note.title"
                  class="bg-transparent border-b border-gray-700 focus:border-blue-500 focus:outline-none pb-1 w-full text-lg sm:text-xl"
                  placeholder="Note Title"
                />
              </h1>
              <p class="text-gray-400 text-xs sm:text-sm">Last edited: {{ note.lastEdited }}</p>
            </div>
            <div class="flex space-x-2 sm:space-x-3 w-full sm:w-auto">
              <button @click="saveNote" :disabled="isSaving" class="flex-1 sm:flex-none px-3 py-2 sm:px-4 sm:py-2 text-sm sm:text-base bg-blue-600 rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                <font-awesome-icon :icon="isSaving ? ['fas', 'spinner'] : ['fas', 'save']" :spin="isSaving" class="mr-2" />
                {{ isSaving ? 'Saving...' : 'Save' }}
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

        <div class="grid grid-cols-1 gap-4 sm:gap-6" style="grid-template-columns: 1fr;">
          <!-- Original Text (OCR Result) -->
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-semibold mb-4">Original Text</h2>
            <div class="relative">
              <textarea
                v-model="note.originalText"
                class="w-full h-64 sm:h-96 bg-gray-700 rounded-lg p-3 sm:p-4 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base overflow-x-hidden resize-none"
                placeholder="OCR extracted text will appear here"
              ></textarea>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button title="Edit Original Text" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'edit']" class="text-xs sm:text-sm" />
                </button>
                <button title="Rescan" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'camera']" class="text-xs sm:text-sm" />
                </button>
              </div>
            </div>
          </div>

          <!-- AI Generated Summary -->
          <div class="bg-gray-800 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col justify-between items-start mb-4 space-y-2" style="flex-direction: column !important;">
              <h2 class="text-base sm:text-lg font-semibold">AI Summary</h2>
              <div class="flex items-center space-x-2 w-full sm:w-auto">
                <span class="text-xs sm:text-sm text-gray-400">Length:</span>
                <select
                  v-model="summaryLength"
                  class="bg-gray-700 rounded px-2 py-1 sm:p-1 text-xs sm:text-sm flex-1 sm:flex-none"
                >
                  <option value="auto">Auto</option>
                  <option value="short">Short</option>
                  <option value="medium">Medium</option>
                  <option value="long">Long</option>
                </select>
              </div>
            </div>
            <div class="relative">
              <textarea
                v-model="note.summary"
                class="w-full h-64 sm:h-96 bg-gray-700 rounded-lg p-3 sm:p-4 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base overflow-x-hidden resize-none"
                placeholder="AI-generated summary will appear here"
              ></textarea>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button @click="generateSummary" :disabled="generatingSummary" title="Regenerate Summary" class="p-1 bg-gray-600 rounded hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed">
                  <font-awesome-icon :icon="['fas', 'sync-alt']" class="text-xs sm:text-sm" :spin="generatingSummary" />
                </button>
                <button title="Copy to Clipboard" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'copy']" class="text-xs sm:text-sm" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Keywords and Tags -->
        <div class="mt-6 bg-gray-800 rounded-lg p-4 sm:p-6">
          <h2 class="text-base sm:text-lg font-semibold mb-4">Keywords & Tags</h2>
          <div class="flex flex-wrap gap-2 mb-4">
            <span
              v-for="(keyword, index) in note.keywords"
              :key="`keyword-${index}`"
              class="px-2 py-1 sm:px-3 sm:py-1 bg-blue-600 rounded-full text-xs sm:text-sm flex items-center"
            >
              {{ keyword }}
              <button @click="removeKeyword(index)" class="ml-1 sm:ml-2 text-xs">
                <font-awesome-icon :icon="['fas', 'times']" />
              </button>
            </span>
            <input
              v-model="newKeyword"
              @keyup.enter="addKeyword"
              class="px-2 py-1 sm:px-3 sm:py-1 bg-gray-700 rounded-full text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 flex-1 min-w-0"
              placeholder="Add keyword..."
            />
          </div>
          <button @click="extractKeywords" class="px-3 py-2 sm:px-3 sm:py-1 bg-gray-700 rounded text-sm hover:bg-gray-600 transition w-full sm:w-auto">
            <font-awesome-icon :icon="['fas', 'magic']" class="mr-1" /> Auto-extract Keywords
          </button>
        </div>

        <!-- Quiz Generator -->
        <div class="mt-6 bg-gray-800 rounded-lg p-4 sm:p-6">
          <h2 class="text-base sm:text-lg font-semibold mb-4">Study Quiz</h2>
          <p class="text-gray-400 text-sm mb-4">Generate quiz questions based on your notes to test your knowledge.</p>
          <div class="flex flex-col space-y-3 mb-6" style="flex-direction: column !important;">
            <button @click="generateQuiz" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition w-full sm:w-auto">
              <font-awesome-icon :icon="['fas', 'question-circle']" class="mr-2" /> Generate Quiz
            </button>
            <div class="flex space-x-3 w-full sm:w-auto">
              <select v-model="quizDifficulty" class="bg-gray-700 rounded px-3 py-2 flex-1 sm:flex-none text-sm">
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
              </select>
              <select v-model="quizQuestionCount" class="bg-gray-700 rounded px-3 py-2 flex-1 sm:flex-none text-sm">
                <option value="5">5 Questions</option>
                <option value="10">10 Questions</option>
                <option value="15">15 Questions</option>
              </select>
            </div>
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
        </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';

export default {
  name: 'NoteEditorView',
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();

    const note = ref({
       title: '',
       lastEdited: new Date().toLocaleString(),
       originalText: '',
       summary: '',
       keywords: []
     });

     const summaryLength = ref('auto');
     const newKeyword = ref('');
     const quizDifficulty = ref('medium');
     const quizQuestionCount = ref('5');
     const quizQuestions = ref([]);
     const isLoading = ref(false);
     const error = ref(null);
     const isSaving = ref(false);
     const generatingSummary = ref(false);
     const showExportOptions = ref(false);
     const sidebarOpen = ref(false);

    onMounted(async () => {
      try {
        const noteId = route.query.id;

        if (noteId) {
          // Fetch the actual note data from API
          isLoading.value = true;
          const response = await api.getNote(noteId);

          if (response.data.success && response.data.data) {
            const noteData = response.data.data;

            note.value = {
              id: noteData.id,
              title: noteData.title || '',
              lastEdited: noteData.last_edited || new Date().toLocaleString(),
              originalText: noteData.original_text || '',
              summary: noteData.summary || '',
              keywords: noteData.keywords ? noteData.keywords.split(',').map(k => k.trim()) : []
            };
          } else {
            error.value = response.data?.error || 'Note not found';
          }
        } else {
          // Check if we have temp image data from OCR
          const tempImageData = store.getters['notes/getTempImageData'];
          if (tempImageData) {
            note.value.originalText = tempImageData.originalText;
            // Generate summary automatically
            generateSummary();
          }
        }
      } catch (error) {
        console.error('Error loading note:', error);
        error.value = 'Failed to load note. Please try again.';
      } finally {
        isLoading.value = false;
      }
    });

    const saveNote = async () => {
      try {
        // Check if user is authenticated
        const userData = localStorage.getItem('user');
        if (!userData) {
          alert('You must be logged in to save notes. Please log in and try again.');
          router.push('/login');
          return;
        }

        let user;
        try {
          user = JSON.parse(userData);
        } catch (e) {
          console.error('Invalid user data in localStorage:', e);
          alert('Authentication data is corrupted. Please log in again.');
          localStorage.removeItem('user');
          localStorage.removeItem('token');
          router.push('/login');
          return;
        }

        if (!user || !user.id) {
          alert('Invalid user session. Please log in again.');
          router.push('/login');
          return;
        }

        if (!note.value.title.trim()) {
          alert('Please enter a title for your note.');
          return;
        }

        if (!note.value.originalText.trim()) {
          alert('Please add some content to your note.');
          return;
        }

        // Set saving state
        isSaving.value = true;

        // Update last edited timestamp
        note.value.lastEdited = new Date().toLocaleString();

        // Prepare note data for API
        const noteData = {
          title: note.value.title,
          text: note.value.originalText,
          summary: note.value.summary,
          keywords: note.value.keywords.join(',')
        };

        console.log('Saving note:', {
          hasId: !!note.value.id,
          noteId: note.value.id,
          noteData: noteData,
          userId: user.id
        });

        // Log current localStorage state for debugging
        console.log('Current localStorage state:', {
          token: !!localStorage.getItem('token'),
          user: localStorage.getItem('user')
        });

        if (note.value.id) {
          // Update existing note
          console.log('Updating existing note with ID:', note.value.id);
          const response = await api.updateNote(note.value.id, noteData);
          console.log('Update response:', response.data);

          if (response.data.success) {
            alert('Note updated successfully!');
            router.push('/notes?refresh=true');
          } else {
            throw new Error(response.data.error || 'Failed to update note');
          }
        } else {
          // Create new note
          console.log('Creating new note');
          const response = await api.createNote(noteData);
          console.log('Create response:', response.data);

          if (response.data.success) {
            note.value.id = response.data.note_id;
            alert('Note saved successfully!');
            router.push('/notes?refresh=true');
          } else {
            throw new Error(response.data.error || 'Failed to save note');
          }
        }
      } catch (error) {
        console.error('Error saving note:', error);

        // Provide more specific error messages
        let errorMessage = 'Failed to save note. Please try again.';

        if (error.response) {
          if (error.response.status === 401) {
            errorMessage = 'Authentication failed. Please log in again.';
          } else if (error.response.status === 403) {
            errorMessage = 'You do not have permission to perform this action.';
          } else if (error.response.data?.error) {
            errorMessage = error.response.data.error;
          }
        } else if (error.message) {
          errorMessage = error.message;
        }

        alert(errorMessage);
      } finally {
        // Reset saving state
        isSaving.value = false;
      }
    };

    const generateSummary = async () => {
      if (!note.value.originalText.trim()) {
        alert('Please add some text to summarize first.');
        return;
      }

      generatingSummary.value = true;
      try {
        // If we have a note ID (editing existing note), use the API
        if (note.value.id) {
          const response = await api.createSummary(note.value.id, { length: summaryLength.value });
          if (response.data.success) {
            // Refresh the note data to get the updated summary
            const noteResponse = await api.getNote(note.value.id);
            if (noteResponse.data.success) {
              note.value.summary = noteResponse.data.data.summary;
            }
            alert('Summary generated successfully!');
          } else {
            alert('Failed to generate summary: ' + (response.data.error || 'Unknown error'));
          }
        } else {
          // For new notes, use the direct GPT service
          const response = await api.gpt.generateSummary(note.value.originalText, { length: summaryLength.value });
          if (response.data) {
            note.value.summary = response.data;
            alert('Summary generated successfully!');
          } else {
            alert('Failed to generate summary. Please try again.');
          }
        }
      } catch (error) {
        console.error('Error generating summary:', error);
        alert('Failed to generate summary. Please try again.');
      } finally {
        generatingSummary.value = false;
      }
    };

    const addKeyword = () => {
      if (newKeyword.value.trim()) {
        note.value.keywords.push(newKeyword.value.trim());
        newKeyword.value = '';
      }
    };

    const removeKeyword = (index) => {
      note.value.keywords.splice(index, 1);
    };

    const extractKeywords = () => {
      console.log('Extracting keywords from text');
      // In a real app, you would call the GPT API here
      // For now, we'll simulate it

      // Simulate API delay
      setTimeout(() => {
        const extractedKeywords = ['Membrane', 'Cytoplasm', 'Organelles', 'Nucleus'];
        extractedKeywords.forEach(keyword => {
          if (!note.value.keywords.includes(keyword)) {
            note.value.keywords.push(keyword);
          }
        });
      }, 1000);
    };

    const generateQuiz = () => {
      console.log('Generating quiz with difficulty:', quizDifficulty.value, 'and', quizQuestionCount.value, 'questions');
      // In a real app, you would call the GPT API here
      // For now, we'll simulate it

      // Simulate API delay
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

    const exportNote = async (format) => {
      if (!note.value || !note.value.id) {
        alert('Please save the note first before exporting');
        return;
      }

      try {
        console.log(`Exporting note ${note.value.id} as ${format}...`);

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

    return {
      note,
      summaryLength,
      newKeyword,
      quizDifficulty,
      quizQuestionCount,
      quizQuestions,
      isLoading,
      error,
      isSaving,
      generatingSummary,
      showExportOptions,
      sidebarOpen,
      saveNote,
      generateSummary,
      addKeyword,
      removeKeyword,
      extractKeywords,
      generateQuiz,
      checkQuizAnswers,
      resetQuiz,
      exportNote
    };
  }
}
</script>