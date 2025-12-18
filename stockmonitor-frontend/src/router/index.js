import { h, resolveComponent } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'

import DefaultLayout from '@/layouts/DefaultLayout'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: DefaultLayout,
    redirect: '/dashboard',
    children: [
      {
        path: '/dashboard',
        name: 'Dashboard',
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () =>
          import(
            /* webpackChunkName: "dashboard" */ '@/views/dashboard/Dashboard.vue'
          ),
      },

      // ===== OPERATION =====
      {
        path: '/inbounds/create',
        name: 'Inbound create',
        component: () => import('@/views/inbound/IncomeCreate.vue'),
      },
      { path: '/map', name: 'Map', component: () => import('@/components/warehouse-map/WarehouseMap.vue') },

      // Inbound (list + detail with tabs)
      { path: '/inbounds', name: 'Inbound List', component: () => import('@/views/inbound/InboundList.vue') },
      {
        path: '/inbounds/:id',
        name: 'Inbound Detail',
        component: () => import('@/views/inbound/InboundDetail.vue'),
        props: true,
        children: [
          { path: '', redirect: { name: 'Inbound Lines' } },
          { path: 'lines',   name: 'Inbound Lines',   component: () => import('@/views/inbound/tabs/InboundLinesTab.vue') },
          { path: 'qr',      name: 'Inbound QR',      component: () => import('@/views/inbound/tabs/InboundQrTab.vue') },
          { path: 'history', name: 'Inbound History', component: () => import('@/views/inbound/tabs/InboundHistoryTab.vue') },
        ],
      },

      { path: '/putaway',  name: 'Put away', component: () => import('@/views/putaway/Putaway.vue') },
      { path: '/transfer', name: 'Transfer', component: () => import('@/views/transfer/TransferK1K2.vue') },

      // ===== ADMIN =====
      { path: '/admin/layouts', name: 'Layouts', component: () => import('@/views/config_layouts/ConfigLayout.vue') },




      // {
      //   path: '/buttons',
      //   name: 'Buttons',
      //   component: {
      //     render() {
      //       return h(resolveComponent('router-view'))
      //     },
      //   },
      //   redirect: '/buttons/standard-buttons',
      //   children: [
      //     {
      //       path: '/buttons/standard-buttons',
      //       name: 'Button Component',
      //       component: () => import('@/views/buttons/Buttons.vue'),
      //     },
      //     {
      //       path: '/buttons/dropdowns',
      //       name: 'Dropdowns',
      //       component: () => import('@/views/buttons/Dropdowns.vue'),
      //     },
      //     {
      //       path: '/buttons/button-groups',
      //       name: 'Button Groups',
      //       component: () => import('@/views/buttons/ButtonGroups.vue'),
      //     },
      //   ],
      // },
    ],
  },
  {
    path: '/pages',
    redirect: '/pages/404',
    name: 'Pages',
    component: {
      render() {
        return h(resolveComponent('router-view'))
      },
    },
    children: [
      {
        path: '404',
        name: 'Page404',
        component: () => import('@/views/pages/Page404'),
      },
      {
        path: '500',
        name: 'Page500',
        component: () => import('@/views/pages/Page500'),
      },
      {
        path: 'login',
        name: 'Login',
        component: () => import('@/views/pages/Login'),
      },
      {
        path: 'register',
        name: 'Register',
        component: () => import('@/views/pages/Register'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    // always scroll to top
    return { top: 0 }
  },
})

export default router
