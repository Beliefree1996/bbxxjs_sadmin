/** When your routing table is too long, you can split it into small modules**/

import Layout from '@/views/layout/Layout'

const table1Router = {
  path: '/table',
  component: Layout,
  redirect: '/table/complex-table',
  name: 'Table1',
  meta: {
    title: '',
    icon: 'import'
  },
  children: [
    {
      path: 'TwNumberTable',
      component: () => import('@/views/table/TwNumberTable'),
      name: 'TwNumberTable',
      meta: { title: 'TwNumberTable' }
    },
    {
      path: 'TwDistribute/:id(\\d+)',
      component: () => import('@/views/table/TwDistribute'),
      name: 'TwDistribute',
      meta: { title: 'TwDistribute', noCache: true },
      hidden: true
    },
  ]
}
export default table1Router

