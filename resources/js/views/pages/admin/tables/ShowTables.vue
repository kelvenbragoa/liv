<script setup>
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { onBeforeMount, reactive, ref, onMounted, watch } from 'vue';
import { RouterView, RouterLink, useRouter, useRoute } from 'vue-router';

// import { debounce } from 'lodash';
import { useToast } from 'primevue/usetoast';
import { debounce } from 'lodash-es';

import moment from 'moment';

const router = useRouter();
const toast = useToast();
const loading1 = ref(null);
const isLoadingDiv = ref(true);
const loadingButtonDelete = ref(false);
let dataIdBeingDeleted = ref(null);
const searchQuery = ref('');
const retriviedData = ref(null);
const tableLogsData = ref(null);
const currentPage = ref(1);
const rowsPerPage = ref(15);
const totalRecords = ref(0);
const displayConfirmation = ref(false);
// const chartData = ref(null);
const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'Consumo Mensal',
      backgroundColor: '#42A5F5',
      data: []
    }
  ]
});

const documentStyle = getComputedStyle(document.documentElement);
const borderColor = documentStyle.getPropertyValue('--surface-border');
const textMutedColor = documentStyle.getPropertyValue('--text-color-secondary');

const chartOptions = {
    maintainAspectRatio: false,
    aspectRatio: 0.8,
    scales: {
        x: {
            stacked: true,
            ticks: {
                color: textMutedColor.trim()
            },
            grid: {
                color: 'transparent',
                borderColor: 'transparent'
            }
        },
        y: {
            stacked: true,
            ticks: {
                color: textMutedColor.trim()
            },
            grid: {
                color: borderColor.trim(),
                borderColor: 'transparent',
                drawTicks: false
            }
        }
    }
};




function goBackUsingBack() {
    if (router) {
        router.back();
    }
}

const closeConfirmation = () => {
    displayConfirmation.value = false;
};
const confirmDeletion = (id) => {
    displayConfirmation.value = true;
    dataIdBeingDeleted.value = id;
};


function getSeverity(status) {
    switch (status) {
        case 1:
            return 'success';

        case 2:
            return 'danger';

        case 3:
            return 'warn';

        case 4:
            return 'danger';

        case 5:
            return 'info';

        case 6:
            return 'info';
    }
}

const getData = async (page = 1) => {
    axios
        .get(`/api/tables/${router.currentRoute.value.params.id}`, {
            params: {
                query: searchQuery.value
            }
        })
        .then((response) => {
            retriviedData.value = response.data.table;
            tableLogsData.value = response.data.logs;
            totalRecords.value = response.data.logs.total;
            // chartData.value = response.data.chart;

            chartData.value.labels = response.data.chart.map(item => item.month_name);
            chartData.value.datasets[0].data = response.data.chart.map(item => item.total);

            isLoadingDiv.value = false;
        })
        .catch((error) => {
            isLoadingDiv.value = false;
            toast.add({ severity: 'error', summary: `${error}`, detail: 'Message Detail', life: 3000 });
            goBackUsingBack();
        });
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios
        .delete(`/api/tables/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter((data) => data.id !== dataIdBeingDeleted.value);
            closeConfirmation();
            toast.add({ severity: 'success', summary: `Sucesso`, detail: 'Sucesso ao apagar', life: 3000 });
        })
        .catch((error) => {
            toast.add({ severity: 'error', summary: `Erro`, detail: `${error}`, life: 3000 });
            loadingButtonDelete.value = false;
        })
        .finally(() => {
            loadingButtonDelete.value = false;
        });
};

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;
    getData(currentPage.value);
};

const debouncedSearch = debounce(() => {
    getData(currentPage.value);
}, 300);

watch(searchQuery,debouncedSearch);

onMounted(() => {
    getData();
});
</script>

<template>
    <div
        class="flex flex-col md:flex-row gap-12 min-h-screen items-center justify-center"
        v-if="isLoadingDiv"
    >
        <div class="w-full">
            <div class="flex flex-col gap-4 text-center">
                <ProgressSpinner
                    style="width: 50px; height: 50px"
                    strokeWidth="8"
                    fill="var(--surface-ground)"
                    animationDuration=".5s"
                    aria-label="Custom ProgressSpinner"
                />
                <p>Por Favor Aguarde...</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-12" v-else>
        <div class="w-full">
            <div class="card flex flex-col gap-4">
                <div class="w-full">
                    <Button
                        label="Voltar"
                        class="mr-2 mb-2"
                        @click="goBackUsingBack"
                        ><i class="pi pi-angle-left"></i> Voltar</Button
                    >
                </div>
                <div class="font-semibold text-xl">Mesa</div>
                <p><strong>Mesa:</strong> {{ retriviedData.name }}</p>
                <p><strong>Capacidade:</strong> {{ retriviedData.capacity }}</p>

                <p>
                    <strong>Limite Mensal:</strong>
                    {{ retriviedData.monthly_limit }} MT
                </p>

                <p>
                    <strong>Estado:</strong>
                    <Tag
                        :value="retriviedData.status.name"
                        :severity="getSeverity(retriviedData.status_id)"
                    />
                </p>

                <p>
                    <strong>Data:</strong>
                    {{
                        moment(retriviedData.created_at).format(
                            "DD-MM-YYYY H:mm",
                        )
                    }}
                </p>

                <hr />

                <div class="card">
                    <div class="font-semibold text-xl mb-4">Consumo Mensal</div>
                    <Chart
                        type="bar"
                        :data="chartData"
                        :options="chartOptions"
                        class="h-80"
                    />
                </div>

                <hr />

                <DataTable
                    :value="tableLogsData.data"
                    :paginator="true"
                    :rows="rowsPerPage"
                    :totalRecords="totalRecords"
                    dataKey="id"
                    :lazy="true"
                    :rowHover="true"
                    :loading="isLoadingDiv"
                    :first="(currentPage - 1) * rowsPerPage"
                    :onPage="onPageChange"
                    showGridlines
                >
                    <template #empty>Nenhuma registro encontrado. </template>
                    <template #loading>
                        Carregando, por favor espere.
                    </template>
                    <Column header="ID" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.id }}
                        </template>
                    </Column>
                    <Column header="Limite Anterior" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.old_limit }}
                        </template>
                    </Column>
                    <Column header="Limite Atual" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.new_limit }}
                        </template>
                    </Column>
                    <Column header="Usuario" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.user.name }}
                        </template>
                    </Column>
                    <Column
                        header="Data"
                        dataType="date"
                        style="min-width: 10rem"
                    >
                        <template #body="{ data }">
                            {{
                                moment(data.created_at).format(
                                    "DD-MM-YYYY H:mm",
                                )
                            }}
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
    <Dialog
        header="Confirmação"
        v-model:visible="displayConfirmation"
        :style="{ width: '350px' }"
        :modal="true"
    >
        <div class="flex align-items-center justify-content-center">
            <i
                class="pi pi-exclamation-triangle mr-3"
                style="font-size: 2rem"
            />
            <span>Tem certeza que deseja proceder?</span>
        </div>
        <template #footer>
            <Button
                label="Não"
                icon="pi pi-times"
                @click="closeConfirmation"
                class="p-button-text"
            />
            <Button
                label="Sim"
                icon="pi pi-check"
                @click="deleteData"
                class="p-button-text"
                autofocus
            />
        </template>
    </Dialog>
</template>
