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
const isReconcileLoading = ref(false);

const stockCenters = ref([]);
const totalRecords = ref(0);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const isPrincipalStock = ref(null);
const createdFrom = ref(null);
const createdTo = ref(null);
const sortBy = ref('name');
const sortDir = ref('asc');

const showReconcileDialog = ref(false);
const reconcileCenter = ref(null);
const reconcileResult = ref(null);
const reconcileOnlyDiff = ref(true);

const rowsPerPageOptions = [10, 15, 25, 50];
const principalOptions = [
    { label: 'Sim', value: 1 },
    { label: 'Não', value: 0 }
];
const sortOptions = [
    { label: 'Nome', value: 'name' },
    { label: 'ID', value: 'id' },
    { label: 'Código', value: 'code' },
    { label: 'Localização', value: 'location' },
    { label: 'Capacidade', value: 'maximum_capacity' },
    { label: 'Stock principal', value: 'is_principal_stock' },
    { label: 'N.º produtos', value: 'products_count' },
    { label: 'Qtd. stock total', value: 'total_stock_quantity' },
    { label: 'Data de criação', value: 'created_at' }
];

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        isPrincipalStock.value != null ||
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

const listSummary = computed(() => {
    const principal = stockCenters.value.filter((item) => Number(item.is_principal_stock) === 1).length;
    const totalStock = stockCenters.value.reduce(
        (sum, item) => sum + Number(item.total_stock_quantity ?? 0),
        0
    );

    return { principal, totalStock };
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

function formatBoolean(value) {
    return Number(value) === 1 ? 'Sim' : 'Não';
}

function getPrincipalSeverity(value) {
    return Number(value) === 1 ? 'success' : 'secondary';
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        is_principal_stock: isPrincipalStock.value ?? undefined,
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

const getData = async (page = 1, { initial = false } = {}) => {
    if (initial) {
        isInitialLoading.value = true;
    } else {
        isTableLoading.value = true;
    }

    try {
        const response = await axios.get('/api/centerstocks', {
            params: buildParams(page)
        });

        stockCenters.value = response.data.data ?? [];
        totalRecords.value = response.data.total ?? 0;
        currentPage.value = response.data.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os centros de stock.',
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
    isPrincipalStock.value = null;
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

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

const exportToExcel = async () => {
    isExporting.value = true;

    try {
        const response = await axios.get('/api/centerstocks/export', {
            params: buildExportParams()
        });

        const rows = response.data.data ?? [];

        if (!rows.length) {
            toast.add({
                severity: 'warn',
                summary: 'Sem dados',
                detail: 'Não há centros de stock para exportar com os filtros actuais.',
                life: 3000
            });
            return;
        }

        const worksheetData = [
            [
                'ID',
                'Nome',
                'Código',
                'Localização',
                'Capacidade máxima',
                'Stock principal',
                'Produtos',
                'Qtd. stock total',
                'Criado em'
            ],
            ...rows.map((row) => [
                row.id,
                row.name,
                row.code ?? '',
                row.location ?? '',
                row.maximum_capacity ?? '',
                row.is_principal_stock ?? '',
                row.products_count ?? 0,
                row.total_stock_quantity ?? 0,
                row.created_at ?? ''
            ])
        ];

        const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
        worksheet['!cols'] = [
            { wch: 8 },
            { wch: 24 },
            { wch: 14 },
            { wch: 22 },
            { wch: 16 },
            { wch: 14 },
            { wch: 12 },
            { wch: 14 },
            { wch: 18 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'CentrosStock');
        XLSX.writeFile(workbook, `centros-stock-${moment().format('YYYY-MM-DD_HHmm')}.xlsx`);

        toast.add({
            severity: 'success',
            summary: 'Exportação concluída',
            detail: `${rows.length} centros exportados para Excel.`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível exportar os centros de stock.',
            life: 3000
        });
    } finally {
        isExporting.value = false;
    }
};

const loadReconcile = async () => {
    if (!reconcileCenter.value) {
        return;
    }

    isReconcileLoading.value = true;

    try {
        const response = await axios.get('/api/centerstock/reconcile', {
            params: {
                center_id: reconcileCenter.value.id,
                only_diff: reconcileOnlyDiff.value ? 1 : 0
            }
        });

        reconcileResult.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível reconciliar o stock.',
            life: 3000
        });
    } finally {
        isReconcileLoading.value = false;
    }
};

const openReconcile = async (center) => {
    reconcileCenter.value = center;
    reconcileResult.value = null;
    showReconcileDialog.value = true;
    await loadReconcile();
};

const closeReconcile = () => {
    showReconcileDialog.value = false;
    reconcileCenter.value = null;
    reconcileResult.value = null;
};

const openStockReport = (centerId) => {
    window.open(`/api/centerstock/report/${centerId}`, '_blank');
};

const debouncedReload = debounce(() => {
    currentPage.value = 1;
    getData(1);
}, 350);

watch([searchQuery, isPrincipalStock, createdFrom, createdTo, sortBy, sortDir], debouncedReload);

watch(reconcileOnlyDiff, () => {
    if (showReconcileDialog.value) {
        loadReconcile();
    }
});

onMounted(() => {
    getData(1, { initial: true });
});
</script>

<template>
    <div v-if="isInitialLoading" class="cstk-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar centros de stock...</p>
    </div>

    <div v-else class="cstk-page">
        <div class="cstk-card">
            <header class="cstk-header">
                <div>
                    <p class="cstk-eyebrow">Inventário</p>
                    <h1>Centros de stock</h1>
                    <p class="cstk-subtitle">{{ paginationSummary }}</p>
                    <div v-if="stockCenters.length" class="cstk-kpis">
                        <span class="cstk-kpi">
                            <i class="pi pi-star-fill" />
                            {{ listSummary.principal }} principal(is) nesta página
                        </span>
                        <span class="cstk-kpi">
                            <i class="pi pi-box" />
                            {{ listSummary.totalStock.toLocaleString('pt-PT') }} un. nesta página
                        </span>
                    </div>
                </div>

                <div class="cstk-header__actions">
                    <Button
                        label="Exportar Excel"
                        icon="pi pi-file-excel"
                        severity="success"
                        outlined
                        :loading="isExporting"
                        :disabled="isExporting"
                        @click="exportToExcel"
                    />
                    <router-link to="/stock/centerstocks/create">
                        <Button label="Novo centro" icon="pi pi-plus" />
                    </router-link>
                </div>
            </header>

            <section class="cstk-filters">
                <div class="cstk-filters__grid">
                    <div class="cstk-field cstk-field--wide">
                        <label>Pesquisar</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Nome, código ou localização..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="cstk-field">
                        <label>Stock principal</label>
                        <Select
                            v-model="isPrincipalStock"
                            :options="principalOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todos"
                            showClear
                            class="w-full"
                        />
                    </div>

                    <div class="cstk-field">
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

                    <div class="cstk-field">
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

                    <div class="cstk-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="cstk-field">
                        <label>Direção</label>
                        <div class="cstk-sort-dir">
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

                <div class="cstk-filters__actions">
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
                :value="stockCenters"
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
                    <div class="cstk-empty">Nenhum centro de stock encontrado.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="cstk-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Nome" style="min-width: 14rem">
                    <template #body="{ data }">
                        <strong>{{ data.name }}</strong>
                    </template>
                </Column>

                <Column header="Código" style="min-width: 9rem">
                    <template #body="{ data }">
                        <code class="cstk-code">{{ data.code || '—' }}</code>
                    </template>
                </Column>

                <Column header="Localização" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.location || '—' }}
                    </template>
                </Column>

                <Column header="Capacidade" style="min-width: 9rem">
                    <template #body="{ data }">
                        {{ data.maximum_capacity || '—' }}
                    </template>
                </Column>

                <Column header="Principal" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Tag
                            :value="formatBoolean(data.is_principal_stock)"
                            :severity="getPrincipalSeverity(data.is_principal_stock)"
                        />
                    </template>
                </Column>

                <Column header="Produtos" style="min-width: 8rem">
                    <template #body="{ data }">
                        <span class="cstk-count">{{ data.products_count ?? 0 }}</span>
                    </template>
                </Column>

                <Column header="Stock total" style="min-width: 9rem">
                    <template #body="{ data }">
                        <span
                            class="cstk-stock"
                            :class="{ 'cstk-stock--empty': (data.total_stock_quantity ?? 0) === 0 }"
                        >
                            {{ data.total_stock_quantity ?? 0 }}
                        </span>
                    </template>
                </Column>

                <Column header="Criado em" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="Acções" style="min-width: 12rem" :exportable="false">
                    <template #body="{ data }">
                        <div class="cstk-actions">
                            <Button
                                v-tooltip.top="'Ver stock'"
                                icon="pi pi-eye"
                                text
                                rounded
                                severity="secondary"
                                @click="router.push(`/stock/centerstocks/${data.id}`)"
                            />
                            <Button
                                v-tooltip.top="'Editar'"
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="info"
                                @click="router.push(`/stock/centerstocks/${data.id}/edit`)"
                            />
                            <Button
                                v-tooltip.top="'Relatório PDF'"
                                icon="pi pi-file-pdf"
                                text
                                rounded
                                severity="warn"
                                @click="openStockReport(data.id)"
                            />
                            <Button
                                v-tooltip.top="'Reconciliar stock'"
                                icon="pi pi-sync"
                                text
                                rounded
                                severity="help"
                                @click="openReconcile(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <Dialog
        v-model:visible="showReconcileDialog"
        :header="`Reconciliação — ${reconcileCenter?.name ?? ''}`"
        :style="{ width: '42rem' }"
        modal
        @hide="closeReconcile"
    >
        <div class="cstk-reconcile">
            <div class="cstk-reconcile__toolbar">
                <label class="cstk-reconcile__toggle">
                    <Checkbox v-model="reconcileOnlyDiff" :binary="true" />
                    Mostrar apenas diferenças
                </label>
                <Button
                    icon="pi pi-refresh"
                    label="Actualizar"
                    text
                    :loading="isReconcileLoading"
                    @click="loadReconcile"
                />
            </div>

            <div v-if="isReconcileLoading" class="cstk-reconcile__loading">
                <ProgressSpinner style="width: 36px; height: 36px" strokeWidth="6" />
            </div>

            <template v-else-if="reconcileResult">
                <div class="cstk-reconcile__summary">
                    <span>{{ reconcileResult.total_products }} produtos analisados</span>
                    <Tag
                        :value="`${reconcileResult.diff_count} diferenças`"
                        :severity="reconcileResult.diff_count ? 'danger' : 'success'"
                    />
                </div>

                <div v-if="!reconcileResult.items?.length" class="cstk-reconcile__ok">
                    Stock coerente com o ledger — sem diferenças.
                </div>

                <DataTable
                    v-else
                    :value="reconcileResult.items"
                    size="small"
                    showGridlines
                    scrollable
                    scrollHeight="320px"
                >
                    <Column header="Produto" style="min-width: 14rem">
                        <template #body="{ data }">
                            {{ data.product_name || `#${data.product_id}` }}
                        </template>
                    </Column>
                    <Column header="Em mão" style="min-width: 6rem">
                        <template #body="{ data }">{{ data.on_hand }}</template>
                    </Column>
                    <Column header="Ledger" style="min-width: 6rem">
                        <template #body="{ data }">{{ data.ledger }}</template>
                    </Column>
                    <Column header="Diff" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Tag
                                :value="String(data.diff)"
                                :severity="data.diff === 0 ? 'success' : 'danger'"
                            />
                        </template>
                    </Column>
                </DataTable>
            </template>
        </div>

        <template #footer>
            <Button label="Fechar" text @click="closeReconcile" />
        </template>
    </Dialog>
</template>

<style scoped>
.cstk-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.cstk-page {
    --cstk-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --cstk-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --cstk-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --cstk-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--cstk-border-soft);
}

.cstk-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--cstk-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--cstk-shadow);
}

