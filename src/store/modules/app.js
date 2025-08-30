// src/store/modules/app.js
const state = {
  // Connection status
  isOnline: navigator.onLine,
  lastSync: null,
  connectionStatus: 'connecting',

  // Global loading states
  loadingStates: new Map(),

  // Global error states
  errors: new Map(),

  // Real-time subscriptions
  subscriptions: new Set(),

  // Optimistic updates queue
  optimisticUpdates: [],

  // Global theme settings
  theme: 'dark', // 'dark', 'light', 'system'
  fontSize: 16,
};

const getters = {
  isLoading: (state) => (key) => state.loadingStates.get(key) || false,
  getError: (state) => (key) => state.errors.get(key),
  hasError: (state) => (key) => state.errors.has(key),
  getConnectionStatus: (state) => state.connectionStatus,
  isConnected: (state) => state.isOnline && state.connectionStatus === 'connected',
  getLastSync: (state) => state.lastSync,

  // Theme getters
  getTheme: (state) => state.theme,
  getFontSize: (state) => state.fontSize,
  getCurrentTheme: (state) => {
    if (state.theme === 'system' && typeof window !== 'undefined') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    return state.theme;
  },

  // Get current theme state for debugging
  getThemeState: (state) => ({
    theme: state.theme,
    fontSize: state.fontSize,
    currentTheme: state.theme === 'system' && typeof window !== 'undefined'
      ? window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
      : state.theme
  }),
  getThemeClasses: (state) => {
    const isSystemDark = typeof window !== 'undefined' && window.matchMedia
      ? window.matchMedia('(prefers-color-scheme: dark)').matches
      : false;

    const isDark = state.theme === 'dark' ||
                  (state.theme === 'system' && isSystemDark);

    return {
      main: isDark ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900',
      header: isDark ? 'bg-gray-800' : 'bg-white border-b border-gray-200',
      sidebar: isDark ? 'bg-gray-800' : 'bg-gray-50 border-r border-gray-200',
      mainContent: isDark ? '' : 'bg-gray-100',
      card: isDark ? 'bg-gray-800' : 'bg-white border border-gray-200',
      text: isDark ? 'text-white' : 'text-gray-900',
      secondaryText: isDark ? 'text-gray-400' : 'text-gray-600',
      input: isDark ? 'bg-gray-700 border-gray-600 text-white' : 'bg-white border-gray-300 text-gray-900',
      button: isDark ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-200 hover:bg-gray-300'
    };
  },
  getFontSizeClasses: (state) => {
    const size = state.fontSize;
    return {
      heading: size >= 18 ? 'text-xl' : size >= 16 ? 'text-lg' : 'text-base',
      body: size >= 16 ? 'text-base' : 'text-sm',
      label: size >= 16 ? 'text-sm' : 'text-xs',
      small: 'text-xs'
    };
  },
};

const mutations = {
  SET_ONLINE_STATUS(state, isOnline) {
    state.isOnline = isOnline;
    state.connectionStatus = isOnline ? 'connected' : 'offline';
  },

  SET_CONNECTION_STATUS(state, status) {
    state.connectionStatus = status;
  },

  SET_LOADING(state, { key, loading }) {
    if (loading) {
      state.loadingStates.set(key, true);
    } else {
      state.loadingStates.delete(key);
    }
  },

  SET_ERROR(state, { key, error }) {
    if (error) {
      state.errors.set(key, {
        message: error.message || error,
        timestamp: new Date(),
        details: error
      });
    } else {
      state.errors.delete(key);
    }
  },

  CLEAR_ERROR(state, key) {
    state.errors.delete(key);
  },

  CLEAR_ALL_ERRORS(state) {
    state.errors.clear();
  },

  SET_LAST_SYNC(state, timestamp) {
    state.lastSync = timestamp;
  },

  ADD_SUBSCRIPTION(state, subscription) {
    state.subscriptions.add(subscription);
  },

  REMOVE_SUBSCRIPTION(state, subscription) {
    state.subscriptions.delete(subscription);
  },

  ADD_OPTIMISTIC_UPDATE(state, update) {
    state.optimisticUpdates.push({
      ...update,
      timestamp: new Date(),
      id: Date.now()
    });
  },

  REMOVE_OPTIMISTIC_UPDATE(state, updateId) {
    state.optimisticUpdates = state.optimisticUpdates.filter(update => update.id !== updateId);
  },

  CLEAR_OPTIMISTIC_UPDATES(state) {
    state.optimisticUpdates = [];
  },

  // Theme mutations
  SET_THEME(state, theme) {
    state.theme = theme;
  },

  SET_FONT_SIZE(state, fontSize) {
    state.fontSize = fontSize;
  },
};

