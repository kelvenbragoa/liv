<script setup>
import { computed, ref, onMounted, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';
import moment from 'moment';

let interval;
const router = useRouter();
const toast = useToast();

const isLoadingDiv = ref(true);
const loadingButtonDelete = ref(false);
const searchQuery = ref('');
const statusFilter = ref(null);
const retriviedData = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);
const totalRecords = ref(0);
const cashregister = ref(null);
const openCashRegisterDialog = ref(false);
const closeCashRegisterDialog = ref(false);
const openListQuickSellDialog = ref(false);
const openingBalance = ref(0);
const totalcash = ref(0);
const quicksells = ref([]);
const totalRecordsQuickSell = ref(0);
const isLoadingQuickSell = ref(false);
const showDialog = ref(false);
const selectedItemToDelete = ref(null);
const confirmationCode = ref(null);
const correct_code = '142502';
const expandedRows = ref([]);
const deleteDialog = ref(false);
const pdfUrl = ref(null);

const STATUS_META = {
    1: { label: 'Livre', tone: 'free', icon: 'pi-check-circle' },
    2: { label: 'Ocupada', tone: 'busy', icon: 'pi-users' },
    3: { label: 'Reservada', tone: 'reserved', icon: 'pi-calendar' },
    4: { label: 'Agrupada', tone: 'grouped', icon: 'pi-sitemap' },
    5: { label: 'Fechamento', tone: 'closing', icon: 'pi-lock' },
    6: { label: 'Manutenção', tone: 'maintenance', icon: 'pi-wrench' }
};

const STATUS_FILTERS = [
    { id: null, label: 'Todas' },
    { id: 1, label: 'Livre' },
    { id: 2, label: 'Ocupada' },
    { id: 3, label: 'Reservada' },
    { id: 4, label: 'Agrupada' },
    { id: 5, label: 'Fechamento' },
    { id: 6, label: 'Manutenção' }
];

const tables = computed(() => retriviedData.value?.data ?? []);

const filteredTables = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();

    return tables.value.filter((table) => {
        const matchesStatus = !statusFilter.value || table.table_status_id === statusFilter.value;
        const matchesSearch =
            !q ||
            (table.name || '').toLowerCase().includes(q) ||
            (table.last_order?.user?.name || '').toLowerCase().includes(q);

        return matchesStatus && matchesSearch;
    });
});

const tableStats = computed(() => {
    const stats = {
        total: tables.value.length,
        free: 0,
        busy: 0,
        other: 0,
        openConsumption: 0
    };

    for (const table of tables.value) {
        const consumption = Number(table.last_order?.total ?? 0);
        stats.openConsumption += consumption;

        if (table.table_status_id === 1) {
            stats.free += 1;
        } else if (table.table_status_id === 2) {
            stats.busy += 1;
        } else {
            stats.other += 1;
        }
    }

    return stats;
});

const cashRegisterLabel = computed(() => {
    if (!cashregister.value) {
        return 'Caixa fechado';
    }

    return `Aberto às ${moment(cashregister.value.created_at).format('HH:mm')}`;
});

function getTableMeta(statusId) {
    return STATUS_META[statusId] || {
        label: 'Indefinido',
        tone: 'maintenance',
        icon: 'pi-question-circle'
    };
}

function getSeverity(status) {
    switch (status) {
        case 1:
            return 'success';
        case 2:
            return 'warn';
        case 3:
            return 'secondary';
        case 4:
            return 'contrast';
        case 5:
            return 'info';
        case 6:
            return 'danger';
        default:
            return 'secondary';
    }
}

function printPDF() {
    const iframe = document.querySelector('iframe');
    if (iframe) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

function closeDialog() {
    showDialog.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
}

function printReceipt(id) {
    axios
        .post(`/api/getquickreceipt/${id}`, {}, { responseType: 'blob' })
        .then((response) => {
            const blob = new Blob([response.data], { type: 'application/pdf' });
            pdfUrl.value = URL.createObjectURL(blob);
            showDialog.value = true;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Recibo gerado com sucesso!',
                life: 3000
            });
        })
        .catch(async (error) => {
            let errorMessage = 'Ocorreu um erro inesperado.';

            if (error.response?.data instanceof Blob) {
                try {
                    const text = await error.response.data.text();
                    const json = JSON.parse(text);
                    errorMessage = json.message || json.error || errorMessage;
                } catch (e) {
                    console.error('Erro ao processar o blob:', e);
                }
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            toast.add({ severity: 'error', summary: 'Erro', detail: errorMessage, life: 3000 });
        });
}

