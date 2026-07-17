<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';
import { createRequestId } from '@/utils/requestId';

const router = useRouter();
const toast = useToast();

const isLoadingDiv = ref(true);
const categories = ref([]);
const selectedProducts = ref([]);
const total = ref(0);
const total_consumed = ref(0);
const isLoadingButton = ref(false);
const order_items = ref([]);
const payment_method_id = ref(1);
const payment_methods = ref([]);
const table = ref(null);
const productSearch = ref('');
const activeCategoryId = ref(null);
const activeSubCategoryId = ref(null);
const compactMode = ref(false);

const CREDIT_PAYMENT_METHOD_ID = 8;
const selectedCustomer = ref(null);
const customerSuggestions = ref([]);
const isCreditPayment = computed(() => Number(payment_method_id.value) === CREDIT_PAYMENT_METHOD_ID);
const amountTendered = ref(null);
const showCashFields = computed(() => !isCreditPayment.value);
const changeAmount = computed(() => {
    const tendered = Number(amountTendered.value ?? 0);
    const orderTotal = Number(total_consumed.value ?? 0);
    if (!tendered || tendered < orderTotal) {
        return 0;
    }
    return tendered - orderTotal;
});

const selectedItemToDelete = ref(null);
const confirmationCode = ref(null);
const correct_code = '012026';
const showDialog = ref(false);
const pdfUrl = ref(null);

const openReceiptDialog = ref(false);
const closeAccountDialog = ref(false);
const payAccountDialog = ref(false);
const deleteDialog = ref(false);

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

const cartCount = computed(() =>
    selectedProducts.value.reduce((sum, item) => sum + item.quantity, 0)
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

function clearCart() {
    selectedProducts.value = [];
    updateTotal();
}

function closeDialog() {
    showDialog.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
}

const searchCustomers = debounce((event) => {
    const query = event.query?.trim() ?? '';

    if (!query) {
        customerSuggestions.value = [];
        return;
    }

    axios
        .get('/api/customers', { params: { query } })
        .then((response) => {
            customerSuggestions.value = response.data.data ?? [];
        })
        .catch(() => {
            customerSuggestions.value = [];
        });
}, 300);

function saveCart() {
    if (!selectedProducts.value.length) {
        toast.add({
            severity: 'warn',
            summary: 'Carrinho vazio',
            detail: 'Adicione produtos antes de enviar para a conta.',
            life: 2500
        });
        return;
    }

    const cartData = {
        products: selectedProducts.value.map((product) => ({
            id: product.id,
            name: product.name,
            quantity: product.quantity,
            total: product.price * product.quantity
        })),
        total: total.value,
        table_id: router.currentRoute.value.params.id,
        request_id: createRequestId()
    };

    isLoadingButton.value = true;
    axios
        .post(`/api/pdv`, cartData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(async (response) => {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Produto encomendado com sucesso!',
                life: 2500
            });
            clearCart();
            await getData();

            const orderId = response.data.order_id;
            if (!orderId) {
                return;
            }

            try {
                const pdfResponse = await axios.post(
                    `/api/getorderreceipt/${orderId}`,
                    {},
                    { responseType: 'blob' }
                );
                const blob = new Blob([pdfResponse.data], { type: 'application/pdf' });
                pdfUrl.value = URL.createObjectURL(blob);
                showDialog.value = true;
            } catch (pdfError) {
                console.error('Erro ao obter recibo:', pdfError);
                toast.add({
                    severity: 'warn',
                    summary: 'Aviso',
                    detail: 'Pedido gravado, mas falhou a impressão do recibo.',
                    life: 4000
                });
            }
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Ocorreu um erro inesperado.',
                life: 3000
            });
        })
        .finally(() => {
            isLoadingButton.value = false;
        });
}

