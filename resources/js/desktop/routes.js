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
    path: '/desktop/cleaning-checklists',
    name: 'desktop.cleaning-checklists',
    component: () => import('./views/CleaningChecklistsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/reports',
    name: 'desktop.reports',
    component: () => import('./views/ReportsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/notifications',
    name: 'desktop.notifications',
    component: () => import('./views/NotificationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/profile',
    name: 'desktop.profile',
    component: () => import('./views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/settings',
    name: 'desktop.settings',
    component: () => import('./views/SettingsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/desktop/help',
    name: 'desktop.help',
    component: () => import('./views/HelpView.vue'),
    meta: { requiresAuth: true }
  },
];
