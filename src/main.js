import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store/modules'
import './assets/css/main.css'

// Font Awesome
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  faHome, faBook, faChartLine, faCog, faBell, faCamera,
  faEdit, faFileExport, faSave, faSyncAlt, faCopy, faTimes,
  faMagic, faQuestionCircle, faPlus, faSearch, faEllipsisV,
  faStar, faStarHalfAlt, faPlayCircle, faRobot, faTags,
  faCheckCircle, faClock, faFileAlt, faBullseye, faSpinner,
  faExclamationCircle, faUserPlus,
  faHighlighter,
  faEye,
  faEyeSlash,
  faUser, faChevronDown, faUpload, faArrowRight, faBookOpen,
  faAlignLeft, faTrash,
  faEnvelope, faLock, faSignInAlt,
  faRocket
} from '@fortawesome/free-solid-svg-icons'
import { 
  faTwitter, faFacebook, faInstagram, faGithub,
  faFacebookF, faGoogle, faApple 
} from '@fortawesome/free-brands-svg-icons'

// Add all icons to the library
library.add(
  faHome, faBook, faChartLine, faCog, faBell, faCamera,
  faEdit, faFileExport, faSave, faSyncAlt, faCopy, faTimes,
  faMagic, faQuestionCircle, faPlus, faSearch, faEllipsisV,
  faStar, faStarHalfAlt, faPlayCircle, faRobot, faTags,
  faCheckCircle, faClock, faFileAlt, faBullseye, faSpinner,
  faExclamationCircle, faUserPlus,
  faTwitter, faFacebook, faInstagram, faGithub,
  faFacebookF, faGoogle, faApple, faHighlighter, faEye, faEyeSlash,
  faUser, faChevronDown, faUpload, faArrowRight, faBookOpen,
  faAlignLeft, faTrash,
  faEnvelope, faLock, faSignInAlt,
  faRocket
)


// Check if there's already an app instance and unmount it
const appContainer = document.getElementById('app')
if (appContainer && appContainer.__vue_app__) {
  appContainer.__vue_app__.unmount()
}

const app = createApp(App)

app.use(store)
app.use(router)
app.component('font-awesome-icon', FontAwesomeIcon)

// Initialize app state and real-time services
try {
  store.dispatch('app/initialize').catch(() => {
    // Error initializing app store
  })
} catch (error) {
  // Error initializing app store
}

// Show body after CSS loads to prevent FOUC
window.addEventListener('load', () => {
  document.body.classList.add('loaded');
});

// Fallback: show body after a short delay in case load event doesn't fire
setTimeout(() => {
  document.body.classList.add('loaded');
}, 100);

app.mount('#app')