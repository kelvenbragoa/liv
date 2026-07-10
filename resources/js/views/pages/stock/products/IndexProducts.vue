<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';
import moment from 'moment';
import * as XLSX from 'xlsx';

const router = useRouter();
const toast = useToast();

const isInitialLoading = ref(true);
const isTableLoading = ref(false);
const isExporting = ref(false);
const loadingButtonDelete = ref(false);
const dataIdBeingDeleted = ref(null);
const displayConfirmation = ref(false);

const products = ref([]);
const categories = ref([]);
const subcategories = ref([]);
const departments = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const categoryId = ref(null);
const subCategoryId = ref(null);
const departmentId = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('name');
const sortDir = ref('asc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Nome', value: 'name' },
    { label: 'ID', value: 'id' },
    { label: 'Categoria', value: 'category_id' },
    { label: 'Subcategoria', value: 'sub_category_id' },
    { label: 'Departamento', value: 'department_id' },
    { label: 'Preço venda', value: 'price' },
    { label: 'Preço compra', value: 'buy_price' },
    { label: 'Stock', value: 'quantity_in_principal_stock' },
    { label: 'Data de criação', value: 'created_at' }
];

const filteredSubcategories = computed(() => {
    if (!categoryId.value) {
        return subcategories.value;
    }

    return subcategories.value.filter((item) => item.category_id === categoryId.value);
});

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        categoryId.value != null ||
        subCategoryId.value != null ||
        departmentId.value != null ||
        createdFrom.value != null ||
        createdTo.value != null ||
        sortBy.value !== 'name' ||
        sortDir.value !== 'asc'
);

const paginationSummary = computed(() => {
    if (!totalRecords.value) {
        return 'Nenhum registo';
    }

    const from = (currentPage.value - 1) * rowsPerPage.value + 1;
    const to = Math.min(currentPage.value * rowsPerPage.value, totalRecords.value);

    return `${from}-${to} de ${totalRecords.value}`;
});

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatPrice(value) {
    if (value == null || value === '') {
        return '—';
    }

    return Number(value).toLocaleString('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDateParam(value) {
    if (!value) {
        return null;
    }

    return moment(value).format('YYYY-MM-DD');
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        category_id: categoryId.value ?? undefined,
        sub_category_id: subCategoryId.value ?? undefined,
        department_id: departmentId.value ?? undefined,
        created_from: formatDateParam(createdFrom.value),
        created_to: formatDateParam(createdTo.value),
        sort_by: sortBy.value,
        sort_dir: sortDir.value
    };
}

function buildExportParams() {
    const { page, per_page, ...params } = buildParams(1);

    return params;
}

const getFilterOptions = async () => {
    try {
        const [productsResponse, departmentsResponse] = await Promise.all([
            axios.get('/api/products/create'),
            axios.get('/api/categories/create')
        ]);

        categories.value = productsResponse.data.categories ?? [];
        subcategories.value = productsResponse.data.sub_categories ?? [];
        departments.value = departmentsResponse.data.departments ?? [];
    } catch (error) {
        console.error('Erro ao carregar filtros:', error);
    }
};

const getData = async (page = 1, { initial = false } = {}) => {
    if (initial) {
        isInitialLoading.value = true;
    } else {
        isTableLoading.value = true;
    }

    try {
        const response = await axios.get('/api/products', {
            params: buildParams(page)
        });

        products.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os produtos.',
            life: 3000
        });

        if (initial) {
            goBackUsingBack();
        }
    } finally {
        isInitialLoading.value = false;
        isTableLoading.value = false;
    }
};

const resetFilters = () => {
    searchQuery.value = '';
    categoryId.value = null;
    subCategoryId.value = null;
    departmentId.value = null;
    createdFrom.value = null;
    createdTo.value = null;
    sortBy.value = 'name';
    sortDir.value = 'asc';
    currentPage.value = 1;
    getData(1);
};

const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
};

const closeConfirmation = () => {
    displayConfirmation.value = false;
    dataIdBeingDeleted.value = null;
};

const confirmDeletion = (id) => {
    dataIdBeingDeleted.value = id;
    displayConfirmation.value = true;
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios
        .delete(`/api/products/${dataIdBeingDeleted.value}`)
        .then(() => {
            closeConfirmation();
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Produto removido com sucesso.',
                life: 3000
            });
            getData(currentPage.value);
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Não foi possível remover o produto.',
                life: 3000
            });
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

