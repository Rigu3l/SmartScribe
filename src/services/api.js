// src/services/api.js
import axios from 'axios'

const api = axios.create({
  baseURL: '/api'
})

// Add auth token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  reimport axios from 'axios';

// Create axios instance
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add request interceptor for auth token
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Add response interceptor for error handling
api.interceptors.response.use(
  response => response,
  error => {
    // Handle 401 Unauthorized errors
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  // Auth endpoints
  auth: {
    login: (credentials) => api.post('/auth/login', credentials),
    register: (userData) => api.post('/auth/register', userData),
    logout: () => api.post('/auth/logout'),
    getUser: () => api.get('/auth/user')
  },
  
  // Notes endpoints
  notes: {
    getAll: () => api.get('/notes'),
    get: (id) => api.get(`/notes/${id}`),
    create: (noteData) => api.post('/notes', noteData),
    update: (id, noteData) => api.put(`/notes/${id}`, noteData),
    delete: (id) => api.delete(`/notes/${id}`)
  },
  
  // OCR endpoints
  ocr: {
    processImage: (formData) => api.post('/ocr/process', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  
  // GPT endpoints
  gpt: {
    generateSummary: (text, options) => api.post('/gpt/summarize', { text, options }),
    generateQuiz: (text, options) => api.post('/gpt/quiz', { text, options }),
    extractKeywords: (text) => api.post('/gpt/keywords', { text })
  },
  
  // Progress endpoints
  progress: {
    getStats: () => api.get('/progress/stats'),
    getActivity: () => api.get('/progress/activity'),
    updateProgress: (progressData) => api.post('/progress/update', progressData)
  },
  
  // Export endpoints
  export: {
    toPdf: (noteId) => api.get(`/export/pdf/${noteId}`, { responseType: 'blob' }),
    toWord: (noteId) => api.get(`/export/word/${noteId}`, { responseType: 'blob' }),
    toExcel: (noteId) => api.get(`/export/excel/${noteId}`, { responseType: 'blob' })
  }
};turn config
})

export default {
  // Auth
  login(credentials) {
    return api.post('/auth/login', credentials)
  },
  register(userData) {
    return api.post('/auth/register', userData)
  },
  logout() {
    return api.post('/auth/logout')
  },
  
  // Notes
  getNotes() {
    return api.get('/notes')
  },
  getNote(id) {
    return api.get(`/notes/${id}`)
  },
  createNote(noteData) {
    return api.post('/notes', noteData)
  },
  updateNote(id, noteData) {
    return api.put(`/notes/${id}`, noteData)
  },
  deleteNote(id) {
    return api.delete(`/notes/${id}`)
  },
  
  // OCR
  scanImage(formData) {
    return api.post('/ocr', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  
  // Summaries
  generateSummary(noteId, options) {
    return api.post(`/notes/${noteId}/summaries`, options)
  },
  
  // Quizzes
  generateQuiz(noteId, options) {
    return api.post(`/notes/${noteId}/quizzes`, options)
  }
}