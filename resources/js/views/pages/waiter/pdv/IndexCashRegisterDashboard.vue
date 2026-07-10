<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoadingDiv = ref(true);
const isLoadingPaymentTab = ref(false);
const isLoadingQuickSellTab = ref(false);
const isLoadingTableTab = ref(false);

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

const cash_register = ref(null);
const total_sales = ref(0);
const total_orders = ref(0);
const average_ticket = ref(0);
const total_tables = ref(0);
const total_quick_sell_amount = ref(0);
const total_tables_amount = ref(0);
const total_quick_sell = ref(0);
const total_payments = ref(0);
const total_payments_amount = ref(0);

const showDialog = ref(false);
const pdfUrl = ref(null);
const printingOrderId = ref(null);

const showDialogOrder = ref(false);
const selectedOrder = ref(null);

function goBackUsingBack() {
    router?.back();
}

function formatMoney(value) {
    return Number(value ?? 0).toLocaleString('pt-PT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function canPrintTableReceipt(order) {
    const statusId = Number(order?.status?.id ?? order?.order_status_id ?? 0);
    return statusId === 3 || statusId === 4;
}

function seeOrderItens(order) {
    selectedOrder.value = order;
    showDialogOrder.value = true;
}

function printPDF() {
    const iframe = document.querySelector('.crdash-receipt-frame');
    if (iframe?.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

function closeReceiptDialog() {
    showDialog.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
}

async function fetchAndShowReceipt(url, orderId) {
    printingOrderId.value = orderId;

    try {
        if (pdfUrl.value) {
            URL.revokeObjectURL(pdfUrl.value);
            pdfUrl.value = null;
        }

        const pdfResponse = await axios.post(url, {}, { responseType: 'blob' });
        const blob = new Blob([pdfResponse.data], { type: 'application/pdf' });
        pdfUrl.value = URL.createObjectURL(blob);
        showDialog.value = true;
    } catch (error) {
        console.error('Erro ao obter recibo:', error);
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível gerar o recibo.',
            life: 3500
        });
    } finally {
        printingOrderId.value = null;
    }
}

async function printQuickSellReceipt(order) {
    await fetchAndShowReceipt(`/api/getquickreceipt/${order.id}`, order.id);
}

async function printTableReceipt(order) {
    if (!canPrintTableReceipt(order)) {
        toast.add({
            severity: 'warn',
            summary: 'Recibo indisponível',
            detail: 'Só é possível imprimir o recibo após o pagamento da conta.',
            life: 3500
        });
        return;
    }

    await fetchAndShowReceipt(`/api/getcustomerreceipt/${order.id}`, order.id);
}

async function printPaymentReceipt(payment) {
    const orderId = payment.order_id;
    const isQuickSell = payment.order?.table_id == null;
    const url = isQuickSell
        ? `/api/getquickreceipt/${orderId}`
        : `/api/getcustomerreceipt/${orderId}`;

    await fetchAndShowReceipt(url, orderId);
}

async function printSelectedOrderReceipt() {
    if (!selectedOrder.value) {
        return;
    }

    const isQuickSell = selectedOrder.value.table_id == null;
    if (isQuickSell) {
        await printQuickSellReceipt(selectedOrder.value);
    } else {
        await printTableReceipt(selectedOrder.value);
    }
}

const getData = async () => {
    try {
        const response = await axios.get('/api/cashregisters/dashboard');
        cash_register.value = response.data.cash_register;
        total_sales.value = response.data.total_sales;
        total_orders.value = response.data.total_orders;
        total_tables.value = response.data.total_tables;
        total_quick_sell.value = response.data.total_quick_sell;
        average_ticket.value = response.data.average_ticket;
        total_quick_sell_amount.value = response.data.total_quick_sell_amount;
        total_tables_amount.value = response.data.total_tables_amount;
        total_payments.value = response.data.total_payments;
        total_payments_amount.value = response.data.total_payments_amount;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar o dashboard.',
            life: 3000
        });
        goBackUsingBack();
    } finally {
        isLoadingDiv.value = false;
    }
};

const getPaymentsData = async (page = 1) => {
    isLoadingPaymentTab.value = true;
    try {
        const response = await axios.get(`/api/cashregisters/paymentreport?page=${page}`);
        paymentreport.value = response.data;
        totalRecordsPayments.value = response.data.total;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Erro ao carregar pagamentos.',
            life: 3000
        });
    } finally {
        isLoadingPaymentTab.value = false;
    }
};

