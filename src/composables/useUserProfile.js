// src/composables/useUserProfile.js
import { ref, computed, watch } from 'vue';
import api from '@/services/api';

export function useUserProfile() {
  const user = ref(null);
  const loading = ref(false);
  const error = ref(null);

  // Get profile picture URL
  const getProfilePictureUrl = (profilePicturePath) => {
    if (!profilePicturePath) return null;
    // Since the backend stores relative paths from public directory, construct the full URL
    // Add timestamp to prevent caching issues
    const timestamp = Date.now();
    // Use relative URL to avoid CORS issues and ensure proper path resolution
    return `/${profilePicturePath}?t=${timestamp}`;
  };

  // Load user profile from API
  const loadUserProfile = async () => {
    try {
      loading.value = true;
      error.value = null;

      // First try to get from localStorage
      const userData = localStorage.getItem('user');
      if (userData) {
        try {
          const parsedUser = JSON.parse(userData);
          user.value = {
            id: parsedUser.id,
            name: parsedUser.name || 'User',
            email: parsedUser.email || 'user@example.com',
            firstName: parsedUser.first_name || parsedUser.firstName || '',
            lastName: parsedUser.last_name || parsedUser.lastName || '',
            profilePicture: parsedUser.profile_picture || parsedUser.profilePicture || null,
            memberSince: parsedUser.created_at ? new Date(parsedUser.created_at).toLocaleDateString() : new Date().toLocaleDateString()
          };
        } catch (parseError) {
          console.error('Error parsing user data from localStorage:', parseError);
          localStorage.removeItem('user'); // Clear corrupted data
        }
      }

      // Then try to fetch fresh data from API
      try {
        const response = await api.getUser();
        if (response.data && response.data.success && response.data.user) {
          const apiUser = response.data.user;
          user.value = {
            id: apiUser.id,
            name: apiUser.name || 'User',
            email: apiUser.email || 'user@example.com',
            firstName: apiUser.first_name || '',
            lastName: apiUser.last_name || '',
            profilePicture: apiUser.profile_picture || null,
            memberSince: apiUser.created_at ? new Date(apiUser.created_at).toLocaleDateString() : new Date().toLocaleDateString()
          };

          // Update localStorage with fresh data
          localStorage.setItem('user', JSON.stringify(apiUser));
        }
      } catch (apiError) {
        console.warn('Could not fetch user profile from API, using localStorage data:', apiError.message);
        // Keep the localStorage data if API fails
      }

    } catch (err) {
      error.value = err.message;
      console.error('Error loading user profile:', err);
    } finally {
      loading.value = false;
    }
  };

  // Update user profile
  const updateUserProfile = async (profileData) => {
    try {
      loading.value = true;
      error.value = null;

      const response = await api.updateProfile(profileData);
      if (response.data && response.data.success) {
        // Reload user profile to get updated data
        await loadUserProfile();
        return { success: true };
      } else {
        const errorMessage = response.data?.error || 'Failed to update profile';
        throw new Error(errorMessage);
      }
    } catch (err) {
      const errorMessage = err.message || 'Network error occurred';
      error.value = errorMessage;
      console.error('Error updating user profile:', err);
      return { success: false, error: errorMessage };
    } finally {
      loading.value = false;
    }
  };

  // Computed properties
  const profilePictureUrl = computed(() => {
    return getProfilePictureUrl(user.value?.profilePicture);
  });

  const displayName = computed(() => {
    return user.value?.name || 'User';
  });

  const isAuthenticated = computed(() => {
    return !!user.value?.id;
  });

  // Logout function
  const logout = () => {
    user.value = null;
    error.value = null;
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  };

  // Check if user has profile picture
  const hasProfilePicture = computed(() => {
    return !!user.value?.profilePicture;
  });

  // Get user initials for avatar fallback
  const getUserInitials = computed(() => {
    if (!user.value?.name) return 'U';
    const names = user.value.name.split(' ');
    if (names.length >= 2) {
      return `${names[0][0]}${names[1][0]}`.toUpperCase();
    }
    return user.value.name[0].toUpperCase();
  });

  // Watch for user changes and update localStorage
  watch(user, (newUser) => {
    if (newUser && newUser.id) {
      // Update localStorage when user data changes
      localStorage.setItem('user', JSON.stringify({
        id: newUser.id,
        name: newUser.name,
        email: newUser.email,
        first_name: newUser.firstName,
        last_name: newUser.lastName,
        profile_picture: newUser.profilePicture,
        created_at: newUser.created_at
      }));
    }
  }, { deep: true });

  return {
    // Reactive data
    user,
    loading,
    error,

    // Computed properties
    profilePictureUrl,
    displayName,
    isAuthenticated,
    hasProfilePicture,
    getUserInitials,

    // Methods
    loadUserProfile,
    updateUserProfile,
    logout,
    getProfilePictureUrl
  };
}