function closeAccount() {
    isLoadingButton.value = true;
    axios
        .get(`/api/pdv/closeaccount/${router.currentRoute.value.params.id}`, {
            params: { request_id: createRequestId() }
        })
        .then(async (response) => {
            closeAccountDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Conta fechada com sucesso!',
                life: 2500
            });

            const orderId = response.data.order_id;
            if (!orderId) {
                return;
            }

            try {
                const pdfResponse = await axios.post(
                    `/api/getfinalreceipt/${orderId}`,
                    {},
                    { responseType: 'blob' }
                );
                const blob = new Blob([pdfResponse.data], { type: 'application/pdf' });
                pdfUrl.value = URL.createObjectURL(blob);
                showDialog.value = true;
            } catch (pdfError) {
                console.error('Erro ao obter recibo:', pdfError);
                toast.add({
                    severity: 'warn',
                    summary: 'Aviso',
                    detail: 'Conta fechada, mas falhou a impressão do recibo.',
                    life: 4000
                });
            }
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Ocorreu um erro inesperado.',
                life: 3000
            });
        })
        .finally(() => {
            isLoadingButton.value = false;
        });
}

function payAccount() {
    if (isCreditPayment.value && !selectedCustomer.value?.id) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Selecione o cliente para venda a crédito.',
            life: 3000
        });
        return;
    }

    if (showCashFields.value && amountTendered.value != null && Number(amountTendered.value) < Number(total_consumed.value)) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'O valor entregue deve ser maior ou igual ao total.',
            life: 3000
        });
        return;
    }

    const payData = {
        payment_method_id: payment_method_id.value,
        table_id: router.currentRoute.value.params.id,
        request_id: createRequestId(),
        ...(showCashFields.value && amountTendered.value != null && amountTendered.value !== ''
            ? { amount_tendered: Number(amountTendered.value) }
            : {}),
        ...(isCreditPayment.value && { customer_id: selectedCustomer.value.id })
    };

    isLoadingButton.value = true;
    axios
        .post(`/api/payaccount`, payData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(async (response) => {
            payAccountDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Conta paga com sucesso!',
                life: 2500
            });

            await getData();

            const orderId = response.data.order_id;
            if (!orderId) {
                return;
            }

            try {
                if (pdfUrl.value) {
                    URL.revokeObjectURL(pdfUrl.value);
                }
                const pdfResponse = await axios.post(
                    `/api/getcustomerreceipt/${orderId}`,
                    {},
                    { responseType: 'blob' }
                );
                const blob = new Blob([pdfResponse.data], { type: 'application/pdf' });
                pdfUrl.value = URL.createObjectURL(blob);
                showDialog.value = true;
            } catch (pdfError) {
                console.error('Erro ao obter recibo:', pdfError);
                toast.add({
                    severity: 'warn',
                    summary: 'Aviso',
                    detail: 'Pagamento gravado, mas falhou a impressão do recibo.',
                    life: 4000
                });
            }
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Ocorreu um erro inesperado.',
                life: 3000
            });
        })
        .finally(() => {
            isLoadingButton.value = false;
        });
}

function addToCart(product) {
    const availableStock = product.quantity_in_principal_stock ?? 0;
    const existingProduct = selectedProducts.value.find((item) => item.id === product.id);

    if (availableStock <= 0) {
        toast.add({
            severity: 'error',
            summary: 'Stock Insuficiente',
            detail: 'Produto sem estoque disponível.',
            life: 2500
        });
        return;
    }

    if (existingProduct) {
        if (existingProduct.quantity < availableStock) {
            existingProduct.quantity += 1;
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Limite de stock',
                detail: `Só há ${availableStock} unidades de ${product.name}.`,
                life: 2500
            });
        }
    } else {
        selectedProducts.value.push({ ...product, quantity: 1 });
    }

    updateTotal();
}

function increaseQty(index) {
    const item = selectedProducts.value[index];
    const availableStock = item.quantity_in_principal_stock ?? 0;

    if (item.quantity < availableStock) {
        item.quantity += 1;
        updateTotal();
    } else {
        toast.add({
            severity: 'warn',
            summary: 'Limite de stock',
            detail: `Só há ${availableStock} unidades de ${item.name}.`,
            life: 2500
        });
    }
}

function decreaseQty(index) {
    const item = selectedProducts.value[index];
    if (item.quantity <= 1) {
        removeFromCart(index);
        return;
    }
    item.quantity -= 1;
    updateTotal();
}

function removeFromCart(index) {
    selectedProducts.value.splice(index, 1);
    updateTotal();
}

