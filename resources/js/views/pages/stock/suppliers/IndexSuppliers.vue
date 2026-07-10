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

const suppliers = ref([]);
const countries = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const country = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('name');
const sortDir = ref('asc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Nome', value: 'name' },
    { label: 'ID', value: 'id' },
    { label: 'Email', value: 'email' },
    { label: 'Telefone', value: 'mobile' },
    { label: 'Cidade', value: 'city' },
    { label: 'País', value: 'country' },
    { label: 'NUIT', value: 'nuit' },
    { label: 'Data de criação', value: 'created_at' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        country.value != null ||
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

function displayValue(value) {
    return value || '—';
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        country: country.value ?? undefined,
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
        const response = await axios.get('/api/suppliers/create');
        countries.value = (response.data.countries ?? []).map((item) => ({
            label: item,
            value: item
        }));
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
        const response = await axios.get('/api/suppliers', {
            params: buildParams(page)
        });

        suppliers.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os fornecedores.',
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
    country.value = null;
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
        .delete(`/api/suppliers/${dataIdBeingDeleted.value}`)
        .then(() => {
            closeConfirmation();
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Fornecedor removido com sucesso.',
                life: 3000
            });
            getData(currentPage.value);
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Não foi possível remover o fornecedor.',
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
        const response = await axios.get('/api/suppliers/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há fornecedores para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Nome',
                'Endereço',
                'Cidade',
                'País',
                'Email',
                'Telefone',
                'NUIT',
                'Website',
                'Criado em'
            ],
            ...rows.map((row) => [
                row.id,
                row.name ?? '',
                row.address ?? '',
                row.city ?? '',
                row.country ?? '',
                row.email ?? '',
                row.mobile ?? '',
                row.nuit ?? '',
                row.website ?? '',
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 28 },
            { wch: 30 },
            { wch: 16 },
            { wch: 14 },
            { wch: 26 },
            { wch: 16 },
            { wch: 16 },
            { wch: 22 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Fornecedores');
        XLSX.writeFile(workbook, `fornecedores-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} fornecedores exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os fornecedores.',
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

watch([searchQuery, country, createdFrom, createdTo, sortBy, sortDir], debouncedReload);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="sup-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar fornecedores...</p>
    </div>

    <div v-else class="sup-page">
        <div class="sup-card">
            <header class="sup-header">
                <div>
                    <p class="sup-eyebrow">Gestão</p>
                    <h1>Fornecedores</h1>
                    <p class="sup-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="sup-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/stock/suppliers/create">
                        <Button label="Novo fornecedor" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="sup-filters">
                <div class="sup-filters__grid">
                    <div class="sup-field sup-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Nome, email, telefone, NUIT, cidade, país..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="sup-field">
                        <label>País</label>
                        <Select
                            v-model="country"
                            :options="countries"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="sup-field">
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

                    <div class="sup-field">
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

                    <div class="sup-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="sup-field">
                        <label>Direção</label>
                        <div class="sup-sort-dir">
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

                <div class="sup-filters__actions">
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
                :value="suppliers"
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
                    <div class="sup-empty">Nenhum fornecedor encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="sup-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Nome" style="min-width: 14rem">
                    <template #body="{ data }">
                        <strong>{{ data.name }}</strong>
                    </template>
                </Column>

                <Column header="Cidade" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ displayValue(data.city) }}
                    </template>
                </Column>

                <Column header="País" style="min-width: 9rem">
                    <template #body="{ data }">
                        <Tag v-if="data.country" :value="data.country" severity="secondary" />
                        <span v-else class="sup-muted">—</span>
                    </template>
                </Column>

                <Column header="Email" style="min-width: 14rem">
                    <template #body="{ data }">
                        <a v-if="data.email" :href="`mailto:${data.email}`" class="sup-link">{{ data.email }}</a>
                        <span v-else class="sup-muted">—</span>
                    </template>
                </Column>

                <Column header="Telefone" style="min-width: 11rem">
                    <template #body="{ data }">
                        <a v-if="data.mobile" :href="`tel:${data.mobile}`" class="sup-link">{{ data.mobile }}</a>
                        <span v-else class="sup-muted">—</span>
                    </template>
                </Column>

                <Column header="NUIT" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ displayValue(data.nuit) }}
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 10rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="sup-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/stock/suppliers/${data.id}`)"
                            />
                            <Button
                                v-tooltip.top="'Editar'"
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="info"
                                @click="router.push(`/stock/suppliers/${data.id}/edit`)"
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
        <p>Tem a certeza que deseja eliminar este fornecedor?</p>
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
.sup-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.sup-page {
    --sup-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --sup-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --sup-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --sup-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--sup-border-soft);
}

.sup-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--sup-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--sup-shadow);
}

.sup-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.sup-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.sup-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.sup-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.sup-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.sup-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--sup-border-soft);
    border-radius: 0.85rem;
    background: var(--sup-muted-bg);
}

.sup-filters__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.sup-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.sup-field--wide {
    grid-column: span 2;
}

.sup-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.sup-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.sup-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.sup-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.sup-link {
    color: var(--primary-color);
    text-decoration: none;
}

.sup-link:hover {
    text-decoration: underline;
}

.sup-muted {
    color: var(--text-color-secondary);
}

.sup-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 960px) {
    .sup-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sup-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .sup-filters__grid {
        grid-template-columns: 1fr;
    }

    .sup-field--wide {
        grid-column: span 1;
    }
}
</style>