const exportToExcel = async () => {
    isExporting.value = true;

    try {
        const response = await axios.get('/api/products/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há produtos para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            ['ID', 'Nome', 'Categoria', 'Subcategoria', 'Departamento', 'Preço venda', 'Preço compra', 'Stock', 'Criado em'],
            ...rows.map((row) => [
                row.id,
                row.name,
                row.category ?? '',
                row.subcategory ?? '',
                row.department ?? '',
                row.price ?? 0,
                row.buy_price ?? 0,
                row.stock ?? 0,
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 30 },
            { wch: 20 },
            { wch: 20 },
            { wch: 20 },
            { wch: 14 },
            { wch: 14 },
            { wch: 10 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Produtos');
        XLSX.writeFile(workbook, `produtos-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} produtos exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os produtos.',
            life: 3000
        });
    } finally {
        isExporting.value = false;
    }
};

const debouncedReload = debounce(() => {
    currentPage.value = 1;
    getData(1);
}, 350);

watch(categoryId, () => {
    if (
        subCategoryId.value &&
        !filteredSubcategories.value.some((item) => item.id === subCategoryId.value)
    ) {
        subCategoryId.value = null;
    }
});

watch(
    [searchQuery, categoryId, subCategoryId, departmentId, createdFrom, createdTo, sortBy, sortDir],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="prod-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar produtos...</p>
    </div>

    <div v-else class="prod-page">
        <div class="prod-card">
            <header class="prod-header">
                <div>
                    <p class="prod-eyebrow">Catálogo</p>
                    <h1>Produtos</h1>
                    <p class="prod-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="prod-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/stock/products/create">
                        <Button label="Novo produto" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="prod-filters">
                <div class="prod-filters__grid">
                    <div class="prod-field">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Nome do produto..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="prod-field">
                        <label>Categoria</label>
                        <Select
                            v-model="categoryId"
                            :options="categories"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todas"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Subcategoria</label>
                        <Select
                            v-model="subCategoryId"
                            :options="filteredSubcategories"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todas"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Departamento</label>
                        <Select
                            v-model="departmentId"
                            :options="departments"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Criado desde</label>
                        <DatePicker
                            v-model="createdFrom"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Início"
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Criado até</label>
                        <DatePicker
                            v-model="createdTo"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Fim"
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="prod-field">
                        <label>Direção</label>
                        <div class="prod-sort-dir">
                            <Button
                                :label="sortDir === 'asc' ? 'Ascendente' : 'Descendente'"
                                :icon="sortDir === 'asc' ? 'pi pi-sort-amount-up' : 'pi pi-sort-amount-down'"
                                severity="secondary"
                                outlined
                                class="w-full"
                                @click="toggleSortDir"
                            />
                        </div>
                    </div>
                </div>

                <div class="prod-filters__actions">
                    <Button
                        v-if="hasActiveFilters"
                        label="Limpar filtros"
                        icon="pi pi-filter-slash"
                        text
                        @click="resetFilters"
                    />
                    <Button
                        label="Actualizar"
                        icon="pi pi-refresh"
                        text
                        :loading="isTableLoading"
                        @click="getData(currentPage)"
                    />
                </div>
            </section>

            <DataTable
                :value="products"
                :paginator="true"
                :rows="rowsPerPage"
                :rowsPerPageOptions="rowsPerPageOptions"
                :totalRecords="totalRecords"
                :lazy="true"
                :loading="isTableLoading"
                :first="(currentPage - 1) * rowsPerPage"
                dataKey="id"
                rowHover
                showGridlines
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
                currentPageReportTemplate="{first}-{last} de {totalRecords}"
                @page="onPageChange"
            >
                <template #empty>
                    <div class="prod-empty">Nenhum produto encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="prod-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Nome" style="min-width: 16rem">
                    <template #body="{ data }">
                        <strong>{{ data.name }}</strong>
                    </template>
                </Column>

                <Column header="Categoria" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.category?.name"
                            :value="data.category.name"
                            severity="info"
                        />
                        <span v-else class="prod-muted">—</span>
                    </template>
                </Column>

                <Column header="Subcategoria" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.subcategory?.name"
                            :value="data.subcategory.name"
                            severity="secondary"
                        />
                        <span v-else class="prod-muted">—</span>
                    </template>
                </Column>

                <Column header="Departamento" style="min-width: 11rem">
                    <template #body="{ data }">
                        <span v-if="data.category?.department?.name">{{ data.category.department.name }}</span>
                        <span v-else class="prod-muted">—</span>
                    </template>
                </Column>

                <Column header="Preço" style="min-width: 8rem">
                    <template #body="{ data }">
                        {{ formatPrice(data.price) }}
                    </template>
                </Column>

                <Column header="Stock" style="min-width: 7rem">
                    <template #body="{ data }">
                        <span
                            class="prod-stock"
                            :class="{
                                'prod-stock--low': (data.quantity_in_principal_stock ?? 0) <= 5,
                                'prod-stock--empty': (data.quantity_in_principal_stock ?? 0) === 0
                            }"
                        >
                            {{ data.quantity_in_principal_stock ?? 0 }}
                        </span>
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 10rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="prod-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/stock/products/${data.id}`)"
                            />
                            <Button
                                v-tooltip.top="'Editar'"
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="info"
                                @click="router.push(`/stock/products/${data.id}/edit`)"
                            />
                            <Button
                                v-tooltip.top="'Eliminar'"
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                @click="confirmDeletion(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <Dialog
        v-model:visible="displayConfirmation"
        header="Confirmar eliminação"
        :style="{ width: '24rem' }"
        modal
    >
        <p>Tem a certeza que deseja eliminar este produto?</p>
        <template #footer>
            <Button label="Cancelar" text @click="closeConfirmation" />
            <Button
                label="Eliminar"
                icon="pi pi-trash"
                severity="danger"
                :loading="loadingButtonDelete"
                @click="deleteData"
            />
        </template>
    </Dialog>
</template>

<style scoped>
.prod-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.prod-page {
    --prod-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --prod-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --prod-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --prod-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--prod-border-soft);
}

.prod-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--prod-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--prod-shadow);
}

.prod-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.prod-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.prod-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.prod-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.prod-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.prod-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--prod-border-soft);
    border-radius: 0.85rem;
    background: var(--prod-muted-bg);
}

.prod-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.prod-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.prod-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.prod-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.prod-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.prod-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.prod-stock {
    display: inline-flex;
    min-width: 1.75rem;
    justify-content: center;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--prod-muted-bg);
    font-weight: 700;
    font-size: 0.82rem;
}

.prod-stock--low {
    background: color-mix(in srgb, var(--orange-500) 15%, transparent);
    color: var(--orange-600);
}

.prod-stock--empty {
    background: color-mix(in srgb, var(--red-500) 15%, transparent);
    color: var(--red-600);
}

.prod-muted {
    color: var(--text-color-secondary);
}

.prod-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 1200px) {
    .prod-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 960px) {
    .prod-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .prod-filters__grid {
        grid-template-columns: 1fr;
    }
}
</style>
