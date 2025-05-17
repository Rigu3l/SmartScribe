<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 flex justify-between items-center">
      <router-link to="/" class="text-xl font-bold">SmartScribe</router-link>
      <div class="space-x-2">
        <router-link to="/login" class="px-4 py-2 border border-white rounded-md hover:bg-gray-800 transition">Sign In</router-link>
        <router-link to="/signup" class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-200 transition">Sign Up</router-link>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
      <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-700">404</h1>
        <h2 class="text-3xl font-semibold mt-4 mb-2">Page Not Found</h2>
        <p class="text-gray-400 mb-8">The page you are looking for doesn't exist or has been moved.</p>
        <router-link to="/" class="px-6 py-3 bg-blue-600 rounded-md hover:bg-blue-700 transition">
          Back to Home
        </router-link>
      </div>
    </main>

    <!-- Footer -->
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
  name: 'NotFoundView'
}
</script>