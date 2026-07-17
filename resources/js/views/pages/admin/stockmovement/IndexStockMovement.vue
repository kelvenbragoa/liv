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

const movements = ref([]);
const stockCenters = ref([]);
const users = ref([]);
const products = ref([]);
const reasons = ref([]);
const directions = ref([]);
const summary = ref({ total_in: 0, total_out: 0, net: 0 });

const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const productId = ref(null);
const stockCenterId = ref(null);
const userId = ref(null);
const direction = ref(null);
const reason = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('created_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50, 100];
const sortOptions = [
    { label: 'Data', value: 'created_at' },
    { label: 'ID', value: 'id' },
    { label: 'Produto', value: 'product_id' },
    { label: 'Centro stock', value: 'stock_center_id' },
    { label: 'Quantidade', value: 'quantity' },
    { label: 'Antes', value: 'quantity_before' },
    { label: 'Depois', value: 'quantity_after' },
    { label: 'Direção', value: 'direction' },
    { label: 'Motivo', value: 'reason' },
    { label: 'Utilizador', value: 'user_id' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        productId.value != null ||
        stockCenterId.value != null ||
        userId.value != null ||
        direction.value != null ||
        reason.value != null ||
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

function formatDateParam(value) {
    if (!value) {
        return null;
    }

    return moment(value).format('YYYY-MM-DD');
}

function formatSignedQty(value) {
    const num = Number(value ?? 0);
    if (num > 0) {
        return `+${num}`;
    }

    return String(num);
}

function qtySeverity(value) {
    const num = Number(value ?? 0);
    if (num > 0) {
        return 'success';
    }
    if (num < 0) {
        return 'danger';
    }

    return 'secondary';
}

function directionSeverity(dir) {
    return dir === 'in' ? 'success' : 'danger';
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        product_id: productId.value ?? undefined,
        stock_center_id: stockCenterId.value ?? undefined,
        user_id: userId.value ?? undefined,
        direction: direction.value ?? undefined,
        reason: reason.value ?? undefined,
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
        const response = await axios.get('/api/stockmovements/create');
        stockCenters.value = response.data.stockcenters ?? [];
        users.value = response.data.users ?? [];
        products.value = response.data.products ?? [];
        reasons.value = response.data.reasons ?? [];
        directions.value = response.data.directions ?? [];
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
        const response = await axios.get('/api/stockmovements', {
            params: buildParams(page)
        });

        movements.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
        summary.value = response.data.summary ?? { total_in: 0, total_out: 0, net: 0 };
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os movimentos de stock.',
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
    productId.value = null;
    stockCenterId.value = null;
    userId.value = null;
    direction.value = null;
    reason.value = null;
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
        const response = await axios.get('/api/stockmovements/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há movimentos para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Data',
                'Produto',
                'Centro stock',
                'Direção',
                'Motivo',
                'Qtd',
                'Qtd assinada',
                'Antes',
                'Depois',
                'Referência',
                'Utilizador',
                'Notas'
            ],
            ...rows.map((row) => [
                row.id,
                row.created_at ?? '',
                row.product ?? '',
                row.stock_center ?? '',
                row.direction ?? '',
                row.reason ?? '',
                row.quantity ?? 0,
                row.signed_quantity ?? 0,
                row.quantity_before ?? 0,
                row.quantity_after ?? 0,
                row.reference ?? '',
                row.user ?? '',
                row.notes ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 16 },
            { wch: 24 },
            { wch: 16 },
            { wch: 10 },
            { wch: 20 },
            { wch: 8 },
            { wch: 10 },
            { wch: 8 },
            { wch: 8 },
            { wch: 16 },
            { wch: 16 },
            { wch: 24 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Movimentos');
        XLSX.writeFile(workbook, `movimentos-stock-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} movimentos exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os movimentos.',
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
        productId,
        stockCenterId,
        userId,
        direction,
        reason,
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
    <div v-if="isInitialLoading" class="stmv-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar movimentos de stock...</p>
    </div>

    <div v-else class="stmv-page">
        <div class="stmv-card">
            <header class="stmv-header">
                <div>
                    <p class="stmv-eyebrow">Stock</p>
                    <h1>Movimentos de stock</h1>
                    <p class="stmv-subtitle">{{ paginationSummary }}</p>
                    <p class="stmv-kpi">
                        <span class="stmv-kpi--pos">+{{ summary.total_in.toLocaleString('pt-PT') }} entradas</span>
                        <span class="stmv-kpi__sep">·</span>
                        <span class="stmv-kpi--neg">−{{ summary.total_out.toLocaleString('pt-PT') }} saídas</span>
                        <span class="stmv-kpi__sep">·</span>
                        <span :class="summary.net >= 0 ? 'stmv-kpi--pos' : 'stmv-kpi--neg'">
                            Líquido {{ formatSignedQty(summary.net) }}
                        </span>
                    </p>
                </div>

                <div class="stmv-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                </div>
            </header>

            <section class="stmv-filters">
                <div class="stmv-filters__grid">
                    <div class="stmv-field stmv-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Produto, centro, utilizador, ID, notas..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="stmv-field stmv-field--wide">
                        <label>Produto</label>
                        <Select
                            v-model="productId"
                            :options="products"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos os produtos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Centro de stock</label>
                        <Select
                            v-model="stockCenterId"
                            :options="stockCenters"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Direção</label>
                        <Select
                            v-model="direction"
                            :options="directions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todas"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Motivo</label>
                        <Select
                            v-model="reason"
                            :options="reasons"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
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

                    <div class="stmv-field">
                        <label>Desde</label>
                        <DatePicker
                            v-model="createdFrom"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Início"
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Até</label>
                        <DatePicker
                            v-model="createdTo"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Fim"
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="stmv-field">
                        <label>Ordem</label>
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

                <div class="stmv-filters__actions">
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
                :value="movements"
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
                    <div class="stmv-empty">Nenhum movimento encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="stmv-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Data" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Produto" style="min-width: 14rem">
                    <template #body="{ data }">
                        <strong>{{ data.product?.name || '—' }}</strong>
                    </template>
                </Column>

                <Column header="Centro" style="min-width: 10rem">
                    <template #body="{ data }">
                        <Tag v-if="data.stock_center?.name" :value="data.stock_center.name" severity="info" />
                        <span v-else class="stmv-muted">—</span>
                    </template>
                </Column>

                <Column header="Direção" style="min-width: 7rem">
                    <template #body="{ data }">
                        <Tag
                            :value="data.direction_label"
                            :severity="directionSeverity(data.direction)"
                        />
                    </template>
                </Column>

                <Column header="Motivo" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ data.reason_label || '—' }}
                    </template>
                </Column>

                <Column header="Qtd" style="min-width: 6rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatSignedQty(data.signed_quantity)"
                            :severity="qtySeverity(data.signed_quantity)"
                        />
                    </template>
                </Column>

                <Column header="Antes → Depois" style="min-width: 9rem">
                    <template #body="{ data }">
                        <code class="stmv-stock-flow">
                            {{ data.quantity_before }} → {{ data.quantity_after }}
                        </code>
                    </template>
                </Column>

                <Column header="Referência" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="stmv-muted">{{ data.reference_label || '—' }}</span>
                    </template>
                </Column>

                <Column header="Utilizador" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.user?.name || '—' }}
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.stmv-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.stmv-page {
    --stmv-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --stmv-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --stmv-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --stmv-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--stmv-border-soft);
}

.stmv-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--stmv-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--stmv-shadow);
}

.stmv-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.stmv-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.stmv-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.stmv-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.stmv-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.stmv-kpi {
    margin: 0.35rem 0 0;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
    font-size: 0.82rem;
    color: var(--text-color-secondary);
}

.stmv-kpi__sep {
    opacity: 0.5;
}

.stmv-kpi--pos {
    color: var(--green-500);
    font-weight: 600;
}

.stmv-kpi--neg {
    color: var(--red-500);
    font-weight: 600;
}

.stmv-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--stmv-border-soft);
    border-radius: 0.85rem;
    background: var(--stmv-muted-bg);
}

.stmv-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.stmv-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.stmv-field--wide {
    grid-column: span 2;
}

.stmv-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.stmv-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.stmv-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.stmv-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.stmv-stock-flow {
    font-size: 0.85rem;
    padding: 0.15rem 0.4rem;
    border-radius: 0.35rem;
    background: var(--stmv-muted-bg);
}

.stmv-muted {
    color: var(--text-color-secondary);
}

@media (max-width: 1200px) {
    .stmv-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .stmv-field--wide {
        grid-column: span 3;
    }
}

@media (max-width: 960px) {
    .stmv-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stmv-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .stmv-filters__grid {
        grid-template-columns: 1fr;
    }

    .stmv-field--wide {
        grid-column: span 1;
    }
}
</style>
