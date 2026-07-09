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

const inventories = ref([]);
const stockCenters = ref([]);
const users = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const stockCenterId = ref(null);
const userId = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('created_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Data criação', value: 'created_at' },
    { label: 'ID', value: 'id' },
    { label: 'Referência', value: 'ref' },
    { label: 'Centro stock', value: 'stock_center_id' },
    { label: 'Utilizador', value: 'user_id' },
    { label: 'N.º produtos', value: 'products_number' },
    { label: 'N.º itens', value: 'items_count' },
    { label: 'Qtd. total', value: 'total_quantity' },
    { label: 'Ajuste total', value: 'total_adjustment' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        stockCenterId.value != null ||
        userId.value != null ||
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

const pageProductsTotal = computed(() =>
    inventories.value.reduce((sum, item) => sum + Number(item.items_count ?? item.products_number ?? 0), 0)
);

const pageAdjustmentTotal = computed(() =>
    inventories.value.reduce((sum, item) => sum + Number(item.total_adjustment ?? 0), 0)
);

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatAdjustment(value) {
    const num = Number(value ?? 0);
    if (num > 0) {
        return `+${num}`;
    }

    return String(num);
}

function adjustmentSeverity(value) {
    const num = Number(value ?? 0);
    if (num > 0) {
        return 'success';
    }
    if (num < 0) {
        return 'danger';
    }

    return 'secondary';
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
        stock_center_id: stockCenterId.value ?? undefined,
        user_id: userId.value ?? undefined,
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
        const response = await axios.get('/api/inventories/create');
        stockCenters.value = response.data.stockcenters ?? [];
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
        const response = await axios.get('/api/inventories', {
            params: buildParams(page)
        });

        inventories.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os inventários.',
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
    stockCenterId.value = null;
    userId.value = null;
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
        const response = await axios.get('/api/inventories/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há inventários para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Referência',
                'Centro stock',
                'Utilizador',
                'N.º produtos',
                'N.º itens',
                'Qtd. total',
                'Ajuste total',
                'Criado em'
            ],
            ...rows.map((row) => [
                row.id,
                row.ref ?? '',
                row.stock_center ?? '',
                row.user ?? '',
                row.products_number ?? 0,
                row.items_count ?? 0,
                row.total_quantity ?? 0,
                row.total_adjustment ?? 0,
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 14 },
            { wch: 20 },
            { wch: 18 },
            { wch: 12 },
            { wch: 10 },
            { wch: 10 },
            { wch: 12 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Inventarios');
        XLSX.writeFile(workbook, `inventarios-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} inventários exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os inventários.',
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
    [searchQuery, stockCenterId, userId, createdFrom, createdTo, sortBy, sortDir],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="inv-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar inventários...</p>
    </div>

    <div v-else class="inv-page">
        <div class="inv-card">
            <header class="inv-header">
                <div>
                    <p class="inv-eyebrow">Inventário</p>
                    <h1>Inventários de stock</h1>
                    <p class="inv-subtitle">{{ paginationSummary }}</p>
                    <p v-if="inventories.length" class="inv-kpi">
                        <i class="pi pi-box" />
                        {{ pageProductsTotal.toLocaleString('pt-PT') }} produtos nesta página
                        <span class="inv-kpi__sep">·</span>
                        <span :class="pageAdjustmentTotal >= 0 ? 'inv-kpi--pos' : 'inv-kpi--neg'">
                            Ajuste {{ formatAdjustment(pageAdjustmentTotal) }} un.
                        </span>
                    </p>
                </div>

                <div class="inv-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/admin/inventories/create">
                        <Button label="Novo inventário" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="inv-filters">
                <div class="inv-filters__grid">
                    <div class="inv-field inv-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="ID, referência, centro, utilizador..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="inv-field">
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

                    <div class="inv-field">
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

                    <div class="inv-field">
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

                    <div class="inv-field">
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

                    <div class="inv-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="inv-field">
                        <label>Direção</label>
                        <div class="inv-sort-dir">
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

                <div class="inv-filters__actions">
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
                :value="inventories"
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
                    <div class="inv-empty">Nenhum inventário encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="inv-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Referência" style="min-width: 9rem">
                    <template #body="{ data }">
                        <code class="inv-ref">{{ data.ref || '—' }}</code>
                    </template>
                </Column>

                <Column header="Centro" style="min-width: 11rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.stockcenter?.name"
                            :value="data.stockcenter.name"
                            severity="info"
                        />
                        <span v-else class="inv-muted">—</span>
                    </template>
                </Column>

                <Column header="Utilizador" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ data.user?.name || '—' }}
                    </template>
                </Column>

                <Column header="Produtos" style="min-width: 7rem">
                    <template #body="{ data }">
                        <span class="inv-count">{{ data.items_count ?? data.products_number ?? 0 }}</span>
                    </template>
                </Column>

                <Column header="Ajuste" style="min-width: 7rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatAdjustment(data.total_adjustment)"
                            :severity="adjustmentSeverity(data.total_adjustment)"
                        />
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 6rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="inv-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/admin/inventories/${data.id}`)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.inv-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.inv-page {
    --inv-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --inv-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --inv-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --inv-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--inv-border-soft);
}

.inv-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--inv-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--inv-shadow);
}

.inv-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.inv-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.inv-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.inv-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.inv-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.inv-kpi {
    margin: 0.35rem 0 0;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
    font-size: 0.82rem;
    color: var(--text-color-secondary);
}

.inv-kpi__sep {
    opacity: 0.5;
}

.inv-kpi--pos {
    color: var(--green-500);
    font-weight: 600;
}

.inv-kpi--neg {
    color: var(--red-500);
    font-weight: 600;
}

.inv-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--inv-border-soft);
    border-radius: 0.85rem;
    background: var(--inv-muted-bg);
}

.inv-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.inv-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.inv-field--wide {
    grid-column: span 2;
}

.inv-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.inv-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.inv-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.inv-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.inv-ref {
    font-size: 0.85rem;
    padding: 0.1rem 0.35rem;
    border-radius: 0.35rem;
    background: var(--inv-muted-bg);
}

.inv-count {
    display: inline-flex;
    min-width: 1.75rem;
    justify-content: center;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--inv-muted-bg);
    font-weight: 700;
    font-size: 0.82rem;
}

.inv-muted {
    color: var(--text-color-secondary);
}

.inv-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 1200px) {
    .inv-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .inv-field--wide {
        grid-column: span 3;
    }
}

@media (max-width: 960px) {
    .inv-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .inv-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .inv-filters__grid {
        grid-template-columns: 1fr;
    }

    .inv-field--wide {
        grid-column: span 1;
    }
}
</style>
