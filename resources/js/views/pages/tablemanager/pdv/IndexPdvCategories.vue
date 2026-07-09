<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';

const router = useRouter();
const toast = useToast();

const isLoadingDiv = ref(true);
const categories = ref([]);
const total_consumed = ref(0);
const order_items = ref([]);
const table = ref(null);
const productSearch = ref('');
const activeCategoryId = ref(null);
const activeSubCategoryId = ref(null);
const compactMode = ref(false);
const showDialog = ref(false);
const pdfUrl = ref(null);
const openReceiptDialog = ref(false);

const activeCategory = computed(() =>
    (categories.value || []).find((c) => c.id === activeCategoryId.value) || null
);

const activeSubCategories = computed(() => activeCategory.value?.sub_categories || []);

const displayedProducts = computed(() => {
    const q = productSearch.value.trim().toLowerCase();
    const list = [];

    for (const category of categories.value || []) {
        for (const sub of category.sub_categories || []) {
            for (const product of sub.products || []) {
                const matchesSearch = !q || (product.name || '').toLowerCase().includes(q);
                const matchesCategory = !activeCategoryId.value || category.id === activeCategoryId.value;
                const matchesSub =
                    !activeSubCategoryId.value || sub.id === activeSubCategoryId.value;

                if (matchesSearch && matchesCategory && matchesSub) {
                    list.push(product);
                }
            }
        }
    }

    return list.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
});

const groupedProductSections = computed(() => {
    if (activeCategoryId.value) {
        return [];
    }

    const q = productSearch.value.trim().toLowerCase();
    const sections = [];

    for (const category of categories.value || []) {
        const subSections = [];

        for (const sub of category.sub_categories || []) {
            const products = (sub.products || [])
                .filter((product) => !q || (product.name || '').toLowerCase().includes(q))
                .sort((a, b) => (a.name || '').localeCompare(b.name || ''));

            if (products.length) {
                subSections.push({
                    id: sub.id,
                    name: sub.name,
                    products
                });
            }
        }

        if (subSections.length) {
            sections.push({
                id: category.id,
                name: category.name,
                subCategories: subSections
            });
        }
    }

    return sections;
});

const showGroupedProducts = computed(
    () => !activeCategoryId.value && groupedProductSections.value.length > 0
);

