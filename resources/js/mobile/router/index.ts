import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { Container } from '@mobile/core/di/Container';

const container = Container.getInstance();
const authRepository = container.getAuthRepository();

// Route guard to check authentication
const requiresAuth = (to: any, from: any, next: any) => {
  console.log('requiresAuth guard - checking authentication...');
  if (authRepository.isAuthenticated()) {
    console.log('User is authenticated, allowing access to:', to.path);
    next();
  } else {
    console.log('User not authenticated, redirecting to login');
    next('/login');
  }
};

// Route guard to redirect authenticated users away from login
const redirectIfAuthenticated = (to: any, from: any, next: any) => {
  console.log('redirectIfAuthenticated guard - checking authentication...');
  if (authRepository.isAuthenticated()) {
    console.log('User is authenticated, redirecting to dashboard');
    next('/dashboard');
  } else {
    console.log('User not authenticated, allowing access to login');
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
    path: '/checklists',
    name: 'ChecklistList',
    component: () => import('@mobile/views/ChecklistListPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/checklist/new',
    name: 'NewChecklist',
    component: () => import('@mobile/views/ChecklistPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/checklist/:id',
    name: 'Checklist',
    component: () => import('@mobile/views/ChecklistPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/checklist',
    redirect: '/checklists'
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
  history: createWebHistory('/mobile/'),
  routes
})

export default router
