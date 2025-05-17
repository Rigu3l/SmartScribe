import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
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
  faExclamationCircle, faUserPlus
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
  faFacebookF, faGoogle, faApple
)

const app = createApp(App)

app.use(store)
app.use(router)
app.component('font-awesome-icon', FontAwesomeIcon)

app.mount('#app')