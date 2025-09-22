import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { AuthService } from '@shared/auth';

// Route guard to check authentication
const requiresAuth = (to: any, from: any, next: any) => {
  if (AuthService.isAuthenticated()) {
    next();
  } else {
    next('/login');
  }
};

// Route guard to redirect authenticated users away from login
const redirectIfAuthenticated = (to: any, from: any, next: any) => {
  if (AuthService.isAuthenticated()) {
    next('/dashboard');
  } else {
    next();
  }
};

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@mobile/views/LoginPage.vue'),
    beforeEnter: redirectIfAuthenticated
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@mobile/views/DashboardPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/checklist',
    name: 'Checklist',
    component: () => import('@mobile/views/ChecklistPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/patients',
    name: 'Patients',
    component: () => import('@mobile/views/PatientsPage.vue'),
    beforeEnter: requiresAuth
  },
  // Fallback route for any unmatched paths
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router
