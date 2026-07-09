<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);

const documentStyle = getComputedStyle(document.documentElement);
const borderColor = documentStyle.getPropertyValue('--surface-border');
const textMutedColor = documentStyle.getPropertyValue('--text-color-primary');
const chartBarColor = documentStyle.getPropertyValue('--p-button-primary-background');

const table = computed(() => details.value?.table ?? null);
const status = computed(() => details.value?.status ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const logs = computed(() => details.value?.logs?.data ?? []);
const orders = computed(() => details.value?.orders ?? []);

const chartData = computed(() => ({
    labels: (details.value?.chart ?? []).map((item) => item.month_name),
    datasets: [{ label: 'Consumo Mensal', backgroundColor: chartBarColor, data: (details.value?.chart ?? []).map((item) => item.total) }]
}));

const chartOptions = {
    maintainAspectRatio: false,
    aspectRatio: 0.8,
    scales: {
        x: { ticks: { color: textMutedColor.trim() }, grid: { color: 'transparent', borderColor: 'transparent' } },
        y: { ticks: { color: textMutedColor.trim() }, grid: { color: borderColor.trim(), borderColor: 'transparent', drawTicks: false } }
    }
};

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function formatMoney(value) {
    return Number(value ?? 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getSeverityById(id) {
    if (id === 1) return 'success';
    if (id === 2 || id === 4) return 'danger';
    if (id === 3) return 'warn';
    return 'info';
}

const getData = async (page = 1) => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/tables/${id}`, { params: { page, per_page: rowsPerPage.value } });
        details.value = response.data;
        currentPage.value = response.data.logs?.current_page ?? page;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Erro', detail: error.response?.data?.message || 'Não foi possível carregar os detalhes da mesa.', life: 3000 });
        goBackUsingBack();
    } finally {
        isLoading.value = false;
    }
};

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

onMounted(() => {
    getData(1);
});
</script>

<template>
    <div class="tblshow-loading" v-if="isLoading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão da mesa...</p>
    </div>

    <div class="tblshow-page" v-else-if="details">
        <div class="tblshow-card">
            <header class="tblshow-header">
                <div>
                    <p class="tblshow-eyebrow">Gestão de salas</p>
                    <h1>{{ table?.name || 'Mesa' }}</h1>
                    <p class="tblshow-subtitle">Capacidade: <strong>{{ table?.capacity ?? 0 }}</strong></p>
                    <p class="tblshow-subtitle">Criada em {{ formatDate(table?.created_at) }}</p>
                </div>
                <div class="tblshow-actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Editar mesa" icon="pi pi-pencil" severity="info" @click="router.push(`/admin/tables/${table?.id}/edit`)" />
                </div>
            </header>

            <section class="tblshow-kpis">
                <div class="tblshow-kpi"><span>Estado</span><strong><Tag :value="status?.name || '—'" :severity="getSeverityById(status?.id)" /></strong></div>
                <div class="tblshow-kpi"><span>Pedidos</span><strong>{{ metrics.orders_count ?? 0 }}</strong></div>
                <div class="tblshow-kpi"><span>Pedidos abertos</span><strong>{{ metrics.open_orders_count ?? 0 }}</strong></div>
                <div class="tblshow-kpi"><span>Consumo total</span><strong>{{ formatMoney(metrics.orders_total) }} MT</strong></div>
                <div class="tblshow-kpi"><span>Ticket médio</span><strong>{{ formatMoney(metrics.average_ticket) }} MT</strong></div>
                <div class="tblshow-kpi"><span>Consumo mês</span><strong>{{ formatMoney(metrics.monthly_total) }} MT</strong></div>
                <div class="tblshow-kpi"><span>Limite mês</span><strong>{{ formatMoney(metrics.monthly_limit) }} MT</strong></div>
                <div class="tblshow-kpi"><span>Saldo limite</span><strong :class="Number(metrics.monthly_balance) >= 0 ? 'tblshow-good':'tblshow-bad'">{{ formatMoney(metrics.monthly_balance) }} MT</strong></div>
                <div class="tblshow-kpi"><span>Último pedido</span><strong>{{ formatDate(metrics.last_order_at) }}</strong></div>
            </section>

            <section class="tblshow-panel">
                <h3>Consumo mensal</h3>
                <Chart type="bar" :data="chartData" :options="chartOptions" class="h-80" />
            </section>

            <section class="tblshow-panel">
                <h3>Pedidos recentes da mesa</h3>
                <DataTable :value="orders" dataKey="id" rowHover showGridlines>
                    <template #empty><div class="tblshow-empty">Sem pedidos registados.</div></template>
                    <Column header="Pedido" style="min-width: 7rem"><template #body="{ data }">#{{ data.id }}</template></Column>
                    <Column header="Estado" style="min-width: 10rem"><template #body="{ data }"><Tag :value="data.status?.name || '—'" :severity="getSeverityById(data.order_status_id)" /></template></Column>
                    <Column header="Operador" style="min-width: 12rem"><template #body="{ data }">{{ data.user?.name || '—' }}</template></Column>
                    <Column header="Total" style="min-width: 8rem"><template #body="{ data }">{{ formatMoney(data.total) }} MT</template></Column>
                    <Column header="Data" style="min-width: 10rem"><template #body="{ data }">{{ formatDate(data.created_at) }}</template></Column>
                </DataTable>
            </section>

            <section class="tblshow-panel">
                <h3>Histórico de alteração de limite</h3>
                <DataTable
                    :value="logs"
                    :paginator="true"
                    :rows="rowsPerPage"
                    :totalRecords="details.logs?.total ?? 0"
                    dataKey="id"
                    :lazy="true"
                    :rowHover="true"
                    :loading="isLoading"
                    :first="(currentPage - 1) * rowsPerPage"
                    @page="onPageChange"
                    showGridlines
                >
                    <template #empty><div class="tblshow-empty">Sem alterações de limite.</div></template>
                    <Column header="ID" style="min-width: 6rem"><template #body="{ data }">#{{ data.id }}</template></Column>
                    <Column header="Limite anterior" style="min-width: 10rem"><template #body="{ data }">{{ formatMoney(data.old_limit) }} MT</template></Column>
                    <Column header="Limite atual" style="min-width: 10rem"><template #body="{ data }">{{ formatMoney(data.new_limit) }} MT</template></Column>
                    <Column header="Utilizador" style="min-width: 12rem"><template #body="{ data }">{{ data.user?.name || '—' }}</template></Column>
                    <Column header="Data" style="min-width: 10rem"><template #body="{ data }">{{ formatDate(data.created_at) }}</template></Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.tblshow-loading{min-height:50vh;display:grid;place-items:center;gap:.75rem;color:var(--text-color-secondary)}
.tblshow-page{--b:color-mix(in srgb,var(--surface-border) 70%,var(--text-color) 30%);--bs:color-mix(in srgb,var(--surface-border) 85%,transparent);--bg:color-mix(in srgb,var(--surface-ground) 75%,var(--text-color) 5%)}
.tblshow-card{display:flex;flex-direction:column;gap:1rem;padding:1.1rem;border:1px solid var(--b);border-radius:1rem;background:var(--surface-card);box-shadow:0 1px 2px rgba(15,23,42,.05),0 0 0 1px var(--bs)}
.tblshow-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.tblshow-actions{display:flex;gap:.5rem;flex-wrap:wrap}
.tblshow-eyebrow{margin:0;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--primary-color)}
.tblshow-header h1{margin:.15rem 0 0;font-size:1.5rem}
.tblshow-subtitle{margin:.2rem 0 0;color:var(--text-color-secondary)}
.tblshow-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.tblshow-kpi{display:flex;flex-direction:column;gap:.2rem;padding:.75rem;border:1px solid var(--bs);border-radius:.8rem;background:var(--bg)}
.tblshow-panel h3{margin:0 0 .6rem;font-size:1rem}
.tblshow-empty{padding:1.2rem;text-align:center;color:var(--text-color-secondary)}
.tblshow-good{color:var(--green-500)}
.tblshow-bad{color:var(--red-500)}
@media (max-width:900px){.tblshow-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){.tblshow-kpis{grid-template-columns:1fr}}
</style>
