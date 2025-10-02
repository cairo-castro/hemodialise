import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import { Container } from '@mobile/core/di/Container';

const container = Container.getInstance();
const authRepository = container.getAuthRepository();

// Route guard to check authentication
const requiresAuth = async (to: any, from: any, next: any) => {
  console.log('requiresAuth guard - checking authentication...');
  const token = authRepository.getStoredToken();

  if (!token) {
    console.log('No token found, redirecting to login');
    next('/login');
    return;
  }

  // Validate token with backend
  try {
    const getCurrentUserUseCase = container.getCurrentUserUseCase();
    await getCurrentUserUseCase.execute();
    console.log('Token is valid, allowing access to:', to.path);
    next();
  } catch (error) {
    console.log('Token validation failed, clearing token and redirecting to login');
    authRepository.removeToken();
    next('/login');
  }
};

// Route guard to redirect authenticated users away from login
const redirectIfAuthenticated = async (to: any, from: any, next: any) => {
  console.log('redirectIfAuthenticated guard - checking authentication...');
  const token = authRepository.getStoredToken();

  if (!token) {
    console.log('No token, allowing access to login');
    next();
    return;
  }

  // Validate token with backend
  try {
    const getCurrentUserUseCase = container.getCurrentUserUseCase();
    await getCurrentUserUseCase.execute();
    console.log('User is authenticated, redirecting to dashboard');
    next('/dashboard');
  } catch (error) {
    console.log('Token validation failed, clearing token and allowing login');
    authRepository.removeToken();
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
  {
    path: '/machines',
    name: 'Machines',
    component: () => import('@mobile/views/MachinesPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/cleaning-controls',
    name: 'CleaningControls',
    component: () => import('@mobile/views/CleaningControlsPage.vue'),
    beforeEnter: requiresAuth
  },
  {
    path: '/cleaning-checklist/new',
    name: 'NewCleaningChecklist',
    component: () => import('@mobile/views/CleaningChecklistNewPage.vue'),
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