function goBackUsingBack() {
    router?.back();
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

function printReceipt() {
    axios
        .post(`/api/getreceipt/${router.currentRoute.value.params.id}`, {}, { responseType: 'blob' })
        .then((response) => {
            const blob = new Blob([response.data], { type: 'application/pdf' });
            pdfUrl.value = URL.createObjectURL(blob);
            showDialog.value = true;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Consumo impresso com sucesso!',
                life: 2500
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

function selectCategory(categoryId) {
    activeCategoryId.value = categoryId;
    const category = (categories.value || []).find((c) => c.id === categoryId);
    activeSubCategoryId.value =
        category?.sub_categories?.length ? category.sub_categories[0].id : null;
}

function selectAllCategories() {
    activeCategoryId.value = null;
    activeSubCategoryId.value = null;
}

const getData = async () => {
    return axios
        .get(`/api/pdv/${router.currentRoute.value.params.id}`)
        .then((response) => {
            table.value = response.data.table;
            total_consumed.value = response.data.total_consumed;
            categories.value = response.data.categories || [];
            order_items.value = response.data.order_items || [];

            if (categories.value.length && !activeCategoryId.value) {
                selectCategory(categories.value[0].id);
            }

            isLoadingDiv.value = false;
        })
        .catch((error) => {
            isLoadingDiv.value = false;
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Erro ao carregar mesa',
                life: 3000
            });
            goBackUsingBack();
        });
};

onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="isLoadingDiv" class="flex min-h-screen items-center justify-center">
        <div class="flex flex-col gap-4 items-center text-center">
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="8"
                fill="var(--surface-ground)"
                animationDuration=".5s"
            />
            <p>A preparar a mesa...</p>
        </div>
    </div>

    <div v-else class="qs-shell">
        <section class="qs-products">
            <header class="qs-toolbar">
                <div class="qs-search-wrap">
                    <i class="pi pi-search qs-search-icon" />
                    <InputText
                        v-model="productSearch"
                        placeholder="Pesquisar produto..."
                        class="qs-search"
                    />
                    <Button
                        v-if="productSearch"
                        icon="pi pi-times"
                        text
                        rounded
                        severity="secondary"
                        @click="productSearch = ''"
                    />
                </div>

                <div class="qs-toolbar-actions">
                    <span class="qs-table-meta">
                        <strong>{{ table?.name || 'Mesa' }}</strong>
                        · {{ total_consumed }} MT consumido
                    </span>
                    <Button
                        :label="compactMode ? 'Com foto' : 'Compacto'"
                        :icon="compactMode ? 'pi pi-image' : 'pi pi-th-large'"
                        text
                        severity="secondary"
                        @click="compactMode = !compactMode"
                    />
                    <Button
                        icon="pi pi-arrow-left"
                        label="Voltar"
                        text
                        severity="secondary"
                        @click="goBackUsingBack"
                    />
                </div>
            </header>

            <div class="qs-filters">
                <button
                    type="button"
                    class="qs-chip"
                    :class="{ 'qs-chip--active': !activeCategoryId }"
                    @click="selectAllCategories"
                >
                    Todos
                </button>
                <button
                    v-for="category in categories"
                    :key="category.id"
                    type="button"
                    class="qs-chip"
                    :class="{ 'qs-chip--active': activeCategoryId === category.id }"
                    @click="selectCategory(category.id)"
                >
                    {{ category.name }}
                </button>
            </div>

            <div v-if="activeCategoryId && activeSubCategories.length" class="qs-filters qs-filters--sub">
                <button
                    v-for="sub in activeSubCategories"
                    :key="sub.id"
                    type="button"
                    class="qs-chip qs-chip--sub"
                    :class="{ 'qs-chip--active': activeSubCategoryId === sub.id }"
                    @click="activeSubCategoryId = sub.id"
                >
                    {{ sub.name }}
                </button>
            </div>

            <div v-if="showGroupedProducts" class="qs-grid" :class="{ 'qs-grid--compact': compactMode }">
                <template v-for="section in groupedProductSections" :key="section.id">
                    <div class="qs-section-header">
                        <span>{{ section.name }}</span>
                        <small>
                            {{
                                section.subCategories.reduce((sum, sub) => sum + sub.products.length, 0)
                            }}
                            produtos
                        </small>
                    </div>

                    <template v-for="sub in section.subCategories" :key="`${section.id}-${sub.id}`">
                        <div class="qs-subsection-header">{{ sub.name }}</div>

                        <article
                            v-for="product in sub.products"
                            :key="product.id"
                            class="qs-product qs-product--readonly"
                            :class="{ 'qs-product--out': (product.quantity_in_principal_stock ?? 0) <= 0 }"
                        >
                            <div v-if="!compactMode" class="qs-product__image">
                                <img
                                    :src="product.image ? `/${product.image}` : '/image/image.png'"
                                    :alt="product.name"
                                />
                            </div>
                            <div class="qs-product__body">
                                <div class="qs-product__name">{{ product.name }}</div>
                                <div class="qs-product__meta">
                                    <span class="qs-product__price">{{ product.price }} MT</span>
                                    <span class="qs-product__stock">
                                        {{ (product.quantity_in_principal_stock ?? 0) > 0
                                            ? `Stock ${product.quantity_in_principal_stock}`
                                            : 'Sem stock' }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    </template>
                </template>
            </div>

            <div
                v-else-if="displayedProducts.length"
                class="qs-grid"
                :class="{ 'qs-grid--compact': compactMode }"
            >
                <article
                    v-for="product in displayedProducts"
                    :key="product.id"
                    class="qs-product qs-product--readonly"
                    :class="{ 'qs-product--out': (product.quantity_in_principal_stock ?? 0) <= 0 }"
                >
                    <div v-if="!compactMode" class="qs-product__image">
                        <img
                            :src="product.image ? `/${product.image}` : '/image/image.png'"
                            :alt="product.name"
                        />
                    </div>
                    <div class="qs-product__body">
                        <div class="qs-product__name">{{ product.name }}</div>
                        <div class="qs-product__meta">
                            <span class="qs-product__price">{{ product.price }} MT</span>
                            <span class="qs-product__stock">
                                {{ (product.quantity_in_principal_stock ?? 0) > 0
                                    ? `Stock ${product.quantity_in_principal_stock}`
                                    : 'Sem stock' }}
                            </span>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="qs-empty-products">
                <i class="pi pi-inbox" />
                <p>Nenhum produto encontrado</p>
            </div>
        </section>

        <aside class="qs-cart">
            <div class="qs-cart__header">
                <div>
                    <h2>Consumo</h2>
                    <p>{{ order_items.length }} {{ order_items.length === 1 ? 'item' : 'itens' }}</p>
                </div>
            </div>

            <div v-if="!order_items.length" class="qs-cart__empty">
                <i class="pi pi-receipt" />
                <p>Sem consumo registado nesta mesa</p>
            </div>

            <div v-else class="qs-cart__list">
                <div
                    v-for="item in order_items"
                    :key="item.id"
                    class="qs-line qs-line--readonly"
                >
                    <div class="qs-line__info">
                        <strong>{{ item.product.name }}</strong>
                        <span>{{ item.quantity }} x {{ item.price }} MT</span>
                    </div>
                    <div class="qs-line__total">
                        {{ item.total }} MT
                    </div>
                </div>
            </div>

            <div class="qs-cart__footer">
                <div class="qs-total">
                    <span>Total consumido</span>
                    <strong>{{ total_consumed }} MT</strong>
                </div>

                <div class="qs-mesa-actions qs-mesa-actions--single">
                    <button type="button" class="qs-action-chip" @click="openReceiptDialog = true">
                        <i class="pi pi-list" />
                        Ver detalhe
                    </button>
                    <button type="button" class="qs-action-chip qs-action-chip--primary" @click="printReceipt">
                        <i class="pi pi-print" />
                        Imprimir
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <Dialog header="Consumo da mesa" v-model:visible="openReceiptDialog" :style="{ width: '32rem' }" modal>
        <div class="qs-dialog-list">
            <div
                v-for="item in order_items"
                :key="item.id"
                class="qs-dialog-line"
            >
                <span>{{ item.quantity }} x {{ item.product.name }}</span>
                <span>{{ item.total }} MT</span>
            </div>
            <p v-if="!order_items.length" class="qs-dialog-empty">Sem consumo registado.</p>
        </div>
        <div class="qs-dialog-total">
            <span>Total consumido</span>
            <strong>{{ total_consumed }} MT</strong>
        </div>
        <template #footer>
            <Button label="Fechar" text @click="openReceiptDialog = false" />
            <Button label="Imprimir" icon="pi pi-print" @click="printReceipt" />
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
.qs-shell {
    --qs-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --qs-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --qs-panel-bg: var(--surface-card);
    --qs-canvas-bg: color-mix(in srgb, var(--surface-ground) 82%, var(--text-color) 6%);
    --qs-card-bg: color-mix(in srgb, var(--surface-card) 88%, var(--surface-ground) 12%);
    --qs-muted-bg: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --qs-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 0 0 1px var(--qs-border-soft);
    --qs-shadow-hover: 0 6px 16px rgba(15, 23, 42, 0.08), 0 0 0 1px color-mix(in srgb, var(--primary-color) 35%, var(--qs-border-soft));

    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 0.85rem;
    height: calc(100vh - 7rem);
    min-height: 560px;
    padding: 0.25rem;
}

.qs-products {
    display: flex;
    flex-direction: column;
    min-height: 0;
    background: var(--qs-panel-bg);
    border: 1px solid var(--qs-border);
    border-radius: 1rem;
    box-shadow: var(--qs-shadow);
    overflow: hidden;
}

.qs-toolbar {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem 0.5rem;
    flex-shrink: 0;
    background: var(--qs-panel-bg);
    border-bottom: 1px solid var(--qs-border-soft);
}

.qs-search-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    background: var(--qs-muted-bg);
    border: 1px solid var(--qs-border-soft);
    border-radius: 999px;
    padding: 0.15rem 0.65rem;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}

.qs-search-icon {
    color: var(--text-color-secondary);
}

.qs-search {
    width: 100%;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
}

.qs-toolbar-actions {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    flex-shrink: 0;
}

.qs-table-meta {
    font-size: 0.82rem;
    color: var(--text-color-secondary);
    white-space: nowrap;
    padding: 0 0.35rem;
}

.qs-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.35rem 1rem 0.75rem;
    flex-shrink: 0;
    background: var(--qs-panel-bg);
    border-bottom: 1px solid var(--qs-border-soft);
}

.qs-filters--sub {
    padding-top: 0;
}

.qs-chip {
    border: 1px solid var(--qs-border-soft);
    background: var(--qs-muted-bg);
    color: var(--text-color);
    border-radius: 999px;
    padding: 0.45rem 0.9rem;
    white-space: nowrap;
    cursor: pointer;
    font-weight: 600;
    box-shadow: var(--qs-shadow);
    transition: 0.15s ease;
}

.qs-chip--sub {
    font-weight: 500;
    opacity: 0.9;
}

.qs-chip--active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: var(--primary-contrast-color, #fff);
}

.qs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 0.85rem;
    padding: 0.85rem 1rem 1rem;
    overflow: auto;
    flex: 1;
    min-height: 0;
    align-content: start;
    background: var(--qs-canvas-bg);
}

