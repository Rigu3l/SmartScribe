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

      <!-- Note Editor Main Content -->
      <main class="flex-grow p-6">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-2xl font-bold">
              <input 
                v-model="note.title" 
                class="bg-transparent border-b border-gray-700 focus:border-blue-500 focus:outline-none pb-1 w-full"
                placeholder="Note Title"
              />
            </h1>
            <p class="text-gray-400 text-sm">Last edited: {{ note.lastEdited }}</p>
          </div>
          <div class="flex space-x-3">
            <button @click="saveNote" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
              <font-awesome-icon :icon="['fas', 'save']" class="mr-2" /> Save
            </button>
            <button class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 transition">
              <font-awesome-icon :icon="['fas', 'file-export']" class="mr-2" /> Export
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Original Text (OCR Result) -->
          <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Original Text</h2>
            <div class="relative">
              <textarea 
                v-model="note.originalText" 
                class="w-full h-96 bg-gray-700 rounded-lg p-4 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="OCR extracted text will appear here"
              ></textarea>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button title="Edit Original Text" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'edit']" />
                </button>
                <button title="Rescan" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'camera']" />
                </button>
              </div>
            </div>
          </div>

          <!-- AI Generated Summary -->
          <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold">AI Summary</h2>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-400">Summary Length:</span>
                <select 
                  v-model="summaryLength" 
                  @change="generateSummary"
                  class="bg-gray-700 rounded p-1 text-sm"
                >
                  <option value="short">Short</option>
                  <option value="medium">Medium</option>
                  <option value="long">Long</option>
                </select>
              </div>
            </div>
            <div class="relative">
              <textarea 
                v-model="note.summary" 
                class="w-full h-96 bg-gray-700 rounded-lg p-4 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="AI-generated summary will appear here"
              ></textarea>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button @click="generateSummary" title="Regenerate Summary" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'sync-alt']" />
                </button>
                <button title="Copy to Clipboard" class="p-1 bg-gray-600 rounded hover:bg-gray-500">
                  <font-awesome-icon :icon="['fas', 'copy']" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Keywords and Tags -->
        <div class="mt-6 bg-gray-800 rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-4">Keywords & Tags</h2>
          <div class="flex flex-wrap gap-2 mb-4">
            <span 
              v-for="(keyword, index) in note.keywords" 
              :key="`keyword-${index}`"
              class="px-3 py-1 bg-blue-600 rounded-full text-sm flex items-center"
            >
              {{ keyword }}
              <button @click="removeKeyword(index)" class="ml-2 text-xs">
                <font-awesome-icon :icon="['fas', 'times']" />
              </button>
            </span>
            <input 
              v-model="newKeyword" 
              @keyup.enter="addKeyword"
              class="px-3 py-1 bg-gray-700 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Add keyword..."
            />
          </div>
          <button @click="extractKeywords" class="px-3 py-1 bg-gray-700 rounded text-sm hover:bg-gray-600 transition">
            <font-awesome-icon :icon="['fas', 'magic']" class="mr-1" /> Auto-extract Keywords
          </button>
        </div>

        <!-- Quiz Generator -->
        <div class="mt-6 bg-gray-800 rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-4">Study Quiz</h2>
          <p class="text-gray-400 mb-4">Generate quiz questions based on your notes to test your knowledge.</p>
          <div class="flex space-x-3 mb-6">
            <button @click="generateQuiz" class="px-4 py-2 bg-blue-600 rounded-md hover:bg-blue-700 transition">
              <font-awesome-icon :icon="['fas', 'question-circle']" class="mr-2" /> Generate Quiz
            </button>
            <select v-model="quizDifficulty" class="bg-gray-700 rounded px-3">
              <option value="easy">Easy</option>
              <option value="medium">Medium</option>
              <option value="hard">Hard</option>
            </select>
            <select v-model="quizQuestionCount" class="bg-gray-700 rounded px-3">
              <option value="5">5 Questions</option>
              <option value="10">10 Questions</option>
              <option value="15">15 Questions</option>
            </select>
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
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';

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
    
    const summaryLength = ref('medium');
    const newKeyword = ref('');
    const quizDifficulty = ref('medium');
    const quizQuestionCount = ref('5');
    const quizQuestions = ref([]);
    
    onMounted(async () => {
      try {
        const noteId = route.query.id;
        
        if (noteId) {
          // In a real app, we would fetch the note from the store
          // await store.dispatch('notes/fetchNote', noteId);
          // note.value = { ...store.getters['notes/getCurrentNote'] };
          
          // For now, we'll use mock data
          setTimeout(() => {
            note.value = {
              id: parseInt(noteId),
              title: 'Biology Notes - Chapter 5',
              lastEdited: new Date().toLocaleString(),
              originalText: 'The cell is the basic structural, functional, and biological unit of all known organisms. Cells are the smallest units of life, and hence are often referred to as the "building blocks of life". The study of cells is called cell biology, cellular biology, or cytology.\n\nCells consist of cytoplasm enclosed within a membrane, which contains many biomolecules such as proteins and nucleic acids. Most plant and animal cells are only visible under a light microscope, with dimensions between 1 and 100 micrometres. Cells were discovered by Robert Hooke in 1665, who named them for their resemblance to cells inhabited by Christian monks in a monastery.',
              summary: 'Cells are the fundamental units of life, discovered by Robert Hooke in 1665. They are microscopic structures containing cytoplasm, proteins, and nucleic acids enclosed within a membrane. Cell biology (cytology) is the scientific study of cells. Cells range from 1-100 micrometers in size and are only visible under microscopes.',
              keywords: ['Cell', 'Biology', 'Cytology', 'Robert Hooke', 'Microscopic']
            };
          }, 500);
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
      }
    });

    const saveNote = async () => {
      try {
        if (!note.value.title.trim()) {
          alert('Please enter a title for your note.');
          return;
        }
        
        if (!note.value.originalText.trim()) {
          alert('Please add some content to your note.');
          return;
        }
        
        // Update last edited timestamp
        note.value.lastEdited = new Date().toLocaleString();
        
        if (note.value.id) {
          // Update existing note
          await store.dispatch('notes/updateNote', {
            id: note.value.id,
            noteData: note.value
          });
        } else {
          // Create new note
          const newNote = await store.dispatch('notes/createNote', note.value);
          note.value.id = newNote.id;
        }
        
        alert('Note saved successfully!');
        router.push('/notes');
      } catch (error) {
        console.error('Error saving note:', error);
        alert('Failed to save note. Please try again.');
      }
    };

    const generateSummary = () => {
      console.log('Generating summary with length:', summaryLength.value);
      // In a real app, you would call the GPT API here
      // For now, we'll simulate it

      const summaries = {
        short: 'Cells are the basic units of life discovered by Robert Hooke in 1665. They contain cytoplasm, proteins, and nucleic acids within a membrane.',
        medium: 'Cells are the fundamental units of life, discovered by Robert Hooke in 1665. They are microscopic structures containing cytoplasm, proteins, and nucleic acids enclosed within a membrane. Cell biology (cytology) is the scientific study of cells.',
        long: 'Cells are the basic structural, functional, and biological units of all known organisms, often called the "building blocks of life." Discovered by Robert Hooke in 1665, they range from 1-100 micrometers in size and are only visible under microscopes. Cells contain cytoplasm enclosed within a membrane, along with biomolecules like proteins and nucleic acids. The scientific study of cells is called cell biology or cytology.'
      };

      // Simulate API delay
      setTimeout(() => {
        note.value.summary = summaries[summaryLength.value];
      }, 1000);
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

    return {
      note,
      summaryLength,
      newKeyword,
      quizDifficulty,
      quizQuestionCount,
      quizQuestions,
      saveNote,
      generateSummary,
      addKeyword,
      removeKeyword,
      extractKeywords,
      generateQuiz,
      checkQuizAnswers,
      resetQuiz
    };
  }
}
</script>