const confirmDelete = (id) => {
    selectedItemToDelete.value = id;
    confirmationCode.value = null;
    deleteDialog.value = true;
};

const deleteItem = () => {
    if (confirmationCode.value !== correct_code) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Código de confirmação inválido',
            life: 3000
        });
        return;
    }

    axios
        .post(`/api/quickorderdelete/${selectedItemToDelete.value}`)
        .then(() => {
            deleteDialog.value = false;
            getData();
            getDataQuickSells();
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Venda removida', life: 3000 });
        })
        .catch((error) => {
            toast.add({ severity: 'error', summary: 'Erro', detail: `${error}`, life: 3000 });
        });
};

const getData = async (page = 1) => {
    return axios
        .get(`/api/pdv?page=${page}`, {
            params: { query: searchQuery.value }
        })
        .then((response) => {
            retriviedData.value = response.data.tables;
            totalRecords.value = retriviedData.value.total;
            cashregister.value = response.data.cash_register;
            totalcash.value = response.data.totalcash;
            isLoadingDiv.value = false;
        })
        .catch((error) => {
            isLoadingDiv.value = false;
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || `${error}`,
                life: 3000
            });
        });
};

const getDataQuickSells = async (page = 1) => {
    axios
        .get(`/api/pdvquicksellslist?page=${page}`, {
            params: { query: searchQuery.value }
        })
        .then((response) => {
            quicksells.value = response.data.quicksells;
            totalRecordsQuickSell.value = response.data.quicksells.total;
        })
        .catch((error) => {
            toast.add({ severity: 'error', summary: 'Erro', detail: `${error}`, life: 3000 });
        });
};

function openCashRegister() {
    axios
        .post('/api/cashregisters/open', {
            opening_balance: openingBalance.value
        })
        .then((response) => {
            openCashRegisterDialog.value = false;
            openingBalance.value = null;
            cashregister.value = response.data;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Caixa aberto com sucesso!',
                life: 3000
            });
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: `Falha ao abrir o caixa. ${error.response?.data?.message}`,
                life: 3000
            });
        });
}

function closeCashRegister() {
    axios
        .post('/api/cashregisters/close', {
            closing_balance: totalcash.value
        })
        .then(() => {
            closeCashRegisterDialog.value = false;
            cashregister.value = null;
            totalcash.value = 0;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Caixa fechado com sucesso!',
                life: 3000
            });
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: `Falha ao fechar o caixa. ${error.response?.data?.message}`,
                life: 3000
            });
        });
}

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getDataQuickSells(currentPage.value);
};

const debouncedSearch = debounce(() => {
    getData(currentPage.value);
}, 300);

watch(searchQuery, debouncedSearch);