.qs-section-header,
.qs-subsection-header {
    grid-column: 1 / -1;
}

.qs-section-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 0.35rem;
    padding: 0.55rem 0.75rem 0.45rem;
    font-size: 1rem;
    font-weight: 800;
    color: var(--text-color);
    background: color-mix(in srgb, var(--qs-panel-bg) 88%, var(--primary-color) 12%);
    border: 1px solid var(--qs-border-soft);
    border-radius: 0.65rem;
    box-shadow: var(--qs-shadow);
}

.qs-section-header small {
    color: var(--text-color-secondary);
    font-size: 0.75rem;
    font-weight: 600;
}

.qs-subsection-header {
    padding: 0.35rem 0.5rem 0.15rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-color-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-left: 3px solid color-mix(in srgb, var(--primary-color) 55%, var(--qs-border));
    margin-left: 0.15rem;
}

.qs-grid--compact {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
}

.qs-product {
    display: flex;
    flex-direction: column;
    text-align: left;
    border: 1px solid var(--qs-border-soft);
    background: var(--qs-card-bg);
    border-radius: 0.9rem;
    overflow: hidden;
    box-shadow: var(--qs-shadow);
    min-height: 220px;
}

.qs-product--readonly {
    cursor: default;
}

.qs-grid--compact .qs-product {
    min-height: 88px;
}

