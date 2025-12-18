import {  cilTransfer, cilQrCode, cilStorage } from '@coreui/icons'
import { CNavItem } from '@coreui/vue'
export default [
  
  // Client
  {
    component: 'CNavTitle',
    name: 'Operation',
  },
  {
    component: 'CNavItem',
    name: 'Map',
    to: '/dashboard',
    icon: 'cilStorage',
    badge: {
    },
  },
  {
    component: 'CNavItem',
    name: 'Nhận hàng',
    to: '/inbounds/create',
    icon: 'cilArrowCircleBottom',
  },
  {
    component: 'CNavItem',
    name: 'Nhập kho',
    to: '/putaway',
    icon: 'cil-pencil',
  },
  {
    component: 'CNavItem',
    name: 'Xuất kho',
    to: '/transfer',
    icon: 'cilTransfer',
  },

  // Admin      
  {
    component: 'CNavTitle',
    name: 'Admin',
  },
  {
    component: 'CNavItem',
    name: 'Layouts',
    to: '/admin/layouts',
    icon: 'cilPuzzle',
  },
  
  // {
  //   component: 'CNavGroup',
  //   name: 'Buttons',
  //   to: '/buttons',
  //   icon: 'cil-cursor',
  //   items: [
  //     {
  //       component: 'CNavItem',
  //       name: 'Buttons',
  //       to: '/buttons/standard-buttons',
  //     },
  //     {
  //       component: 'CNavItem',
  //       name: 'Button Groups',
  //       to: '/buttons/button-groups',
  //     },
  //     {
  //       component: 'CNavItem',
  //       name: 'Loading Button',
  //       href: 'https://coreui.io/vue/docs/components/loading-button.html',
  //       external: true,
  //       badge: {
  //         color: 'danger',
  //         text: 'PRO',
  //       },
  //     },
  //     {
  //       component: 'CNavItem',
  //       name: 'Dropdowns',
  //       to: '/buttons/dropdowns',
  //     },
  //   ],
  // },
]