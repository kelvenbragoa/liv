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

const orders = ref([]);
const orderStatuses = ref([]);
const tables = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const orderStatusId = ref(null);
const tableId = ref(null);
const quickSaleOnly = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('created_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50];
const quickSaleOptions = [
    { label: 'Venda rápida', value: 1 },
    { label: 'Com mesa', value: 0 }
];
const sortOptions = [
    { label: 'Data', value: 'created_at' },
    { label: 'ID', value: 'id' },
    { label: 'Valor', value: 'total' },
    { label: 'Mesa', value: 'table_id' },
    { label: 'Estado', value: 'order_status_id' },
    { label: 'Utilizador', value: 'user_id' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        orderStatusId.value != null ||
        tableId.value != null ||
        quickSaleOnly.value != null ||
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

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatAmount(value) {
    if (value == null || value === '') {
        return '—';
    }

    return `${Number(value).toLocaleString('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })} MZN`;
}

function formatDateParam(value) {
    if (!value) {
        return null;
    }

    return moment(value).format('YYYY-MM-DD');
}

function getStatusSeverity(statusId) {
    switch (Number(statusId)) {
        case 1:
            return 'warn';
        case 2:
            return 'info';
        case 3:
            return 'success';
        default:
            return 'secondary';
    }
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        order_status_id: orderStatusId.value ?? undefined,
        table_id: tableId.value ?? undefined,
        quick_sale: quickSaleOnly.value != null ? quickSaleOnly.value : undefined,
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
        const response = await axios.get('/api/orders/create');
        orderStatuses.value = response.data.order_statuses ?? [];
        tables.value = response.data.tables ?? [];
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
        const response = await axios.get('/api/orders', {
            params: buildParams(page)
        });

        orders.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar as encomendas.',
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
    orderStatusId.value = null;
    tableId.value = null;
    quickSaleOnly.value = null;
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
        const response = await axios.get('/api/orders/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há encomendas para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            ['ID', 'Total (MZN)', 'Valor entregue (MZN)', 'Troco (MZN)', 'Mesa', 'Estado', 'Utilizador', 'Criado em'],
            ...rows.map((row) => [
                row.id,
                row.total ?? 0,
                row.amount_tendered ?? '',
                row.change_amount ?? '',
                row.table ?? '',
                row.status ?? '',
                row.user ?? '',
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 14 },
            { wch: 16 },
            { wch: 12 },
            { wch: 18 },
            { wch: 14 },
            { wch: 22 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Encomendas');
        XLSX.writeFile(workbook, `encomendas-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} encomendas exportadas para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar as encomendas.',
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

watch(tableId, (value) => {
    if (value != null) {
        quickSaleOnly.value = null;
    }
});

watch(quickSaleOnly, (value) => {
    if (value === 1) {
        tableId.value = null;
    }
});

watch(
    [searchQuery, orderStatusId, tableId, quickSaleOnly, createdFrom, createdTo, sortBy, sortDir],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="ord-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar encomendas...</p>
    </div>

    <div v-else class="ord-page">
        <div class="ord-card">
            <header class="ord-header">
                <div>
                    <p class="ord-eyebrow">Operações</p>
                    <h1>Encomendas</h1>
                    <p class="ord-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="ord-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                </div>
            </header>

            <section class="ord-filters">
                <div class="ord-filters__grid">
                    <div class="ord-field ord-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="ID, valor, mesa ou utilizador..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="ord-field">
                        <label>Estado</label>
                        <Select
                            v-model="orderStatusId"
                            :options="orderStatuses"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="ord-field">
                        <label>Mesa</label>
                        <Select
                            v-model="tableId"
                            :options="tables"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todas"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="ord-field">
                        <label>Tipo</label>
                        <Select
                            v-model="quickSaleOnly"
                            :options="quickSaleOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todos"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="ord-field">
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

                    <div class="ord-field">
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

                    <div class="ord-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="ord-field">
                        <label>Direção</label>
                        <div class="ord-sort-dir">
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

                <div class="ord-filters__actions">
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
                :value="orders"
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
                    <div class="ord-empty">Nenhuma encomenda encontrada.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="ord-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Valor" style="min-width: 10rem">
                    <template #body="{ data }">
                        <strong class="ord-amount">{{ formatAmount(data.total) }}</strong>
                    </template>
                </Column>

                <Column header="Mesa" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.table?.name"
                            :value="data.table.name"
                            severity="secondary"
                        />
                        <Tag v-else value="Venda rápida" severity="info" />
                    </template>
                </Column>

                <Column header="Estado" style="min-width: 10rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.status?.name"
                            :value="data.status.name"
                            :severity="getStatusSeverity(data.order_status_id)"
                        />
                        <span v-else class="ord-muted">—</span>
                    </template>
                </Column>

                <Column header="Efetuada por" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.user?.name || '—' }}
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 6rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="ord-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/admin/orders/${data.id}`)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.ord-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.ord-page {
    --ord-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --ord-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --ord-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --ord-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--ord-border-soft);
}

.ord-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--ord-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--ord-shadow);
}

.ord-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.ord-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.ord-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.ord-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.ord-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.ord-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--ord-border-soft);
    border-radius: 0.85rem;
    background: var(--ord-muted-bg);
}

.ord-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.ord-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.ord-field--wide {
    grid-column: span 2;
}

.ord-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.ord-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.ord-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.ord-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.ord-amount {
    color: var(--primary-color);
}

.ord-muted {
    color: var(--text-color-secondary);
}

.ord-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 1200px) {
    .ord-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .ord-field--wide {
        grid-column: span 3;
    }
}

@media (max-width: 960px) {
    .ord-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ord-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .ord-filters__grid {
        grid-template-columns: 1fr;
    }

    .ord-field--wide {
        grid-column: span 1;
    }
}
</style>
