<script setup>
import { CustomerService } from '@/service/CustomerService';
import { ProductService } from '@/service/ProductService';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { onBeforeMount, reactive, ref, onMounted, watch } from 'vue';
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router';
import { useForm } from 'vee-validate';
// import { debounce } from 'lodash';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';
import moment from 'moment';
// import { object, string, number, date } from 'yup';
import * as yup from 'yup';


const router = useRouter();
const toast = useToast();
const loading1 = ref(null);
const isLoadingDiv = ref(true);
const loadingButtonDelete = ref(false);
const loadingproduct = ref(false);
let dataIdBeingDeleted = ref(null);
const searchQuery = ref('');
const retriviedData = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);
const totalRecords = ref(0);
const displayConfirmation = ref(false);
const departments = ref(null);
const isLoadingButton = ref(false);
const stockcenters = ref([]);
const stockcenterproducts = ref([]);


const schema = yup.object({
    reference: yup.string().required().trim().label('Codigo'),
    transfer_date: yup.string().required().trim().label('transfer'),
    stock_center_origin_id: yup.string().required().trim().label('CentrodeStock'),
    stock_center_destination_id: yup.string().required().trim().label('CentrodeStock'),

});
const { defineField, handleSubmit, resetForm, errors, setErrors } = useForm({
    validationSchema: schema
});

const [reference] = defineField('reference');
const [transfer_date] = defineField('transfer_date');
const [stock_center_origin_id] = defineField('stock_center_origin_id');
const [stock_center_destination_id] = defineField('stock_center_destination_id');

const image = ref();



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

function getSeverity(status) {
    switch (status) {
        case 'unqualified':
            return 'danger';

        case 'qualified':
            return 'success';

        case 'new':
            return 'info';

        case 'negotiation':
            return 'warn';

        case 'renewal':
            return null;
    }
}

const onSubmit = handleSubmit((values) => {

    const transferData = stockcenterproducts.value.map(product => ({
                id: product.id,
                product_id: product.product.id,
                stock_center_id: product.stock_center_id,
                quantity: product.transferQuantity, // transferQuantity será enviado
    }));
    values.stockcenterproducts = transferData;


    if (image.value != null) {
        values.image = image.value;
    }
    isLoadingButton.value = true;
    axios
        .post(`/api/stocktransfers`, values,{
            headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then((response) => {
            // resetForm();
            router.back();
            toast.add({ severity: 'success', summary: `Successo`, detail: 'Categoria criado com sucesso', life: 3000 });
        })
        .catch((error) => {
            isLoadingButton.value = false;
            toast.add({ severity: 'error', summary: `Erro`, detail: `${error.response.data.message}`, life: 3000 });
            if (error.response.data.errors) {
                setErrors(error.response.data.errors);
            }
        })
        .finally(() => {
            isLoadingButton.value = false;
        });
});

const getData = async (page = 1) => {
    axios
        .get(`/api/stocktransfers/create`, {
            params: {
                query: searchQuery.value
            }
        })
        .then((response) => {
            stockcenters.value = response.data.stockcenters;

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
        .delete(`/api/stocktransfers/${dataIdBeingDeleted.value}`)
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

const getProducts = (stockcenter) => {
    loadingproduct.value=true;
    axios.get(`/api/auxiliar-product/${stockcenter}`)
   .then((response)=>{

    stockcenterproducts.value = response.data.stockcenterproducts;
    loadingproduct.value=false;
   })
   .catch((error)=>{
    toast.add({ severity: 'error', summary: `${error}`, detail: 'Message Detail', life: 3000 });
    loadingproduct.value=false;
   })
}

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
    
    <div class="flex flex-col md:flex-row gap-12" v-else>
        <div class="w-full">
            
            <div class="card flex flex-col gap-4">
                <div class="w-full">
                    <Button label="Voltar" class="mr-2 mb-2" @click="goBackUsingBack"><i class="pi pi-angle-left"></i> Voltar</Button>
                </div>
                <div class="font-semibold text-xl">Centro de Stock</div>
                <small class="p-error">Os campos marcados * sao considerados campos obrigatorios.</small>
                <form @submit="onSubmit">
                    <div class="flex flex-col gap-2">
                        <label for="reference">REF</label>
                        <InputText v-model="reference" id="reference" placeholder="Nome" :class="{ 'p-invalid': errors.reference }" type="text" />
                        <small id="reference-help" class="p-error">{{ errors.reference }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="transfer_date">Data</label>
                        <InputText v-model="transfer_date" id="transfer_date" placeholder="Codigo" :class="{ 'p-invalid': errors.transfer_date }" type="date" />
                        <small id="transfer_date-help" class="p-error">{{ errors.transfer_date }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="stock_center_origin_id">Centro de Sock de Origem</label>
                        <Select v-model="stock_center_origin_id" :options="stockcenters" optionLabel="name" optionValue="id" placeholder="Selecionar" :class="{ 'p-invalid': errors.stock_center_origin_id }" @change="getProducts(stock_center_origin_id)" />
                        <small id="stock_center_origin_id-help" class="p-error">{{ errors.stock_center_origin_id }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="stock_center_destination_id">Centro de Sock de Destino</label>
                        <Select v-model="stock_center_destination_id" :options="stockcenters" optionLabel="name" optionValue="id" placeholder="Selecionar" :class="{ 'p-invalid': errors.stock_center_destination_id }" />
                        <small id="stock_center_destination_id-help" class="p-error">{{ errors.stock_center_destination_id }}</small>
                    </div>

                    <hr>
                    <DataTable
                    :value="stockcenterproducts"
                    dataKey="id"
                    :rowHover="true"
                    :loading="isLoadingDiv"
                    showGridlines
                    >
                    <template #header>
                        
                    </template>
                    <template #empty>Nenhuma registro encontrado. </template>
                    <template #loading> Carregando, por favor espere. </template>
                    <Column header="ID" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.id }}
                        </template>
                    </Column>
                    <Column header="Nome" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.product.name }}
                        </template>
                    </Column>
                    <Column header="Categoria" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.product.category.name }}
                        </template>
                    </Column>
                    <Column header="SubCategoria" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.product.subcategory.name }}
                        </template>
                    </Column>
                    <Column header="Stock" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.quantity }}
                        </template>
                    </Column>
                    <Column header="Quantidade a Transferir" dataType="number" style="min-width: 10rem">
                        <template #body="{ data }">
                            <InputText v-model="data.transferQuantity" placeholder="Quantidade" :min="0" :max="data.quantity"  type="number" :style="{ 'width': '100%' }"/>
                        </template>
                    </Column>
                </DataTable>
                   
                    
                    <Button label="Submeter" class="mr-2 mb-2 mt-2" @click="onSubmit" :disabled="isLoadingButton"></Button>
                    <ProgressSpinner style="width: 35px; height: 35px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" aria-label="Custom ProgressSpinner" v-if="isLoadingButton" />
                </form>
            </div>
        </div>
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


