<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

const method = computed(() => details.value?.method ?? null);
const status = computed(() => details.value?.status ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const recentPayments = computed(() => details.value?.recent_payments ?? []);

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

function getStatusSeverity(statusId) {
    switch (Number(statusId)) {
        case 1: return 'success';
        case 2: return 'danger';
        case 3: return 'warn';
        default: return 'secondary';
    }
}

function yesNo(value) {
    return value ? 'Sim' : 'Não';
}

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/paymentmethods/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar o método de pagamento.',
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
    <div v-if="isLoading" class="pmshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar método de pagamento...</p>
    </div>

    <div v-else-if="details" class="pmshow-page">
        <div class="pmshow-card">
            <header class="pmshow-header">
                <div>
                    <p class="pmshow-eyebrow">Gestão financeira</p>
                    <h1>{{ method?.name || 'Método de pagamento' }}</h1>
                    <p class="pmshow-subtitle">Criado em {{ formatDate(method?.created_at) }}</p>
                </div>
                <div class="pmshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Editar" icon="pi pi-pencil" severity="info" @click="router.push(`/admin/paymentmethods/${method?.id}/edit`)" />
                    <Button label="Ver pagamentos" icon="pi pi-list" severity="secondary" outlined @click="router.push('/admin/payments')" />
                </div>
            </header>

            <section class="pmshow-kpis">
                <div class="pmshow-kpi">
                    <span class="pmshow-kpi__label">Estado</span>
                    <Tag v-if="status?.name" :value="status.name" :severity="getStatusSeverity(method?.status_id)" />
                    <strong v-else>—</strong>
                </div>
                <div class="pmshow-kpi">
                    <span class="pmshow-kpi__label">Pagamentos (total)</span>
                    <strong>{{ metrics.payments_count ?? 0 }}</strong>
                </div>
                <div class="pmshow-kpi pmshow-kpi--highlight">
                    <span class="pmshow-kpi__label">Volume total</span>
                    <strong>{{ formatMoney(metrics.payments_total) }} MT</strong>
                </div>
                <div class="pmshow-kpi">
                    <span class="pmshow-kpi__label">Este mês (qtd)</span>
                    <strong>{{ metrics.payments_this_month_count ?? 0 }}</strong>
                </div>
                <div class="pmshow-kpi">
                    <span class="pmshow-kpi__label">Este mês (valor)</span>
                    <strong>{{ formatMoney(metrics.payments_this_month_total) }} MT</strong>
                </div>
                <div class="pmshow-kpi">
                    <span class="pmshow-kpi__label">Último pagamento</span>
                    <strong class="pmshow-kpi__text">{{ formatDate(metrics.latest_payment_at) }}</strong>
                </div>
            </section>

            <section class="pmshow-info">
                <h3>Configuração</h3>
                <div class="pmshow-info__grid">
                    <p><strong>Conta na receita:</strong> {{ yesNo(method?.counts_in_revenue) }}</p>
                    <p><strong>É crédito:</strong> {{ yesNo(method?.is_credit) }}</p>
                    <p><strong>Estado:</strong> {{ status?.name || '—' }}</p>
                    <p><strong>ID:</strong> #{{ method?.id }}</p>
                </div>
            </section>

            <section class="pmshow-panel">
                <h3>Pagamentos recentes</h3>
                <DataTable :value="recentPayments" dataKey="id" rowHover stripedRows responsiveLayout="scroll">
                    <template #empty><div class="pmshow-empty">Sem pagamentos com este método.</div></template>
                    <Column header="Pagamento" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Button
                                :label="'#' + data.id"
                                link
                                size="small"
                                @click="router.push(`/admin/payments/${data.id}`)"
                            />
                        </template>
                    </Column>
                    <Column header="Pedido" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Button
                                v-if="data.order_id"
                                :label="'#' + data.order_id"
                                link
                                size="small"
                                @click="router.push(`/admin/orders/${data.order_id}`)"
                            />
                            <span v-else>—</span>
                        </template>
                    </Column>
                    <Column header="Cliente" style="min-width: 10rem">
                        <template #body="{ data }">{{ data.customer_name || '—' }}</template>
                    </Column>
                    <Column header="Valor" style="min-width: 8rem">
                        <template #body="{ data }">{{ formatMoney(data.amount) }} MT</template>
                    </Column>
                    <Column header="Data" style="min-width: 10rem">
                        <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.pmshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.pmshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.pmshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.pmshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.pmshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.pmshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.pmshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.pmshow-subtitle { margin: .2rem 0 0; color: var(--text-color-secondary); }
.pmshow-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; }
.pmshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.pmshow-kpi--highlight { border-color: color-mix(in srgb, var(--primary-color) 35%, var(--bs)); }
.pmshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.pmshow-kpi__text { font-size: .9rem; }
.pmshow-info, .pmshow-panel { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.pmshow-info h3, .pmshow-panel h3 { margin: 0 0 .6rem; font-size: 1rem; }
.pmshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem .9rem; }
.pmshow-info__grid p { margin: 0; }
.pmshow-empty { padding: 1.2rem; text-align: center; color: var(--text-color-secondary); }
@media (max-width: 1200px) { .pmshow-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 640px) { .pmshow-kpis, .pmshow-info__grid { grid-template-columns: 1fr; } }
</style>
