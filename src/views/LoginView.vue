<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white overflow-x-hidden">
    <!-- Header -->
    <header class="p-6 flex justify-between items-center">
      <router-link to="/" class="text-xl sm:text-2xl font-bold text-white hover:text-blue-400 transition-colors">
        SmartScribe
      </router-link>
      <div class="flex space-x-3">
        <router-link to="/contact" class="px-4 py-2 text-sm sm:text-base text-gray-400 hover:text-white transition-colors">
          Contact
        </router-link>
        <router-link to="/signup" class="px-4 py-2 text-sm sm:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          Sign Up
        </router-link>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8" style="min-height: calc(100vh - 200px);">
      <div class="bg-gray-800 rounded-2xl shadow-2xl p-8 sm:p-10 w-full max-w-md border border-gray-700">
        <!-- Logo Section -->
        <div class="flex justify-center mb-8">
          <div class="w-24 h-24 sm:w-28 sm:h-28 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full overflow-hidden flex items-center justify-center shadow-xl">
            <img :src="logo" alt="SmartScribe Logo" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-full" />
          </div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Welcome Back</h1>
          <p class="text-gray-400 text-sm sm:text-base">Sign in to your SmartScribe account</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-gray-300">
              Email Address
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <font-awesome-icon :icon="['fas', 'envelope']" class="text-gray-500 text-sm" />
              </div>
              <input
                id="email"
                type="email"
                v-model="email"
                placeholder="Enter your email"
                class="w-full pl-10 pr-4 py-3 text-sm sm:text-base border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-700 text-white placeholder-gray-400 focus:bg-gray-600"
                required
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-gray-300">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <font-awesome-icon :icon="['fas', 'lock']" class="text-gray-500 text-sm" />
              </div>
              <input
                id="password"
                :type="passwordVisible ? 'text' : 'password'"
                v-model="password"
                placeholder="Enter your password"
                class="w-full pl-10 pr-12 py-3 text-sm sm:text-base border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-700 text-white placeholder-gray-400 focus:bg-gray-600"
                required
              />
              <button
                type="button"
                @click="passwordVisible = !passwordVisible"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-300 transition-colors"
              >
                <font-awesome-icon :icon="['fas', passwordVisible ? 'eye-slash' : 'eye']" class="text-sm" />
              </button>
            </div>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold text-sm sm:text-base hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="flex items-center justify-center">
              <font-awesome-icon :icon="['fas', 'spinner']" spin class="mr-2" />
              Signing in...
            </span>
            <span v-else class="flex items-center justify-center">
              <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="mr-2" />
              Sign In
            </span>
          </button>
        </form>
        
        <!-- Divider -->
        <div class="relative my-8">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-600"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-gray-800 text-gray-400">Or continue with</span>
          </div>
        </div>

        <!-- Social Login Buttons -->
        <div class="grid grid-cols-3 gap-3 mb-8">
          <button class="w-full flex items-center justify-center px-4 py-3 border border-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200">
            <font-awesome-icon :icon="['fab', 'google']" class="text-red-400 text-lg" />
          </button>
          <button class="w-full flex items-center justify-center px-4 py-3 border border-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200">
            <font-awesome-icon :icon="['fab', 'facebook-f']" class="text-blue-400 text-lg" />
          </button>
          <button class="w-full flex items-center justify-center px-4 py-3 border border-gray-600 rounded-lg hover:bg-gray-700 transition-colors duration-200">
            <font-awesome-icon :icon="['fab', 'apple']" class="text-gray-300 text-lg" />
          </button>
        </div>

        <!-- Sign Up Link -->
        <div class="text-center">
          <p class="text-gray-400 text-sm">
            Don't have an account?
            <router-link to="/signup" class="text-blue-400 hover:text-blue-300 font-medium hover:underline transition-colors">
              Sign up for free
            </router-link>
          </p>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="p-6 text-center text-gray-400 text-sm">
      <div class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-6">
        <a href="#" class="hover:text-gray-300 transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-gray-300 transition-colors">Terms of Service</a>
        <a href="#" class="hover:text-gray-300 transition-colors">Help Center</a>
      </div>
      <div class="mt-4 text-gray-500">
        © 2025 SmartScribe. All rights reserved.
      </div>
    </footer>
  </div>
</template>

<script>
import logo from '@/assets/image/logo.jpg'
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

export default {
  name: 'LoginView',
  setup() {
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

        // Use the centralized API service
        const response = await api.login({
          email: email.value,
          password: password.value
        });

        if (response.data && response.data.user) {
          // Store user data in localStorage
          localStorage.setItem("user", JSON.stringify(response.data.user));
          localStorage.setItem("token", response.data.token || '');

          console.log('🔐 Login successful - Token stored:', response.data.token);
          console.log('👤 User data stored:', response.data.user);

          router.push('/dashboard');
        } else {
          throw new Error('Invalid response from server');
        }

      } catch (error) {
        errorMessage.value = error.response?.data?.error || error.message || 'Failed to login. Please try again.';
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
      passwordVisible,
      logo
    };
  }
}
</script>