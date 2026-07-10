<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isInitialLoading = ref(true);
const isTableLoading = ref(false);
const isReportLoading = ref(false);
const isReconcileLoading = ref(false);

const details = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const searchQuery = ref('');
const stockStatus = ref(null);
const sortBy = ref('quantity');
const sortDir = ref('desc');

const showReconcileDialog = ref(false);
const reconcileResult = ref(null);
const reconcileOnlyDiff = ref(true);

const showReportDialog = ref(false);
const pdfUrl = ref(null);

const rowsPerPageOptions = [10, 15, 25, 50];
const stockStatusOptions = [
    { label: 'Todos', value: null },
    { label: 'Ruptura (0)', value: 'zero' },
    { label: 'Stock baixo (1-5)', value: 'low' },
    { label: 'Disponível (>5)', value: 'available' }
];
const sortOptions = [
    { label: 'Quantidade', value: 'quantity' },
    { label: 'Produto', value: 'product_name' },
    { label: 'ID', value: 'id' },
    { label: 'Registo', value: 'created_at' }
];

const stockcenter = computed(() => details.value?.stockcenter ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const products = computed(() => details.value?.stockcenterproducts?.data ?? []);
const topProducts = computed(() => details.value?.top_products_by_stock ?? []);
const totalRecords = computed(() => details.value?.stockcenterproducts?.total ?? 0);

const stockHealthLabel = computed(() => {
    const ruptures = Number(metrics.value.zero_stock_products ?? 0);
    const low = Number(metrics.value.low_stock_products ?? 0);

    if (ruptures > 0) {
        return 'Atenção crítica';
    }
    if (low > 0) {
        return 'Atenção moderada';
    }
    return 'Saudável';
});

const stockHealthSeverity = computed(() => {
    const ruptures = Number(metrics.value.zero_stock_products ?? 0);
    const low = Number(metrics.value.low_stock_products ?? 0);

    if (ruptures > 0) {
        return 'danger';
    }
    if (low > 0) {
        return 'warn';
    }
    return 'success';
});

const hasActiveFilters = computed(
    () =>
        !!searchQuery.value ||
        stockStatus.value != null ||
        sortBy.value !== 'quantity' ||
        sortDir.value !== 'desc'
);

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString('pt-PT');
}

function displayValue(value) {
    return value || '—';
}

function stockSeverity(quantity) {
    const qty = Number(quantity ?? 0);
    if (qty <= 0) {
        return 'danger';
    }
    if (qty <= 5) {
        return 'warn';
    }
    return 'success';
}

function buildParams(page = currentPage.value) {
    return {
        page,
        per_page: rowsPerPage.value,
        query: searchQuery.value || undefined,
        stock_status: stockStatus.value ?? undefined,
        sort_by: sortBy.value,
        sort_dir: sortDir.value
    };
}

const getData = async (page = 1, { initial = false } = {}) => {
    if (initial) {
        isInitialLoading.value = true;
    } else {
        isTableLoading.value = true;
    }

    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/centerstocks/${id}`, {
            params: buildParams(page)
        });

        details.value = response.data;
        currentPage.value = response.data.stockcenterproducts?.current_page ?? page;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar o centro de stock.',
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
    stockStatus.value = null;
    sortBy.value = 'quantity';
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

const downloadReportStock = async () => {
    isReportLoading.value = true;

    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/centerstock/report/${id}`, {
            responseType: 'blob'
        });

        if (pdfUrl.value) {
            URL.revokeObjectURL(pdfUrl.value);
        }

        pdfUrl.value = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        showReportDialog.value = true;

        toast.add({
            severity: 'success',
            summary: 'Relatório gerado',
            detail: 'O relatório de stock foi gerado com sucesso.',
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível gerar o relatório.',
            life: 3000
        });
    } finally {
        isReportLoading.value = false;
    }
};

