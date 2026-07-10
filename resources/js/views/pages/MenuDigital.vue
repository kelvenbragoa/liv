<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';

const isLoading = ref(true);
const error = ref(null);
const menu = ref(null);
const searchQuery = ref('');
const activeCategoryId = ref(null);

const restaurant = computed(() => menu.value?.restaurant ?? { name: 'LIV BEIRA' });
const categories = computed(() => menu.value?.categories ?? []);

const filteredCategories = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return categories.value;

    return categories.value
        .map((category) => ({
            ...category,
            products: (category.products ?? []).filter((product) => {
                const haystack = [
                    product.name,
                    product.subcategory_name,
                    product.category_name,
                    category.name
                ]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                return haystack.includes(q);
            })
        }))
        .filter((category) => category.products.length > 0);
});

const totalVisibleProducts = computed(() =>
    filteredCategories.value.reduce((sum, category) => sum + (category.products?.length ?? 0), 0)
);

function formatMoney(value) {
    const number = Number(value ?? 0);
    return number.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function scrollToCategory(categoryId) {
    activeCategoryId.value = categoryId;
    const el = document.getElementById(`mdig-cat-${categoryId}`);
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function loadMenu() {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/api/menu-digital');
        menu.value = response.data;
        activeCategoryId.value = response.data.categories?.[0]?.id ?? null;
    } catch (e) {
        error.value = e.response?.data?.message || 'Não foi possível carregar o menu.';
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    loadMenu();
});
</script>

<template>
    <div class="mdig-page">
        <header class="mdig-header">
            <div class="mdig-header__brand">
                <p class="mdig-header__eyebrow">Menu digital</p>
                <h1>Liv Beira</h1>
            </div>

            <div class="mdig-search">
                <i class="pi pi-search" />
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Pesquisar prato ou bebida..."
                    autocomplete="off"
                    enterkeyhint="search"
                />
                <button v-if="searchQuery" type="button" class="mdig-search__clear" @click="searchQuery = ''">
                    <i class="pi pi-times" />
                </button>
            </div>
        </header>

        <nav v-if="!isLoading && filteredCategories.length" class="mdig-nav" aria-label="Categorias">
            <button
                v-for="category in filteredCategories"
                :key="category.id"
                type="button"
                class="mdig-nav__chip"
                :class="{ 'mdig-nav__chip--active': activeCategoryId === category.id }"
                @click="scrollToCategory(category.id)"
            >
                {{ category.name }}
                <span>{{ category.products.length }}</span>
            </button>
        </nav>

        <main class="mdig-main">
            <div v-if="isLoading" class="mdig-state">
                <ProgressSpinner style="width: 42px; height: 42px" strokeWidth="7" />
                <p>A carregar menu...</p>
            </div>

            <div v-else-if="error" class="mdig-state mdig-state--error">
                <i class="pi pi-exclamation-circle" />
                <p>{{ error }}</p>
                <button type="button" class="mdig-retry" @click="loadMenu">Tentar novamente</button>
            </div>

            <div v-else-if="!filteredCategories.length" class="mdig-state">
                <i class="pi pi-search" />
                <h2>Nada encontrado</h2>
                <p>Tente outro termo de pesquisa.</p>
            </div>

            <template v-else>
                <section
                    v-for="category in filteredCategories"
                    :key="category.id"
                    :id="`mdig-cat-${category.id}`"
                    class="mdig-section"
                >
                    <div class="mdig-section__head">
                        <h2>{{ category.name }}</h2>
                        <span>{{ category.products.length }}</span>
                    </div>

                    <ul class="mdig-list">
                        <li
                            v-for="product in category.products"
                            :key="product.id"
                            class="mdig-item"
                        >
                            <div class="mdig-item__info">
                                <span class="mdig-item__name">{{ product.name }}</span>
                                <span v-if="product.subcategory_name" class="mdig-item__meta">
                                    {{ product.subcategory_name }}
                                </span>
                            </div>
                            <span class="mdig-item__price">{{ formatMoney(product.price) }} MT</span>
                        </li>
                    </ul>
                </section>
            </template>
        </main>

        <footer class="mdig-footer">
            <p>Preços em Meticais (MT). Sujeitos a alteração.</p>
            <p v-if="totalVisibleProducts">{{ totalVisibleProducts }} itens disponíveis</p>
        </footer>
    </div>
</template>

<style scoped>
.mdig-page {
    --mdig-bg: #f8f6f2;
    --mdig-surface: #ffffff;
    --mdig-surface-2: #f1ede6;
    --mdig-border: rgba(15, 23, 42, 0.1);
    --mdig-text: #1e293b;
    --mdig-muted: #64748b;
    --mdig-accent: #b45309;
    --mdig-accent-soft: rgba(180, 83, 9, 0.1);

    min-height: 100dvh;
    background:
        radial-gradient(circle at top, rgba(180, 83, 9, 0.06), transparent 38%),
        var(--mdig-bg);
    color: var(--mdig-text);
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
}

.mdig-header {
    position: sticky;
    top: 0;
    z-index: 20;
    padding: calc(0.9rem + env(safe-area-inset-top)) 1rem 0.85rem;
    background: rgba(248, 246, 242, 0.94);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--mdig-border);
}

