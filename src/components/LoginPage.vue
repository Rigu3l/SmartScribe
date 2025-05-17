<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 flex justify-between items-center">
      <div class="text-xl font-bold">SmartScribe</div>
      <div class="space-x-2">
        <button @click="navigateTo('signup')" class="px-4 py-2 border border-white rounded-md hover:bg-gray-800 transition">Sign Up</button>
        <button @click="navigateTo('contact')" class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-200 transition">Contact</button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
      <div class="bg-gray-800 rounded-lg p-8 w-full max-w-md">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
            <img src="/placeholder.svg?height=40&width=40" alt="SmartScribe Logo" class="w-10 h-10" />
          </div>
        </div>
        
        <h2 class="text-xl font-semibold text-center mb-1">Continue with SmartScribe</h2>
        <p class="text-sm text-center text-gray-400 mb-6">Sign in to SmartScribe using your email account</p>
        
        <form @submit.prevent="handleLogin">
          <div class="mb-4">
            <input 
              type="email" 
              v-model="email" 
              placeholder="Email.Example@gmail.com" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500"
              required
            />
          </div>
          
          <div class="mb-6">
            <input 
              type="password" 
              v-model="password" 
              placeholder="Password" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500"
              required
            />
          </div>
          
          <button 
            type="submit" 
            class="w-full p-3 bg-white text-gray-900 rounded font-medium hover:bg-gray-200 transition"
          >
            Sign In
          </button>
        </form>
        
        <div class="flex justify-center space-x-4 my-6">
          <button class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
            <i class="fab fa-facebook-f"></i>
          </button>
          <button class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
            <i class="fab fa-google text-gray-900"></i>
          </button>
          <button class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
            <i class="fab fa-apple text-gray-900"></i>
          </button>
        </div>
        
        <div class="text-center text-sm">
          <p class="text-gray-400">
            Don't have a SmartScribe Account? 
            <a @click="navigateTo('signup')" class="text-white cursor-pointer hover:underline">Sign Up</a>
          </p>
        </div>
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

<script setup>
import { ref } from 'vue';

const email = ref('');
const password = ref('');

const handleLogin = () => {
  // Here you would implement authentication logic
  console.log('Login attempt with:', email.value, password.value);
  // After successful login, navigate to dashboard
  navigateTo('dashboard');
};

const navigateTo = (route) => {
  console.log('Navigating to:', route);
  // In a real app, you would use Vue Router
  // router.push({ name: route });
};
</script>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

body {
  margin: 0;
  font-family: 'Arial', sans-serif;
}
</style>