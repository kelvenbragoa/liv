<script setup>
import { CustomerService } from '@/service/CustomerService';
import { ProductService } from '@/service/ProductService';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { onBeforeMount, reactive, ref, onMounted, watch } from 'vue';
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router';

// import { debounce } from 'lodash';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';

import moment from 'moment';

const router = useRouter();
const toast = useToast();
const loading1 = ref(null);
const isLoadingDiv = ref(true);
const loadingButtonDelete = ref(false);
let dataIdBeingDeleted = ref(null);
const searchQuery = ref('');
const retriviedData = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);
const totalRecords = ref(0);
const displayConfirmation = ref(false);

// Definindo os itens do Menubar
const nestedMenuitems = [
  {
    label: 'Venda Rápida',
    items: [
      { 
        label: 'Inicar Venda Rápida', 
        icon: 'pi pi-fw pi-shopping-cart', 
        command: () => { router.push('/waiter/pdv/quicksell') }  // Abre o dialog ao clicar
      },
    ]
  },
  {
    label: 'Pedidos',
    items: [
      { 
        label: 'Mesas', 
        icon: 'pi pi-fw pi-folder-open', 
        command: () => { openFileDialog.value = true }  // Abre o dialog ao clicar
      },
    ]
  },
  {
    label: 'Caixa',
    items: [
      { 
        label: 'Fecho de caixa', 
        icon: 'pi pi-fw pi-lock', 
        command: () => { openFileDialog.value = true }  // Abre o dialog ao clicar
      },
      { 
        label: 'Relatório de caixa', 
        icon: 'pi pi-fw pi-check', 
        command: () => { openFileDialog.value = true }  // Abre o dialog ao clicar
      },
    ]
  }
];

function goBackUsingBack() {
    if (router) {
        router.back();
    }
}

const closeConfirmation = () => {
    displayConfirmation.value = false;
};
const confirmDeletion = (id) => {
    displayConfirmation.value = true;
    dataIdBeingDeleted.value = id;
};

function getSeverity2(status) {
    switch (status) {
        case 1:
            return 'red';

        case 2:
            return 'red';

        case 3:
            return 'warn';

        case 4:
            return 'danger';

        case 5:
            return 'info';
        
        case 6:
            return 'info';
    }
}
function getSeverity(status) {
    switch (status) {
        case 1:
            return 'success';

        case 2:
            return 'danger';

        case 3:
            return 'warn';

        case 4:
            return 'danger';

        case 5:
            return 'info';
        
        case 6:
            return 'info';
    }
}

const getData = async (page = 1) => {
    axios
        .get(`/api/pdv?page=${page}`, {
            params: {
                query: searchQuery.value
            }
        })
        .then((response) => {
            retriviedData.value = response.data;
            totalRecords.value = response.data.total;
            isLoadingDiv.value = false;
        })
        .catch((error) => {
            isLoadingDiv.value = false;
            toast.add({ severity: 'error', summary: `${error}`, detail: 'Message Detail', life: 3000 });
            goBackUsingBack();
        });
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios
        .delete(`/api/tables/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter((data) => data.id !== dataIdBeingDeleted.value);
            closeConfirmation();
            toast.add({ severity: 'success', summary: `Sucesso`, detail: 'Sucesso ao apagar', life: 3000 });
        })
        .catch((error) => {
            toast.add({ severity: 'error', summary: `Erro`, detail: `${error}`, life: 3000 });
            loadingButtonDelete.value = false;
        })
        .finally(() => {
            loadingButtonDelete.value = false;
        });
};

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

const debouncedSearch = debounce(() => {
    getData(currentPage.value);
}, 300);

watch(searchQuery,debouncedSearch);

onMounted(() => {
    getData();
});

</script>

<template>
    <div class="flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"  v-if="isLoadingDiv">
            <div class="w-full">
                <div class="flex flex-col gap-4 text-center">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" aria-label="Custom ProgressSpinner" />
                    <p>Por Favor Aguarde...</p>
                </div>
            </div>
    </div>
    
        
        <div v-else>
            <div class="mb-2">
                        <Menubar :model="nestedMenuitems">
                            <template #end>
                                <p>Total Venda Hoje: 0 MT</p>
                            </template>
                        </Menubar>
                    </div>
            <div class="grid grid-cols-12 gap-8">
                    <div class="col-span-12 lg:col-span-6 xl:col-span-3" v-for="(table,index) in retriviedData.data" :key="table.id">
                        <router-link :to="'/waiter/pdv/' + table.id + '/categories'">
                            <div class="card mb-0" :class="{
                                    'bg-green-100': table.table_status_id === 1, 
                                    'bg-red-100': table.table_status_id === 2
                                }">
                                <div class="flex justify-between mb-4">
                                    <div>
                                        <!-- <span class="block text-muted-color font-medium mb-4 text-xxl">{{table.name}}</span> -->
                                        <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">{{table.name}}</div>
                                    </div>
                                    <div :class="[
                                            'flex items-center justify-center rounded-full', 
                                            `bg-${getSeverity2(table.status_id)}-100`, 
                                            `dark:bg-${getSeverity2(table.status_id)}-400/10`
                                        ]" 
                                        style="width: 2.5rem; height: 2.5rem">
                                        <i class="pi pi-list text-blue-500 !text-xl" aria-label="Carrinho de Compras"></i>
                                    </div>
                                </div>
                                <div class="flex justify-between mb-4">
                                    <span class="text-primary font-medium">Capacidade: {{table.capacity}} </span>
                                    <span><Tag :value="table.status.name" :severity="getSeverity(table.table_status_id)" /></span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                    
            </div>
                <!-- <div class="card flex flex-col gap-4" v-for="(index,table) in retriviedData.data" :key="table.id">
                <div class="col-span-12 lg:col-span-6 xl:col-span-3">
                    <div class="card mb-0">
                        <div class="flex justify-between mb-4">
                            <div>
                                <span class="block text-muted-color font-medium mb-4">Orders</span>
                                <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">152</div>
                            </div>
                            <div class="flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-border" style="width: 2.5rem; height: 2.5rem">
                                <i class="pi pi-shopping-cart text-blue-500 !text-xl"></i>
                            </div>
                        </div>
                        <span class="text-primary font-medium">24 new </span>
                        <span class="text-muted-color">since last visit</span>
                    </div>
                </div>                   
            </div> -->
        </div>
    <Dialog header="Confirmação" v-model:visible="displayConfirmation" :style="{ width: '350px' }" :modal="true">
        <div class="flex align-items-center justify-content-center">
            <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
            <span>Tem certeza que deseja proceder?</span>
        </div>
        <template #footer>
            <Button label="Não" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
            <Button label="Sim" icon="pi pi-check" @click="deleteData" class="p-button-text" autofocus />
        </template>
    </Dialog>
</template>