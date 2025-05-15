<script setup lang="ts">
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { onMounted, ref, computed} from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    name?: string;
    machineries: number;
    maintainances: number;
    loansData: any[];
    loans: number;
    rentals: number;
    machineriesData: any[]; //  Add type here
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const chartRef = ref<HTMLCanvasElement | null>(null);
const chartRefDuplicate = ref<HTMLCanvasElement | null>(null);

const fertilizerCount = ref(0);
const cashCount = ref(0);

const calculateLoanCounts = () => {
    fertilizerCount.value = props.loansData.reduce((count, item) => {
    return count + item.loans.filter(loan => loan.type === 'Loan Fertilizer').length;
    }, 0);

    cashCount.value = props.loansData.reduce((count, item) => {
    return count + item.loans.filter(loan => loan.type === 'Cash').length;
    }, 0);
};


function groupAndCountMachineStatus(machineData: any[], statuses: string[]) {
    const groupedData = {};

    machineData.forEach((machine) => {
        const { machine_name, status } = machine;

        if (!groupedData[machine_name]) {
            groupedData[machine_name] = {
                machine_name: machine_name,
            };
            statuses.forEach((s) => (groupedData[machine_name][s] = 0));
        }
        if (groupedData[machine_name].hasOwnProperty(status)) {
            groupedData[machine_name][status]++;
        }
    });

    const result = Object.values(groupedData);
    return result;
}

function groupAndCountLoanStatus(loanData: any[], statuses: string[]) {
    const groupedData = {};

    loanData.forEach((loan) => {
        const purposes = loan?.loans || []; // Ensure we have an array to loop through
        const { status } = loan;

        purposes.forEach((purposeObj) => {
            const purpose = purposeObj.purpose.item || purposeObj.purpose;
            if (purpose) {
                if (!groupedData[purpose]) {
                    groupedData[purpose] = {
                        purpose: purpose,
                    };
                    statuses.forEach((s) => (groupedData[purpose][s] = 0));
                }
                if (groupedData[purpose].hasOwnProperty(status)) {
                    groupedData[purpose][status]++;
                }
            }
        });
    });

    const result = Object.values(groupedData);
    return result;
}

onMounted(() => {
    calculateLoanCounts();
    const originalStatuses = ['Available', 'Under Maintenance', 'In Use', 'Rented'];
    console.log('props.machineriesData',props.loansData)
    const groupedDataOriginal = groupAndCountMachineStatus(props.machineriesData, originalStatuses);
    console.log('Original Grouped Data:', groupedDataOriginal);

    const duplicateStatuses = ['Active', 'Paid', 'Overdue'];
    const groupedDataDuplicate = groupAndCountLoanStatus(props.loansData, duplicateStatuses);
    console.log('Duplicate Grouped Data:', groupedDataDuplicate);

    const createChart = (
        chartRef: typeof chartRef,
        groupedData: any[],
        labels: string[],
        datasetLabels: string[],
        backgroundColors: string[],
        borderColors: string[],
        titleText: string,
    ) => {
        if (chartRef.value) {
            const datasets = datasetLabels.map((label, index) => ({
                label: label,
                data: groupedData.map((item) => item[label] || 0), // Handle cases where a status might not exist
                backgroundColor: backgroundColors[index % backgroundColors.length],
                borderColor: borderColors[index % borderColors.length],
                borderWidth: 1,
            }));

            new Chart(chartRef.value, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: titleText,
                            font: {
                                size: 16,
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            precision: 0,
                        },
                    },
                },
            });
        }
    };

    // Create the original chart
    createChart(
        chartRef,
        groupedDataOriginal,
        groupedDataOriginal.map((item) => item.machine_name),
        originalStatuses,
        ['rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)'],
        ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)'],
        'Machine Status by Name',
    );

    // Create the duplicate chart with different statuses (Loan Status by Purpose)
    createChart(
        chartRefDuplicate,
        groupedDataDuplicate,
        groupedDataDuplicate.map((item) => item.purpose), // Use 'purpose' for labels
        duplicateStatuses,
        ['rgba(255, 99, 132, 0.7)', 'rgba(255, 159, 64, 0.7)', 'rgba(255, 205, 86, 0.7)'], // Example colors
        ['rgba(255, 99, 132, 1)', 'rgba(255, 159, 64, 1)', 'rgba(255, 205, 86, 1)'], // Example border colors
        'Loan Status by Purpose', // Update the title to be more accurate
    );
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of machineries</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ machineriesData.length }}</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-pink-500 text-white shadow-lg">                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of available machineries</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ machineriesData?.filter(machine=> machine.status === 'Available').length }}</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-yellow-500 text-white shadow-lg">                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of machineries in use</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ machineriesData?.filter(machine=> machine.status === 'In Use').length }}</CardTitle>
                    </CardHeader>
                </Card>
                
                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-green-500 text-white shadow-lg">                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of machineries under maintainance</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ machineriesData?.filter(machine=> machine.status === 'Under Maintenance').length }}</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-purple-500 text-white shadow-lg">                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of approved fertilizer loan</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ fertilizerCount  }}</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-red-500 text-white shadow-lg">                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of cash loan</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ cashCount }}</CardTitle>
                    </CardHeader>
                </Card>

                <!-- <Card class="flex h-[120px] items-center justify-center rounded-xl bg-green-500 text-white shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of loans</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ loans }}</CardTitle>
                    </CardHeader>
                </Card> -->

                <!-- <Card class="flex h-[120px] items-center justify-center rounded-xl bg-yellow-500 text-gray-900 shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of maintainance</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ maintainances }}</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-purple-500 text-gray-900 shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-xl opacity-80">Total number of rentals</CardTitle>
                        <CardTitle class="text-2xl font-bold">{{ rentals }}</CardTitle>
                    </CardHeader>
                </Card> -->
            </div>

            <!-- <Card class="mt-4">
                <CardHeader>
                    <CardTitle>Machine Status Overview</CardTitle>
                </CardHeader>
                <div class="p-4">
                    <canvas ref="chartRef" width="400" height="200"></canvas>
                </div>
            </Card>

            <Card class="mt-4">
                <CardHeader>
                    <CardTitle>Loan Status by Purpose</CardTitle>
                </CardHeader>
                <div class="p-4">
                    <canvas ref="chartRefDuplicate" width="400" height="200"></canvas>
                </div>
            </Card> -->
        </div>
    </AppLayout>
</template>
