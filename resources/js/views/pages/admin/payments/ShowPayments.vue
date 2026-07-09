<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

const payment = computed(() => details.value?.payment ?? null);
const method = computed(() => details.value?.method ?? null);
const customer = computed(() => details.value?.customer ?? null);
const order = computed(() => details.value?.order ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const orderPayments = computed(() => details.value?.order_payments ?? []);

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
        const response = await axios.get(`/api/payments/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do pagamento.',
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
    <div v-if="isLoading" class="payshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do pagamento...</p>
    </div>

    <div v-else-if="details" class="payshow-page">
        <div class="payshow-card">
            <header class="payshow-header">
                <div>
                    <p class="payshow-eyebrow">Gestão financeira</p>
                    <h1>Pagamento #{{ payment?.id }}</h1>
                    <p class="payshow-subtitle">Método: <strong>{{ method?.name || '—' }}</strong></p>
                    <p class="payshow-subtitle">Cliente: <strong>{{ customer?.name || '—' }}</strong></p>
                    <p class="payshow-subtitle">Pedido: <strong>#{{ order?.id || '—' }}</strong></p>
                    <p class="payshow-subtitle">Criado em {{ formatDate(payment?.created_at) }}</p>
                </div>
                <div class="payshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Ver listagem" icon="pi pi-list" severity="info" @click="router.push('/admin/payments')" />
                </div>
            </header>

            <section class="payshow-kpis">
                <div class="payshow-kpi"><span>Valor</span><strong>{{ formatMoney(payment?.amount) }} MT</strong></div>
                <div class="payshow-kpi"><span>Total pedido</span><strong>{{ formatMoney(metrics.order_total) }} MT</strong></div>
                <div class="payshow-kpi"><span>Total pago pedido</span><strong>{{ formatMoney(metrics.order_payments_total) }} MT</strong></div>
                <div class="payshow-kpi"><span>Saldo pedido</span><strong :class="Number(metrics.order_balance) > 0 ? 'payshow-bad' : 'payshow-good'">{{ formatMoney(metrics.order_balance) }} MT</strong></div>
                <div class="payshow-kpi"><span>Uso método (qtd)</span><strong>{{ metrics.method_usage_count ?? 0 }}</strong></div>
                <div class="payshow-kpi"><span>Uso método (total)</span><strong>{{ formatMoney(metrics.method_usage_total) }} MT</strong></div>
            </section>

            <section class="payshow-panel">
                <h3>Pagamentos do mesmo pedido</h3>
                <DataTable :value="orderPayments" dataKey="id" rowHover showGridlines>
                    <template #empty><div class="payshow-empty">Sem outros pagamentos para este pedido.</div></template>
                    <Column header="ID" style="min-width: 5rem"><template #body="{ data }">#{{ data.id }}</template></Column>
                    <Column header="Método" style="min-width: 12rem"><template #body="{ data }">{{ data.method?.name || '—' }}</template></Column>
                    <Column header="Valor" style="min-width: 8rem"><template #body="{ data }">{{ formatMoney(data.amount) }} MT</template></Column>
                    <Column header="Data" style="min-width: 10rem"><template #body="{ data }">{{ formatDate(data.created_at) }}</template></Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.payshow-loading{min-height:50vh;display:grid;place-items:center;gap:.75rem;color:var(--text-color-secondary)}
.payshow-page{--b:color-mix(in srgb,var(--surface-border) 70%,var(--text-color) 30%);--bs:color-mix(in srgb,var(--surface-border) 85%,transparent);--bg:color-mix(in srgb,var(--surface-ground) 75%,var(--text-color) 5%)}
.payshow-card{display:flex;flex-direction:column;gap:1rem;padding:1.1rem;border:1px solid var(--b);border-radius:1rem;background:var(--surface-card);box-shadow:0 1px 2px rgba(15,23,42,.05),0 0 0 1px var(--bs)}
.payshow-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.payshow-header__actions{display:flex;gap:.5rem;flex-wrap:wrap}
.payshow-eyebrow{margin:0;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--primary-color)}
.payshow-header h1{margin:.15rem 0 0;font-size:1.5rem}
.payshow-subtitle{margin:.2rem 0 0;color:var(--text-color-secondary)}
.payshow-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.75rem}
.payshow-kpi{display:flex;flex-direction:column;gap:.2rem;padding:.75rem;border:1px solid var(--bs);border-radius:.8rem;background:var(--bg)}
.payshow-panel h3{margin:0 0 .6rem;font-size:1rem}
.payshow-empty{padding:1.2rem;text-align:center;color:var(--text-color-secondary)}
.payshow-good{color:var(--green-500)}
.payshow-bad{color:var(--red-500)}
@media (max-width:1200px){.payshow-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:640px){.payshow-kpis{grid-template-columns:1fr}}
</style>