.qs-product--out {
    opacity: 0.45;
}

.qs-product__image {
    height: 132px;
    flex-shrink: 0;
    overflow: hidden;
    background: color-mix(in srgb, var(--qs-muted-bg) 80%, var(--text-color) 8%);
    border-bottom: 1px solid var(--qs-border-soft);
}

.qs-product__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.qs-product__body {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 0.7rem 0.75rem 0.85rem;
    background: var(--qs-card-bg);
    border-top: 1px solid var(--qs-border-soft);
}

.qs-product__name {
    font-weight: 700;
    line-height: 1.2;
    font-size: 0.95rem;
}

.qs-product__meta {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    align-items: baseline;
}

.qs-product__price {
    color: var(--primary-color);
    font-weight: 800;
}

.qs-product__stock {
    color: var(--text-color-secondary);
    font-size: 0.75rem;
}

.qs-empty-products {
    flex: 1;
    display: grid;
    place-items: center;
    color: var(--text-color-secondary);
    gap: 0.5rem;
    padding: 2rem;
    background: var(--qs-canvas-bg);
}

.qs-empty-products i {
    font-size: 2rem;
}

.qs-cart {
    display: flex;
    flex-direction: column;
    min-height: 0;
    background: var(--qs-panel-bg);
    border: 1px solid var(--qs-border);
    border-radius: 1rem;
    box-shadow: var(--qs-shadow);
    overflow: hidden;
}

