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

const supplier = computed(() => details.value?.supplier ?? null);
const metrics = computed(() => details.value?.metrics ?? {});

const movementBalance = computed(() => Number(metrics.value.entry_notes_count ?? 0) - Number(metrics.value.exit_notes_count ?? 0));

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/suppliers/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do fornecedor.',
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
    <div v-if="isLoading" class="supshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do fornecedor...</p>
    </div>

    <div v-else-if="details" class="supshow-page">
        <div class="supshow-card">
            <header class="supshow-header">
                <div>
                    <p class="supshow-eyebrow">Gestão de fornecedores</p>
                    <h1>{{ supplier?.name || 'Fornecedor' }}</h1>
                    <p class="supshow-subtitle">Criado em {{ formatDate(supplier?.created_at) }}</p>
                </div>
                <div class="supshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Editar fornecedor" icon="pi pi-pencil" severity="info" @click="router.push(`/admin/suppliers/${supplier?.id}/edit`)" />
                </div>
            </header>

            <section class="supshow-kpis">
                <div class="supshow-kpi">
                    <span class="supshow-kpi__label">Notas de entrada</span>
                    <strong>{{ metrics.entry_notes_count ?? 0 }}</strong>
                </div>
                <div class="supshow-kpi">
                    <span class="supshow-kpi__label">Notas de saída</span>
                    <strong>{{ metrics.exit_notes_count ?? 0 }}</strong>
                </div>
                <div class="supshow-kpi">
                    <span class="supshow-kpi__label">Encomendas</span>
                    <strong>{{ metrics.purchase_orders_count ?? 0 }}</strong>
                </div>
                <div class="supshow-kpi">
                    <span class="supshow-kpi__label">Total encomendado</span>
                    <strong>{{ formatMoney(metrics.purchase_orders_total) }} MT</strong>
                </div>
                <div class="supshow-kpi">
                    <span class="supshow-kpi__label">Saldo movimento</span>
                    <strong :class="movementBalance >= 0 ? 'supshow-good' : 'supshow-bad'">{{ movementBalance }}</strong>
                </div>
            </section>

            <section class="supshow-info">
                <h3>Informação de contacto</h3>
                <div class="supshow-info__grid">
                    <p><strong>Email:</strong> {{ displayValue(supplier?.email) }}</p>
                    <p><strong>Telefone:</strong> {{ displayValue(supplier?.mobile) }}</p>
                    <p><strong>Website:</strong> {{ displayValue(supplier?.website) }}</p>
                    <p><strong>NUIT:</strong> {{ displayValue(supplier?.nuit) }}</p>
                    <p><strong>Cidade:</strong> {{ displayValue(supplier?.city) }}</p>
                    <p><strong>País:</strong> {{ displayValue(supplier?.country) }}</p>
                    <p class="supshow-address"><strong>Endereço:</strong> {{ displayValue(supplier?.address) }}</p>
                    <p><strong>Última encomenda:</strong> {{ formatDate(metrics.latest_purchase_order_date) }}</p>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.supshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.supshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.supshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.supshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.supshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.supshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.supshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.supshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.supshow-kpis { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: .75rem; }
.supshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.supshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.supshow-info { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.supshow-info h3 { margin: 0 0 .6rem; font-size: 1rem; }
.supshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .5rem .9rem; }
.supshow-info__grid p { margin: 0; }
.supshow-address { grid-column: span 2; }
.supshow-good { color: var(--green-500); }
.supshow-bad { color: var(--red-500); }
@media (max-width: 1100px) { .supshow-kpis { grid-template-columns: repeat(2, minmax(0,1fr)); } }
@media (max-width: 640px) { .supshow-kpis, .supshow-info__grid { grid-template-columns: 1fr; } .supshow-address { grid-column: span 1; } }
</style>