onMounted(() => {
    getData();
    getDataQuickSells();
    interval = setInterval(() => {
        getData();
        getDataQuickSells();
    }, 30000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <div v-if="isLoadingDiv" class="pdv-floor-loading">
        <ProgressSpinner
            style="width: 50px; height: 50px"
            strokeWidth="8"
            fill="var(--surface-ground)"
            animationDuration=".5s"
        />
        <p>A carregar o salão...</p>
    </div>

    <div v-else class="pdv-floor">
        <header class="pdv-floor__hero">
            <div class="pdv-floor__intro">
                <div>
                    <p class="pdv-floor__eyebrow">Ponto de venda</p>
                    <h1>Salão</h1>
                    <p class="pdv-floor__subtitle">
                        {{ tableStats.total }} mesas · actualização automática a cada 30s
                    </p>
                </div>

                <div class="pdv-floor__quick-actions">
                    <Button
                        label="Venda rápida"
                        icon="pi pi-bolt"
                        @click="router.push('/admin/pdv/quicksell')"
                    />
                    <Button
                        label="Vendas rápidas"
                        icon="pi pi-list"
                        severity="secondary"
                        outlined
                        @click="openListQuickSellDialog = true"
                    />
                    <Button
                        v-if="!cashregister"
                        label="Abrir caixa"
                        icon="pi pi-unlock"
                        severity="success"
                        outlined
                        @click="openCashRegisterDialog = true"
                    />
                    <Button
                        v-else
                        label="Fechar caixa"
                        icon="pi pi-lock"
                        severity="danger"
                        outlined
                        @click="closeCashRegisterDialog = true"
                    />
                    <Button
                        icon="pi pi-chart-bar"
                        severity="secondary"
                        text
                        v-tooltip.bottom="'Relatório de caixa'"
                        @click="router.push('/admin/cashregisters/dashboard')"
                    />
                </div>
            </div>

            <div class="pdv-floor__kpis">
                <article class="pdv-kpi">
                    <span class="pdv-kpi__label">Vendas hoje</span>
                    <strong class="pdv-kpi__value">{{ totalcash }} MT</strong>
                    <small>Total registado no caixa</small>
                </article>

                <article class="pdv-kpi" :class="cashregister ? 'pdv-kpi--success' : 'pdv-kpi--danger'">
                    <span class="pdv-kpi__label">Estado do caixa</span>
                    <strong class="pdv-kpi__value">{{ cashRegisterLabel }}</strong>
                    <small>{{ cashregister ? 'Operação activa' : 'Abra o caixa para vender' }}</small>
                </article>

                <article class="pdv-kpi pdv-kpi--free">
                    <span class="pdv-kpi__label">Mesas livres</span>
                    <strong class="pdv-kpi__value">{{ tableStats.free }}</strong>
                    <small>{{ tableStats.busy }} ocupadas · {{ tableStats.other }} outras</small>
                </article>

                <article class="pdv-kpi">
                    <span class="pdv-kpi__label">Consumo em aberto</span>
                    <strong class="pdv-kpi__value">{{ tableStats.openConsumption }} MT</strong>
                    <small>Soma das contas activas</small>
                </article>
            </div>
        </header>

        <section class="pdv-floor__toolbar">
            <div class="pdv-floor__search">
                <i class="pi pi-search" />
                <InputText
                    v-model="searchQuery"
                    placeholder="Pesquisar mesa ou responsável..."
                    class="pdv-floor__search-input"
                />
                <Button
                    v-if="searchQuery"
                    icon="pi pi-times"
                    text
                    rounded
                    severity="secondary"
                    @click="searchQuery = ''"
                />
            </div>

            <div class="pdv-floor__filters">
                <button
                    v-for="filter in STATUS_FILTERS"
                    :key="filter.label"
                    type="button"
                    class="pdv-filter-chip"
                    :class="{ 'pdv-filter-chip--active': statusFilter === filter.id }"
                    @click="statusFilter = filter.id"
                >
                    {{ filter.label }}
                </button>
            </div>
        </section>

        <section v-if="filteredTables.length" class="pdv-floor__grid">
            <router-link
                v-for="table in filteredTables"
                :key="table.id"
                :to="`/admin/pdv/${table.id}/categories`"
                class="pdv-table-card"
                :class="`pdv-table-card--${getTableMeta(table.table_status_id).tone}`"
            >
                <div class="pdv-table-card__top">
                    <div class="pdv-table-card__icon">
                        <i :class="['pi', getTableMeta(table.table_status_id).icon]" />
                    </div>
                    <Tag
                        :value="table.status?.name || getTableMeta(table.table_status_id).label"
                        :severity="getSeverity(table.table_status_id)"
                    />
                </div>

                <div class="pdv-table-card__name">{{ table.name }}</div>

                <div class="pdv-table-card__meta">
                    <span>
                        <i class="pi pi-users" />
                        {{ table.capacity }} lugares
                    </span>
                </div>

                <div class="pdv-table-card__consumption">
                    <span>Consumo</span>
                    <strong>{{ table.last_order?.total ?? 0 }} MT</strong>
                </div>

                <div class="pdv-table-card__footer">
                    <span v-if="table.last_order?.user?.name" class="pdv-table-card__waiter">
                        <i class="pi pi-user" />
                        {{ table.last_order.user.name }}
                    </span>
                    <span v-else class="pdv-table-card__waiter pdv-table-card__waiter--empty">
                        Sem atendimento activo
                    </span>
                    <span class="pdv-table-card__cta">
                        Abrir
                        <i class="pi pi-arrow-right" />
                    </span>
                </div>
            </router-link>
        </section>

        <section v-else class="pdv-floor__empty">
            <i class="pi pi-table" />
            <h3>Nenhuma mesa encontrada</h3>
            <p>Ajuste a pesquisa ou o filtro de estado.</p>
        </section>
    </div>

    <Dialog
        header="Abertura de caixa"
        v-model:visible="openCashRegisterDialog"
        :style="{ width: '24rem' }"
        modal
    >
        <label for="opening_balance" class="block font-medium mb-2">Saldo inicial (MZN)</label>
        <InputNumber
            v-model="openingBalance"
            inputId="opening_balance"
            mode="currency"
            currency="MZN"
            locale="pt-MZ"
            :min="-1"
            placeholder="0.00"
            class="w-full"
        />
        <template #footer>
            <Button label="Cancelar" text @click="openCashRegisterDialog = false" />
            <Button label="Abrir caixa" icon="pi pi-check" @click="openCashRegister" />
        </template>
    </Dialog>

    <Dialog
        header="Fechamento de caixa"
        v-model:visible="closeCashRegisterDialog"
        :style="{ width: '24rem' }"
        modal
    >
        <label for="closing_balance" class="block font-medium mb-2">Saldo final (MZN)</label>
        <InputNumber
            v-model="totalcash"
            inputId="closing_balance"
            mode="currency"
            currency="MZN"
            locale="pt-MZ"
            :min="-1"
            placeholder="0.00"
            class="w-full"
        />
        <template #footer>
            <Button label="Cancelar" text @click="closeCashRegisterDialog = false" />
            <Button label="Fechar caixa" icon="pi pi-check" severity="danger" @click="closeCashRegister" />
        </template>
    </Dialog>

    <Dialog
        header="Vendas rápidas de hoje"
        v-model:visible="openListQuickSellDialog"
        :style="{ width: '90vw', maxWidth: '940px' }"
        modal
    >
        <DataTable
            v-model:expandedRows="expandedRows"
            :paginator="true"
            :rows="rowsPerPage"
            :totalRecords="totalRecordsQuickSell"
            dataKey="id"
            :lazy="true"
            :rowHover="true"
            :loading="isLoadingQuickSell"
            :first="(currentPage - 1) * rowsPerPage"
            :onPage="onPageChange"
            :value="quicksells.data"
            tableStyle="min-width: 60rem"
        >
            <Column expander style="width: 5rem" />
            <Column header="Ações" style="min-width: 8rem">
                <template #body="{ data }">
                    <Button icon="pi pi-print" text rounded @click="printReceipt(data.id)" />
                    <Button
                        icon="pi pi-trash"
                        text
                        rounded
                        severity="danger"
                        @click="confirmDelete(data.id)"
                    />
                </template>
            </Column>
            <Column header="ID">
                <template #body="{ data }">#{{ data.id }}</template>
            </Column>
            <Column header="Valor">
                <template #body="{ data }">{{ data.total }} MT</template>
            </Column>
            <Column header="Operador">
                <template #body="{ data }">{{ data.user.name }}</template>
            </Column>
            <Column header="Estado">
                <template #body="{ data }">{{ data.status.name }}</template>
            </Column>
            <Column header="Itens">
                <template #body="{ data }">{{ data.itens.length }}</template>
            </Column>
            <Column header="Data">
                <template #body="{ data }">
                    {{ moment(data.created_at).format('DD-MM-YYYY HH:mm') }}
                </template>
            </Column>

            <template #expansion="slotProps">
                <div class="pdv-expand-panel">
                    <h5>Itens da venda #{{ slotProps.data.id }}</h5>
                    <DataTable :value="slotProps.data.itens">
                        <Column header="Produto">
                            <template #body="{ data }">{{ data.product.name }}</template>
                        </Column>
                        <Column header="Qtd">
                            <template #body="{ data }">{{ data.quantity }}</template>
                        </Column>
                        <Column header="Preço">
                            <template #body="{ data }">{{ data.price }} MT</template>
                        </Column>
                        <Column header="Total">
                            <template #body="{ data }">{{ data.total }} MT</template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>

        <template #footer>
            <Button label="Fechar" text @click="openListQuickSellDialog = false" />
        </template>
    </Dialog>

    <Dialog header="Confirmar exclusão" v-model:visible="deleteDialog" :style="{ width: '22rem' }" modal>
        <p class="mb-3">Insira o código para confirmar a exclusão da venda.</p>
        <InputText
            v-model="confirmationCode"
            type="password"
            placeholder="Código de confirmação"
            class="w-full"
        />
        <template #footer>
            <Button label="Cancelar" text @click="deleteDialog = false" />
            <Button label="Confirmar" severity="danger" @click="deleteItem" />
        </template>
    </Dialog>

    <Dialog
        v-model:visible="showDialog"
        header="Recibo"
        :modal="true"
        :style="{ width: '600px' }"
        :closable="false"
    >
        <iframe
            v-if="pdfUrl"
            :src="pdfUrl"
            style="width: 100%; height: 500px"
            frameborder="0"
        />
        <template #footer>
            <Button label="Imprimir" icon="pi pi-print" @click="printPDF" />
            <Button label="Fechar" icon="pi pi-times" class="p-button-text" @click="closeDialog" />
        </template>
    </Dialog>
</template>

<style scoped>
.pdv-floor {
    --pdv-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --pdv-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --pdv-panel-bg: var(--surface-card);
    --pdv-canvas-bg: color-mix(in srgb, var(--surface-ground) 82%, var(--text-color) 6%);
    --pdv-card-bg: color-mix(in srgb, var(--surface-card) 88%, var(--surface-ground) 12%);
    --pdv-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --pdv-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--pdv-border-soft);
    --pdv-shadow-hover: 0 10px 24px rgba(15, 23, 42, 0.08), 0 0 0 1px color-mix(in srgb, var(--primary-color) 30%, var(--pdv-border-soft));

    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-height: calc(100vh - 8rem);
}

.pdv-floor-loading {
    min-height: 60vh;
    display: grid;
    place-items: center;
    gap: 0.75rem;
    color: var(--text-color-secondary);
}

.pdv-floor__hero {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.1rem 1.15rem;
    border: 1px solid var(--pdv-border);
    border-radius: 1rem;
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--primary-color) 10%, transparent), transparent 42%),
        var(--pdv-panel-bg);
    box-shadow: var(--pdv-shadow);
}

