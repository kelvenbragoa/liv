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

function displayValue(value) {
    return value || '—';
}

const customer = computed(() => details.value?.customer ?? null);
const metrics = computed(() => details.value?.metrics ?? {});

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/customers/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do cliente.',
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
    <div v-if="isLoading" class="custshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do cliente...</p>
    </div>

    <div v-else-if="details" class="custshow-page">
        <div class="custshow-card">
            <header class="custshow-header">
                <div>
                    <p class="custshow-eyebrow">Gestão de clientes</p>
                    <h1>{{ customer?.name || 'Cliente' }}</h1>
                    <p class="custshow-subtitle">Criado em {{ formatDate(customer?.created_at) }}</p>
                </div>
                <div class="custshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Editar cliente" icon="pi pi-pencil" severity="info" @click="router.push(`/admin/customers/${customer?.id}/edit`)" />
                </div>
            </header>

            <section class="custshow-kpis">
                <div class="custshow-kpi">
                    <span class="custshow-kpi__label">Pagamentos</span>
                    <strong>{{ metrics.payments_count ?? 0 }}</strong>
                </div>
                <div class="custshow-kpi">
                    <span class="custshow-kpi__label">Total pago</span>
                    <strong>{{ formatMoney(metrics.payments_total) }} MT</strong>
                </div>
                <div class="custshow-kpi">
                    <span class="custshow-kpi__label">Reservas</span>
                    <strong>{{ metrics.reservations_count ?? 0 }}</strong>
                </div>
                <div class="custshow-kpi">
                    <span class="custshow-kpi__label">Liquidações crédito</span>
                    <strong>{{ metrics.credit_settlements_count ?? 0 }}</strong>
                </div>
                <div class="custshow-kpi">
                    <span class="custshow-kpi__label">Crédito em aberto</span>
                    <strong :class="Number(metrics.credit_remaining) > 0 ? 'custshow-bad' : 'custshow-good'">
                        {{ formatMoney(metrics.credit_remaining) }} MT
                    </strong>
                </div>
            </section>

            <section class="custshow-info">
                <h3>Informação do cliente</h3>
                <div class="custshow-info__grid">
                    <p><strong>Email:</strong> {{ displayValue(customer?.email) }}</p>
                    <p><strong>Telefone:</strong> {{ displayValue(customer?.mobile) }}</p>
                    <p><strong>NIF:</strong> {{ displayValue(customer?.tax_number) }}</p>
                    <p><strong>Última reserva:</strong> {{ formatDate(metrics.latest_reservation) }}</p>
                    <p class="custshow-address"><strong>Endereço:</strong> {{ displayValue(customer?.address) }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.custshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.custshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.custshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.custshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.custshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.custshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.custshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.custshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.custshow-kpis { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: .75rem; }
.custshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.custshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.custshow-info { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.custshow-info h3 { margin: 0 0 .6rem; font-size: 1rem; }
.custshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .5rem .9rem; }
.custshow-info__grid p { margin: 0; }
.custshow-address { grid-column: span 2; }
.custshow-good { color: var(--green-500); }
.custshow-bad { color: var(--red-500); }
@media (max-width: 1100px) { .custshow-kpis { grid-template-columns: repeat(2, minmax(0,1fr)); } }
@media (max-width: 640px) { .custshow-kpis, .custshow-info__grid { grid-template-columns: 1fr; } .custshow-address { grid-column: span 1; } }
</style>