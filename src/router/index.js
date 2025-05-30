import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import SignupView from '../views/SignupView.vue'
import NotesView from '../views/NotesView.vue'
import ProgressView from '../views/ProgressView.vue'
import SettingsView from '../views/SettingsView.vue'
import NoteDetailView from '../views/NoteDetailView.vue'
import NoteEditorView from '@/views/NoteEditorView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
// import { meta } from '@babel/eslint-parser'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/signup',
    name: 'signup',
    component: SignupView
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/notes',
    name: 'notes',
    component: NotesView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/notes/:id',
    name: 'note-detail',
    component: NoteDetailView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/notes/edit',
    name: 'note-editor',
    component: NoteEditorView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/progress',
    name: 'progress',
    component: ProgressView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    //meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    //meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('user') !== null;
  
  if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
    next('/login');
  } else {
    next();
  }
})

export default router