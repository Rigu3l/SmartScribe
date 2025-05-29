<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 flex justify-between items-center">
      <router-link to="/" class="text-xl font-bold">SmartScribe</router-link>
      <div class="space-x-2">
        <router-link to="/signup" class="px-4 py-2 border border-white rounded-md hover:bg-gray-800 transition">Sign Up</router-link>
        <router-link to="/contact" class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-200 transition">Contact</router-link>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
      <div class="bg-gray-800 rounded-lg p-8 w-full max-w-md">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
            <font-awesome-icon :icon="['fas', 'book']" class="text-gray-900 text-2xl" />
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
          
          <div class="mb-6 relative">
            <input 
              :type="passwordVisible ? 'text' : 'password'" 
              v-model="password" 
              placeholder="Password" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500 pr-10"
              required
            />
            <button
              type="button"
              @click="passwordVisible = !passwordVisible"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white"
            >
              <font-awesome-icon :icon="['fas', passwordVisible ? 'eye-slash' : 'eye']" />
            </button>
          </div>
          
          <button 
            type="submit" 
            class="w-full p-3 bg-white text-gray-900 rounded font-medium hover:bg-gray-200 transition"
            :disabled="isLoading"
          >
            <span v-if="isLoading">
              <font-awesome-icon :icon="['fas', 'spinner']" spin />
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>
        
        <div class="flex justify-center space-x-4 my-6">
          <button class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
            <font-awesome-icon :icon="['fab', 'facebook-f']" />
          </button>
          <button class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
            <font-awesome-icon :icon="['fab', 'google']" class="text-gray-900" />
          </button>
          <button class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
            <font-awesome-icon :icon="['fab', 'apple']" class="text-gray-900" />
          </button>
        </div>
        
        <div class="text-center text-sm">
          <p class="text-gray-400">
            Don't have a SmartScribe Account? 
            <router-link to="/signup" class="text-white cursor-pointer hover:underline">Sign Up</router-link>
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

<script>
import { ref } from 'vue';
// import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'LoginView',
  setup() {
    // const store = useStore();
    const router = useRouter();
    
    const email = ref('');
    const password = ref('');
    const isLoading = ref(false);
    const errorMessage = ref('');

    const passwordVisible = ref(false);

    const handleLogin = async () => {
      try {
        isLoading.value = true;
        errorMessage.value = '';
        
        // await store.dispatch('auth/login', {
        //   email: email.value,
        //   password: password.value
        // });
        let data = await axios.post('http://localhost:3000/api/auth/login', {
          email: email.value,
          password: password.value
        });

        if (data) {
          localStorage.setItem("user", JSON.stringify(data.data.user));
          router.push('/dashboard');
        }
        
      } catch (error) {
        errorMessage.value = error.message || 'Failed to login. Please try again.';
      } finally {
        isLoading.value = false;
      }
    };

    return {
      email,
      password,
      isLoading,
      errorMessage,
      handleLogin,
      passwordVisible
    };
  }
}
</script>