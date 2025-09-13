import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import api from '../services/api'

export function useStudyTime() {
  // Reactive state
  const currentSession = ref(null)
  const isTracking = ref(false)
  const elapsedTime = ref(0)
  const startTime = ref(null)
  const timer = ref(null)
  const sessionStats = ref({
    totalSessions: 0,
    totalMinutes: 0,
    totalHours: 0,
    avgSessionMinutes: 0,
    totalNotesStudied: 0,
    totalQuizzesTaken: 0,
    avgQuizScore: null,
    lastSessionDate: null,
    currentStreak: 0
  })

  // Activity tracking
  const currentActivity = ref('general_study')
  const activities = ref([])
  const notesStudied = ref(0)
  const quizzesTaken = ref(0)
  const averageScore = ref(null)

  // Computed properties
  const formattedElapsedTime = computed(() => {
    const hours = Math.floor(elapsedTime.value / 3600)
    const minutes = Math.floor((elapsedTime.value % 3600) / 60)
    const seconds = elapsedTime.value % 60

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    }
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
  })

  const isActiveSession = computed(() => {
    return currentSession.value && !currentSession.value.duration_minutes
  })

  // Timer functions
  const startTimer = () => {
    if (timer.value) return

    startTime.value = Date.now()
    timer.value = setInterval(() => {
      elapsedTime.value = Math.floor((Date.now() - startTime.value) / 1000)
    }, 1000)
  }

  const stopTimer = () => {
    if (timer.value) {
      clearInterval(timer.value)
      timer.value = null
    }
  }

  const resetTimer = () => {
    stopTimer()
    elapsedTime.value = 0
    startTime.value = null
  }

  // API functions
  const startStudySession = async (activity = 'general_study') => {
    try {
      const response = await api.post('?resource=study-sessions&action=start', {
        activity: activity
      })

      if (response.data.success) {
        currentSession.value = response.data.data
        currentActivity.value = activity
        isTracking.value = true
        startTimer()

        // Add activity to activities array
        activities.value.push({
          type: activity,
          timestamp: new Date(),
          sessionId: currentSession.value.id
        })

        return { success: true, session: currentSession.value }
      } else {
        throw new Error(response.data.message || 'Failed to start session')
      }
    } catch (error) {
      console.error('Error starting study session:', error)
      return { success: false, error: error.message }
    }
  }

  const endStudySession = async (sessionData = {}) => {
    if (!currentSession.value) {
      return { success: false, error: 'No active session' }
    }

    try {
      const endData = {
        session_id: currentSession.value.id,
        notes_studied: sessionData.notesStudied || notesStudied.value,
        quizzes_taken: sessionData.quizzesTaken || quizzesTaken.value,
        average_score: sessionData.averageScore || averageScore.value,
        focus_level: sessionData.focusLevel || 'medium'
      }

      const response = await api.post('?resource=study-sessions&action=end', endData)

      if (response.data.success) {
        const endedSession = response.data.data
        currentSession.value = null
        isTracking.value = false
        stopTimer()

        // Reset activity counters
        notesStudied.value = 0
        quizzesTaken.value = 0
        averageScore.value = null

        return { success: true, session: endedSession }
      } else {
        throw new Error(response.data.message || 'Failed to end session')
      }
    } catch (error) {
      console.error('Error ending study session:', error)
      return { success: false, error: error.message }
    }
  }

  const updateActivity = async (activity) => {
    if (!currentSession.value) return

    try {
      const response = await api.post('?resource=study-sessions&action=update-activity', {
        session_id: currentSession.value.id,
        activity: activity
      })

      if (response.data.success) {
        currentActivity.value = activity
        activities.value.push({
          type: activity,
          timestamp: new Date(),
          sessionId: currentSession.value.id
        })
      }
    } catch (error) {
      console.error('Error updating activity:', error)
    }
  }

  const getActiveSession = async () => {
    try {
      const response = await api.get('?resource=study-sessions&action=active')

      if (response.data.success && response.data.data) {
        currentSession.value = response.data.data
        isTracking.value = true

        // Resume timer if session is active
        if (currentSession.value.start_time) {
          const sessionStart = new Date(currentSession.value.start_time).getTime()
          const now = Date.now()
          elapsedTime.value = Math.floor((now - sessionStart) / 1000)
          startTimer()
        }

        return { success: true, session: currentSession.value }
      } else {
        currentSession.value = null
        isTracking.value = false
        return { success: true, session: null }
      }
    } catch (error) {
      console.error('Error getting active session:', error)
      return { success: false, error: error.message }
    }
  }

  const getStudyStats = async (startDate = null, endDate = null) => {
    try {
      let url = '?resource=study-sessions&action=stats'
      if (startDate) url += `&start_date=${startDate}`
      if (endDate) url += `&end_date=${endDate}`

      const response = await api.get(url)

      if (response.data.success) {
        sessionStats.value = response.data.data
        return { success: true, stats: sessionStats.value }
      } else {
        throw new Error(response.data.message || 'Failed to get stats')
      }
    } catch (error) {
      console.error('Error getting study stats:', error)
      return { success: false, error: error.message }
    }
  }

  const getDailyStats = async (startDate, endDate) => {
    try {
      const response = await api.get(`?resource=study-sessions&action=daily-stats&start_date=${startDate}&end_date=${endDate}`)

      if (response.data.success) {
        return { success: true, dailyStats: response.data.data }
      } else {
        throw new Error(response.data.message || 'Failed to get daily stats')
      }
    } catch (error) {
      console.error('Error getting daily stats:', error)
      return { success: false, error: error.message }
    }
  }

  const getStudyStreak = async () => {
    try {
      const response = await api.get('?resource=study-sessions&action=streak')

      if (response.data.success) {
        return { success: true, streak: response.data.data }
      } else {
        throw new Error(response.data.message || 'Failed to get streak')
      }
    } catch (error) {
      console.error('Error getting study streak:', error)
      return { success: false, error: error.message }
    }
  }

  // Activity tracking helpers
  const incrementNotesStudied = () => {
    notesStudied.value++
  }

  const incrementQuizzesTaken = (score = null) => {
    quizzesTaken.value++
    if (score !== null) {
      // Update average score
      if (averageScore.value === null) {
        averageScore.value = score
      } else {
        averageScore.value = (averageScore.value + score) / 2
      }
    }
  }

  const setCurrentActivity = (activity) => {
    currentActivity.value = activity
    if (isTracking.value) {
      updateActivity(activity)
    }
  }

  // Auto-save functionality
  const autoSaveInterval = ref(null)

  const startAutoSave = () => {
    if (autoSaveInterval.value) return

    autoSaveInterval.value = setInterval(async () => {
      if (isTracking.value && currentSession.value) {
        // Update session with current activity data
        try {
          await api.post('?resource=study-sessions&action=update-activity', {
            session_id: currentSession.value.id,
            activity: currentActivity.value
          })
        } catch (error) {
          console.error('Auto-save failed:', error)
        }
      }
    }, 30000) // Auto-save every 30 seconds
  }

  const stopAutoSave = () => {
    if (autoSaveInterval.value) {
      clearInterval(autoSaveInterval.value)
      autoSaveInterval.value = null
    }
  }

  // Lifecycle
  onMounted(async () => {
    // Check for active session on component mount
    await getActiveSession()

    // Start auto-save if tracking
    if (isTracking.value) {
      startAutoSave()
    }
  })

  onUnmounted(() => {
    stopTimer()
    stopAutoSave()
  })

  // Watch for tracking state changes
  watch(isTracking, (newValue) => {
    if (newValue) {
      startAutoSave()
    } else {
      stopAutoSave()
    }
  })

  return {
    // State
    currentSession,
    isTracking,
    elapsedTime,
    sessionStats,
    currentActivity,
    activities,
    notesStudied,
    quizzesTaken,
    averageScore,

    // Computed
    formattedElapsedTime,
    isActiveSession,

    // Methods
    startStudySession,
    endStudySession,
    updateActivity,
    getActiveSession,
    getStudyStats,
    getDailyStats,
    getStudyStreak,

    // Activity helpers
    incrementNotesStudied,
    incrementQuizzesTaken,
    setCurrentActivity,

    // Timer controls
    startTimer,
    stopTimer,
    resetTimer
  }
}