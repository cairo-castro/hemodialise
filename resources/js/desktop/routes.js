export const routes = [
  {
    path: '/desktop',
    name: 'desktop.home',
    component: () => import('./views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/checklists',
    name: 'desktop.checklists',
    component: () => import('./views/ChecklistsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/machines',
    name: 'desktop.machines',
    component: () => import('./views/MachinesView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/patients',
    name: 'desktop.patients',
    component: () => import('./views/PatientsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/profile',
    name: 'desktop.profile',
    component: () => import('./views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
];