const printPDF = () => {
    const iframe = document.querySelector('.cstkshow-report iframe');
    if (iframe?.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
};

const closeReportDialog = () => {
    showReportDialog.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
};

const loadReconcile = async () => {
    if (!stockcenter.value?.id) {
        return;
    }

    isReconcileLoading.value = true;

    try {
        const response = await axios.get('/api/centerstock/reconcile', {
            params: {
                center_id: stockcenter.value.id,
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

const openReconcile = async () => {
    reconcileResult.value = null;
    showReconcileDialog.value = true;
    await loadReconcile();
};

const closeReconcile = () => {
    showReconcileDialog.value = false;
    reconcileResult.value = null;
};

const debouncedReload = debounce(() => {
    currentPage.value = 1;
    getData(1);
}, 350);

watch([searchQuery, stockStatus, sortBy, sortDir], debouncedReload);

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
    <div v-if="isInitialLoading" class="cstkshow-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar centro de stock...</p>
    </div>

    <div v-else-if="details" class="cstkshow-page">
        <div class="cstkshow-card">
            <header class="cstkshow-header">
                <div>
                    <p class="cstkshow-eyebrow">Inventário</p>
                    <h1>{{ stockcenter?.name || 'Centro de stock' }}</h1>
                    <p class="cstkshow-subtitle">
                        Código: <strong>{{ displayValue(stockcenter?.code) }}</strong>
                        <span class="cstkshow-sep">·</span>
                        Localização: <strong>{{ displayValue(stockcenter?.location) }}</strong>
                    </p>
                    <p class="cstkshow-subtitle">
                        Capacidade máx.: <strong>{{ displayValue(stockcenter?.maximum_capacity) }}</strong>
                        <span class="cstkshow-sep">·</span>
                        Criado em {{ formatDate(stockcenter?.created_at) }}
                    </p>
                    <div class="cstkshow-badges">
                        <Tag
                            v-if="Number(stockcenter?.is_principal_stock) === 1"
                            value="Stock principal"
                            severity="success"
                        />
                        <Tag :severity="stockHealthSeverity" :value="stockHealthLabel" />
                    </div>
                </div>

                <div class="cstkshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        label="Reconciliar"
                        icon="pi pi-sync"
                        severity="secondary"
                        outlined
                        @click="openReconcile"
                    />
                    <Button
                        label="Relatório PDF"
                        icon="pi pi-file-pdf"
                        severity="help"
                        outlined
                        :loading="isReportLoading"
                        :disabled="isReportLoading"
                        @click="downloadReportStock"
                    />
                    <Button
                        label="Editar centro"
                        icon="pi pi-pencil"
                        severity="info"
                        @click="router.push(`/stock/centerstocks/${stockcenter?.id}/edit`)"
                    />
                </div>
            </header>

            <section class="cstkshow-kpis">
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Produtos</span>
                    <strong>{{ formatNumber(metrics.products_count) }}</strong>
                </div>
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Stock total</span>
                    <strong>{{ formatNumber(metrics.total_quantity) }} un.</strong>
                </div>
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Rupturas</span>
                    <strong class="cstkshow-kpi--danger">{{ formatNumber(metrics.zero_stock_products) }}</strong>
                </div>
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Stock baixo</span>
                    <strong class="cstkshow-kpi--warn">{{ formatNumber(metrics.low_stock_products) }}</strong>
                </div>
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Transferências entrada</span>
                    <strong>{{ formatNumber(metrics.transfers_in_count) }}</strong>
                </div>
                <div class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Transferências saída</span>
                    <strong>{{ formatNumber(metrics.transfers_out_count) }}</strong>
                </div>
                <div v-if="metrics.capacity_usage_percent != null" class="cstkshow-kpi">
                    <span class="cstkshow-kpi__label">Uso capacidade</span>
                    <strong>{{ Number(metrics.capacity_usage_percent).toFixed(1) }}%</strong>
                </div>
            </section>

            <section v-if="topProducts.length" class="cstkshow-top">
                <h3>Top produtos por stock</h3>
                <div class="cstkshow-top__chips">
                    <Tag
                        v-for="item in topProducts"
                        :key="item.id"
                        :value="`${item.product?.name || 'Produto'}: ${formatNumber(item.quantity)} un.`"
                        severity="info"
                    />
                </div>
            </section>

            <section class="cstkshow-filters">
                <div class="cstkshow-filters__grid">
                    <div class="cstkshow-field cstkshow-field--wide">
                        <label>Pesquisar produto</label>
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="searchQuery"
                                placeholder="Nome, categoria ou subcategoria..."
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <div class="cstkshow-field">
                        <label>Estado stock</label>
                        <Select
                            v-model="stockStatus"
                            :options="stockStatusOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Todos"
                            class="w-full"
                        />
                    </div>

                    <div class="cstkshow-field">
                        <label>Ordenar por</label>
                        <Select
                            v-model="sortBy"
                            :options="sortOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="cstkshow-field">
                        <label>Direção</label>
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

                <div class="cstkshow-filters__actions">
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
                    <div class="cstkshow-empty">Nenhum produto encontrado neste centro.</div>
                </template>

                <Column header="ID" style="min-width: 5rem">
                    <template #body="{ data }">
                        <span class="cstkshow-id">#{{ data.id }}</span>
                    </template>
                </Column>

                <Column header="Produto" style="min-width: 16rem">
                    <template #body="{ data }">
                        <strong>{{ data.product?.name || '—' }}</strong>
                    </template>
                </Column>

                <Column header="Categoria" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.product?.category?.name || '—' }}
                    </template>
                </Column>

                <Column header="Subcategoria" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.product?.subcategory?.name || '—' }}
                    </template>
                </Column>

                <Column header="Stock" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Tag :value="`${formatNumber(data.quantity)} un.`" :severity="stockSeverity(data.quantity)" />
                    </template>
                </Column>

                <Column header="Actualizado" style="min-width: 11rem">
                    <template #body="{ data }">
                        {{ formatDate(data.updated_at || data.created_at) }}
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <Dialog
        v-model:visible="showReconcileDialog"
        :header="`Reconciliação — ${stockcenter?.name ?? ''}`"
        :style="{ width: '42rem' }"
        modal
        @hide="closeReconcile"
    >
        <div class="cstkshow-reconcile">
            <div class="cstkshow-reconcile__toolbar">
                <label class="cstkshow-reconcile__toggle">
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

            <div v-if="isReconcileLoading" class="cstkshow-reconcile__loading">
                <ProgressSpinner style="width: 36px; height: 36px" strokeWidth="6" />
            </div>

            <template v-else-if="reconcileResult">
                <div class="cstkshow-reconcile__summary">
                    <span>{{ reconcileResult.total_products }} produtos analisados</span>
                    <Tag
                        :value="`${reconcileResult.diff_count} diferenças`"
                        :severity="reconcileResult.diff_count ? 'danger' : 'success'"
                    />
                </div>

                <div v-if="!reconcileResult.items?.length" class="cstkshow-reconcile__ok">
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

    <Dialog
        v-model:visible="showReportDialog"
        header="Relatório de stock"
        :style="{ width: '52rem' }"
        modal
        class="cstkshow-report"
        @hide="closeReportDialog"
    >
        <iframe v-if="pdfUrl" :src="pdfUrl" style="width: 100%; height: 520px" frameborder="0" />

        <template #footer>
            <Button label="Imprimir" icon="pi pi-print" @click="printPDF" />
            <Button label="Fechar" text @click="closeReportDialog" />
        </template>
    </Dialog>
</template>

<style scoped>
.cstkshow-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.cstkshow-page {
    --cstkshow-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --cstkshow-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --cstkshow-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --cstkshow-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--cstkshow-border-soft);
}

.cstkshow-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--cstkshow-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--cstkshow-shadow);
}