.cstk-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.cstk-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.cstk-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.cstk-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.cstk-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.cstk-kpis {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.cstk-kpi {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: var(--text-color-secondary);
}

.cstk-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--cstk-border-soft);
    border-radius: 0.85rem;
    background: var(--cstk-muted-bg);
}

.cstk-filters__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.cstk-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.cstk-field--wide {
    grid-column: span 2;
}

.cstk-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.cstk-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.cstk-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.cstk-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.cstk-code {
    font-size: 0.85rem;
    padding: 0.1rem 0.35rem;
    border-radius: 0.35rem;
    background: var(--cstk-muted-bg);
}

.cstk-count,
.cstk-stock {
    display: inline-flex;
    min-width: 1.75rem;
    justify-content: center;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: var(--cstk-muted-bg);
    font-weight: 700;
    font-size: 0.82rem;
}

.cstk-stock--empty {
    background: color-mix(in srgb, var(--red-500) 12%, transparent);
    color: var(--red-600);
}

.cstk-actions {
    display: flex;
    gap: 0.1rem;
    flex-wrap: wrap;
}

.cstk-reconcile {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.cstk-reconcile__toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cstk-reconcile__toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
}

.cstk-reconcile__loading {
    display: grid;
    place-items: center;
    padding: 2rem;
}

.cstk-reconcile__summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    color: var(--text-color-secondary);
}

.cstk-reconcile__ok {
    padding: 1.25rem;
    text-align: center;
    border-radius: 0.75rem;
    background: color-mix(in srgb, var(--green-500) 10%, transparent);
    color: var(--green-700);
    font-weight: 600;
}

@media (max-width: 960px) {
    .cstk-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cstk-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .cstk-filters__grid {
        grid-template-columns: 1fr;
    }

    .cstk-field--wide {
        grid-column: span 1;
    }
}
</style>