.pdv-floor__intro {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.pdv-floor__eyebrow {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary-color);
}

.pdv-floor__intro h1 {
    margin: 0.2rem 0 0;
    font-size: clamp(1.6rem, 2vw, 2rem);
    letter-spacing: -0.03em;
}

.pdv-floor__subtitle {
    margin: 0.35rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.9rem;
}

.pdv-floor__quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.pdv-floor__kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.pdv-kpi {
    padding: 0.85rem 0.95rem;
    border-radius: 0.85rem;
    border: 1px solid var(--pdv-border-soft);
    background: var(--pdv-muted-bg);
    box-shadow: var(--pdv-shadow);
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.pdv-kpi__label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-color-secondary);
}

.pdv-kpi__value {
    font-size: 1.35rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.pdv-kpi small {
    color: var(--text-color-secondary);
    font-size: 0.78rem;
}

.pdv-kpi--success {
    border-color: color-mix(in srgb, #22c55e 35%, var(--pdv-border-soft));
    background: color-mix(in srgb, #22c55e 8%, var(--pdv-card-bg));
}

.pdv-kpi--danger {
    border-color: color-mix(in srgb, #ef4444 35%, var(--pdv-border-soft));
    background: color-mix(in srgb, #ef4444 8%, var(--pdv-card-bg));
}

.pdv-kpi--free {
    border-color: color-mix(in srgb, #10b981 35%, var(--pdv-border-soft));
    background: color-mix(in srgb, #10b981 8%, var(--pdv-card-bg));
}

.pdv-floor__toolbar {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--pdv-border);
    border-radius: 1rem;
    background: var(--pdv-panel-bg);
    box-shadow: var(--pdv-shadow);
}

.pdv-floor__search {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    border: 1px solid var(--pdv-border-soft);
    background: var(--pdv-muted-bg);
}

.pdv-floor__search i {
    color: var(--text-color-secondary);
}

.pdv-floor__search-input {
    width: 100%;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
}

.pdv-floor__filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.pdv-filter-chip {
    border: 1px solid var(--pdv-border-soft);
    background: var(--pdv-muted-bg);
    color: var(--text-color);
    border-radius: 999px;
    padding: 0.42rem 0.85rem;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    box-shadow: var(--pdv-shadow);
    transition: 0.15s ease;
}

.pdv-filter-chip--active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: var(--primary-contrast-color, #fff);
}

.pdv-floor__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 0.85rem;
}

.pdv-table-card {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid var(--pdv-border-soft);
    background: var(--pdv-card-bg);
    box-shadow: var(--pdv-shadow);
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.pdv-table-card::before {
    content: '';
    position: absolute;
    inset: 0 auto 0 0;
    width: 4px;
    background: var(--pdv-accent, var(--primary-color));
}

.pdv-table-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--pdv-shadow-hover);
    border-color: color-mix(in srgb, var(--primary-color) 35%, var(--pdv-border-soft));
}

.pdv-table-card--free {
    --pdv-accent: #10b981;
}

.pdv-table-card--busy {
    --pdv-accent: #f59e0b;
}

.pdv-table-card--reserved {
    --pdv-accent: #3b82f6;
}

.pdv-table-card--grouped {
    --pdv-accent: #8b5cf6;
}

.pdv-table-card--closing {
    --pdv-accent: #0ea5e9;
}

.pdv-table-card--maintenance {
    --pdv-accent: #94a3b8;
}

.pdv-table-card__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    padding-left: 0.35rem;
}

.pdv-table-card__icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.75rem;
    display: grid;
    place-items: center;
    background: color-mix(in srgb, var(--pdv-accent) 14%, var(--pdv-card-bg));
    color: var(--pdv-accent);
    border: 1px solid color-mix(in srgb, var(--pdv-accent) 25%, var(--pdv-border-soft));
}

.pdv-table-card__name {
    padding-left: 0.35rem;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.pdv-table-card__meta {
    padding-left: 0.35rem;
    color: var(--text-color-secondary);
    font-size: 0.82rem;
}

.pdv-table-card__meta i {
    margin-right: 0.25rem;
}

.pdv-table-card__consumption {
    margin-top: 0.15rem;
    margin-left: 0.35rem;
    padding: 0.65rem 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid var(--pdv-border-soft);
    background: var(--pdv-muted-bg);
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.5rem;
}

.pdv-table-card__consumption span {
    color: var(--text-color-secondary);
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.pdv-table-card__consumption strong {
    font-size: 1.05rem;
    color: var(--primary-color);
}

.pdv-table-card__footer {
    padding-left: 0.35rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-top: auto;
}

.pdv-table-card__waiter {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    color: var(--text-color-secondary);
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pdv-table-card__waiter--empty {
    font-style: italic;
}

.pdv-table-card__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary-color);
    flex-shrink: 0;
}

.pdv-floor__empty {
    min-height: 280px;
    display: grid;
    place-items: center;
    text-align: center;
    gap: 0.35rem;
    border: 1px dashed var(--pdv-border-soft);
    border-radius: 1rem;
    background: var(--pdv-canvas-bg);
    color: var(--text-color-secondary);
}

.pdv-floor__empty i {
    font-size: 2rem;
}

.pdv-floor__empty h3 {
    margin: 0;
    color: var(--text-color);
}

.pdv-expand-panel {
    padding: 0.75rem;
    border-radius: 0.75rem;
    background: var(--pdv-muted-bg);
}

.pdv-expand-panel h5 {
    margin: 0 0 0.75rem;
}

@media (max-width: 1100px) {
    .pdv-floor__kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .pdv-floor__kpis {
        grid-template-columns: 1fr;
    }

    .pdv-floor__quick-actions {
        width: 100%;
    }

    .pdv-floor__quick-actions :deep(.p-button) {
        flex: 1 1 auto;
    }
}
</style>
