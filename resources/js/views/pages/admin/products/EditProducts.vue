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
let dataIdBeingDeleted = ref(null);
const searchQuery = ref('');
const retriviedData = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);
const totalRecords = ref(0);
const displayConfirmation = ref(false);
const categories = ref(null);
const sub_categories = ref(null);
const filtered_sub_categories = ref(null);

const isLoadingButton = ref(false);


const schema = yup.object({
    category_id: yup.string().required().trim().label('Categoria'),
    name: yup.string().required().trim().label('Name'),
    price: yup.string().required().trim().label('Preco'),
    sub_category_id: yup.string().required().trim().label('Name'),

});
const { defineField, handleSubmit, resetForm, errors, setErrors } = useForm({
    validationSchema: schema
});
const [category_id] = defineField('category_id');
const [sub_category_id] = defineField('sub_category_id');
const [price] = defineField('price');
const [buy_price] = defineField('buy_price');

const [name] = defineField('name');
const [_method] = defineField('_method');
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
    if (image.value != null) {
        values.image = image.value;
    }
    isLoadingButton.value = true;
    axios
        .post(`/api/products/${router.currentRoute.value.params.id}`, values,{
            headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then((response) => {
            // resetForm();
            router.back();
            toast.add({ severity: 'success', summary: `Successo`, detail: 'Produto criado com sucesso', life: 3000 });
        })
        .catch((error) => {
            isLoadingButton.value = false;
            toast.add({ severity: 'error', summary: `Erro}`, detail: `${error.response.data.message}`, life: 3000 });
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
        .get(`/api/products/${router.currentRoute.value.params.id}/edit`, {
            params: {
                query: searchQuery.value
            }
        })
        .then((response) => {
            retriviedData.value = response.data.product;
            name.value = retriviedData.value.name;
            buy_price.value = retriviedData.value.buy_price;
            price.value = retriviedData.value.price;
            category_id.value = retriviedData.value.category_id;
            sub_category_id.value = retriviedData.value.sub_category_id;
            image.value = retriviedData.value.image;

            categories.value = response.data.categories;
            sub_categories.value = response.data.sub_categories;
            filtered_sub_categories.value = sub_categories.value.filter(sub => sub.category_id === category_id.value);

            _method.value = 'put';
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
        .delete(`/api/products/${dataIdBeingDeleted.value}`)
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

const onFileUpload = (event) => {
    image.value = event.files[0];
    console.log(image.value);
};

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

const debouncedSearch = debounce(() => {
    getData(currentPage.value);
}, 300);

// watch(searchQuery,debouncedSearch);
watch(category_id, (newCategoryId) => {
    if (newCategoryId) {
        filtered_sub_categories.value = sub_categories.value.filter(sub => sub.category_id === newCategoryId);
    } else {
        filtered_sub_categories.value = []; // Reset if no category is selected
    }
});

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
                <div class="font-semibold text-xl">Produto</div>
                <small class="p-error">Os campos marcados * sao considerados campos obrigatorios.</small>
                <form @submit="onSubmit">
                    <div class="flex flex-col gap-2">
                        <label for="name1">Nome</label>
                        <InputText v-model="name" id="name" placeholder="Nome da Produto" :class="{ 'p-invalid': errors.name }" type="text" />
                        <small id="name-help" class="p-error">{{ errors.name }}</small>

                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="name1">Preço de Venda</label>
                        <InputText v-model="price" id="price" placeholder="Preço de Venda" :class="{ 'p-invalid': errors.price }" type="number" />
                        <small id="price-help" class="p-error">{{ errors.price }}</small>

                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="buy_price">Preço de Compra</label>
                        <InputText v-model="buy_price" id="buy_price" placeholder="Preço de Compra" :class="{ 'p-invalid': errors.buy_price }" type="number" />
                        <small id="buy_price-help" class="p-error">{{ errors.buy_price }}</small>

                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="name1">Categoria</label>
                        <Select v-model="category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Selecionar" :class="{ 'p-invalid': errors.category_id }" />
                        <small id="category_id-help" class="p-error">{{ errors.category_id }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="name1">SubCategoria</label>
                        <Select v-model="sub_category_id" :options="filtered_sub_categories" optionLabel="name" optionValue="id" placeholder="Selecionar" :class="{ 'p-invalid': errors.sub_category_id }" />
                        <small id="sub_category_id-help" class="p-error">{{ errors.sub_category_id }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="name1">Imagem</label>
                        <FileUpload mode="basic" name="image[]" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />

                    </div>
                    <div class="col-6 lg:col-3 xl:col-3">
                            <img :src="'/'+image" alt="" weigth="100" height="100" style="border-radius: 15px" />
                        </div>
                    <Button label="Submeter" class="mr-2 mb-2" @click="onSubmit" :disabled="isLoadingButton"></Button>
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


