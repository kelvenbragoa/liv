<script setup>
import { computed, ref, onMounted, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';

let interval;
const router = useRouter();
const toast = useToast();

const isLoadingDiv = ref(true);
const searchQuery = ref('');
const statusFilter = ref(null);
const retriviedData = ref(null);
const currentPage = ref(1);

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

const getData = async (page = 1) => {
    return axios
        .get(`/api/pdv?page=${page}`, {
            params: { query: searchQuery.value }
        })
        .then((response) => {
            retriviedData.value = response.data.tables;
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

const debouncedSearch = debounce(() => {
    getData(currentPage.value);
}, 300);

watch(searchQuery, debouncedSearch);

onMounted(() => {
    getData();
    interval = setInterval(() => {
        getData();
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
                    <p class="pdv-floor__eyebrow">Gestão de mesas</p>
                    <h1>Salão</h1>
                    <p class="pdv-floor__subtitle">
                        {{ tableStats.total }} mesas · actualização automática a cada 30s
                    </p>
                </div>
            </div>

            <div class="pdv-floor__kpis pdv-floor__kpis--three">
                <article class="pdv-kpi">
                    <span class="pdv-kpi__label">Total de mesas</span>
                    <strong class="pdv-kpi__value">{{ tableStats.total }}</strong>
                    <small>Visão geral do salão</small>
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
                :to="`/tablemanager/pdv/${table.id}/categories`"
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
                        Ver mesa
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

.pdv-floor__kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.pdv-floor__kpis--three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    border: 1px solid color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 45%, var(--pdv-border-soft));
    background:
        linear-gradient(
            145deg,
            color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 28%, var(--pdv-card-bg)),
            color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 16%, var(--pdv-card-bg))
        );
    box-shadow: var(--pdv-shadow);
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.pdv-table-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--pdv-shadow-hover);
    border-color: color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 58%, var(--pdv-border-soft));
    background:
        linear-gradient(
            145deg,
            color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 36%, var(--pdv-card-bg)),
            color-mix(in srgb, var(--pdv-accent, var(--primary-color)) 22%, var(--pdv-card-bg))
        );
}

.pdv-table-card--free { --pdv-accent: #10b981; }
.pdv-table-card--busy { --pdv-accent: #f59e0b; }
.pdv-table-card--reserved { --pdv-accent: #3b82f6; }
.pdv-table-card--grouped { --pdv-accent: #8b5cf6; }
.pdv-table-card--closing { --pdv-accent: #0ea5e9; }
.pdv-table-card--maintenance { --pdv-accent: #94a3b8; }

.pdv-table-card__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.pdv-table-card__icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.75rem;
    display: grid;
    place-items: center;
    background: color-mix(in srgb, var(--pdv-accent) 32%, var(--pdv-card-bg));
    color: var(--pdv-accent);
    border: 1px solid color-mix(in srgb, var(--pdv-accent) 48%, var(--pdv-border-soft));
}

.pdv-table-card__name {
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.pdv-table-card__meta {
    color: var(--text-color-secondary);
    font-size: 0.82rem;
}

.pdv-table-card__meta i {
    margin-right: 0.25rem;
}

.pdv-table-card__consumption {
    margin-top: 0.15rem;
    padding: 0.65rem 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid color-mix(in srgb, var(--pdv-accent) 35%, var(--pdv-border-soft));
    background: color-mix(in srgb, var(--pdv-accent) 22%, var(--pdv-card-bg));
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
    color: var(--pdv-accent);
}

.pdv-table-card__footer {
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
    color: var(--pdv-accent);
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

@media (max-width: 1100px) {
    .pdv-floor__kpis,
    .pdv-floor__kpis--three {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .pdv-floor__kpis,
    .pdv-floor__kpis--three {
        grid-template-columns: 1fr;
    }
}
</style>
