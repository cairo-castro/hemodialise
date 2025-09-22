import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { Container } from '@/core/di/Container';

const container = Container.getInstance();
const authRepository = container.getAuthRepository();

// Route guard to check authentication
const requiresAuth = (to: any, from: any, next: any) => {
  if (authRepository.isAuthenticated()) {
    next();
  } else {
    next('/login');
  }
};

// Route guard to redirect authenticated users away from login
const redirectIfAuthenticated = (to: any, from: any, next: any) => {
  if (authRepository.isAuthenticated()) {
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
    component: () => import('@/views/LoginPage.vue'),
    beforeEnter: redirectIfAuthenticated
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/checklist',
    name: 'Checklist',
    component: () => import('@/views/ChecklistPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/patients',
    name: 'Patients',
    component: () => import('@/views/PatientsPage.vue'),
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
