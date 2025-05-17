<template>
  <div class="min-h-screen flex flex-col bg-gray-900 text-white">
    <!-- Header -->
    <header class="p-4 flex justify-between items-center">
      <router-link to="/" class="text-xl font-bold">SmartScribe</router-link>
      <div class="space-x-2">
        <router-link to="/login" class="px-4 py-2 border border-white rounded-md hover:bg-gray-800 transition">Sign In</router-link>
        <router-link to="/contact" class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-200 transition">Contact</router-link>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
      <div class="bg-gray-800 rounded-lg p-8 w-full max-w-md">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
            <font-awesome-icon :icon="['fas', 'user-plus']" class="text-gray-900 text-2xl" />
          </div>
        </div>
        
        <h2 class="text-xl font-semibold text-center mb-1">Create Your Account</h2>
        <p class="text-sm text-center text-gray-400 mb-6">Sign up for SmartScribe to start digitizing your notes</p>
        
        <form @submit.prevent="handleSignup">
          <div class="mb-4">
            <input 
              type='text' 
              v-model="name" 
              placeholder="Full Name" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500"
              required
            />
          </div>
          
          <div class="mb-4">
            <input 
              type="email" 
              v-model="email" 
              placeholder="Email Address" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500"
              required
            />
          </div>
          
          <div class="mb-4 relative">
            <input 
              ref="passwordInput"
              type="password" 
              v-model="password" 
              placeholder="Password" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500 pr-10"
              required
            />
            <button
              type="button"
              @click="togglePasswordField('passwordInput')"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white"
            >
              <font-awesome-icon :icon="['fas', 'eye']" class="password-visible-icon hidden" />
              <font-awesome-icon :icon="['fas', 'eye-slash']" class="password-hidden-icon" />
            </button>
          </div>
          
          <div class="mb-6 relative">
            <input 
              ref="confirmPasswordInput"
              type="password" 
              v-model="confirmPassword" 
              placeholder="Confirm Password" 
              class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:border-blue-500 pr-10"
              required
            />
            <button
              type="button"
              @click="togglePasswordField('confirmPasswordInput')"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white"
            >
              <font-awesome-icon :icon="['fas', 'eye']" class="password-visible-icon hidden" />
              <font-awesome-icon :icon="['fas', 'eye-slash']" class="password-hidden-icon" />
            </button>
          </div>
          
          <button 
            type="submit" 
            class="w-full p-3 bg-white text-gray-900 rounded font-medium hover:bg-gray-200 transition"
            :disabled="isLoading"
          >
            <span v-if="isLoading">
              <font-awesome-icon :icon="['fas', 'spinner']" spin />
              Creating account...
            </span>
            <span v-else>Create Account</span>
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
            Already have an account? 
            <router-link to="/login" class="text-white cursor-pointer hover:underline">Sign In</router-link>
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
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
// import { icon } from '@fortawesome/fontawesome-svg-core';

export default {
  name: 'SignupView',
  setup() {
    const store = useStore();
    const router = useRouter();
    
    const name = ref('');
    const email = ref('');
    const password = ref('');
    const confirmPassword = ref('');
    const isLoading = ref(false);
    const errorMessage = ref('');

    const passwordInput = ref(null);
    const confirmPasswordInput = ref(null);

    const togglePasswordField = (inputRef) => {
      const inputElement = inputRef === 'passwordInput' ? passwordInput.value : confirmPasswordInput.value;
      const button = inputElement.nextElementSibling;
      const visibleIcon = button.querySelector('.password-visible-icon');
      const hiddenIcon = button.querySelector('.password-hidden-icon');
      
      if (inputElement.type === 'password') {
        inputElement.type = 'text';
        visibleIcon.classList.remove('hidden');
        hiddenIcon.classList.add('hidden');
      } else {
        inputElement.type = 'password';
        visibleIcon.classList.add('hidden');
        hiddenIcon.classList.remove('hidden');
      }
    };

    const handleSignup = async () => {
      try {
        // Validate passwords match
        if (password.value !== confirmPassword.value) {
          errorMessage.value = 'Passwords do not match';
          return;
        }
        
        isLoading.value = true;
        errorMessage.value = '';
        
        await store.dispatch('auth/register', {
          name: name.value,
          email: email.value,
          password: password.value
        });
        
        router.push('/dashboard');
      } catch (error) {
        errorMessage.value = error.message || 'Failed to create account. Please try again.';
      } finally {
        isLoading.value = false;
      }
    };

    return {
      name,
      email,
      password,
      confirmPassword,
      isLoading,
      errorMessage,
      handleSignup,
      passwordInput,
      confirmPasswordInput,
      togglePasswordField
    };
  }
}
</script>