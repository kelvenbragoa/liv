import AppLayout from '@/layout/AppLayout.vue';
import AppAdminLayout from '@/layout_admin/AppAdminLayout.vue';

import AppWaiterLayout from '@/layout_waiter/AppWaiterLayout.vue';
import AppKitchenLayout from '@/layout_kitchen/AppKitchenLayout.vue';
import AppBarLayout from '@/layout_bar/AppBarLayout.vue';
import AppStockLayout from '@/layout_stock/AppStockLayout.vue';


import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/admin/dashboard',
            component: AppAdminLayout,
            children: [
                {
                    path: '/admin/dashboard',
                    name: 'admin.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/Dashboard.vue')
                },
                {
                    path: '/admin/categories',
                    name: 'admin.categories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/categories/IndexCategories.vue')
                },
                {
                    path: '/admin/categories/create',
                    name: 'admin.categories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/categories/CreateCategories.vue')
                },
                {
                    path: '/admin/categories/:id',
                    name: 'admin.categories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/categories/ShowCategories.vue')
                },
                {
                    path: '/admin/categories/:id/edit',
                    name: 'admin.categories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/categories/EditCategories.vue')
                },

                {
                    path: '/admin/subcategories',
                    name: 'admin.subcategories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/subcategories/IndexSubCategories.vue')
                },
                {
                    path: '/admin/subcategories/create',
                    name: 'admin.subcategories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/subcategories/CreateSubCategories.vue')
                },
                {
                    path: '/admin/subcategories/:id',
                    name: 'admin.subcategories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/subcategories/ShowSubCategories.vue')
                },
                {
                    path: '/admin/subcategories/:id/edit',
                    name: 'admin.subcategories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/subcategories/EditSubCategories.vue')
                },


                {
                    path: '/admin/products',
                    name: 'admin.products',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/products/IndexProducts.vue')
                },
                {
                    path: '/admin/products/create',
                    name: 'admin.products.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/products/CreateProducts.vue')
                },
                {
                    path: '/admin/products/:id',
                    name: 'admin.products.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/products/ShowProducts.vue')
                },
                {
                    path: '/admin/products/:id/edit',
                    name: 'admin.products.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/products/EditProducts.vue')
                },


                {
                    path: '/admin/customers',
                    name: 'admin.customers',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/customers/IndexCustomers.vue')
                },
                {
                    path: '/admin/customers/create',
                    name: 'admin.customers.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/customers/CreateCustomers.vue')
                },
                {
                    path: '/admin/customers/:id',
                    name: 'admin.customers.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/customers/ShowCustomers.vue')
                },
                {
                    path: '/admin/customers/:id/edit',
                    name: 'admin.customers.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/customers/EditCustomers.vue')
                },

                {
                    path: '/admin/tables',
                    name: 'admin.tables',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/tables/IndexTables.vue')
                },
                {
                    path: '/admin/tables/create',
                    name: 'admin.tables.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/tables/CreateTables.vue')
                },
                {
                    path: '/admin/tables/:id',
                    name: 'admin.tables.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/tables/ShowTables.vue')
                },
                {
                    path: '/admin/tables/:id/edit',
                    name: 'admin.tables.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/tables/EditTables.vue')
                },

                {
                    path: '/admin/paymentmethods',
                    name: 'admin.paymentmethods',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/paymentmethods/IndexPaymentMethods.vue')
                },
                {
                    path: '/admin/paymentmethods/create',
                    name: 'admin.paymentmethods.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/paymentmethods/CreatePaymentMethods.vue')
                },
                {
                    path: '/admin/paymentmethods/:id',
                    name: 'admin.paymentmethods.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/paymentmethods/ShowPaymentMethods.vue')
                },
                {
                    path: '/admin/paymentmethods/:id/edit',
                    name: 'admin.paymentmethods.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/paymentmethods/EditPaymentMethods.vue')
                },

                {
                    path: '/admin/users',
                    name: 'admin.users',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/users/IndexUsers.vue')
                },
                {
                    path: '/admin/users/create',
                    name: 'admin.users.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/users/CreateUsers.vue')
                },
                {
                    path: '/admin/users/:id',
                    name: 'admin.users.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/users/ShowUsers.vue')
                },
                {
                    path: '/admin/users/:id/edit',
                    name: 'admin.users.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/users/EditUsers.vue')
                },

                {
                    path: '/admin/reservations',
                    name: 'admin.reservations',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/reservations/IndexReservations.vue')
                },
                {
                    path: '/admin/reservations/create',
                    name: 'admin.reservations.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/reservations/CreateReservations.vue')
                },
                {
                    path: '/admin/reservations/:id',
                    name: 'admin.reservations.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/reservations/ShowReservations.vue')
                },
                {
                    path: '/admin/reservations/:id/edit',
                    name: 'admin.reservations.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/reservations/EditReservations.vue')
                },

                {
                    path: '/admin/pdv',
                    name: 'admin.pdv',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexPDV.vue')
                },
                {
                    path: '/admin/pdv/:id/categories',
                    name: 'admin.pdv.categories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexPdvCategories.vue')
                },
                {
                    path: '/admin/pdv/quicksell',
                    name: 'admin.pdv.quicksell',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexPdvQuickSell.vue')
                },

                {
                    path: '/admin/payments',
                    name: 'admin.payments',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/payments/IndexPayments.vue')
                },
                {
                    path: '/admin/payments/:id',
                    name: 'admin.payments.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/payments/ShowPayments.vue')
                },

                {
                    path: '/admin/orders',
                    name: 'admin.orders',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/orders/IndexOrders.vue')
                },

                {
                    path: '/admin/orders/:id',
                    name: 'admin.orders.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/orders/ShowOrders.vue')
                },


                {
                    path: '/admin/centerstocks',
                    name: 'admin.centerstocks',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/centerstocks/IndexCenterStocks.vue')
                },
                {
                    path: '/admin/centerstocks/create',
                    name: 'admin.centerstocks.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/centerstocks/CreateCenterStocks.vue')
                },
                {
                    path: '/admin/centerstocks/:id',
                    name: 'admin.centerstocks.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/centerstocks/ShowCenterStocks.vue')
                },
                {
                    path: '/admin/centerstocks/:id/edit',
                    name: 'admin.centerstocks.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/centerstocks/EditCenterStocks.vue')
                },


                {
                    path: '/admin/suppliers',
                    name: 'admin.suppliers',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/suppliers/IndexSuppliers.vue')
                },
                {
                    path: '/admin/suppliers/create',
                    name: 'admin.suppliers.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/suppliers/CreateSuppliers.vue')
                },
                {
                    path: '/admin/suppliers/:id',
                    name: 'admin.suppliers.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/suppliers/ShowSuppliers.vue')
                },
                {
                    path: '/admin/suppliers/:id/edit',
                    name: 'admin.suppliers.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/suppliers/EditSuppliers.vue')
                },


                {
                    path: '/admin/stocktransfers',
                    name: 'admin.stocktransfers',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/stocktransfers/IndexStockTransfers.vue')
                },
                {
                    path: '/admin/stocktransfers/create',
                    name: 'admin.stocktransfers.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/stocktransfers/CreateStockTransfers.vue')
                },
                {
                    path: '/admin/stocktransfers/:id',
                    name: 'admin.stocktransfers.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/stocktransfers/ShowStockTransfers.vue')
                },
                {
                    path: '/admin/stocktransfers/:id/edit',
                    name: 'admin.stocktransfers.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/stocktransfers/EditStockTransfers.vue')
                },




                {
                    path: '/admin/inventories',
                    name: 'admin.inventories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/inventories/IndexInventories.vue')
                },
                {
                    path: '/admin/inventories/create',
                    name: 'admin.inventories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/inventories/CreateInventories.vue')
                },
                {
                    path: '/admin/inventories/:id',
                    name: 'admin.inventories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/inventories/ShowInventories.vue')
                },
                {
                    path: '/admin/inventories/:id/edit',
                    name: 'admin.inventories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/inventories/EditInventories.vue')
                },


                {
                    path: '/admin/entrynotes',
                    name: 'admin.entrynotes',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/entrynotes/IndexEntryNotes.vue')
                },
                {
                    path: '/admin/entrynotes/create',
                    name: 'admin.entrynotes.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/entrynotes/CreateEntryNotes.vue')
                },
                {
                    path: '/admin/entrynotes/:id',
                    name: 'admin.entrynotes.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/entrynotes/ShowEntryNotes.vue')
                },
                {
                    path: '/admin/entrynotes/:id/edit',
                    name: 'admin.entrynotes.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/entrynotes/EditEntryNotes.vue')
                },


                {
                    path: '/admin/exitnotes',
                    name: 'admin.exitnotes',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/exitnotes/IndexExitNotes.vue')
                },
                {
                    path: '/admin/exitnotes/create',
                    name: 'admin.exitnotes.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/exitnotes/CreateExitNotes.vue')
                },
                {
                    path: '/admin/exitnotes/:id',
                    name: 'admin.exitnotes.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/exitnotes/ShowExitNotes.vue')
                },
                {
                    path: '/admin/exitnotes/:id/edit',
                    name: 'admin.exitnotes.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/exitnotes/EditExitNotes.vue')
                },



                {
                    path: '/admin/cashregisters',
                    name: 'admin.cashregisters',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/cashregisters/IndexCashRegisters.vue')
                },
                {
                    path: '/admin/cashregisters/create',
                    name: 'admin.cashregisters.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/cashregisters/CreateCashRegisters.vue')
                },
                {
                    path: '/admin/cashregisters/:id',
                    name: 'admin.cashregisters.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/cashregisters/ShowCashRegisters.vue')
                },
                {
                    path: '/admin/cashregisters/:id/edit',
                    name: 'admin.cashregisters.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/cashregisters/EditCashRegisters.vue')
                },

                {
                    path: '/admin/cashregisters/dashboard',
                    name: 'admin.cashregisters.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexCashRegisterDashboard.vue')
                },
                {
                    path: '/admin/dailydashboard',
                    name: 'admin.dailydashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/cashregisters/DailyDashboard.vue')
                },

                


                
            ]
        },
        {
            path: '/stock/dashboard',
            component: AppStockLayout,
            children: [
                {
                    path: '/stock/dashboard',
                    name: 'stock.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/Dashboard.vue')
                },
                {
                    path: '/stock/categories',
                    name: 'stock.categories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/categories/IndexCategories.vue')
                },
                {
                    path: '/stock/categories/create',
                    name: 'stock.categories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/categories/CreateCategories.vue')
                },
                {
                    path: '/stock/categories/:id',
                    name: 'stock.categories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/categories/ShowCategories.vue')
                },
                {
                    path: '/stock/categories/:id/edit',
                    name: 'stock.categories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/categories/EditCategories.vue')
                },

                {
                    path: '/stock/subcategories',
                    name: 'stock.subcategories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/subcategories/IndexSubCategories.vue')
                },
                {
                    path: '/stock/subcategories/create',
                    name: 'stock.subcategories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/subcategories/CreateSubCategories.vue')
                },
                {
                    path: '/stock/subcategories/:id',
                    name: 'stock.subcategories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/subcategories/ShowSubCategories.vue')
                },
                {
                    path: '/stock/subcategories/:id/edit',
                    name: 'stock.subcategories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/subcategories/EditSubCategories.vue')
                },


                {
                    path: '/stock/products',
                    name: 'stock.products',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/products/IndexProducts.vue')
                },
                {
                    path: '/stock/products/create',
                    name: 'stock.products.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/products/CreateProducts.vue')
                },
                {
                    path: '/stock/products/:id',
                    name: 'stock.products.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/products/ShowProducts.vue')
                },
                {
                    path: '/stock/products/:id/edit',
                    name: 'stock.products.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/products/EditProducts.vue')
                },

                {
                    path: '/stock/centerstocks',
                    name: 'stock.centerstocks',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/centerstocks/IndexCenterStocks.vue')
                },
                {
                    path: '/stock/centerstocks/create',
                    name: 'stock.centerstocks.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/centerstocks/CreateCenterStocks.vue')
                },
                {
                    path: '/stock/centerstocks/:id',
                    name: 'stock.centerstocks.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/centerstocks/ShowCenterStocks.vue')
                },
                {
                    path: '/stock/centerstocks/:id/edit',
                    name: 'stock.centerstocks.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/centerstocks/EditCenterStocks.vue')
                },


                {
                    path: '/stock/suppliers',
                    name: 'stock.suppliers',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/suppliers/IndexSuppliers.vue')
                },
                {
                    path: '/stock/suppliers/create',
                    name: 'stock.suppliers.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/suppliers/CreateSuppliers.vue')
                },
                {
                    path: '/stock/suppliers/:id',
                    name: 'stock.suppliers.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/suppliers/ShowSuppliers.vue')
                },
                {
                    path: '/stock/suppliers/:id/edit',
                    name: 'stock.suppliers.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/suppliers/EditSuppliers.vue')
                },


                {
                    path: '/stock/stocktransfers',
                    name: 'stock.stocktransfers',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/stocktransfers/IndexStockTransfers.vue')
                },
                {
                    path: '/stock/stocktransfers/create',
                    name: 'stock.stocktransfers.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/stocktransfers/CreateStockTransfers.vue')
                },
                {
                    path: '/stock/stocktransfers/:id',
                    name: 'stock.stocktransfers.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/stocktransfers/ShowStockTransfers.vue')
                },
                {
                    path: '/stock/stocktransfers/:id/edit',
                    name: 'stock.stocktransfers.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/stocktransfers/EditStockTransfers.vue')
                },




                {
                    path: '/stock/inventories',
                    name: 'stock.inventories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/inventories/IndexInventories.vue')
                },
                {
                    path: '/stock/inventories/create',
                    name: 'stock.inventories.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/inventories/CreateInventories.vue')
                },
                {
                    path: '/stock/inventories/:id',
                    name: 'stock.inventories.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/inventories/ShowInventories.vue')
                },
                {
                    path: '/stock/inventories/:id/edit',
                    name: 'stock.inventories.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/inventories/EditInventories.vue')
                },


                {
                    path: '/stock/entrynotes',
                    name: 'stock.entrynotes',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/entrynotes/IndexEntryNotes.vue')
                },
                {
                    path: '/stock/entrynotes/create',
                    name: 'stock.entrynotes.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/entrynotes/CreateEntryNotes.vue')
                },
                {
                    path: '/stock/entrynotes/:id',
                    name: 'stock.entrynotes.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/entrynotes/ShowEntryNotes.vue')
                },
                {
                    path: '/stock/entrynotes/:id/edit',
                    name: 'stock.entrynotes.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/entrynotes/EditEntryNotes.vue')
                },


                {
                    path: '/stock/exitnotes',
                    name: 'stock.exitnotes',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/exitnotes/IndexExitNotes.vue')
                },
                {
                    path: '/stock/exitnotes/create',
                    name: 'stock.exitnotes.create',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/exitnotes/CreateExitNotes.vue')
                },
                {
                    path: '/stock/exitnotes/:id',
                    name: 'stock.exitnotes.show',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/exitnotes/ShowExitNotes.vue')
                },
                {
                    path: '/stock/exitnotes/:id/edit',
                    name: 'stock.exitnotes.edit',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/stock/exitnotes/EditExitNotes.vue')
                },

            ]
        },
        {
            path: '/bar/dashboard',
            component: AppBarLayout,
            children: [
                {
                    path: '/bar/dashboard',
                    name: 'bar.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/bar/Dashboard.vue')
                },
                {
                    path: '/bar/pdv',
                    name: 'bar.pdv',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/bar/pdv/IndexPdv.vue')
                },
            ]
        },
        {
            path: '/kitchen/dashboard',
            component: AppKitchenLayout,
            children: [
                {
                    path: '/kitchen/dashboard',
                    name: 'kitchen.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/kitchen/Dashboard.vue')
                },
                {
                    path: '/kitchen/pdv',
                    name: 'kitchen.pdv',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/kitchen/pdv/IndexPdv.vue')
                },
            ]
        },
        {
            path: '/waiter/dashboard',
            component: AppWaiterLayout,
            children: [
                {
                    path: '/waiter/dashboard',
                    name: 'waiter.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/waiter/Dashboard.vue')
                },
                {
                    path: '/waiter/pdv',
                    name: 'waiter.pdv',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/waiter/pdv/IndexPdv.vue')
                },
                {
                    path: '/waiter/pdv/:id/categories',
                    name: 'waiter.pdv.categories',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexPdvCategories.vue')
                },
                {
                    path: '/waiter/pdv/quicksell',
                    name: 'waiter.pdv.quicksell',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/admin/pdv/IndexPdvQuickSell.vue')
                },

                {
                    path: '/waiter/delivery',
                    name: 'waiter.delivery',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/waiter/pdv/IndexDelivery.vue')
                },

                {
                    path: '/waiter/cashregisters/dashboard',
                    name: 'waiter.cashregisters.dashboard',
                    meta: {
                        requiresAuth: true
                    },
                    component: () => import('@/views/pages/waiter/pdv/IndexCashRegisterDashboard.vue')
                },
            ]
        },
        {
            path: '/dashboard',
            component: AppLayout,
            children: [
                {
                    path: '/dashboard',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue')
                },
                {
                    path: '/uikit/formlayout',
                    name: 'formlayout',
                    component: () => import('@/views/uikit/FormLayout.vue')
                },
                {
                    path: '/uikit/input',
                    name: 'input',
                    component: () => import('@/views/uikit/InputDoc.vue')
                },
                {
                    path: '/uikit/button',
                    name: 'button',
                    component: () => import('@/views/uikit/ButtonDoc.vue')
                },
                {
                    path: '/uikit/table',
                    name: 'table',
                    component: () => import('@/views/uikit/TableDoc.vue')
                },
                {
                    path: '/uikit/list',
                    name: 'list',
                    component: () => import('@/views/uikit/ListDoc.vue')
                },
                {
                    path: '/uikit/tree',
                    name: 'tree',
                    component: () => import('@/views/uikit/TreeDoc.vue')
                },
                {
                    path: '/uikit/panel',
                    name: 'panel',
                    component: () => import('@/views/uikit/PanelsDoc.vue')
                },

                {
                    path: '/uikit/overlay',
                    name: 'overlay',
                    component: () => import('@/views/uikit/OverlayDoc.vue')
                },
                {
                    path: '/uikit/media',
                    name: 'media',
                    component: () => import('@/views/uikit/MediaDoc.vue')
                },
                {
                    path: '/uikit/message',
                    name: 'message',
                    component: () => import('@/views/uikit/MessagesDoc.vue')
                },
                {
                    path: '/uikit/file',
                    name: 'file',
                    component: () => import('@/views/uikit/FileDoc.vue')
                },
                {
                    path: '/uikit/menu',
                    name: 'menu',
                    component: () => import('@/views/uikit/MenuDoc.vue')
                },
                {
                    path: '/uikit/charts',
                    name: 'charts',
                    component: () => import('@/views/uikit/ChartDoc.vue')
                },
                {
                    path: '/uikit/misc',
                    name: 'misc',
                    component: () => import('@/views/uikit/MiscDoc.vue')
                },
                {
                    path: '/uikit/timeline',
                    name: 'timeline',
                    component: () => import('@/views/uikit/TimelineDoc.vue')
                },
                {
                    path: '/pages/empty',
                    name: 'empty',
                    component: () => import('@/views/pages/Empty.vue')
                },
                {
                    path: '/pages/crud',
                    name: 'crud',
                    component: () => import('@/views/pages/Crud.vue')
                },
                {
                    path: '/documentation',
                    name: 'documentation',
                    component: () => import('@/views/pages/Documentation.vue')
                }
            ]
        },
        {
            path: '/',
            name: 'landing',
            component: () => import('@/views/pages/Landing.vue')
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/views/pages/NotFound.vue')
        },

        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/views/pages/auth/Login.vue')
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/views/pages/auth/Access.vue')
        },
        {
            path: '/auth/error',
            name: 'error',
            component: () => import('@/views/pages/auth/Error.vue')
        }
    ]
});

export default router;
