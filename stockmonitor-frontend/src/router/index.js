import { h, resolveComponent } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'

import DefaultLayout from '@/layouts/DefaultLayout'

import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: DefaultLayout,
    redirect: '/dashboard',
    meta: { requiresAuth: true }, 
    children: [
      {
        path: '/dashboard',
        name: 'Map',
        component: () => import('@/components/warehouse-map/WarehouseMap.vue'),
      },
      // ===== OPERATION =====
      {
        path: '/inbounds/create',
        name: 'Nhận hàng',
        component: () => import('@/views/inbound/IncomeCreate.vue'),
      },
      { path: '/putaway',  name: 'Nhập kho', component: () => import('@/views/putaway/Putaway.vue') },
      { path: '/transfer', name: 'Xuất kho', component: () => import('@/views/transfer/TransferK1K2.vue') },

      // ===== ADMIN =====
      { path: '/admin/layouts', name: 'Layouts', component: () => import('@/views/config_layouts/ConfigLayout.vue') },

      // ===== BUTTONS (EXAMPLE) =====
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
        path: '/pages/login',
        name: 'Login',
        component: () => import('@/views/pages/Login'),
        meta: { guest: true },
      },
      {
        path: '/pages/register',
        name: 'Register',
        component: () => import('@/views/pages/Register'),
        meta: { guest: true },
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

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Chỉ fetch user khi route cần auth
  if (to.meta.requiresAuth && !auth.user) {
    try {
      await auth.fetchUser()
    } catch (e) {
      // ignore
    }
  }

  // Chưa login mà vào private
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return '/pages/login'
  }

  // Đã login mà vẫn vào login
  if (to.meta.guest && auth.isAuthenticated) {
    return '/'
  }
})


export default router
