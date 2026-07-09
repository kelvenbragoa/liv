<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);

const isLoadingQuickSellTab = ref(false);
const isLoadingTableTab = ref(false);
const isLoadingPaymentTab = ref(false);

const quicksellreport = ref({ data: [] });
const tablesellreport = ref({ data: [] });
const paymentreport = ref({ data: [] });

const totalRecordsQuickSell = ref(0);
const totalRecordsTable = ref(0);
const totalRecordsPayments = ref(0);

const currentPageQuickSell = ref(1);
const currentPageTableSell = ref(1);
const currentPagePayments = ref(1);
const rowsPerPage = ref(100);

const showDialogOrder = ref(false);
const selectedOrder = ref(null);

const cashRegister = computed(() => details.value?.cash_register ?? null);
const user = computed(() => details.value?.user ?? null);
const status = computed(() => details.value?.status ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const paymentsByMethod = computed(() => details.value?.payments_by_method ?? []);

const cashRegisterId = computed(() => router.currentRoute.value.params.id);

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
        case 2: return 'secondary';
        default: return 'info';
    }
}

function differenceClass(value) {
    const n = Number(value ?? 0);
    if (n > 0) return 'crshow-good';
    if (n < 0) return 'crshow-bad';
    return 'crshow-neutral';
}

function seeOrderItens(order) {
    selectedOrder.value = order;
    showDialogOrder.value = true;
}

const getData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/cashregister/${cashRegisterId.value}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar o caixa.',
            life: 3000
        });
        goBackUsingBack();
    } finally {
        isLoading.value = false;
    }
};

const getQuickSellData = async (page = 1) => {
    isLoadingQuickSellTab.value = true;
    try {
        const response = await axios.get('/api/cashregisters/quicksellreport', {
            params: { page, user: cashRegisterId.value }
        });
        quicksellreport.value = response.data;
        totalRecordsQuickSell.value = response.data.total ?? 0;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Falha ao carregar vendas rápidas.',
            life: 3000
        });
    } finally {
        isLoadingQuickSellTab.value = false;
    }
};

const getTableData = async (page = 1) => {
    isLoadingTableTab.value = true;
    try {
        const response = await axios.get('/api/cashregisters/tablesellreport', {
            params: { page, user: cashRegisterId.value }
        });
        tablesellreport.value = response.data;
        totalRecordsTable.value = response.data.total ?? 0;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Falha ao carregar vendas em mesa.',
            life: 3000
        });
    } finally {
        isLoadingTableTab.value = false;
    }
};

const getPaymentsData = async (page = 1) => {
    isLoadingPaymentTab.value = true;
    try {
        const response = await axios.get('/api/cashregisters/paymentreport', {
            params: { page, user: cashRegisterId.value }
        });
        paymentreport.value = response.data;
        totalRecordsPayments.value = response.data.total ?? 0;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Falha ao carregar pagamentos.',
            life: 3000
        });
    } finally {
        isLoadingPaymentTab.value = false;
    }
};

const onPageChangeQuickSell = (event) => {
    currentPageQuickSell.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getQuickSellData(currentPageQuickSell.value);
};

const onPageChangeTableSell = (event) => {
    currentPageTableSell.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getTableData(currentPageTableSell.value);
};

const onPageChangePayments = (event) => {
    currentPagePayments.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getPaymentsData(currentPagePayments.value);
};

onMounted(async () => {
    await getData();
    getQuickSellData();
    getTableData();
    getPaymentsData();
});
</script>

