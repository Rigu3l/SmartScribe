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
  return config
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