const getTableData = async (page = 1) => {
    isLoadingTableTab.value = true;
    try {
        const response = await axios.get(`/api/cashregisters/tablesellreport?page=${page}`);
        tablesellreport.value = response.data;
        totalRecordsTable.value = response.data.total;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Erro ao carregar vendas em mesas.',
            life: 3000
        });
    } finally {
        isLoadingTableTab.value = false;
    }
};

const getQuickSellData = async (page = 1) => {
    isLoadingQuickSellTab.value = true;
    try {
        const response = await axios.get(`/api/cashregisters/quicksellreport?page=${page}`);
        quicksellreport.value = response.data;
        totalRecordsQuickSell.value = response.data.total;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Erro ao carregar vendas rápidas.',
            life: 3000
        });
    } finally {
        isLoadingQuickSellTab.value = false;
    }
};

const refreshAll = async () => {
    isLoadingDiv.value = true;
    await Promise.all([
        getData(),
        getPaymentsData(currentPagePayments.value),
        getQuickSellData(currentPageQuickSell.value),
        getTableData(currentPageTableSell.value)
    ]);
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

onMounted(() => {
    refreshAll();
});
</script>

<template>
    <div v-if="isLoadingDiv" class="crdash-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar dashboard do caixa...</p>
    </div>

    <div v-else class="crdash-page">
        <header class="crdash-header">
            <div>
                <p class="crdash-eyebrow">Ponto de venda</p>
                <h1>Dashboard do caixa</h1>
                <p class="crdash-subtitle">
                    Aberto às <strong>{{ formatDate(cash_register?.created_at) }}</strong>
                    · Operador <strong>{{ cash_register?.user?.name || '—' }}</strong>
                </p>
            </div>
            <div class="crdash-header__actions">
                <Button label="Actualizar" icon="pi pi-refresh" outlined :loading="isLoadingDiv" @click="refreshAll" />
                <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
            </div>
        </header>

        <section class="crdash-kpis">
            <div class="crdash-kpi">
                <span>Total vendas</span>
                <strong>{{ formatMoney(total_sales) }} MT</strong>
            </div>
            <div class="crdash-kpi">
                <span>Pedidos</span>
                <strong>{{ total_orders }}</strong>
            </div>
            <div class="crdash-kpi">
                <span>Ticket médio</span>
                <strong>{{ formatMoney(average_ticket) }} MT</strong>
            </div>
            <div class="crdash-kpi">
                <span>Mesas abertas</span>
                <strong>{{ total_tables }}</strong>
                <small>{{ formatMoney(total_tables_amount) }} MT</small>
            </div>
            <div class="crdash-kpi">
                <span>Vendas rápidas</span>
                <strong>{{ total_quick_sell }}</strong>
                <small>{{ formatMoney(total_quick_sell_amount) }} MT</small>
            </div>
            <div class="crdash-kpi">
                <span>Pagamentos</span>
                <strong>{{ total_payments }}</strong>
                <small>{{ formatMoney(total_payments_amount) }} MT</small>
            </div>
        </section>

        <section class="crdash-panel">
            <Tabs value="0">
                <TabList>
                    <Tab value="0">Vendas rápidas</Tab>
                    <Tab value="1">Vendas em mesas</Tab>
                    <Tab value="2">Pagamentos efectuados</Tab>
                </TabList>
                <TabPanels>
                    <TabPanel value="0">
                        <DataTable
                            :value="quicksellreport.data"
                            :paginator="true"
                            :rows="rowsPerPage"
                            :totalRecords="totalRecordsQuickSell"
                            dataKey="id"
                            :lazy="true"
                            :rowHover="true"
                            :loading="isLoadingQuickSellTab"
                            :first="(currentPageQuickSell - 1) * rowsPerPage"
                            :onPage="onPageChangeQuickSell"
                            showGridlines
                        >
                            <template #empty>Nenhum registo encontrado.</template>
                            <template #loading>A carregar vendas rápidas...</template>

                            <Column header="ID" style="min-width: 5rem">
                                <template #body="{ data }">#{{ data.id }}</template>
                            </Column>
                            <Column header="Tipo" style="min-width: 8rem">
                                <template #body>Pedido rápido</template>
                            </Column>
                            <Column header="Garçom" style="min-width: 10rem">
                                <template #body="{ data }">{{ data.user?.name || '—' }}</template>
                            </Column>
                            <Column header="Estado" style="min-width: 8rem">
                                <template #body="{ data }">{{ data.status?.name || '—' }}</template>
                            </Column>
                            <Column header="Itens" style="min-width: 5rem">
                                <template #body="{ data }">{{ data.itens?.length ?? 0 }}</template>
                            </Column>
                            <Column header="Valor" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <strong>{{ formatMoney(data.total) }} MT</strong>
                                </template>
                            </Column>
                            <Column header="Data" style="min-width: 10rem">
                                <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                            </Column>
                            <Column header="Acções" style="min-width: 8rem" :exportable="false">
                                <template #body="{ data }">
                                    <div class="crdash-actions">
                                        <Button
                                            icon="pi pi-eye"
                                            text
                                            rounded
                                            severity="secondary"
                                            v-tooltip.top="'Ver itens'"
                                            @click="seeOrderItens(data)"
                                        />
                                        <Button
                                            icon="pi pi-print"
                                            text
                                            rounded
                                            severity="info"
                                            v-tooltip.top="'Imprimir recibo'"
                                            :loading="printingOrderId === data.id"
                                            @click="printQuickSellReceipt(data)"
                                        />
                                    </div>
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
                            :lazy="true"
                            :rowHover="true"
                            :loading="isLoadingTableTab"
                            :first="(currentPageTableSell - 1) * rowsPerPage"
                            :onPage="onPageChangeTableSell"
                            showGridlines
                        >
                            <template #empty>Nenhum registo encontrado.</template>
                            <template #loading>A carregar vendas em mesas...</template>

                            <Column header="ID" style="min-width: 5rem">
                                <template #body="{ data }">#{{ data.id }}</template>
                            </Column>
                            <Column header="Mesa" style="min-width: 8rem">
                                <template #body="{ data }">{{ data.table?.name || '—' }}</template>
                            </Column>
                            <Column header="Garçom" style="min-width: 10rem">
                                <template #body="{ data }">{{ data.user?.name || '—' }}</template>
                            </Column>
                            <Column header="Estado" style="min-width: 8rem">
                                <template #body="{ data }">{{ data.status?.name || '—' }}</template>
                            </Column>
                            <Column header="Itens" style="min-width: 5rem">
                                <template #body="{ data }">{{ data.itens?.length ?? 0 }}</template>
                            </Column>
                            <Column header="Valor" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <strong>{{ formatMoney(data.total) }} MT</strong>
                                </template>
                            </Column>
                            <Column header="Data" style="min-width: 10rem">
                                <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                            </Column>
                            <Column header="Acções" style="min-width: 8rem" :exportable="false">
                                <template #body="{ data }">
                                    <div class="crdash-actions">
                                        <Button
                                            icon="pi pi-eye"
                                            text
                                            rounded
                                            severity="secondary"
                                            v-tooltip.top="'Ver itens'"
                                            @click="seeOrderItens(data)"
                                        />
                                        <Button
                                            icon="pi pi-print"
                                            text
                                            rounded
                                            :severity="canPrintTableReceipt(data) ? 'info' : 'secondary'"
                                            v-tooltip.top="canPrintTableReceipt(data) ? 'Imprimir recibo' : 'Disponível após pagamento'"
                                            :disabled="!canPrintTableReceipt(data)"
                                            :loading="printingOrderId === data.id"
                                            @click="printTableReceipt(data)"
                                        />
                                    </div>
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
                            :lazy="true"
                            :rowHover="true"
                            :loading="isLoadingPaymentTab"
                            :first="(currentPagePayments - 1) * rowsPerPage"
                            :onPage="onPageChangePayments"
                            showGridlines
                        >
                            <template #empty>Nenhum registo encontrado.</template>
                            <template #loading>A carregar pagamentos...</template>

                            <Column header="ID" style="min-width: 5rem">
                                <template #body="{ data }">#{{ data.id }}</template>
                            </Column>
                            <Column header="Pedido" style="min-width: 6rem">
                                <template #body="{ data }">#{{ data.order_id }}</template>
                            </Column>
                            <Column header="Origem" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.order?.table_id == null ? 'Venda rápida' : data.order?.table?.name }}
                                </template>
                            </Column>
                            <Column header="Método" style="min-width: 10rem">
                                <template #body="{ data }">{{ data.method?.name || '—' }}</template>
                            </Column>
                            <Column header="Valor" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <strong>{{ formatMoney(data.amount) }} MT</strong>
                                </template>
                            </Column>
                            <Column header="Data" style="min-width: 10rem">
                                <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                            </Column>
                            <Column header="Acções" style="min-width: 6rem" :exportable="false">
                                <template #body="{ data }">
                                    <Button
                                        icon="pi pi-print"
                                        text
                                        rounded
                                        severity="info"
                                        v-tooltip.top="'Reimprimir recibo'"
                                        :loading="printingOrderId === data.order_id"
                                        @click="printPaymentReceipt(data)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </section>
    </div>

    <Dialog
        v-model:visible="showDialog"
        header="Recibo"
        :modal="true"
        :style="{ width: 'min(640px, 96vw)' }"
        @hide="closeReceiptDialog"
    >
        <iframe
            v-if="pdfUrl"
            :src="pdfUrl"
            class="crdash-receipt-frame"
            frameborder="0"
        />

        <template #footer>
            <Button label="Imprimir" icon="pi pi-print" @click="printPDF" />
            <Button label="Fechar" icon="pi pi-times" text @click="closeReceiptDialog" />
        </template>
    </Dialog>

    <Dialog
        v-model:visible="showDialogOrder"
        :header="selectedOrder ? `Pedido #${selectedOrder.id}` : 'Itens do pedido'"
        :modal="true"
        :style="{ width: 'min(720px, 96vw)' }"
    >
        <template v-if="selectedOrder">
            <div class="crdash-order-meta">
                <span>{{ selectedOrder.table_id == null ? 'Venda rápida' : selectedOrder.table?.name }}</span>
                <span>{{ selectedOrder.status?.name }}</span>
                <span>{{ formatMoney(selectedOrder.total) }} MT</span>
            </div>

            <DataTable :value="selectedOrder.itens" dataKey="id" rowHover showGridlines>
                <template #empty>Sem itens registados.</template>
                <Column header="Produto" style="min-width: 14rem">
                    <template #body="{ data }">{{ data.product?.name || '—' }}</template>
                </Column>
                <Column header="Qtd." style="min-width: 5rem">
                    <template #body="{ data }">{{ data.quantity }}</template>
                </Column>
                <Column header="Preço" style="min-width: 7rem">
                    <template #body="{ data }">{{ formatMoney(data.price) }} MT</template>
                </Column>
                <Column header="Total" style="min-width: 7rem">
                    <template #body="{ data }">
                        <strong>{{ formatMoney(data.total) }} MT</strong>
                    </template>
                </Column>
            </DataTable>
        </template>

        <template #footer>
            <Button label="Fechar" text @click="showDialogOrder = false" />
            <Button
                label="Imprimir recibo"
                icon="pi pi-print"
                :loading="printingOrderId === selectedOrder?.id"
                @click="printSelectedOrderReceipt"
            />
        </template>
    </Dialog>