.mdig-header__eyebrow {
    margin: 0;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--mdig-accent);
}

.mdig-header h1 {
    margin: 0.15rem 0 0;
    font-size: clamp(1.45rem, 5vw, 1.9rem);
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.mdig-search {
    margin-top: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.7rem 0.85rem;
    border-radius: 999px;
    background: var(--mdig-surface);
    border: 1px solid var(--mdig-border);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.mdig-search i {
    color: var(--mdig-muted);
    font-size: 0.95rem;
}

.mdig-search input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    color: var(--mdig-text);
    font-size: 1rem;
    min-width: 0;
}

.mdig-search input::placeholder {
    color: #94a3b8;
}

.mdig-search__clear {
    border: none;
    background: var(--mdig-surface-2);
    color: var(--mdig-muted);
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 999px;
    display: grid;
    place-items: center;
}

.mdig-nav {
    position: sticky;
    top: calc(6.8rem + env(safe-area-inset-top));
    z-index: 15;
    display: flex;
    gap: 0.45rem;
    overflow-x: auto;
    padding: 0.65rem 1rem;
    background: rgba(248, 246, 242, 0.92);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--mdig-border);
    scrollbar-width: none;
}

.mdig-nav::-webkit-scrollbar {
    display: none;
}

.mdig-nav__chip {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid var(--mdig-border);
    background: var(--mdig-surface);
    color: var(--mdig-text);
    border-radius: 999px;
    padding: 0.48rem 0.8rem;
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
}

.mdig-nav__chip span {
    font-size: 0.72rem;
    color: var(--mdig-muted);
    background: var(--mdig-surface-2);
    border-radius: 999px;
    padding: 0.1rem 0.45rem;
}

.mdig-nav__chip--active {
    background: var(--mdig-accent);
    border-color: var(--mdig-accent);
    color: #fff;
}

.mdig-nav__chip--active span {
    color: rgba(255, 255, 255, 0.88);
    background: rgba(255, 255, 255, 0.18);
}

.mdig-main {
    padding: 0.85rem 1rem 1.25rem;
}

.mdig-section {
    scroll-margin-top: calc(8rem + env(safe-area-inset-top));
    margin-bottom: 1.1rem;
    padding: 0.85rem 0.95rem;
    border-radius: 1rem;
    background: var(--mdig-surface);
    border: 1px solid var(--mdig-border);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
}

.mdig-section__head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.75rem;
    margin-bottom: 0.55rem;
    padding-bottom: 0.45rem;
    border-bottom: 2px solid var(--mdig-accent-soft);
}

.mdig-section__head h2 {
    margin: 0;
    font-size: 1.05rem;
    letter-spacing: -0.02em;
    color: var(--mdig-accent);
}

.mdig-section__head span {
    color: var(--mdig-muted);
    font-size: 0.78rem;
    font-weight: 600;
}

.mdig-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.mdig-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.55rem 0;
    border-bottom: 1px dashed var(--mdig-border);
}

.mdig-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.mdig-item__info {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.mdig-item__name {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.3;
}

.mdig-item__meta {
    font-size: 0.76rem;
    color: var(--mdig-muted);
}

.mdig-item__price {
    flex-shrink: 0;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--mdig-accent);
    white-space: nowrap;
    padding-top: 0.05rem;
}

.mdig-state {
    min-height: 50vh;
    display: grid;
    place-items: center;
    text-align: center;
    gap: 0.55rem;
    color: var(--mdig-muted);
    padding: 2rem 1rem;
}

.mdig-state i {
    font-size: 2rem;
}

.mdig-state h2 {
    margin: 0;
    color: var(--mdig-text);
    font-size: 1.1rem;
}

.mdig-state--error i {
    color: #dc2626;
}

.mdig-retry {
    margin-top: 0.35rem;
    border: 1px solid var(--mdig-border);
    background: var(--mdig-surface);
    color: var(--mdig-text);
    border-radius: 999px;
    padding: 0.55rem 1rem;
    font-weight: 600;
}

.mdig-footer {
    padding: 0.5rem 1rem 1rem;
    text-align: center;
    color: var(--mdig-muted);
    font-size: 0.75rem;
}

.mdig-footer p {
    margin: 0.15rem 0;
}

@media (min-width: 720px) {
    .mdig-page {
        max-width: 640px;
        margin: 0 auto;
        border-left: 1px solid var(--mdig-border);
        border-right: 1px solid var(--mdig-border);
    }
}
</style>
