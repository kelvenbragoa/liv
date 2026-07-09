<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);
const itemSearch = ref('');

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function displayValue(value) {
    return value || '—';
}

function adjustmentClass(value) {
    const n = Number(value ?? 0);
    if (n > 0) return 'invshow-good';
    if (n < 0) return 'invshow-bad';
    return 'invshow-neutral';
}

function formatAdjustment(value) {
    const n = Number(value ?? 0);
    if (n > 0) return `+${n}`;
    return String(n);
}

const inventory = computed(() => details.value?.inventory ?? null);
const stockCenter = computed(() => details.value?.stock_center ?? null);
const user = computed(() => details.value?.user ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const items = computed(() => details.value?.items ?? []);

const filteredItems = computed(() => {
    const q = itemSearch.value.trim().toLowerCase();
    if (!q) return items.value;
    return items.value.filter((item) => String(item.product_name ?? '').toLowerCase().includes(q));
});

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/inventories/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar o inventário.',
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
    <div v-if="isLoading" class="invshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar inventário...</p>
    </div>

    <div v-else-if="details" class="invshow-page">
        <div class="invshow-card">
            <header class="invshow-header">
                <div>
                    <p class="invshow-eyebrow">Movimento de stock · Inventário</p>
                    <h1>{{ inventory?.ref || 'Inventário' }}</h1>
                    <p class="invshow-subtitle">Registado em {{ formatDate(inventory?.created_at) }}</p>
                </div>
                <div class="invshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        v-if="stockCenter?.id"
                        label="Ver centro"
                        icon="pi pi-building"
                        severity="secondary"
                        outlined
                        @click="router.push(`/admin/centerstocks/${stockCenter.id}`)"
                    />
                </div>
            </header>

            <section class="invshow-kpis">
                <div class="invshow-kpi">
                    <span class="invshow-kpi__label">Linhas</span>
                    <strong>{{ metrics.items_count ?? 0 }}</strong>
                </div>
                <div class="invshow-kpi">
                    <span class="invshow-kpi__label">Com diferença</span>
                    <strong>{{ metrics.items_with_difference ?? 0 }}</strong>
                </div>
                <div class="invshow-kpi">
                    <span class="invshow-kpi__label">Ajustes positivos</span>
                    <strong class="invshow-good">+{{ metrics.positive_adjustments ?? 0 }}</strong>
                </div>
                <div class="invshow-kpi">
                    <span class="invshow-kpi__label">Ajustes negativos</span>
                    <strong class="invshow-bad">-{{ metrics.negative_adjustments ?? 0 }}</strong>
                </div>
                <div class="invshow-kpi invshow-kpi--highlight">
                    <span class="invshow-kpi__label">Saldo líquido</span>
                    <strong :class="adjustmentClass(metrics.net_adjustment)">{{ formatAdjustment(metrics.net_adjustment) }}</strong>
                </div>
            </section>

            <section class="invshow-info">
                <h3>Detalhes do inventário</h3>
                <div class="invshow-info__grid">
                    <p><strong>Referência:</strong> {{ displayValue(inventory?.ref) }}</p>
                    <p><strong>Centro de stock:</strong> {{ displayValue(stockCenter?.name) }}</p>
                    <p><strong>Produtos declarados:</strong> {{ metrics.products_number ?? 0 }}</p>
                    <p><strong>Registado por:</strong> {{ displayValue(user?.name) }}</p>
                    <p v-if="inventory?.obs" class="invshow-obs"><strong>Observações:</strong> {{ inventory.obs }}</p>
                </div>
            </section>

            <section class="invshow-table-section">
                <div class="invshow-table-header">
                    <h3>Contagem e ajustes</h3>
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="itemSearch" placeholder="Pesquisar produto..." />
                    </IconField>
                </div>

                <DataTable
                    :value="filteredItems"
                    dataKey="id"
                    :rowHover="true"
                    stripedRows
                    responsiveLayout="scroll"
                    class="invshow-table"
                >
                    <template #empty>Nenhum produto encontrado.</template>
                    <Column header="Produto" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="invshow-product">
                                <span>{{ data.product_name || '—' }}</span>
                                <Button
                                    v-if="data.product_id"
                                    icon="pi pi-external-link"
                                    text
                                    rounded
                                    size="small"
                                    @click="router.push(`/admin/products/${data.product_id}`)"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column header="Stock sistema" style="min-width: 8rem">
                        <template #body="{ data }">{{ data.last_quantity }}</template>
                    </Column>
                    <Column header="Contagem física" style="min-width: 9rem">
                        <template #body="{ data }">
                            <strong>{{ data.quantity }}</strong>
                        </template>
                    </Column>
                    <Column header="Ajuste" style="min-width: 8rem">
                        <template #body="{ data }">
                            <span :class="adjustmentClass(data.adjustment)">{{ formatAdjustment(data.adjustment) }}</span>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.invshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.invshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.invshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.invshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.invshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.invshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.invshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.invshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.invshow-kpis { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: .75rem; }
.invshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.invshow-kpi--highlight { border-color: color-mix(in srgb, var(--primary-color) 35%, var(--bs)); }
.invshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.invshow-info, .invshow-table-section { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.invshow-info h3, .invshow-table-header h3 { margin: 0; font-size: 1rem; }
.invshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .5rem .9rem; margin-top: .6rem; }
.invshow-info__grid p { margin: 0; }
.invshow-obs { grid-column: span 2; }
.invshow-table-header { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: .75rem; }
.invshow-product { display: flex; align-items: center; gap: .25rem; }
.invshow-good { color: var(--green-500); font-weight: 600; }
.invshow-bad { color: var(--red-500); font-weight: 600; }
.invshow-neutral { color: var(--text-color-secondary); font-weight: 600; }
@media (max-width: 1100px) { .invshow-kpis { grid-template-columns: repeat(2, minmax(0,1fr)); } }
@media (max-width: 640px) { .invshow-kpis, .invshow-info__grid { grid-template-columns: 1fr; } .invshow-obs { grid-column: span 1; } }
</style>
