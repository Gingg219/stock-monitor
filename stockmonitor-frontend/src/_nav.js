import {  cilTransfer, cilQrCode, cilStorage } from '@coreui/icons'
import { CNavItem } from '@coreui/vue'
export default [
  {
    component: 'CNavItem',
    name: 'Dashboard',
    to: '/dashboard',
    icon: 'cil-speedometer',
    badge: {
      color: 'primary',
      text: 'NEW',
    },
  },
  // Client
  {
    component: 'CNavTitle',
    name: 'Operation',
  },
  {
    component: 'CNavItem',
    name: 'Map',
    to: '/map',
    icon: 'cilStorage',
  },
  {
    component: 'CNavItem',
    name: 'Inbound',
    to: '/inbounds',
    icon: 'cilArrowCircleBottom',
  },
  {
    component: 'CNavItem',
    name: 'Inbound create',
    to: '/inbounds/create',
    icon: 'cilArrowCircleBottom',
  },
  {
    component: 'CNavItem',
    name: 'Put away',
    to: '/putaway',
    icon: 'cil-pencil',
  },
  {
    component: 'CNavItem',
    name: 'Transfer',
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
