<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString('pt-PT');
}

function formatMoney(value) {
    const number = Number(value ?? 0);
    return number.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const category = computed(() => details.value?.category ?? null);
const department = computed(() => details.value?.department ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const subcategoryBreakdown = computed(() => details.value?.subcategory_breakdown ?? []);
const topProducts = computed(() => details.value?.top_products_by_stock ?? []);

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

const getData = async () => {
    isLoading.value = true;

    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/categories/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes da categoria.',
            life: 3000
        });
        goBackUsingBack();
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="isLoading" class="catshow-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar visão da categoria...</p>
    </div>

    <div v-else-if="details" class="catshow-page">
        <div class="catshow-card">
            <header class="catshow-header">
                <div>
                    <p class="catshow-eyebrow">Gestão de catálogo</p>
                    <h1>{{ category?.name || 'Categoria' }}</h1>
                    <p class="catshow-subtitle">
                        Departamento: <strong>{{ department?.name || '—' }}</strong>
                    </p>
                    <p class="catshow-subtitle">Criada em {{ formatDate(category?.created_at) }}</p>
                </div>

                <div class="catshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        label="Editar categoria"
                        icon="pi pi-pencil"
                        severity="info"
                        @click="router.push(`/stock/categories/${category?.id}/edit`)"
                    />
                </div>
            </header>

            <section class="catshow-kpis">
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Subcategorias</span>
                    <strong>{{ formatNumber(metrics.sub_categories_count) }}</strong>
                </div>
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Produtos</span>
                    <strong>{{ formatNumber(metrics.products_count) }}</strong>
                </div>
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Stock total</span>
                    <strong>{{ formatNumber(metrics.products_total_stock) }} un.</strong>
                </div>
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Preço médio</span>
                    <strong>{{ formatMoney(metrics.products_avg_price) }} MT</strong>
                </div>
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Rupturas de stock</span>
                    <strong class="catshow-kpi--danger">{{ formatNumber(metrics.zero_stock_products) }}</strong>
                </div>
                <div class="catshow-kpi">
                    <span class="catshow-kpi__label">Stock baixo (1-5)</span>
                    <strong class="catshow-kpi--warn">{{ formatNumber(metrics.low_stock_products) }}</strong>
                </div>
            </section>

            <section class="catshow-health">
                <span>Saúde de stock da categoria</span>
                <Tag :severity="stockHealthSeverity" :value="stockHealthLabel" />
            </section>

            <div class="catshow-grid">
                <section class="catshow-panel">
                    <h3>Distribuição por subcategoria</h3>
                    <DataTable :value="subcategoryBreakdown" dataKey="id" rowHover showGridlines>
                        <template #empty>
                            <div class="catshow-empty">Sem subcategorias registadas.</div>
                        </template>

                        <Column header="Subcategoria" style="min-width: 14rem">
                            <template #body="{ data }">
                                <strong>{{ data.name }}</strong>
                            </template>
                        </Column>
                        <Column header="Produtos" style="min-width: 8rem">
                            <template #body="{ data }">
                                {{ formatNumber(data.products_count) }}
                            </template>
                        </Column>
                        <Column header="Stock total" style="min-width: 8rem">
                            <template #body="{ data }">
                                {{ formatNumber(data.total_stock) }} un.
                            </template>
                        </Column>
                    </DataTable>
                </section>

                <section class="catshow-panel">
                    <h3>Top produtos por stock</h3>
                    <DataTable :value="topProducts" dataKey="id" rowHover showGridlines>
                        <template #empty>
                            <div class="catshow-empty">Sem produtos registados para esta categoria.</div>
                        </template>

                        <Column header="Produto" style="min-width: 14rem">
                            <template #body="{ data }">
                                <strong>{{ data.name }}</strong>
                            </template>
                        </Column>
                        <Column header="Stock" style="min-width: 7rem">
                            <template #body="{ data }">
                                {{ formatNumber(data.quantity_in_principal_stock) }} un.
                            </template>
                        </Column>
                        <Column header="Preço" style="min-width: 7rem">
                            <template #body="{ data }">
                                {{ formatMoney(data.price) }} MT
                            </template>
                        </Column>
                        <Column header="Registo" style="min-width: 10rem">
                            <template #body="{ data }">
                                {{ formatDate(data.created_at) }}
                            </template>
                        </Column>
                    </DataTable>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.catshow-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.catshow-page {
    --catshow-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --catshow-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --catshow-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --catshow-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--catshow-border-soft);
}

.catshow-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--catshow-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--catshow-shadow);
}

.catshow-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.catshow-header__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.catshow-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.catshow-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
}

.catshow-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.9rem;
}

.catshow-kpis {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.75rem;
}

.catshow-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem;
    border: 1px solid var(--catshow-border-soft);
    border-radius: 0.8rem;
    background: var(--catshow-muted-bg);
}

.catshow-kpi__label {
    font-size: 0.78rem;
    color: var(--text-color-secondary);
}

.catshow-kpi strong {
    font-size: 1.05rem;
}

.catshow-kpi--danger {
    color: var(--red-500);
}

.catshow-kpi--warn {
    color: var(--orange-500);
}

.catshow-health {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    border-radius: 0.8rem;
    border: 1px solid var(--catshow-border-soft);
}

.catshow-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.catshow-panel {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.catshow-panel h3 {
    margin: 0;
    font-size: 1rem;
}

.catshow-empty {
    padding: 1.5rem;
    text-align: center;
    color: var(--text-color-secondary);
}

@media (max-width: 1280px) {
    .catshow-kpis {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 960px) {
    .catshow-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .catshow-kpis {
        grid-template-columns: 1fr;
    }
}
</style>