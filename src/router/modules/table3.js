/** When your routing table is too long, you can split it into small modules**/

import Layout from '@/views/layout/Layout'

const table3Router = {
  path: '/table',
  component: Layout,
  redirect: '/table/complex-table',
  name: 'Table3',
  meta: {
    title: '',
    icon: 'guide'
  },
  children: [
    {
      path: 'Recharge',
      component: () => import('@/views/table/Recharge'),
      name: 'Recharge',
      meta: { title: 'recharge' }
    },
    {
      path: 'ReChargeEwm/:id(\\d+)',
      component: () => import('@/views/table/ReChargeEwm'),
      name: 'ReChargeEwm',
      meta: { title: 'rechargeewm', noCache: true },
      hidden: true
    },
  ]
}
export default table3Router

