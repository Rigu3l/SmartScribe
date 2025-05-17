import api from '@/services/api';

const state = {
  user: JSON.parse(localStorage.getItem('user')) || null,
  token: localStorage.getItem('token') || null,
  isAuthenticated: !!localStorage.getItem('token')
};

const getters = {
  getUser: state => state.user,
  isAuthenticated: state => state.isAuthenticated
};

const actions = {
  async login({ commit }, credentials) {
    try {
      const response = await api.auth.login(credentials);
      const { user, token } = response.data;
      
      // Save to localStorage
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      
      // Update state
      commit('SET_AUTH', { user, token });
      return user;
    } catch (error) {
      console.error('Login error:', error);
      throw new Error(error.response?.data?.message || 'Login failed. Please check your credentials.');
    }
  },
  
  async register({ commit }, userData) {
    try {
      const response = await api.auth.register(userData);
      const { user, token } = response.data;
      
      // Save to localStorage
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      
      // Update state
      commit('SET_AUTH', { user, token });
      return user;
    } catch (error) {
      console.error('Registration error:', error);
      throw new Error(error.response?.data?.message || 'Registration failed. Please try again.');
    }
  },
  
  async logout({ commit }) {
    try {
      await api.auth.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Clear localStorage
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      // Update state
      commit('CLEAR_AUTH');
    }
  },
  
  async fetchUser({ commit }) {
    try {
      const response = await api.auth.getUser();
      const user = response.data;
      
      // Update localStorage
      localStorage.setItem('user', JSON.stringify(user));
      
      // Update state
      commit('SET_USER', user);
      return user;
    } catch (error) {
      console.error('Fetch user error:', error);
      throw new Error('Failed to fetch user data.');
    }
  }
};

const mutations = {
  SET_AUTH(state, { user, token }) {
    state.user = user;
    state.token = token;
    state.isAuthenticated = true;
  },
  
  SET_USER(state, user) {
    state.user = user;
  },
  
  CLEAR_AUTH(state) {
    state.user = null;
    state.token = null;
    state.isAuthenticated = false;
  }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};