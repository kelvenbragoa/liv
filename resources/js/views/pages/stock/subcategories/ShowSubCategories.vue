<script setup>
import { computed, onMounted, ref } from 'vue';
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

const subcategory = computed(() => details.value?.subcategory ?? null);
const category = computed(() => details.value?.category ?? null);
const department = computed(() => details.value?.department ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
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
        const response = await axios.get(`/api/subcategories/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes da subcategoria.',
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
    <div v-if="isLoading" class="subshow-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar visão da subcategoria...</p>
    </div>

    <div v-else-if="details" class="subshow-page">
        <div class="subshow-card">
            <header class="subshow-header">
                <div>
                    <p class="subshow-eyebrow">Gestão de catálogo</p>
                    <h1>{{ subcategory?.name || 'Subcategoria' }}</h1>
                    <p class="subshow-subtitle">
                        Categoria: <strong>{{ category?.name || '—' }}</strong>
                    </p>
                    <p class="subshow-subtitle">
                        Departamento: <strong>{{ department?.name || '—' }}</strong>
                    </p>
                    <p class="subshow-subtitle">Criada em {{ formatDate(subcategory?.created_at) }}</p>
                </div>

                <div class="subshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        label="Editar subcategoria"
                        icon="pi pi-pencil"
                        severity="info"
                        @click="router.push(`/stock/subcategories/${subcategory?.id}/edit`)"
                    />
                </div>
            </header>

            <section class="subshow-kpis">
                <div class="subshow-kpi">
                    <span class="subshow-kpi__label">Produtos</span>
                    <strong>{{ formatNumber(metrics.products_count) }}</strong>
                </div>
                <div class="subshow-kpi">
                    <span class="subshow-kpi__label">Stock total</span>
                    <strong>{{ formatNumber(metrics.products_total_stock) }} un.</strong>
                </div>
                <div class="subshow-kpi">
                    <span class="subshow-kpi__label">Preço médio</span>
                    <strong>{{ formatMoney(metrics.products_avg_price) }} MT</strong>
                </div>
                <div class="subshow-kpi">
                    <span class="subshow-kpi__label">Rupturas</span>
                    <strong class="subshow-kpi--danger">{{ formatNumber(metrics.zero_stock_products) }}</strong>
                </div>
                <div class="subshow-kpi">
                    <span class="subshow-kpi__label">Stock baixo (1-5)</span>
                    <strong class="subshow-kpi--warn">{{ formatNumber(metrics.low_stock_products) }}</strong>
                </div>
            </section>

            <section class="subshow-health">
                <span>Saúde de stock da subcategoria</span>
                <Tag :severity="stockHealthSeverity" :value="stockHealthLabel" />
            </section>

            <section class="subshow-panel">
                <h3>Top produtos por stock</h3>
                <DataTable :value="topProducts" dataKey="id" rowHover showGridlines>
                    <template #empty>
                        <div class="subshow-empty">Sem produtos registados para esta subcategoria.</div>
                    </template>

                    <Column header="Produto" style="min-width: 16rem">
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
</template>

<style scoped>
.subshow-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.subshow-page {
    --subshow-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --subshow-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --subshow-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --subshow-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--subshow-border-soft);
}

.subshow-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--subshow-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--subshow-shadow);
}

.subshow-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.subshow-header__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.subshow-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.subshow-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
}

.subshow-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
}

.subshow-kpis {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.75rem;
}

.subshow-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem;
    border: 1px solid var(--subshow-border-soft);
    border-radius: 0.8rem;
    background: var(--subshow-muted-bg);
}

.subshow-kpi__label {
    font-size: 0.78rem;
    color: var(--text-color-secondary);
}

.subshow-kpi--danger {
    color: var(--red-500);
}

.subshow-kpi--warn {
    color: var(--orange-500);
}

.subshow-health {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    border-radius: 0.8rem;
    border: 1px solid var(--subshow-border-soft);
}

.subshow-panel h3 {
    margin: 0 0 0.6rem;
    font-size: 1rem;
}

.subshow-empty {
    padding: 1.5rem;
    text-align: center;
    color: var(--text-color-secondary);
}

@media (max-width: 1100px) {
    .subshow-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .subshow-kpis {
        grid-template-columns: 1fr;
    }
}
</style>