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

const payments = ref([]);
const paymentMethods = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const paymentMethodId = ref(null);
const orderId = ref('');
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('created_at');
const sortDir = ref('desc');

const rowsPerPageOptions = [10, 15, 25, 50];
const sortOptions = [
    { label: 'Data', value: 'created_at' },
    { label: 'ID', value: 'id' },
    { label: 'Valor', value: 'amount' },
    { label: 'Encomenda', value: 'order_id' },
    { label: 'Método', value: 'payment_method_id' },
    { label: 'Cliente', value: 'customer_id' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        paymentMethodId.value != null ||
        !!orderId.value ||
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

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        payment_method_id: paymentMethodId.value ?? undefined,
        order_id: orderId.value || undefined,
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
        const response = await axios.get('/api/payments/create');
        paymentMethods.value = response.data.payment_methods ?? [];
    } catch (error) {
        console.error('Erro ao carregar métodos de pagamento:', error);
    }
};

const getData = async (page = 1, { initial = false } = {}) => {
    if (initial) {
        isInitialLoading.value = true;
    } else {
        isTableLoading.value = true;
    }

    try {
        const response = await axios.get('/api/payments', {
            params: buildParams(page)
        });

        payments.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os pagamentos.',
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
    paymentMethodId.value = null;
    orderId.value = '';
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
        const response = await axios.get('/api/payments/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há pagamentos para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            ['ID', 'Valor (MZN)', 'Método', 'Encomenda', 'Cliente', 'Criado em'],
            ...rows.map((row) => [
                row.id,
                row.amount ?? 0,
                row.payment_method ?? '',
                row.order_id ?? '',
                row.customer ?? '',
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 14 },
            { wch: 22 },
            { wch: 12 },
            { wch: 24 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Pagamentos');
        XLSX.writeFile(workbook, `pagamentos-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} pagamentos exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os pagamentos.',
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
    [searchQuery, paymentMethodId, orderId, createdFrom, createdTo, sortBy, sortDir],
    debouncedReload
);

onMounted(async () => {
    await Promise.all([getFilterOptions(), getData(1, { initial: true })]);
});
</script>

<template>
    <div v-if="isInitialLoading" class="pays-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar pagamentos...</p>
    </div>

    <div v-else class="pays-page">
        <div class="pays-card">
            <header class="pays-header">
                <div>
                    <p class="pays-eyebrow">Financeiro</p>
                    <h1>Pagamentos</h1>
                    <p class="pays-subtitle">{{ paginationSummary }}</p>
                </div>

                <div class="pays-header__actions">
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

            <section class="pays-filters">
                <div class="pays-filters__grid">
                    <div class="pays-field pays-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="ID, encomenda, valor, método ou cliente..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="pays-field">
                        <label>Método de pagamento</label>
                        <Select
                            v-model="paymentMethodId"
                            :options="paymentMethods"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Todos"
                            showClear
                            filter
                            class="w-full"
                        />
                    </div>

                    <div class="pays-field">
                        <label>ID encomenda</label>
                        <InputText
                            v-model="orderId"
                            placeholder="Ex: 1024"
                            class="w-full"
                        />
                    </div>

                    <div class="pays-field">
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

                    <div class="pays-field">
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

                    <div class="pays-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="pays-field">
                        <label>Direção</label>
                        <div class="pays-sort-dir">
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

                <div class="pays-filters__actions">
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
                :value="payments"
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
                    <div class="pays-empty">Nenhum pagamento encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="pays-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Valor" style="min-width: 10rem">
                    <template #body="{ data }">
                        <strong class="pays-amount">{{ formatAmount(data.amount) }}</strong>
                    </template>
                </Column>

                <Column header="Método" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.method?.name"
                            :value="data.method.name"
                            severity="info"
                        />
                        <span v-else class="pays-muted">—</span>
                    </template>
                </Column>

                <Column header="Encomenda" style="min-width: 8rem">
                    <template #body="{ data }">
                        <span class="pays-order">#{{ data.order_id }}</span>
                    </template>
                </Column>

                <Column header="Cliente" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.customer?.name || '—' }}
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 6rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="pays-actions">
                            <Button
                                v-tooltip.top="'Ver'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/admin/payments/${data.id}`)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
.pays-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.pays-page {
    --pays-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --pays-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --pays-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --pays-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--pays-border-soft);
}

.pays-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--pays-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--pays-shadow);
}

.pays-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.pays-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.pays-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.pays-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.pays-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.pays-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--pays-border-soft);
    border-radius: 0.85rem;
    background: var(--pays-muted-bg);
}

.pays-filters__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.pays-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.pays-field--wide {
    grid-column: span 2;
}

.pays-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.pays-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.pays-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.pays-id,
.pays-order {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.pays-amount {
    color: var(--primary-color);
}

.pays-muted {
    color: var(--text-color-secondary);
}

.pays-actions {
    display: flex;
    gap: 0.15rem;
}

@media (max-width: 960px) {
    .pays-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pays-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .pays-filters__grid {
        grid-template-columns: 1fr;
    }

    .pays-field--wide {
        grid-column: span 1;
    }
}
</style>
