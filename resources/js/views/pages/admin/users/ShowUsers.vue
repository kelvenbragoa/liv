<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

const user = computed(() => details.value?.user ?? null);
const role = computed(() => details.value?.role ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const recentOrders = computed(() => details.value?.recent_orders ?? []);
const recentCashRegisters = computed(() => details.value?.recent_cash_registers ?? []);

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

function getRoleSeverity(roleName) {
    const name = (roleName || '').toLowerCase();
    if (name.includes('admin')) return 'danger';
    if (name.includes('gerente') || name.includes('manager')) return 'warn';
    if (name.includes('caixa') || name.includes('cash')) return 'info';
    return 'secondary';
}

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/users/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar os detalhes do utilizador.',
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
    <div v-if="isLoading" class="usrshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do utilizador...</p>
    </div>

    <div v-else-if="details" class="usrshow-page">
        <div class="usrshow-card">
            <header class="usrshow-header">
                <div>
                    <p class="usrshow-eyebrow">Gestão de utilizadores</p>
                    <h1>{{ user?.name || 'Utilizador' }}</h1>
                    <p class="usrshow-subtitle">{{ user?.email }}</p>
                    <p class="usrshow-subtitle">Criado em {{ formatDate(user?.created_at) }}</p>
                </div>
                <div class="usrshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button label="Editar" icon="pi pi-pencil" severity="info" @click="router.push(`/admin/users/${user?.id}/edit`)" />
                </div>
            </header>

            <section class="usrshow-kpis">
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Perfil</span>
                    <Tag v-if="role?.name" :value="role.name" :severity="getRoleSeverity(role.name)" />
                    <strong v-else>—</strong>
                </div>
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Pedidos</span>
                    <strong>{{ metrics.orders_count ?? 0 }}</strong>
                </div>
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Itens registados</span>
                    <strong>{{ metrics.order_items_count ?? 0 }}</strong>
                </div>
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Total vendido</span>
                    <strong>{{ formatMoney(metrics.order_items_total) }} MT</strong>
                </div>
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Caixas</span>
                    <strong>{{ metrics.cash_registers_count ?? 0 }}</strong>
                </div>
                <div class="usrshow-kpi">
                    <span class="usrshow-kpi__label">Caixas abertos</span>
                    <strong :class="Number(metrics.open_cash_registers_count) > 0 ? 'usrshow-warn' : ''">
                        {{ metrics.open_cash_registers_count ?? 0 }}
                    </strong>
                </div>
            </section>

            <section class="usrshow-info">
                <h3>Informação da conta</h3>
                <div class="usrshow-info__grid">
                    <p><strong>Email:</strong> {{ user?.email || '—' }}</p>
                    <p><strong>Perfil:</strong> {{ role?.name || '—' }}</p>
                    <p><strong>Último pedido:</strong> {{ formatDate(metrics.latest_order_at) }}</p>
                    <p><strong>Email verificado:</strong> {{ user?.email_verified_at ? formatDate(user.email_verified_at) : 'Não' }}</p>
                </div>
            </section>

            <section class="usrshow-panel">
                <h3>Pedidos recentes</h3>
                <DataTable :value="recentOrders" dataKey="id" rowHover stripedRows responsiveLayout="scroll">
                    <template #empty><div class="usrshow-empty">Sem pedidos registados por este utilizador.</div></template>
                    <Column header="Pedido" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Button
                                :label="'#' + data.id"
                                link
                                size="small"
                                @click="router.push(`/admin/orders/${data.id}`)"
                            />
                        </template>
                    </Column>
                    <Column header="Tipo" style="min-width: 9rem">
                        <template #body="{ data }">
                            {{ data.is_quick_sell ? 'Venda rápida' : (data.table_name || 'Mesa') }}
                        </template>
                    </Column>
                    <Column header="Estado" style="min-width: 8rem">
                        <template #body="{ data }">{{ data.status_name || '—' }}</template>
                    </Column>
                    <Column header="Total" style="min-width: 8rem">
                        <template #body="{ data }">{{ formatMoney(data.total) }} MT</template>
                    </Column>
                    <Column header="Data" style="min-width: 10rem">
                        <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                    </Column>
                </DataTable>
            </section>

            <section class="usrshow-panel">
                <h3>Caixas recentes</h3>
                <DataTable :value="recentCashRegisters" dataKey="id" rowHover stripedRows responsiveLayout="scroll">
                    <template #empty><div class="usrshow-empty">Sem caixas associados.</div></template>
                    <Column header="Caixa" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Button
                                :label="'#' + data.id"
                                link
                                size="small"
                                @click="router.push(`/admin/cashregisters/${data.id}`)"
                            />
                        </template>
                    </Column>
                    <Column header="Estado" style="min-width: 8rem">
                        <template #body="{ data }">{{ data.status_name || '—' }}</template>
                    </Column>
                    <Column header="Abertura" style="min-width: 8rem">
                        <template #body="{ data }">{{ formatMoney(data.opening_balance) }} MT</template>
                    </Column>
                    <Column header="Fecho" style="min-width: 8rem">
                        <template #body="{ data }">{{ formatMoney(data.closing_balance) }} MT</template>
                    </Column>
                    <Column header="Aberto em" style="min-width: 10rem">
                        <template #body="{ data }">{{ formatDate(data.opened_at) }}</template>
                    </Column>
                    <Column header="Fechado em" style="min-width: 10rem">
                        <template #body="{ data }">{{ formatDate(data.closed_at) }}</template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.usrshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.usrshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.usrshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.usrshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.usrshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.usrshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.usrshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.usrshow-subtitle { margin: .2rem 0 0; color: var(--text-color-secondary); }
.usrshow-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; }
.usrshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.usrshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.usrshow-info, .usrshow-panel { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.usrshow-info h3, .usrshow-panel h3 { margin: 0 0 .6rem; font-size: 1rem; }
.usrshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem .9rem; }
.usrshow-info__grid p { margin: 0; }
.usrshow-empty { padding: 1.2rem; text-align: center; color: var(--text-color-secondary); }
.usrshow-warn { color: var(--orange-500); }
@media (max-width: 1200px) { .usrshow-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 640px) { .usrshow-kpis, .usrshow-info__grid { grid-template-columns: 1fr; } }
</style>
