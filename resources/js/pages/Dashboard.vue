<script setup lang="ts">
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import Chart from 'chart.js/auto'; // Import Chart.js
import { onMounted, ref } from 'vue';

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
    loans: number;
    rentals: number;
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const chartRef = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (chartRef.value) {
        new Chart(chartRef.value, {
            type: 'bar', // or 'pie', 'line', etc.
            data: {
                labels: ['Machineries', 'Loans', 'Maintainances', 'Rentals'],
                datasets: [
                    {
                        label: 'Counts',
                        data: [props.machineries, props.loans, props.maintainances, props.rentals],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                        ],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 206, 86, 1)', 'rgba(153, 102, 255, 1)'],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0, // Ensure whole numbers on the y-axis
                    },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-2xl font-bold">{{ machineries }}</CardTitle>
                        <CardTitle class="text-sm opacity-80">Total number of machines</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-green-500 text-white shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-2xl font-bold">{{ loans }}</CardTitle>
                        <CardTitle class="text-sm opacity-80">Total number of loans</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-yellow-500 text-gray-900 shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-2xl font-bold">{{ maintainances }}</CardTitle>
                        <CardTitle class="text-sm opacity-80">Total number of maintainance</CardTitle>
                    </CardHeader>
                </Card>

                <Card class="flex h-[120px] items-center justify-center rounded-xl bg-purple-500 text-gray-900 shadow-lg">
                    <CardHeader class="text-center">
                        <CardTitle class="text-2xl font-bold">{{ maintainances }}</CardTitle>
                        <CardTitle class="text-sm opacity-80">Total number of rentals</CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <Card class="mt-4">
                <CardHeader>
                    <CardTitle>Dashboard Overview</CardTitle>
                </CardHeader>
                <div class="p-4">
                    <canvas ref="chartRef" width="400" height="200"></canvas>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
