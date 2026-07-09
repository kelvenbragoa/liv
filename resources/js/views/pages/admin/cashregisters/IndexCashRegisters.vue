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

const cashRegisters = ref([]);
const statuses = ref([]);
const users = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const statusId = ref(null);
const userId = ref(null);
const openedFrom = ref(null);
const openedTo = ref(null);
const closedFrom = ref(null);
const closedTo = ref(null);
const sortBy = ref('opened_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Abertura', value: 'opened_at' },
    { label: 'ID', value: 'id' },
    { label: 'Valor vendas', value: 'order_itens_total' },
    { label: 'Saldo fecho', value: 'closing_balance' },
    { label: 'Saldo abertura', value: 'opening_balance' },
    { label: 'Estado', value: 'cash_register_status_id' },
    { label: 'Utilizador', value: 'user_id' },
    { label: 'Fecho', value: 'closed_at' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        statusId.value != null ||
        userId.value != null ||
        openedFrom.value != null ||
        openedTo.value != null ||
        closedFrom.value != null ||
        closedTo.value != null ||
        sortBy.value !== 'opened_at' ||
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
    })} MT`;
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
            return 'success';
        case 2:
            return 'secondary';
        default:
            return 'info';
    }
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        cash_register_status_id: statusId.value ?? undefined,
        user_id: userId.value ?? undefined,
        opened_from: formatDateParam(openedFrom.value),
        opened_to: formatDateParam(openedTo.value),
        closed_from: formatDateParam(closedFrom.value),
        closed_to: formatDateParam(closedTo.value),
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
        const response = await axios.get('/api/cashregister/create');
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
        const response = await axios.get('/api/cashregister', {
            params: buildParams(page)
        });

        cashRegisters.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os registos de caixa.',
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
    statusId.value = null;
    userId.value = null;
    openedFrom.value = null;
    openedTo.value = null;
    closedFrom.value = null;
    closedTo.value = null;
    sortBy.value = 'opened_at';
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
        const response = await axios.get('/api/cashregister/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há registos de caixa para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Utilizador',
                'Valor vendas (MT)',
                'Saldo abertura (MT)',
                'Saldo fecho (MT)',
                'Estado',
                'Abertura',
                'Fecho'
            ],
            ...rows.map((row) => [
                row.id,
                row.user ?? '',
                row.order_itens_total ?? 0,
                row.opening_balance ?? 0,
                row.closing_balance ?? 0,
                row.status ?? '',
                row.opened_at ?? '',
                row.closed_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 22 },
            { wch: 16 },
            { wch: 16 },
            { wch: 16 },
            { wch: 12 },
            { wch: 18 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Caixas');
        XLSX.writeFile(workbook, `caixas-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} registos exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os registos de caixa.',
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
    [searchQuery, statusId, userId, openedFrom, openedTo, closedFrom, closedTo, sortBy, sortDir],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="cr-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar registos de caixa...</p>
    </div>

    <div v-else class="cr-page">
        <div class="cr-card">
            <header class="cr-header">
                <div>
                    <p class="cr-eyebrow">Financeiro</p>
                    <h1>Registos de caixa</h1>
                    <p class="cr-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="cr-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/admin/dailydashboard">
                        <Button label="Painel diário" icon="pi pi-chart-line" severity="secondary" outlined />
                    </router-link>
                </div>
            </header>

            <section class="cr-filters">
                <div class="cr-filters__grid">
                    <div class="cr-field cr-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="ID ou utilizador..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="cr-field">
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

                    <div class="cr-field">
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

                    <div class="cr-field">
                        <label>Aberto desde</label>
                        <DatePicker
                            v-model="openedFrom"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Início"
                            class="w-full"
                        />
                    </div>

                    <div class="cr-field">
                        <label>Aberto até</label>
                        <DatePicker
                            v-model="openedTo"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Fim"
                            class="w-full"
                        />
                    </div>

                    <div class="cr-field">
                        <label>Fechado desde</label>
                        <DatePicker
                            v-model="closedFrom"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Início"
                            class="w-full"
                        />
                    </div>

                    <div class="cr-field">
                        <label>Fechado até</label>
                        <DatePicker
                            v-model="closedTo"
                            dateFormat="dd/mm/yy"
                            showIcon
                            showButtonBar
                            placeholder="Fim"
                            class="w-full"
                        />
                    </div>

                    <div class="cr-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="cr-field">
                        <label>Direção</label>
                        <div class="cr-sort-dir">
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

                <div class="cr-filters__actions">
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
                :value="cashRegisters"
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
                    <div class="cr-empty">Nenhum registo de caixa encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="cr-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Utilizador" style="min-width: 12rem">
                    <template #body="{ data }">
                        <strong>{{ data.user?.name || '—' }}</strong>
                    </template>
                </Column>

                <Column header="Valor vendas" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatAmount(data.order_itens_total) }}
                    </template>
                </Column>

                <Column header="Saldo fecho" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatAmount(data.closing_balance) }}
                    </template>
                </Column>

                <Column header="Estado" style="min-width: 9rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.status?.name"
                            :value="data.status.name"
                            :severity="getStatusSeverity(data.cash_register_status_id)"
                        />
                        <span v-else class="cr-muted">—</span>
                    </template>
                </Column>

                <Column header="Abertura" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.opened_at) }}
                    </template>
                </Column>

                <Column header="Fechado às" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.closed_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 6rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="cr-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/admin/cashregisters/${data.id}`)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.cr-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.cr-page {
    --cr-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --cr-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --cr-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --cr-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--cr-border-soft);
}

.cr-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--cr-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--cr-shadow);
}

.cr-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.cr-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.cr-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.cr-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.cr-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.cr-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--cr-border-soft);
    border-radius: 0.85rem;
    background: var(--cr-muted-bg);
}

.cr-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.cr-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.cr-field--wide {
    grid-column: span 2;
}

.cr-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.cr-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.cr-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.cr-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.cr-muted {
    color: var(--text-color-secondary);
}

.cr-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 1200px) {
    .cr-filters__grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .cr-field--wide {
        grid-column: span 3;
    }
}

@media (max-width: 960px) {
    .cr-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cr-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .cr-filters__grid {
        grid-template-columns: 1fr;
    }

    .cr-field--wide {
        grid-column: span 1;
    }
}
</style>
