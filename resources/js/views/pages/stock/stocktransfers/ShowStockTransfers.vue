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

const transfer = computed(() => details.value?.transfer ?? null);
const origin = computed(() => details.value?.origin ?? null);
const destination = computed(() => details.value?.destination ?? null);
const status = computed(() => details.value?.status ?? null);
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
        const response = await axios.get(`/api/stocktransfers/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar a transferência.',
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
    <div v-if="isLoading" class="stxfshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar transferência...</p>
    </div>

    <div v-else-if="details" class="stxfshow-page">
        <div class="stxfshow-card">
            <header class="stxfshow-header">
                <div>
                    <p class="stxfshow-eyebrow">Movimento de stock · Transferência</p>
                    <h1>{{ transfer?.ref || 'Transferência' }}</h1>
                    <p class="stxfshow-subtitle">
                        Data da transferência: {{ formatDate(transfer?.transfer_date || transfer?.created_at) }}
                    </p>
                </div>
                <div class="stxfshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        v-if="origin?.id"
                        label="Centro origem"
                        icon="pi pi-sign-out"
                        severity="secondary"
                        outlined
                        @click="router.push(`/stock/centerstocks/${origin.id}`)"
                    />
                    <Button
                        v-if="destination?.id"
                        label="Centro destino"
                        icon="pi pi-sign-in"
                        severity="info"
                        outlined
                        @click="router.push(`/stock/centerstocks/${destination.id}`)"
                    />
                </div>
            </header>

            <section class="stxfshow-route">
                <div class="stxfshow-route__center">
                    <span class="stxfshow-route__label">Origem</span>
                    <strong>{{ displayValue(origin?.name) }}</strong>
                </div>
                <div class="stxfshow-route__arrow">
                    <i class="pi pi-arrow-right"></i>
                </div>
                <div class="stxfshow-route__center">
                    <span class="stxfshow-route__label">Destino</span>
                    <strong>{{ displayValue(destination?.name) }}</strong>
                </div>
                <Tag v-if="status?.name" :value="status.name" severity="info" class="stxfshow-route__status" />
            </section>

            <section class="stxfshow-kpis">
                <div class="stxfshow-kpi">
                    <span class="stxfshow-kpi__label">Linhas</span>
                    <strong>{{ metrics.items_count ?? 0 }}</strong>
                </div>
                <div class="stxfshow-kpi stxfshow-kpi--highlight">
                    <span class="stxfshow-kpi__label">Total transferido</span>
                    <strong>{{ metrics.total_quantity ?? 0 }}</strong>
                </div>
                <div class="stxfshow-kpi">
                    <span class="stxfshow-kpi__label">Registado por</span>
                    <strong class="stxfshow-kpi__text">{{ displayValue(user?.name) }}</strong>
                </div>
            </section>

            <section class="stxfshow-table-section">
                <div class="stxfshow-table-header">
                    <h3>Produtos transferidos</h3>
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
                    class="stxfshow-table"
                >
                    <template #empty>Nenhum produto encontrado.</template>
                    <Column header="Produto" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="stxfshow-product">
                                <span>{{ data.product_name || '—' }}</span>
                                <Button
                                    v-if="data.product_id"
                                    icon="pi pi-external-link"
                                    text
                                    rounded
                                    size="small"
                                    @click="router.push(`/stock/products/${data.product_id}`)"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column header="Origem" style="min-width: 10rem">
                        <template #body="{ data }">{{ data.origin_name || '—' }}</template>
                    </Column>
                    <Column header="Destino" style="min-width: 10rem">
                        <template #body="{ data }">{{ data.destination_name || '—' }}</template>
                    </Column>
                    <Column header="Quantidade" style="min-width: 8rem">
                        <template #body="{ data }">
                            <strong>{{ data.quantity }}</strong>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.stxfshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.stxfshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.stxfshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.stxfshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.stxfshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.stxfshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.stxfshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.stxfshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.stxfshow-route { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.stxfshow-route__center { display: flex; flex-direction: column; gap: .15rem; min-width: 10rem; }
.stxfshow-route__label { font-size: .75rem; color: var(--text-color-secondary); text-transform: uppercase; letter-spacing: .05em; }
.stxfshow-route__arrow { color: var(--primary-color); font-size: 1.1rem; }
.stxfshow-route__status { margin-left: auto; }
.stxfshow-kpis { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .75rem; }
.stxfshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.stxfshow-kpi--highlight { border-color: color-mix(in srgb, var(--primary-color) 35%, var(--bs)); }
.stxfshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.stxfshow-kpi__text { font-size: .95rem; }
.stxfshow-table-section { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.stxfshow-table-header { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: .75rem; }
.stxfshow-table-header h3 { margin: 0; font-size: 1rem; }
.stxfshow-product { display: flex; align-items: center; gap: .25rem; }
@media (max-width: 900px) { .stxfshow-kpis { grid-template-columns: 1fr; } .stxfshow-route__status { margin-left: 0; } }
</style>
