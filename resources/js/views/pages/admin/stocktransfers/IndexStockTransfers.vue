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

const transfers = ref([]);
const stockCenters = ref([]);
const statuses = ref([]);
const users = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const originId = ref(null);
const destinationId = ref(null);
const statusId = ref(null);
const userId = ref(null);
const transferFrom = ref(null);
const transferTo = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('created_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Data criação', value: 'created_at' },
    { label: 'Data transferência', value: 'transfer_date' },
    { label: 'ID', value: 'id' },
    { label: 'Referência', value: 'ref' },
    { label: 'Origem', value: 'stock_center_origin_id' },
    { label: 'Destino', value: 'stock_center_destination_id' },
    { label: 'Utilizador', value: 'user_id' },
    { label: 'N.º itens', value: 'items_count' },
    { label: 'Qtd. total', value: 'total_quantity' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        originId.value != null ||
        destinationId.value != null ||
        statusId.value != null ||
        userId.value != null ||
        transferFrom.value != null ||
        transferTo.value != null ||
        createdFrom.value != null ||
        createdTo.value != null ||
        sortBy.value !== 'created_at' ||
        sortDir.value !== 'desc'
);

const paginationSummary = computed(() => {
    if (!totalRecords.value) {
        return 'Nenhum registo';
    }

    const from = (currentPage.value - 1) * rowsPerPage.value + 1;
    const to = Math.min(currentPage.value * rowsPerPage.value, totalRecords.value);

    return `${from}-${to} de ${totalRecords.value}`;
});

const pageQuantityTotal = computed(() =>
    transfers.value.reduce((sum, item) => sum + Number(item.total_quantity ?? 0), 0)
);

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatDateOnly(value) {
    return value ? moment(value).format('DD-MM-YYYY') : '—';
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
        stock_center_origin_id: originId.value ?? undefined,
        stock_center_destination_id: destinationId.value ?? undefined,
        stock_center_transfer_status_id: statusId.value ?? undefined,
        user_id: userId.value ?? undefined,
        transfer_from: formatDateParam(transferFrom.value),
        transfer_to: formatDateParam(transferTo.value),
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
        const response = await axios.get('/api/stocktransfers/create');
        stockCenters.value = response.data.stockcenters ?? [];
        statuses.value = response.data.statuses ?? [];
        users.value = response.data.users ?? [];
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
        const response = await axios.get('/api/stocktransfers', {
            params: buildParams(page)
        });

        transfers.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar as transferências.',
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
    originId.value = null;
    destinationId.value = null;
    statusId.value = null;
    userId.value = null;
    transferFrom.value = null;
    transferTo.value = null;
    createdFrom.value = null;
    createdTo.value = null;
    sortBy.value = 'created_at';
    sortDir.value = 'desc';
    currentPage.value = 1;
    getData(1);
};

const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
};

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