<template>
    <div v-if="isLoading" class="crshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar visão do caixa...</p>
    </div>

    <div v-else-if="details" class="crshow-page">
        <div class="crshow-card">
            <header class="crshow-header">
                <div>
                    <p class="crshow-eyebrow">Gestão de caixa</p>
                    <h1>Caixa #{{ cashRegister?.id }}</h1>
                    <p class="crshow-subtitle">
                        Operador: <strong>{{ user?.name || '—' }}</strong>
                        · Aberto: {{ formatDate(cashRegister?.opened_at) }}
                        · Fechado: {{ formatDate(cashRegister?.closed_at) }}
                    </p>
                </div>
                <div class="crshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        v-if="user?.id"
                        label="Ver utilizador"
                        icon="pi pi-user"
                        severity="secondary"
                        outlined
                        @click="router.push(`/admin/users/${user.id}`)"
                    />
                </div>
            </header>

            <section class="crshow-status">
                <Tag
                    v-if="status?.name"
                    :value="status.name"
                    :severity="getStatusSeverity(status?.id)"
                />
                <span class="crshow-status__meta">Saldo inicial: {{ formatMoney(cashRegister?.opening_balance) }} MT</span>
            </section>

            <section class="crshow-kpis">
                <div class="crshow-kpi crshow-kpi--highlight">
                    <span class="crshow-kpi__label">Total vendas</span>
                    <strong>{{ formatMoney(metrics.total_sales) }} MT</strong>
                </div>
                <div class="crshow-kpi">
                    <span class="crshow-kpi__label">Pedidos</span>
                    <strong>{{ metrics.orders_count ?? 0 }}</strong>
                </div>
                <div class="crshow-kpi">
                    <span class="crshow-kpi__label">Ticket médio</span>
                    <strong>{{ formatMoney(metrics.average_ticket) }} MT</strong>
                </div>
                <div class="crshow-kpi">
                    <span class="crshow-kpi__label">Mesas</span>
                    <strong>{{ metrics.table_orders_count ?? 0 }} · {{ formatMoney(metrics.table_orders_amount) }} MT</strong>
                </div>
                <div class="crshow-kpi">
                    <span class="crshow-kpi__label">Venda rápida</span>
                    <strong>{{ metrics.quick_sell_orders_count ?? 0 }} · {{ formatMoney(metrics.quick_sell_orders_amount) }} MT</strong>
                </div>
                <div class="crshow-kpi">
                    <span class="crshow-kpi__label">Pagamentos</span>
                    <strong>{{ metrics.payments_count ?? 0 }} · {{ formatMoney(metrics.payments_amount) }} MT</strong>
                </div>
            </section>

            <section class="crshow-info">
                <h3>Fecho de caixa</h3>
                <div class="crshow-info__grid">
                    <p><strong>Saldo esperado:</strong> {{ formatMoney(metrics.expected_balance) }} MT</p>
                    <p><strong>Saldo sistema:</strong> {{ formatMoney(cashRegister?.automatic_closing_balance) }} MT</p>
                    <p><strong>Saldo declarado:</strong> {{ formatMoney(cashRegister?.closing_balance) }} MT</p>
                    <p>
                        <strong>Diferença:</strong>
                        <span :class="differenceClass(cashRegister?.difference)">{{ formatMoney(cashRegister?.difference) }} MT</span>
                    </p>
                </div>
            </section>

            <section v-if="paymentsByMethod.length" class="crshow-panel">
                <h3>Pagamentos por método</h3>
                <DataTable :value="paymentsByMethod" dataKey="method_id" rowHover stripedRows responsiveLayout="scroll">
                    <Column header="Método" style="min-width: 12rem">
                        <template #body="{ data }">
                            <Button
                                v-if="data.method_id"
                                :label="data.method_name || '—'"
                                link
                                size="small"
                                @click="router.push(`/admin/paymentmethods/${data.method_id}`)"
                            />
                            <span v-else>—</span>
                        </template>
                    </Column>
                    <Column header="Quantidade" style="min-width: 8rem">
                        <template #body="{ data }">{{ data.payments_count }}</template>
                    </Column>
                    <Column header="Total" style="min-width: 8rem">
                        <template #body="{ data }">{{ formatMoney(data.payments_total) }} MT</template>
                    </Column>
                </DataTable>
            </section>

            <section class="crshow-panel">
                <Tabs value="0">
                    <TabList>
                        <Tab value="0">Vendas rápidas</Tab>
                        <Tab value="1">Vendas em mesas</Tab>
                        <Tab value="2">Pagamentos</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="0">
                            <DataTable
                                :value="quicksellreport.data"
                                :paginator="true"
                                :rows="rowsPerPage"
                                :totalRecords="totalRecordsQuickSell"
                                dataKey="id"
                                lazy
                                rowHover
                                stripedRows
                                :loading="isLoadingQuickSellTab"
                                :first="(currentPageQuickSell - 1) * rowsPerPage"
                                @page="onPageChangeQuickSell"
                                responsiveLayout="scroll"
                            >
                                <template #empty><div class="crshow-empty">Sem vendas rápidas neste caixa.</div></template>
                                <Column header="Pedido" style="min-width: 6rem">
                                    <template #body="{ data }">#{{ data.id }}</template>
                                </Column>
                                <Column header="Operador" style="min-width: 10rem">
                                    <template #body="{ data }">{{ data.user?.name || '—' }}</template>
                                </Column>
                                <Column header="Estado" style="min-width: 8rem">
                                    <template #body="{ data }">{{ data.status?.name || '—' }}</template>
                                </Column>
                                <Column header="Itens" style="min-width: 6rem">
                                    <template #body="{ data }">{{ data.itens?.length ?? 0 }}</template>
                                </Column>
                                <Column header="Total" style="min-width: 8rem">
                                    <template #body="{ data }">{{ formatMoney(data.total) }} MT</template>
                                </Column>
                                <Column header="Data" style="min-width: 10rem">
                                    <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                                </Column>
                                <Column header="Ações" style="min-width: 6rem">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-eye" text rounded @click="seeOrderItens(data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <TabPanel value="1">
                            <DataTable
                                :value="tablesellreport.data"
                                :paginator="true"
                                :rows="rowsPerPage"
                                :totalRecords="totalRecordsTable"
                                dataKey="id"
                                lazy
                                rowHover
                                stripedRows
                                :loading="isLoadingTableTab"
                                :first="(currentPageTableSell - 1) * rowsPerPage"
                                @page="onPageChangeTableSell"
                                responsiveLayout="scroll"
                            >
                                <template #empty><div class="crshow-empty">Sem vendas em mesa neste caixa.</div></template>
                                <Column header="Pedido" style="min-width: 6rem">
                                    <template #body="{ data }">#{{ data.id }}</template>
                                </Column>
                                <Column header="Mesa" style="min-width: 8rem">
                                    <template #body="{ data }">{{ data.table?.name || '—' }}</template>
                                </Column>
                                <Column header="Operador" style="min-width: 10rem">
                                    <template #body="{ data }">{{ data.user?.name || '—' }}</template>
                                </Column>
                                <Column header="Estado" style="min-width: 8rem">
                                    <template #body="{ data }">{{ data.status?.name || '—' }}</template>
                                </Column>
                                <Column header="Total" style="min-width: 8rem">
                                    <template #body="{ data }">{{ formatMoney(data.total) }} MT</template>
                                </Column>
                                <Column header="Data" style="min-width: 10rem">
                                    <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                                </Column>
                                <Column header="Ações" style="min-width: 6rem">
                                    <template #body="{ data }">
                                        <Button icon="pi pi-eye" text rounded @click="seeOrderItens(data)" />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <TabPanel value="2">
                            <DataTable
                                :value="paymentreport.data"
                                :paginator="true"
                                :rows="rowsPerPage"
                                :totalRecords="totalRecordsPayments"
                                dataKey="id"
                                lazy
                                rowHover
                                stripedRows
                                :loading="isLoadingPaymentTab"
                                :first="(currentPagePayments - 1) * rowsPerPage"
                                @page="onPageChangePayments"
                                responsiveLayout="scroll"
                            >
                                <template #empty><div class="crshow-empty">Sem pagamentos neste caixa.</div></template>
                                <Column header="ID" style="min-width: 5rem">
                                    <template #body="{ data }">#{{ data.id }}</template>
                                </Column>
                                <Column header="Pedido" style="min-width: 6rem">
                                    <template #body="{ data }">#{{ data.order_id }}</template>
                                </Column>
                                <Column header="Origem" style="min-width: 10rem">
                                    <template #body="{ data }">
                                        {{ data.order?.table_id == null ? 'Venda rápida' : (data.order?.table?.name || 'Mesa') }}
                                    </template>
                                </Column>
                                <Column header="Método" style="min-width: 10rem">
                                    <template #body="{ data }">{{ data.method?.name || '—' }}</template>
                                </Column>
                                <Column header="Valor" style="min-width: 8rem">
                                    <template #body="{ data }">{{ formatMoney(data.amount) }} MT</template>
                                </Column>
                                <Column header="Data" style="min-width: 10rem">
                                    <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </section>
        </div>
    </div>

    <Dialog
        v-model:visible="showDialogOrder"
        :header="selectedOrder ? `Itens do pedido #${selectedOrder.id}` : 'Itens do pedido'"
        modal
        :style="{ width: 'min(720px, 95vw)' }"
    >
        <DataTable v-if="selectedOrder" :value="selectedOrder.itens" responsiveLayout="scroll">
            <Column header="Produto" style="min-width: 12rem">
                <template #body="{ data }">{{ data.product?.name || '—' }}</template>
            </Column>
            <Column header="Qtd" style="min-width: 5rem">
                <template #body="{ data }">{{ data.quantity }}</template>
            </Column>
            <Column header="Preço" style="min-width: 7rem">
                <template #body="{ data }">{{ formatMoney(data.price) }} MT</template>
            </Column>
            <Column header="Total" style="min-width: 7rem">
                <template #body="{ data }">{{ formatMoney(data.total) }} MT</template>
            </Column>
        </DataTable>
    </Dialog>
</template>

<style scoped>
.crshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.crshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.crshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.crshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.crshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.crshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.crshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.crshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.crshow-status { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; padding: .65rem .9rem; border: 1px solid var(--bs); border-radius: .75rem; background: var(--bg); }
.crshow-status__meta { color: var(--text-color-secondary); font-size: .9rem; }
.crshow-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; }
.crshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.crshow-kpi--highlight { border-color: color-mix(in srgb, var(--primary-color) 35%, var(--bs)); }
.crshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.crshow-info, .crshow-panel { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.crshow-info h3, .crshow-panel h3 { margin: 0 0 .6rem; font-size: 1rem; }
.crshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem .9rem; }
.crshow-info__grid p { margin: 0; }
.crshow-empty { padding: 1.2rem; text-align: center; color: var(--text-color-secondary); }
.crshow-good { color: var(--green-500); font-weight: 600; }
.crshow-bad { color: var(--red-500); font-weight: 600; }
.crshow-neutral { color: var(--text-color-secondary); font-weight: 600; }
@media (max-width: 1200px) { .crshow-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 640px) { .crshow-kpis, .crshow-info__grid { grid-template-columns: 1fr; } }
</style>
