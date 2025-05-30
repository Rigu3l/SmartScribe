<template>
  <div class="flex flex-col min-h-screen bg-gray-900 text-white">
    <header class="p-4 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="space-x-2">
        <router-link to="/login" class="px-4 py-2 border border-white rounded-md hover:bg-gray-800 transition">Sign In</router-link>
        <router-link to="/signup" class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-200 transition">Sign Up</router-link>
      </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-12">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
          <h1 class="text-4xl font-bold mb-4">SmartScribe: AI Study Helper</h1>
          <p class="text-xl text-gray-400">Transform your study notes into digital content with OCR and AI</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mb-12">
          <div class="bg-gray-800 p-6 rounded-lg">
            <div class="text-blue-500 text-3xl mb-4">
              <font-awesome-icon :icon="['fas', 'camera']" />
            </div>
            <h2 class="text-xl font-semibold mb-2">OCR-based Note Scanning</h2>
            <p class="text-gray-400">Take pictures of your handwritten or printed study notes and convert them to digital text instantly.</p>
          </div>

          <div class="bg-gray-800 p-6 rounded-lg">
            <div class="text-green-500 text-3xl mb-4">
              <font-awesome-icon :icon="['fas', 'robot']" />
            </div>
            <h2 class="text-xl font-semibold mb-2">AI Summarization</h2>
            <p class="text-gray-400">Generate concise summaries of your notes using advanced AI technology.</p>
          </div>

          <div class="bg-gray-800 p-6 rounded-lg">
            <div class="text-yellow-500 text-3xl mb-4">
              <font-awesome-icon :icon="['fas', 'chart-line']" />
            </div>
            <h2 class="text-xl font-semibold mb-2">Study Progress Tracker</h2>
            <p class="text-gray-400">Track how many notes you've scanned and reviewed to stay on top of your studies.</p>
          </div>

          <div class="bg-gray-800 p-6 rounded-lg">
            <div class="text-purple-500 text-3xl mb-4">
              <font-awesome-icon :icon="['fas', 'highlighter']" />
            </div>
            <h2 class="text-xl font-semibold mb-2">Keyword Highlighting</h2>
            <p class="text-gray-400">Automatically highlight important terms and concepts in your notes.</p>
          </div>
        </div>

        <div class="text-center">
          <router-link to="/signup" class="px-6 py-3 bg-blue-600 text-white rounded-md text-lg font-medium hover:bg-blue-700 transition">Get Started</router-link>
        </div>
      </div>
    </main>

    <footer class="p-4 bg-gray-800 text-gray-400 flex justify-between items-center text-sm">
      <div class="flex space-x-4">
        <a href="#" class="hover:text-white">Docs</a>
        <a href="#" class="hover:text-white">Guides</a>
        <a href="#" class="hover:text-white">Help</a>
        <a href="#" class="hover:text-white">Contact</a>
      </div>
      <div>© 2025 SmartScribe Inc.</div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'HomeView'
}
</script>