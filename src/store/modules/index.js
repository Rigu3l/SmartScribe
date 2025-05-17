import { createStore } from 'vuex';
import auth from './modules/auth';
import notes from './modules/notes';
import progress from './modules/progress';

export default createStore({
  state: {
    appName: 'SmartScribe',
    appVersion: '1.0.0',
    isLoading: false,
    globalError: null
  },
  
  getters: {
    getAppName: state => state.appName,
    getAppVersion: state => state.appVersion,
    isAppLoading: state => state.isLoading,
    getGlobalError: state => state.globalError
  },
  
  mutations: {
    SET_LOADING(state, isLoading) {
      state.isLoading = isLoading;
    },
    
    SET_GLOBAL_ERROR(state, error) {
      state.globalError = error;
    },
    
    CLEAR_GLOBAL_ERROR(state) {
      state.globalError = null;
    }
  },
  
  actions: {
    setLoading({ commit }, isLoading) {
      commit('SET_LOADING', isLoading);
    },
    
    setGlobalError({ commit }, error) {
      commit('SET_GLOBAL_ERROR', error);
    },
    
    clearGlobalError({ commit }) {
      commit('CLEAR_GLOBAL_ERROR');
    }
  },
  
  modules: {
    auth,
    notes,
    progress
  }
});