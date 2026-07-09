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

function formatMoney(value) {
    const number = Number(value ?? 0);
    return number.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString('pt-PT');
}

function movementReasonLabel(reason) {
    const map = {
        entrada: 'Entradas',
        saida: 'Saídas',
        transferencia_saida: 'Transferência saída',
        transferencia_entrada: 'Transferência entrada',
        inventory: 'Inventários',
        adjustment: 'Ajustes'
    };

    return map[reason] || reason;
}

const product = computed(() => details.value?.product ?? null);
const category = computed(() => details.value?.category ?? null);
const subcategory = computed(() => details.value?.subcategory ?? null);
const department = computed(() => details.value?.department ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const movementSummary = computed(() => details.value?.movement_summary ?? []);

const stockSeverity = computed(() => {
    const status = metrics.value.stock_status;
    if (status === 'ruptura') {
        return 'danger';
    }
    if (status === 'baixo') {
        return 'warn';
    }
    return 'success';
});

const stockLabel = computed(() => {
    const status = metrics.value.stock_status;
    if (status === 'ruptura') {
        return 'Ruptura';
    }
    if (status === 'baixo') {
        return 'Stock baixo';
    }
    return 'Stock normal';
});

const getData = async () => {
    isLoading.value = true;

    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/products/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do produto.',
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
    <div v-if="isLoading" class="prdshow-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar visão do produto...</p>
    </div>

    <div v-else-if="details" class="prdshow-page">
        <div class="prdshow-card">
            <header class="prdshow-header">
                <div>
                    <p class="prdshow-eyebrow">Gestão de catálogo</p>
                    <h1>{{ product?.name || 'Produto' }}</h1>
                    <p class="prdshow-subtitle">
                        Categoria: <strong>{{ category?.name || '—' }}</strong>
                    </p>
                    <p class="prdshow-subtitle">
                        Subcategoria: <strong>{{ subcategory?.name || '—' }}</strong>
                    </p>
                    <p class="prdshow-subtitle">
                        Departamento: <strong>{{ department?.name || '—' }}</strong>
                    </p>
                    <p class="prdshow-subtitle">Criado em {{ formatDate(product?.created_at) }}</p>
                </div>

                <div class="prdshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        label="Editar produto"
                        icon="pi pi-pencil"
                        severity="info"
                        @click="router.push(`/admin/products/${product?.id}/edit`)"
                    />
                </div>
            </header>

            <section class="prdshow-kpis">
                <div class="prdshow-kpi">
                    <span class="prdshow-kpi__label">Preço venda</span>
                    <strong>{{ formatMoney(product?.price) }} MT</strong>
                </div>
                <div class="prdshow-kpi">
                    <span class="prdshow-kpi__label">Preço compra</span>
                    <strong>{{ formatMoney(product?.buy_price) }} MT</strong>
                </div>
                <div class="prdshow-kpi">
                    <span class="prdshow-kpi__label">Margem bruta</span>
                    <strong :class="Number(metrics.gross_margin) >= 0 ? 'prdshow-kpi--good' : 'prdshow-kpi--bad'">
                        {{ formatMoney(metrics.gross_margin) }} MT
                    </strong>
                </div>
                <div class="prdshow-kpi">
                    <span class="prdshow-kpi__label">Markup</span>
                    <strong :class="Number(metrics.markup_percent) >= 0 ? 'prdshow-kpi--good' : 'prdshow-kpi--bad'">
                        {{ Number(metrics.markup_percent ?? 0).toFixed(2) }}%
                    </strong>
                </div>
                <div class="prdshow-kpi">
                    <span class="prdshow-kpi__label">Stock principal</span>
                    <strong>{{ formatNumber(product?.quantity_in_principal_stock) }} un.</strong>
                </div>
            </section>

            <section class="prdshow-health">
                <span>Estado de stock</span>
                <Tag :severity="stockSeverity" :value="stockLabel" />
            </section>

            <section class="prdshow-panel">
                <h3>Resumo de movimentos de stock</h3>
                <DataTable :value="movementSummary" dataKey="reason" rowHover showGridlines>
                    <template #empty>
                        <div class="prdshow-empty">Sem movimentos de stock registados.</div>
                    </template>

                    <Column header="Tipo" style="min-width: 14rem">
                        <template #body="{ data }">
                            {{ movementReasonLabel(data.reason) }}
                        </template>
                    </Column>
                    <Column header="Movimentos" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ formatNumber(data.total) }}
                        </template>
                    </Column>
                    <Column header="Qtd. total" style="min-width: 9rem">
                        <template #body="{ data }">
                            {{ formatNumber(data.quantity_sum) }} un.
                        </template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.prdshow-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.prdshow-page {
    --prdshow-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --prdshow-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --prdshow-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --prdshow-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--prdshow-border-soft);
}

.prdshow-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem;
    border: 1px solid var(--prdshow-border);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: var(--prdshow-shadow);
}

.prdshow-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.prdshow-header__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.prdshow-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.prdshow-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
}

.prdshow-subtitle {
    margin: 0.25rem 0 0;
    color: var(--text-color-secondary);
}

.prdshow-kpis {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.75rem;
}

.prdshow-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem;
    border: 1px solid var(--prdshow-border-soft);
    border-radius: 0.8rem;
    background: var(--prdshow-muted-bg);
}

.prdshow-kpi__label {
    font-size: 0.78rem;
    color: var(--text-color-secondary);
}

.prdshow-kpi--good {
    color: var(--green-500);
}

.prdshow-kpi--bad {
    color: var(--red-500);
}

.prdshow-health {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    border-radius: 0.8rem;
    border: 1px solid var(--prdshow-border-soft);
}

.prdshow-panel h3 {
    margin: 0 0 0.6rem;
    font-size: 1rem;
}

.prdshow-empty {
    padding: 1.5rem;
    text-align: center;
    color: var(--text-color-secondary);
}

@media (max-width: 1100px) {
    .prdshow-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .prdshow-kpis {
        grid-template-columns: 1fr;
    }
}
</style>