const exportToExcel = async () => {
    isExporting.value = true;

    try {
        const response = await axios.get('/api/stocktransfers/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há transferências para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Referência',
                'Origem',
                'Destino',
                'Utilizador',
                'Estado',
                'Itens',
                'Qtd. total',
                'Data transferência',
                'Criado em'
            ],
            ...rows.map((row) => [
                row.id,
                row.ref ?? '',
                row.origin ?? '',
                row.destination ?? '',
                row.user ?? '',
                row.status ?? '',
                row.items_count ?? 0,
                row.total_quantity ?? 0,
                row.transfer_date ?? '',
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 16 },
            { wch: 20 },
            { wch: 20 },
            { wch: 18 },
            { wch: 12 },
            { wch: 8 },
            { wch: 10 },
            { wch: 16 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Transferencias');
        XLSX.writeFile(workbook, `transferencias-stock-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} transferências exportadas para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar as transferências.',
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

watch(
    [
        searchQuery,
        originId,
        destinationId,
        statusId,
        userId,
        transferFrom,
        transferTo,
        createdFrom,
        createdTo,
        sortBy,
        sortDir
    ],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="stxf-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar transferências...</p>
    </div>

    <div v-else class="stxf-page">
        <div class="stxf-card">
            <header class="stxf-header">
                <div>
                    <p class="stxf-eyebrow">Inventário</p>
                    <h1>Transferências de stock</h1>
                    <p class="stxf-subtitle">{{ paginationSummary }}</p>
                    <p v-if="transfers.length" class="stxf-kpi">
                        <i class="pi pi-arrow-right-arrow-left" />
                        {{ pageQuantityTotal.toLocaleString('pt-PT') }} un. movimentadas nesta página
                    </p>
                </div>

                <div class="stxf-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/admin/stocktransfers/create">
                        <Button label="Nova transferência" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="stxf-filters">
                <div class="stxf-filters__grid">
                    <div class="stxf-field stxf-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="ID, referência, centro ou utilizador..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="stxf-field">
                        <label>Origem</label>
                        <Select
                            v-model="originId"
                            :options="stockCenters"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Destino</label>
                        <Select
                            v-model="destinationId"
                            :options="stockCenters"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Estado</label>
                        <Select
                            v-model="statusId"
                            :options="statuses"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Utilizador</label>
                        <Select
                            v-model="userId"
                            :options="users"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Transferência desde</label>
                        <DatePicker
                            v-model="transferFrom"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Início"
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Transferência até</label>
                        <DatePicker
                            v-model="transferTo"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Fim"
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
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

                    <div class="stxf-field">
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

                    <div class="stxf-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="stxf-field">
                        <label>Direção</label>
                        <div class="stxf-sort-dir">
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

                <div class="stxf-filters__actions">
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
                :value="transfers"
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
                    <div class="stxf-empty">Nenhuma transferência encontrada.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="stxf-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Referência" style="min-width: 10rem">
                    <template #body="{ data }">
                        <code class="stxf-ref">{{ data.ref || '—' }}</code>
                    </template>
                </Column>

                <Column header="Origem" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.stockcenterorigin?.name"
                            :value="data.stockcenterorigin.name"
                            severity="warn"
                        />
                        <span v-else class="stxf-muted">—</span>
                    </template>
                </Column>

                <Column header="Destino" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.stockcenterdestination?.name"
                            :value="data.stockcenterdestination.name"
                            severity="success"
                        />
                        <span v-else class="stxf-muted">—</span>
                    </template>
                </Column>

                <Column header="Utilizador" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.user?.name || '—' }}
                    </template>
                </Column>

                <Column header="Itens" style="min-width: 6rem">
                    <template #body="{ data }">
                        <span class="stxf-count">{{ data.items_count ?? 0 }}</span>
                    </template>
                </Column>

                <Column header="Qtd." style="min-width: 6rem">
                    <template #body="{ data }">
                        <strong>{{ data.total_quantity ?? 0 }}</strong>
                    </template>
                </Column>

                <Column header="Data transf." style="min-width: 9rem">
                    <template #body="{ data }">
                        {{ formatDateOnly(data.transfer_date) }}
                    </template>
                </Column>

                <Column header="Estado" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.status?.name"
                            :value="data.status.name"
                            severity="info"
                        />
                        <span v-else class="stxf-muted">—</span>
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 6rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="stxf-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/admin/stocktransfers/${data.id}`)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.stxf-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.stxf-page {
    --stxf-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --stxf-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --stxf-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --stxf-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--stxf-border-soft);
}

.stxf-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--stxf-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--stxf-shadow);
}

.stxf-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.stxf-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.stxf-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.stxf-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.stxf-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.stxf-kpi {
    margin: 0.35rem 0 0;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: var(--text-color-secondary);
}

.stxf-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--stxf-border-soft);
    border-radius: 0.85rem;
    background: var(--stxf-muted-bg);
}

.stxf-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.stxf-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.stxf-field--wide {
    grid-column: span 2;
}

.stxf-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.stxf-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.stxf-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.stxf-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.stxf-ref {
    font-size: 0.85rem;
    padding: 0.1rem 0.35rem;
    border-radius: 0.35rem;
    background: var(--stxf-muted-bg);
}

.stxf-count {
    display: inline-flex;
    min-width: 1.75rem;
    justify-content: center;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--stxf-muted-bg);
    font-weight: 700;
    font-size: 0.82rem;
}

.stxf-muted {
    color: var(--text-color-secondary);
}

.stxf-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 1200px) {
    .stxf-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .stxf-field--wide {
        grid-column: span 3;
    }
}

@media (max-width: 960px) {
    .stxf-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stxf-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .stxf-filters__grid {
        grid-template-columns: 1fr;
    }

    .stxf-field--wide {
        grid-column: span 1;
    }
}
</style>