</template>

<style scoped>
.crdash-loading {
    min-height: 50vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.crdash-page {
    --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --bs: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.crdash-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.1rem;
    border: 1px solid var(--b);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--bs);
}

.crdash-header__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.crdash-eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.crdash-header h1 {
    margin: 0.15rem 0 0;
    font-size: 1.5rem;
}

.crdash-subtitle {
    margin: 0.35rem 0 0;
    color: var(--text-color-secondary);
}

.crdash-kpis {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.75rem;
}

.crdash-kpi {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.85rem;
    border: 1px solid var(--bs);
    border-radius: 0.85rem;
    background: var(--bg);
}

.crdash-kpi span {
    font-size: 0.82rem;
    color: var(--text-color-secondary);
}

.crdash-kpi strong {
    font-size: 1.1rem;
}

.crdash-kpi small {
    color: var(--text-color-secondary);
    font-size: 0.78rem;
}

.crdash-panel {
    padding: 1rem;
    border: 1px solid var(--b);
    border-radius: 1rem;
    background: var(--surface-card);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--bs);
}

.crdash-actions {
    display: flex;
    gap: 0.15rem;
}

.crdash-order-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
    font-size: 0.9rem;
    color: var(--text-color-secondary);
}

.crdash-receipt-frame {
    width: 100%;
    height: 500px;
    border: 1px solid var(--bs);
    border-radius: 0.5rem;
}

@media (max-width: 1200px) {
    .crdash-kpis {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .crdash-kpis {
        grid-template-columns: 1fr;
    }
}
</style>
