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

const categories = ref([]);
const departments = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const departmentId = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('name');
const sortDir = ref('asc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Nome', value: 'name' },
    { label: 'ID', value: 'id' },
    { label: 'Departamento', value: 'department_id' },
    { label: 'Subcategorias', value: 'sub_categories_count' },
    { label: 'Produtos', value: 'products_count' },
    { label: 'Data de criação', value: 'created_at' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
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

const getDepartments = async () => {
    try {
        const response = await axios.get('/api/categories/create');
        departments.value = response.data.departments ?? [];
    } catch (error) {
        console.error('Erro ao carregar departamentos:', error);
    }
};

const getData = async (page = 1, { initial = false } = {}) => {
    if (initial) {
        isInitialLoading.value = true;
    } else {
        isTableLoading.value = true;
    }

    try {
        const response = await axios.get('/api/categories', {
            params: buildParams(page)
        });

        categories.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar as categorias.',
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
        .delete(`/api/categories/${dataIdBeingDeleted.value}`)
        .then(() => {
            closeConfirmation();
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Categoria removida com sucesso.',
                life: 3000
            });
            getData(currentPage.value);
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Não foi possível remover a categoria.',
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
        const response = await axios.get('/api/categories/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há categorias para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            ['ID', 'Nome', 'Departamento', 'Subcategorias', 'Produtos', 'Criado em'],
            ...rows.map((row) => [
                row.id,
                row.name,
                row.department ?? '',
                row.sub_categories_count ?? 0,
                row.products_count ?? 0,
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 28 },
            { wch: 22 },
            { wch: 14 },
            { wch: 12 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Categorias');
        XLSX.writeFile(workbook, `categorias-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} categorias exportadas para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar as categorias.',
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

watch([searchQuery, departmentId, createdFrom, createdTo, sortBy, sortDir], debouncedReload);

onMounted(async () => {
    await Promise.all([getDepartments(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="cat-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar categorias...</p>
    </div>

    <div v-else class="cat-page">
        <div class="cat-card">
            <header class="cat-header">
                <div>
                    <p class="cat-eyebrow">Catálogo</p>
                    <h1>Categorias</h1>
                    <p class="cat-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="cat-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/stock/categories/create">
                        <Button label="Nova categoria" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="cat-filters">
                <div class="cat-filters__grid">
                    <div class="cat-field">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Nome da categoria..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="cat-field">
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

                    <div class="cat-field">
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

                    <div class="cat-field">
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

                    <div class="cat-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="cat-field">
                        <label>Direção</label>
                        <div class="cat-sort-dir">
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

                <div class="cat-filters__actions">
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
                :value="categories"
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
                    <div class="cat-empty">Nenhuma categoria encontrada.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="cat-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Nome" style="min-width: 14rem">
                    <template #body="{ data }">
                        <strong>{{ data.name }}</strong>
                    </template>
                </Column>

                <Column header="Departamento" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.department?.name"
                            :value="data.department.name"
                            severity="secondary"
                        />
                        <span v-else class="cat-muted">—</span>
                    </template>
                </Column>

                <Column header="Subcategorias" style="min-width: 8rem">
                    <template #body="{ data }">
                        <span class="cat-count">{{ data.sub_categories_count ?? 0 }}</span>
                    </template>
                </Column>

                <Column header="Produtos" style="min-width: 8rem">
                    <template #body="{ data }">
                        <span class="cat-count">{{ data.products_count ?? 0 }}</span>
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 10rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="cat-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/stock/categories/${data.id}`)"
                            />
                            <Button
                                v-tooltip.top="'Editar'"
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="info"
                                @click="router.push(`/stock/categories/${data.id}/edit`)"
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
        <p>Tem a certeza que deseja eliminar esta categoria?</p>
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
.cat-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.cat-page {
    --cat-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --cat-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --cat-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --cat-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--cat-border-soft);
}

.cat-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--cat-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--cat-shadow);
}

.cat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.cat-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.cat-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.cat-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.cat-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.cat-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--cat-border-soft);
    border-radius: 0.85rem;
    background: var(--cat-muted-bg);
}

.cat-filters__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.cat-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.cat-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.cat-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.cat-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.cat-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.cat-count {
    display: inline-flex;
    min-width: 1.75rem;
    justify-content: center;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--cat-muted-bg);
    font-weight: 700;
    font-size: 0.82rem;
}

.cat-muted {
    color: var(--text-color-secondary);
}

.cat-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 960px) {
    .cat-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .cat-filters__grid {
        grid-template-columns: 1fr;
    }
}
</style>
