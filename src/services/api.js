// src/services/api.js
import axios from 'axios'

const api = axios.create({
  baseURL: process.env.NODE_ENV === 'production'
    ? 'http://localhost/SmartScribe-main/public'
    : 'http://localhost/SmartScribe-main/public'
})

// Add auth token to requests
api.interceptors.request.use(config => {
  console.log('🚀 API Request:', config.method?.toUpperCase(), config.url)

  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
    console.log('🔑 Added auth token to request')
  } else {
    console.log('⚠️  No auth token found')
  }

  // Add user ID from localStorage for authentication
  const userData = localStorage.getItem('user')
  if (userData) {
    try {
      const user = JSON.parse(userData)
      if (user && user.id) {
        config.headers['X-User-ID'] = user.id
        console.log('👤 Added user ID to request:', user.id, '- Full user object:', user)
      } else {
        console.log('⚠️  User data found but no ID:', user)
      }
    } catch (error) {
      console.error('❌ Error parsing user data:', error, '- Raw data:', userData)
    }
  } else {
    console.log('⚠️  No user data found in localStorage')
  }

  console.log('📋 Final request headers:', config.headers)

  // Don't set Content-Type for FormData - let axios handle it
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
    console.log('API: Sending FormData (Content-Type auto-managed)')
  } else if (config.data && typeof config.data === 'object') {
    // Ensure JSON requests have proper Content-Type
    config.headers['Content-Type'] = 'application/json'
    console.log('API: Sending JSON data with Content-Type: application/json')
  }

  return config
})

// Add response interceptor for debugging
api.interceptors.response.use(
  response => {
    console.log('API Response:', response.status, response.config.method?.toUpperCase(), response.config.url)
    return response
  },
  error => {
    console.error('API Error:', error.response?.status, error.response?.statusText, error.config?.method?.toUpperCase(), error.config?.url)
    if (error.response?.data) {
      console.error('API Error Data:', error.response.data)
    }
    return Promise.reject(error)
  }
)

export default {
  // Auth
  login(credentials) {
    // Explicitly stringify the data to ensure proper JSON format
    return api.post('/index.php?resource=auth&action=login', JSON.stringify(credentials), {
      headers: {
        'Content-Type': 'application/json'
      }
    })
  },
  register(userData) {
    // Explicitly stringify the data to ensure proper JSON format
    return api.post('/index.php?resource=auth&action=register', JSON.stringify(userData), {
      headers: {
        'Content-Type': 'application/json'
      }
    })
  },
  logout() {
    return api.post('/index.php?resource=auth&action=logout')
  },
  getUser() {
    return api.get('/index.php?resource=auth&action=profile')
  },
  updateProfile(profileData) {
    // Explicitly stringify the data to ensure proper JSON format
    return api.put('/index.php?resource=auth&action=profile', JSON.stringify(profileData), {
      headers: {
        'Content-Type': 'application/json'
      }
    })
  },
  uploadProfilePicture(formData) {
    return api.post('/index.php?resource=auth&action=upload-profile-picture', formData)
  },
  
  // Notes
  getNotes() {
    return api.get('/index.php?resource=notes')
  },
  getNote(id) {
    return api.get(`/index.php?resource=notes&id=${id}`)
  },
  createNote(noteData) {
    // If there's an image file, we need to send FormData
    if (noteData.image && noteData.image instanceof File) {
      console.log('API: Creating FormData for file upload');
      const formData = new FormData()
      formData.append('title', noteData.title)
      formData.append('text', noteData.text)
      formData.append('image', noteData.image)

      console.log('API: FormData contents:');
      for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
          console.log(`${key}: File(${value.name}, ${value.size} bytes, ${value.type})`);
        } else {
          console.log(`${key}: ${value}`);
        }
      }

      // Don't manually set Content-Type for FormData - let axios handle it
      return api.post('/index.php?resource=notes', formData)
    } else {
      console.log('API: Creating JSON note (no file)');
      // For text-only notes, send as JSON
      return api.post('/index.php?resource=notes', {
        title: noteData.title,
        text: noteData.text
      })
    }
  },
  updateNote(id, noteData) {
    // Convert JSON data to FormData to match backend expectations
    const formData = new FormData()
    formData.append('title', noteData.title)
    formData.append('text', noteData.text)
    if (noteData.summary) {
      formData.append('summary', noteData.summary)
    }
    if (noteData.keywords) {
      formData.append('keywords', noteData.keywords)
    }

    return api.post(`/index.php?resource=notes&id=${id}`, formData)
  },
  deleteNote(id) {
    return api.delete(`/index.php?resource=notes&id=${id}`)
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
  
  // Summaries
  getSummaries() {
    return api.get('/summaries')
  },
  getSummary(id) {
    return api.get(`/summaries/${id}`)
  },
  createSummary(noteId, options) {
    return api.post(`/summaries`, { note_id: noteId, ...options })
  },

  // Progress
  getProgressStats() {
    return api.get('/progress/stats')
  },

  // Dashboard
  getDashboardStats() {
    return api.get('/index.php?resource=dashboard&action=stats')
  },

  // Settings
  getSettings() {
    return api.get('/settings')
  },
  updateSettings(settings) {
    // Explicitly stringify the data to ensure proper JSON format
    return api.put('/settings', JSON.stringify(settings), {
      headers: {
        'Content-Type': 'application/json'
      }
    })
  },

  // Quizzes
  getQuizzes() {
    return api.get('/quizzes')
  },
  getQuiz(id) {
    return api.get(`/quizzes/${id}`)
  },
  createQuiz(noteId, options) {
    return api.post(`/quizzes`, { note_id: noteId, ...options })
  },
  updateQuiz(id, data) {
    return api.put(`/quizzes/${id}`, data)
  },
  generateQuiz(noteId, options) {
    return api.post(`/notes/${noteId}/quizzes`, options)
  }
}