const actions = {
  setOnlineStatus({ commit }, isOnline) {
    commit('SET_ONLINE_STATUS', isOnline);
  },

  setConnectionStatus({ commit }, status) {
    commit('SET_CONNECTION_STATUS', status);
  },

  setLoading({ commit }, payload) {
    commit('SET_LOADING', payload);
  },

  setError({ commit }, payload) {
    commit('SET_ERROR', payload);
  },

  clearError({ commit }, key) {
    commit('CLEAR_ERROR', key);
  },

  clearAllErrors({ commit }) {
    commit('CLEAR_ALL_ERRORS');
  },

  updateLastSync({ commit }) {
    commit('SET_LAST_SYNC', new Date());
  },

  addSubscription({ commit }, subscription) {
    commit('ADD_SUBSCRIPTION', subscription);
  },

  removeSubscription({ commit }, subscription) {
    commit('REMOVE_SUBSCRIPTION', subscription);
  },

  addOptimisticUpdate({ commit }, update) {
    commit('ADD_OPTIMISTIC_UPDATE', update);
  },

  removeOptimisticUpdate({ commit }, updateId) {
    commit('REMOVE_OPTIMISTIC_UPDATE', updateId);
  },

  clearOptimisticUpdates({ commit }) {
    commit('CLEAR_OPTIMISTIC_UPDATES');
  },

  // Theme actions
  setTheme({ commit, dispatch }, theme) {
    commit('SET_THEME', theme);
    dispatch('applyTheme', theme);
    dispatch('saveThemeSettings');
  },

  setFontSize({ commit, dispatch }, fontSize) {
    commit('SET_FONT_SIZE', fontSize);
    dispatch('saveThemeSettings');
  },

  applyTheme(theme) {
    // Only run on client-side
    if (typeof window === 'undefined' || typeof document === 'undefined') {
      return;
    }

    const root = document.documentElement;
    const body = document.body;

    if (theme === 'dark') {
      root.classList.add('dark');
      body.classList.add('dark');
    } else if (theme === 'light') {
      root.classList.remove('dark');
      body.classList.remove('dark');
    } else if (theme === 'system') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      root.classList.toggle('dark', prefersDark);
      body.classList.toggle('dark', prefersDark);
    }
  },

  saveThemeSettings({ state }) {
    // Save to localStorage
    const settings = {
      theme: state.theme,
      fontSize: state.fontSize
    };
    localStorage.setItem('smartscribe_theme_settings', JSON.stringify(settings));
  },

  loadThemeSettings({ commit, dispatch }) {
    // Load from localStorage
    const savedSettings = localStorage.getItem('smartscribe_theme_settings');
    if (savedSettings) {
      try {
        const settings = JSON.parse(savedSettings);
        if (settings.theme) {
          commit('SET_THEME', settings.theme);
        }
        if (settings.fontSize) {
          commit('SET_FONT_SIZE', settings.fontSize);
        }
        // Apply the loaded theme
        dispatch('applyTheme', settings.theme || 'dark');
      } catch (error) {
        console.error('Error loading theme settings:', error);
      }
    } else {
      // Apply default theme
      dispatch('applyTheme', 'dark');
    }
  },

  // Initialize app state
  initialize({ dispatch }) {
    // Set up online/offline detection
    if (typeof window !== 'undefined') {
      window.addEventListener('online', () => {
        dispatch('setOnlineStatus', true);
        dispatch('setConnectionStatus', 'connected');
      });

      window.addEventListener('offline', () => {
        dispatch('setOnlineStatus', false);
        dispatch('setConnectionStatus', 'offline');
      });

      // Initial status
      dispatch('setOnlineStatus', navigator.onLine);

      // Load and apply theme settings
      dispatch('loadThemeSettings');
    }
  },

  // Cleanup on app destroy
  cleanup({ state, dispatch }) {
    // Clean up subscriptions
    state.subscriptions.forEach(subscription => {
      if (typeof subscription.cleanup === 'function') {
        subscription.cleanup();
      }
    });

    // Clear all errors
    dispatch('clearAllErrors');

    // Clear optimistic updates
    dispatch('clearOptimisticUpdates');
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};