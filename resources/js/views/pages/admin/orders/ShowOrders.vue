<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

const order = computed(() => details.value?.order ?? null);
const table = computed(() => details.value?.table ?? null);
const status = computed(() => details.value?.status ?? null);
const user = computed(() => details.value?.user ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const items = computed(() => details.value?.items ?? []);
const payments = computed(() => details.value?.payments ?? []);

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

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/orders/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do pedido.',
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
    <div v-if="isLoading" class="ordshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do pedido...</p>
    </div>

    <div v-else-if="details" class="ordshow-page">
        <div class="ordshow-card">
            <header class="ordshow-header">
                <div>
                    <p class="ordshow-eyebrow">Gestão de pedidos</p>
                    <h1>Pedido #{{ order?.id }}</h1>
                    <p class="ordshow-subtitle">Mesa: <strong>{{ table?.name || 'Venda rápida' }}</strong></p>
                    <p class="ordshow-subtitle">Estado: <strong>{{ status?.name || '—' }}</strong></p>
                    <p class="ordshow-subtitle">Operador: <strong>{{ user?.name || '—' }}</strong></p>
                    <p class="ordshow-subtitle">Criado em {{ formatDate(order?.created_at) }}</p>
                </div>
                <div class="ordshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Ver listagem" icon="pi pi-list" severity="info" @click="router.push('/admin/orders')" />
                </div>
            </header>

            <section class="ordshow-kpis">
                <div class="ordshow-kpi"><span>Total pedido</span><strong>{{ formatMoney(order?.total) }} MT</strong></div>
                <div class="ordshow-kpi"><span>Itens</span><strong>{{ metrics.items_count ?? 0 }}</strong></div>
                <div class="ordshow-kpi"><span>Qtd. total</span><strong>{{ metrics.total_quantity ?? 0 }}</strong></div>
                <div class="ordshow-kpi"><span>Produtos distintos</span><strong>{{ metrics.distinct_products ?? 0 }}</strong></div>
                <div class="ordshow-kpi"><span>Pago</span><strong>{{ formatMoney(metrics.payments_total) }} MT</strong></div>
                <div class="ordshow-kpi"><span>Saldo</span><strong :class="Number(metrics.balance_remaining) > 0 ? 'ordshow-bad' : 'ordshow-good'">{{ formatMoney(metrics.balance_remaining) }} MT</strong></div>
            </section>

            <section class="ordshow-panel">
                <h3>Itens do pedido</h3>
                <DataTable :value="items" dataKey="id" rowHover showGridlines>
                    <template #empty><div class="ordshow-empty">Sem itens registados.</div></template>
                    <Column header="Produto" style="min-width: 16rem"><template #body="{ data }">{{ data.product?.name || '—' }}</template></Column>
                    <Column header="Qtd." style="min-width: 6rem"><template #body="{ data }">{{ data.quantity }}</template></Column>
                    <Column header="Preço" style="min-width: 8rem"><template #body="{ data }">{{ formatMoney(data.price) }} MT</template></Column>
                    <Column header="Total" style="min-width: 8rem"><template #body="{ data }"><strong>{{ formatMoney(data.total) }} MT</strong></template></Column>
                </DataTable>
            </section>

            <section class="ordshow-panel">
                <h3>Pagamentos do pedido</h3>
                <DataTable :value="payments" dataKey="id" rowHover showGridlines>
                    <template #empty><div class="ordshow-empty">Sem pagamentos associados.</div></template>
                    <Column header="ID" style="min-width: 5rem"><template #body="{ data }">#{{ data.id }}</template></Column>
                    <Column header="Método" style="min-width: 12rem"><template #body="{ data }">{{ data.method?.name || '—' }}</template></Column>
                    <Column header="Valor" style="min-width: 8rem"><template #body="{ data }">{{ formatMoney(data.amount) }} MT</template></Column>
                    <Column header="Data" style="min-width: 11rem"><template #body="{ data }">{{ formatDate(data.created_at) }}</template></Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.ordshow-loading{min-height:50vh;display:grid;place-items:center;gap:.75rem;color:var(--text-color-secondary)}
.ordshow-page{--b:color-mix(in srgb,var(--surface-border) 70%,var(--text-color) 30%);--bs:color-mix(in srgb,var(--surface-border) 85%,transparent);--bg:color-mix(in srgb,var(--surface-ground) 75%,var(--text-color) 5%)}
.ordshow-card{display:flex;flex-direction:column;gap:1rem;padding:1.1rem;border:1px solid var(--b);border-radius:1rem;background:var(--surface-card);box-shadow:0 1px 2px rgba(15,23,42,.05),0 0 0 1px var(--bs)}
.ordshow-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.ordshow-header__actions{display:flex;gap:.5rem;flex-wrap:wrap}
.ordshow-eyebrow{margin:0;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--primary-color)}
.ordshow-header h1{margin:.15rem 0 0;font-size:1.5rem}
.ordshow-subtitle{margin:.2rem 0 0;color:var(--text-color-secondary)}
.ordshow-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.75rem}
.ordshow-kpi{display:flex;flex-direction:column;gap:.2rem;padding:.75rem;border:1px solid var(--bs);border-radius:.8rem;background:var(--bg)}
.ordshow-panel h3{margin:0 0 .6rem;font-size:1rem}
.ordshow-empty{padding:1.2rem;text-align:center;color:var(--text-color-secondary)}
.ordshow-good{color:var(--green-500)}
.ordshow-bad{color:var(--red-500)}
@media (max-width:1200px){.ordshow-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:640px){.ordshow-kpis{grid-template-columns:1fr}}
</style>