.qs-cart__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1rem 0.75rem;
    border-bottom: 1px solid var(--qs-border-soft);
    background: var(--qs-panel-bg);
}

.qs-cart__header h2 {
    margin: 0;
    font-size: 1.15rem;
}

.qs-cart__header p {
    margin: 0.15rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.85rem;
}

.qs-cart__empty {
    flex: 1;
    display: grid;
    place-items: center;
    text-align: center;
    color: var(--text-color-secondary);
    padding: 1.5rem;
    gap: 0.5rem;
    background: var(--qs-canvas-bg);
}

.qs-cart__empty i {
    font-size: 2rem;
}

.qs-cart__list {
    flex: 1;
    overflow: auto;
    padding: 0.65rem 0.75rem;
    background: var(--qs-canvas-bg);
}

.qs-line {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 0.5rem;
    align-items: center;
    padding: 0.7rem 0.55rem;
    margin-bottom: 0.45rem;
    border: 1px solid var(--qs-border-soft);
    border-radius: 0.75rem;
    background: var(--qs-card-bg);
    box-shadow: var(--qs-shadow);
}

.qs-line__info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.qs-line__info strong {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.qs-line__info span {
    color: var(--text-color-secondary);
    font-size: 0.8rem;
}

.qs-line__total {
    font-weight: 700;
    min-width: 4.5rem;
    text-align: right;
}

.qs-cart__footer {
    border-top: 1px solid var(--qs-border-soft);
    padding: 0.9rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: var(--qs-muted-bg);
}

.qs-total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}

.qs-total span {
    color: var(--text-color-secondary);
    font-weight: 600;
}

.qs-total strong {
    font-size: 1.65rem;
    letter-spacing: -0.02em;
}

.qs-mesa-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.45rem;
}

.qs-action-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 1px solid var(--qs-border-soft);
    background: var(--qs-card-bg);
    border-radius: 0.75rem;
    padding: 0.75rem 0.5rem;
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    box-shadow: var(--qs-shadow);
}

.qs-action-chip--primary {
    border-color: color-mix(in srgb, var(--primary-color) 55%, var(--qs-border-soft));
    background: color-mix(in srgb, var(--primary-color) 12%, var(--qs-card-bg));
    color: var(--primary-color);
}

.qs-dialog-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    max-height: 18rem;
    overflow: auto;
}

.qs-dialog-line {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.65rem 0.75rem;
    border: 1px solid var(--qs-border-soft);
    border-radius: 0.65rem;
    background: var(--qs-muted-bg);
}

.qs-dialog-empty {
    color: var(--text-color-secondary);
    text-align: center;
    padding: 1rem;
}

.qs-dialog-total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-top: 0.85rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--qs-border-soft);
}

.qs-dialog-total strong {
    font-size: 1.25rem;
    color: var(--primary-color);
}

@media (max-width: 1100px) {
    .qs-shell {
        grid-template-columns: 1fr;
        height: auto;
    }

    .qs-cart {
        min-height: 420px;
    }

    .qs-table-meta {
        display: none;
    }
}
</style>
