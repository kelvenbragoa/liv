<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import moment from 'moment';

const router = useRouter();
const toast = useToast();

const isLoading = ref(true);
const details = ref(null);
const itemSearch = ref('');

function goBackUsingBack() {
    router?.back();
}

function formatDate(value) {
    return value ? moment(value).format('DD-MM-YYYY HH:mm') : '—';
}

function displayValue(value) {
    return value || '—';
}

const note = computed(() => details.value?.note ?? null);
const stockCenter = computed(() => details.value?.stock_center ?? null);
const supplier = computed(() => details.value?.supplier ?? null);
const user = computed(() => details.value?.user ?? null);
const metrics = computed(() => details.value?.metrics ?? {});
const items = computed(() => details.value?.items ?? []);

const filteredItems = computed(() => {
    const q = itemSearch.value.trim().toLowerCase();
    if (!q) return items.value;
    return items.value.filter((item) => String(item.product_name ?? '').toLowerCase().includes(q));
});

const getData = async () => {
    isLoading.value = true;
    try {
        const id = router.currentRoute.value.params.id;
        const response = await axios.get(`/api/exitnotes/${id}`);
        details.value = response.data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: error.response?.data?.message || 'Não foi possível carregar a nota de saída.',
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
    <div v-if="isLoading" class="extnshow-loading">
        <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="8" fill="var(--surface-ground)" animationDuration=".5s" />
        <p>A carregar nota de saída...</p>
    </div>

    <div v-else-if="details" class="extnshow-page">
        <div class="extnshow-card">
            <header class="extnshow-header">
                <div>
                    <p class="extnshow-eyebrow">Movimento de stock · Saída</p>
                    <h1>{{ note?.ref || 'Nota de saída' }}</h1>
                    <p class="extnshow-subtitle">Registada em {{ formatDate(note?.created_at) }}</p>
                </div>
                <div class="extnshow-header__actions">
                    <Button label="Voltar" icon="pi pi-arrow-left" outlined @click="goBackUsingBack" />
                    <Button
                        v-if="stockCenter?.id"
                        label="Ver centro"
                        icon="pi pi-building"
                        severity="secondary"
                        outlined
                        @click="router.push(`/admin/centerstocks/${stockCenter.id}`)"
                    />
                    <Button
                        v-if="supplier?.id"
                        label="Ver fornecedor"
                        icon="pi pi-truck"
                        severity="info"
                        outlined
                        @click="router.push(`/admin/suppliers/${supplier.id}`)"
                    />
                </div>
            </header>

            <section class="extnshow-kpis">
                <div class="extnshow-kpi">
                    <span class="extnshow-kpi__label">Linhas</span>
                    <strong>{{ metrics.items_count ?? 0 }}</strong>
                </div>
                <div class="extnshow-kpi">
                    <span class="extnshow-kpi__label">Produtos declarados</span>
                    <strong>{{ metrics.products_number ?? 0 }}</strong>
                </div>
                <div class="extnshow-kpi extnshow-kpi--highlight">
                    <span class="extnshow-kpi__label">Total retirado</span>
                    <strong class="extnshow-bad">-{{ metrics.total_quantity ?? 0 }}</strong>
                </div>
            </section>

            <section class="extnshow-info">
                <h3>Detalhes do documento</h3>
                <div class="extnshow-info__grid">
                    <p><strong>Referência interna:</strong> {{ displayValue(note?.ref) }}</p>
                    <p><strong>Documento:</strong> {{ displayValue(note?.document_ref) }}</p>
                    <p><strong>Série:</strong> {{ displayValue(note?.serie) }}</p>
                    <p><strong>Centro de stock:</strong> {{ displayValue(stockCenter?.name) }}</p>
                    <p><strong>Fornecedor:</strong> {{ displayValue(supplier?.name) }}</p>
                    <p><strong>Registado por:</strong> {{ displayValue(user?.name) }}</p>
                    <p v-if="note?.obs" class="extnshow-obs"><strong>Observações:</strong> {{ note.obs }}</p>
                </div>
            </section>

            <section class="extnshow-table-section">
                <div class="extnshow-table-header">
                    <h3>Produtos retirados</h3>
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="itemSearch" placeholder="Pesquisar produto..." />
                    </IconField>
                </div>

                <DataTable
                    :value="filteredItems"
                    dataKey="id"
                    :rowHover="true"
                    stripedRows
                    responsiveLayout="scroll"
                    class="extnshow-table"
                >
                    <template #empty>Nenhum produto encontrado.</template>
                    <Column header="Produto" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="extnshow-product">
                                <span>{{ data.product_name || '—' }}</span>
                                <Button
                                    v-if="data.product_id"
                                    icon="pi pi-external-link"
                                    text
                                    rounded
                                    size="small"
                                    @click="router.push(`/admin/products/${data.product_id}`)"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column header="Stock anterior" style="min-width: 8rem">
                        <template #body="{ data }">{{ data.last_quantity }}</template>
                    </Column>
                    <Column header="Quantidade retirada" style="min-width: 9rem">
                        <template #body="{ data }">
                            <span class="extnshow-bad">-{{ data.quantity }}</span>
                        </template>
                    </Column>
                    <Column header="Stock após saída" style="min-width: 9rem">
                        <template #body="{ data }">
                            <strong>{{ data.stock_after }}</strong>
                        </template>
                    </Column>
                </DataTable>
            </section>
        </div>
    </div>
</template>

<style scoped>
.extnshow-loading { min-height: 50vh; display: grid; place-items: center; gap: .75rem; color: var(--text-color-secondary); }
.extnshow-page { --b: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%); --bs: color-mix(in srgb, var(--surface-border) 85%, transparent); --bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%); }
.extnshow-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.1rem; border: 1px solid var(--b); border-radius: 1rem; background: var(--surface-card); box-shadow: 0 1px 2px rgba(15,23,42,.05), 0 0 0 1px var(--bs); }
.extnshow-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
.extnshow-header__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.extnshow-eyebrow { margin: 0; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-color); }
.extnshow-header h1 { margin: .15rem 0 0; font-size: 1.5rem; }
.extnshow-subtitle { margin: .25rem 0 0; color: var(--text-color-secondary); }
.extnshow-kpis { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .75rem; }
.extnshow-kpi { display: flex; flex-direction: column; gap: .2rem; padding: .75rem; border: 1px solid var(--bs); border-radius: .8rem; background: var(--bg); }
.extnshow-kpi--highlight { border-color: color-mix(in srgb, var(--red-500) 35%, var(--bs)); }
.extnshow-kpi__label { font-size: .78rem; color: var(--text-color-secondary); }
.extnshow-info, .extnshow-table-section { padding: .9rem; border: 1px solid var(--bs); border-radius: .85rem; background: var(--bg); }
.extnshow-info h3, .extnshow-table-header h3 { margin: 0; font-size: 1rem; }
.extnshow-info__grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .5rem .9rem; margin-top: .6rem; }
.extnshow-info__grid p { margin: 0; }
.extnshow-obs { grid-column: span 2; }
.extnshow-table-header { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: .75rem; }
.extnshow-product { display: flex; align-items: center; gap: .25rem; }
.extnshow-bad { color: var(--red-500); font-weight: 600; }
@media (max-width: 900px) { .extnshow-kpis, .extnshow-info__grid { grid-template-columns: 1fr; } .extnshow-obs { grid-column: span 1; } }
</style>