.cstkshow-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.cstkshow-header__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.cstkshow-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.cstkshow-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.cstkshow-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.88rem;
}

.cstkshow-sep {
    opacity: 0.5;
    margin: 0 0.25rem;
}

.cstkshow-badges {
    margin-top: 0.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.cstkshow-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.cstkshow-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem;
    border: 1px solid var(--cstkshow-border-soft);
    border-radius: 0.8rem;
    background: var(--cstkshow-muted-bg);
}

.cstkshow-kpi__label {
    font-size: 0.78rem;
    color: var(--text-color-secondary);
}

.cstkshow-kpi--danger {
    color: var(--red-500);
}

.cstkshow-kpi--warn {
    color: var(--orange-500);
}

.cstkshow-top h3 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
}

.cstkshow-top__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.cstkshow-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid var(--cstkshow-border-soft);
    border-radius: 0.85rem;
    background: var(--cstkshow-muted-bg);
}

.cstkshow-filters__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.cstkshow-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.cstkshow-field--wide {
    grid-column: span 2;
}

.cstkshow-field label {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-color-secondary);
}

.cstkshow-filters__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.cstkshow-empty {
    padding: 2rem;
    text-align: center;
    color: var(--text-color-secondary);
}

.cstkshow-id {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.cstkshow-reconcile {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.cstkshow-reconcile__toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.cstkshow-reconcile__toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.cstkshow-reconcile__loading {
    display: grid;
    place-items: center;
    padding: 2rem;
}

.cstkshow-reconcile__summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.cstkshow-reconcile__ok {
    padding: 1rem;
    border-radius: 0.75rem;
    background: color-mix(in srgb, var(--green-500) 12%, transparent);
    color: var(--text-color-secondary);
    text-align: center;
}

@media (max-width: 1200px) {
    .cstkshow-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cstkshow-filters__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cstkshow-field--wide {
        grid-column: span 2;
    }
}

@media (max-width: 640px) {
    .cstkshow-kpis,
    .cstkshow-filters__grid {
        grid-template-columns: 1fr;
    }

    .cstkshow-field--wide {
        grid-column: span 1;
    }
}
</style>