function updateTotal() {
    total.value = selectedProducts.value.reduce(
        (sum, item) => sum + item.price * item.quantity,
        0
    );
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

function selectPayment(methodId) {
    payment_method_id.value = methodId;
    if (Number(methodId) === CREDIT_PAYMENT_METHOD_ID) {
        amountTendered.value = null;
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

const confirmDelete = (itemId) => {
    selectedItemToDelete.value = itemId;
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
        .post(`/api/orderitem/${selectedItemToDelete.value}`)
        .then((response) => {
            total_consumed.value = response.data.total_consumed;
            categories.value = response.data.categories;
            order_items.value = response.data.order_items;
            payment_methods.value = response.data.payment_methods;
            payment_method_id.value = 1;
            deleteDialog.value = false;
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Item removido', life: 2500 });
        })
        .catch((error) => {
            toast.add({ severity: 'error', summary: 'Erro', detail: `${error}`, life: 3000 });
        });
};

const getData = async () => {
    return axios
        .get(`/api/pdv/${router.currentRoute.value.params.id}`)
        .then((response) => {
            table.value = response.data.table;
            total_consumed.value = response.data.total_consumed;
            categories.value = response.data.categories || [];
            order_items.value = response.data.order_items || [];
            payment_methods.value = response.data.payment_methods || [];
            payment_method_id.value = payment_methods.value[0]?.id ?? 1;

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

watch(payment_method_id, (value) => {
    if (Number(value) !== CREDIT_PAYMENT_METHOD_ID) {
        selectedCustomer.value = null;
        customerSuggestions.value = [];
    }
});

watch(payAccountDialog, (visible) => {
    if (!visible) {
        selectedCustomer.value = null;
        customerSuggestions.value = [];
        amountTendered.value = null;
    }
});

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
                        autofocus
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

                        <button
                            v-for="product in sub.products"
                            :key="product.id"
                            type="button"
                            class="qs-product"
                            :class="{ 'qs-product--out': (product.quantity_in_principal_stock ?? 0) <= 0 }"
                            :disabled="(product.quantity_in_principal_stock ?? 0) <= 0"
                            @click="addToCart(product)"
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
                        </button>
                    </template>
                </template>
            </div>

            <div
                v-else-if="displayedProducts.length"
                class="qs-grid"
                :class="{ 'qs-grid--compact': compactMode }"
            >
                <button
                    v-for="product in displayedProducts"
                    :key="product.id"
                    type="button"
                    class="qs-product"
                    :class="{ 'qs-product--out': (product.quantity_in_principal_stock ?? 0) <= 0 }"
                    :disabled="(product.quantity_in_principal_stock ?? 0) <= 0"
                    @click="addToCart(product)"
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
                </button>
            </div>

            <div v-else class="qs-empty-products">
                <i class="pi pi-inbox" />
                <p>Nenhum produto encontrado</p>
            </div>
        </section>

        <aside class="qs-cart">
            <div class="qs-cart__header">
                <div>
                    <h2>Novo pedido</h2>
                    <p>{{ cartCount }} {{ cartCount === 1 ? 'item' : 'itens' }}</p>
                </div>
                <Button
                    v-if="selectedProducts.length"
                    icon="pi pi-trash"
                    text
                    rounded
                    severity="danger"
                    v-tooltip.left="'Limpar'"
                    @click="clearCart"
                />
            </div>

            <div v-if="!selectedProducts.length" class="qs-cart__empty">
                <i class="pi pi-shopping-cart" />
                <p>Toque num produto para começar</p>
            </div>

            <div v-else class="qs-cart__list">
                <div
                    v-for="(item, index) in selectedProducts"
                    :key="item.id"
                    class="qs-line"
                >
                    <div class="qs-line__info">
                        <strong>{{ item.name }}</strong>
                        <span>{{ item.price }} MT</span>
                    </div>
                    <div class="qs-line__qty">
                        <button type="button" class="qs-qty-btn" @click="decreaseQty(index)">−</button>
                        <span>{{ item.quantity }}</span>
                        <button type="button" class="qs-qty-btn" @click="increaseQty(index)">+</button>
                    </div>
                    <div class="qs-line__total">
                        {{ item.price * item.quantity }} MT
                    </div>
                    <button type="button" class="qs-line__remove" @click="removeFromCart(index)">
                        <i class="pi pi-times" />
                    </button>
                </div>
            </div>

            <div class="qs-cart__footer">
                <div class="qs-total">
                    <span>Novo pedido</span>
                    <strong>{{ total }} MT</strong>
                </div>

                <Button
                    :label="isLoadingButton ? 'A processar...' : `Adicionar à conta · ${total} MT`"
                    icon="pi pi-plus"
                    class="qs-pay-btn"
                    :loading="isLoadingButton"
                    :disabled="isLoadingButton || !selectedProducts.length"
                    @click="saveCart"
                />

                <div class="qs-mesa-actions">
                    <button type="button" class="qs-action-chip" @click="openReceiptDialog = true">
                        <i class="pi pi-list" />
                        Consumo
                    </button>
                    <button type="button" class="qs-action-chip" @click="printReceipt">
                        <i class="pi pi-print" />
                        Imprimir
                    </button>
                    <button type="button" class="qs-action-chip" @click="closeAccountDialog = true">
                        <i class="pi pi-lock" />
                        Fechar
                    </button>
                    <button type="button" class="qs-action-chip qs-action-chip--primary" @click="payAccountDialog = true">
                        <i class="pi pi-wallet" />
                        Pagar
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <Dialog header="Consumo da Mesa" v-model:visible="openReceiptDialog" :style="{ width: '32rem' }" modal>
        <div class="qs-dialog-list">
            <div
                v-for="item in order_items"
                :key="item.id"
                class="qs-dialog-line"
            >
                <span>{{ item.quantity }} x {{ item.product.name }}</span>
                <span class="qs-dialog-line__right">
                    {{ item.total }} MT
                    <button type="button" class="qs-dialog-remove" @click="confirmDelete(item.id)">
                        <i class="pi pi-trash" />
                    </button>
                </span>
            </div>
            <p v-if="!order_items.length" class="qs-dialog-empty">Sem consumo registado.</p>
        </div>
        <div class="qs-dialog-total">
            <span>Total consumido</span>
            <strong>{{ total_consumed }} MT</strong>
        </div>
        <template #footer>
            <Button label="Fechar" text @click="openReceiptDialog = false" />
        </template>
    </Dialog>

    <Dialog header="Confirmar exclusão" v-model:visible="deleteDialog" :style="{ width: '22rem' }" modal>
        <p class="mb-3">Insira o código para confirmar a exclusão do item.</p>
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

    <Dialog header="Fechar conta" v-model:visible="closeAccountDialog" :style="{ width: '32rem' }" modal>
        <div class="qs-dialog-list">
            <div
                v-for="item in order_items"
                :key="item.id"
                class="qs-dialog-line"
            >
                <span>{{ item.quantity }} x {{ item.product.name }}</span>
                <span>{{ item.total }} MT</span>
            </div>
        </div>
        <div class="qs-dialog-total">
            <span>Total</span>
            <strong>{{ total_consumed }} MT</strong>
        </div>
        <template #footer>
            <Button label="Cancelar" text @click="closeAccountDialog = false" />
            <Button label="Fechar conta" icon="pi pi-lock" :loading="isLoadingButton" @click="closeAccount" />
        </template>
    </Dialog>

    <Dialog header="Pagar conta" v-model:visible="payAccountDialog" :style="{ width: '32rem' }" modal>
        <div class="qs-dialog-list">
            <div
                v-for="item in order_items"
                :key="item.id"
                class="qs-dialog-line"
            >
                <span>{{ item.quantity }} x {{ item.product.name }}</span>
                <span>{{ item.total }} MT</span>
            </div>
        </div>

        <div class="qs-pay-methods qs-pay-methods--dialog">
            <Button
                v-for="method in payment_methods"
                :key="method.id"
                :class="{ 'qs-pay-chip--active': payment_method_id === method.id }"
                @click="selectPayment(method.id)"
            >
                {{ method.name }}
            </Button>
        </div>

        <div v-if="isCreditPayment" class="qs-credit">
            <label>Cliente</label>
            <AutoComplete
                v-model="selectedCustomer"
                :suggestions="customerSuggestions"
                optionLabel="name"
                placeholder="Pesquisar cliente..."
                class="w-full"
                forceSelection
                @complete="searchCustomers"
            />
        </div>

        <div v-if="showCashFields" class="qs-cash">
            <label for="pay_amount_tendered">Valor entregue (MZN)</label>
            <InputNumber
                v-model="amountTendered"
                inputId="pay_amount_tendered"
                mode="decimal"
                :min="0"
                :minFractionDigits="2"
                :maxFractionDigits="2"
                placeholder="Opcional"
                class="w-full"
            />
            <div v-if="changeAmount > 0" class="qs-change">
                <span>Troco</span>
                <strong>{{ changeAmount.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} MT</strong>
            </div>
        </div>

        <div class="qs-dialog-total">
            <span>Total</span>
            <strong>{{ total_consumed }} MT</strong>
        </div>

        <template #footer>
            <Button label="Cancelar" text @click="payAccountDialog = false" />
            <Button
                label="Pagar conta"
                icon="pi pi-wallet"
                :loading="isLoadingButton"
                @click="payAccount"
            />
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
    cursor: pointer;
    box-shadow: var(--qs-shadow);
    transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
    min-height: 220px;
}

.qs-grid--compact .qs-product {
    min-height: 88px;
}

.qs-product:hover:not(:disabled) {
    transform: translateY(-1px);
    border-color: color-mix(in srgb, var(--primary-color) 45%, var(--qs-border-soft));
    box-shadow: var(--qs-shadow-hover);
}

.qs-product:active:not(:disabled) {
    transform: scale(0.98);
}

.qs-product--out {
    opacity: 0.45;
    cursor: not-allowed;
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
    grid-template-columns: minmax(0, 1fr) auto auto auto;
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

.qs-line__info span,
.qs-line__total {
    color: var(--text-color-secondary);
    font-size: 0.8rem;
}

.qs-line__total {
    font-weight: 700;
    color: var(--text-color);
    min-width: 4.5rem;
    text-align: right;
}

.qs-line__qty {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: var(--qs-muted-bg);
    border: 1px solid var(--qs-border-soft);
    border-radius: 999px;
    padding: 0.15rem;
}

.qs-line__qty span {
    min-width: 1.25rem;
    text-align: center;
    font-weight: 700;
}

.qs-qty-btn,
.qs-line__remove {
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 999px;
    border: 1px solid var(--qs-border-soft);
    background: var(--qs-card-bg);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
}

.qs-line__remove {
    background: var(--qs-muted-bg);
    color: var(--text-color-secondary);
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

.qs-pay-methods {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.45rem;
}

.qs-pay-methods--dialog {
    margin: 0.75rem 0;
}

.qs-pay-chip {
    border: 1px solid var(--qs-border-soft);
    background: var(--qs-card-bg);
    border-radius: 0.75rem;
    padding: 0.65rem 0.5rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: var(--qs-shadow);
}

.qs-pay-chip--active {
    border-color: color-mix(in srgb, var(--primary-color) 55%, var(--qs-border-soft));
    background: color-mix(in srgb, var(--primary-color) 12%, var(--qs-card-bg));
    color: var(--primary-color);
    box-shadow: var(--qs-shadow-hover);
}

.qs-credit {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
}

.qs-credit label {
    font-weight: 600;
    font-size: 0.85rem;
}

.qs-cash {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
}

.qs-cash label {
    font-weight: 600;
    font-size: 0.85rem;
}

.qs-change {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 0.65rem;
    border-radius: 0.65rem;
    background: color-mix(in srgb, var(--green-500) 10%, var(--qs-card-bg));
    border: 1px solid color-mix(in srgb, var(--green-500) 25%, var(--qs-border-soft));
    font-size: 0.9rem;
}

.qs-change strong {
    color: var(--green-600);
    font-size: 1rem;
}

.qs-pay-btn {
    width: 100%;
    justify-content: center;
    font-weight: 800 !important;
    font-size: 1.05rem !important;
    padding: 0.9rem 1rem !important;
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
    padding: 0.6rem 0.5rem;
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

.qs-dialog-line__right {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
}

.qs-dialog-remove {
    border: none;
    background: transparent;
    color: var(--red-500, #ef4444);
    cursor: pointer;
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
