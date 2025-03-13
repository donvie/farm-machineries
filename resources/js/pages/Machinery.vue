<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BrowserMultiFormatReader } from '@zxing/browser';
import QRCode from 'qrcode';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Machinery', href: '/machinery' }];

const isDialogOpen = ref(false);
const isDialogScannerOpen = ref(false);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const headersView = ['Id', 'Name', 'Status', 'Remarks'];
const headersViewRental = ['Id', 'Name', 'Machine Name', 'Created At'];

const props = defineProps<{
    name?: string;
    machineries: {
        data: Array<{
            id: number;
            machine_name: string;
            type: string;
            status: string;
            year_acquired: string;
            maintainances: any;
            rentals: any;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

console.log('propsprops', props);

const headers = ['Id', 'Machine Name', 'Type', 'Status', 'Year Acquired', 'Last Maintainance Date', 'Next Scheduled Maintainance', 'Created At'];
let form = useForm({
    machine_name: '',
    type: '',
    status: 'Available',
    year_acquired: '',
    last_maintenance_date: '',
    next_scheduled_maintenance: '',
    image: null,
});

const result = ref('');
const videoElement = ref(null);

const onScan = () => {
    isDialogScannerOpen.value = true;
    setTimeout(() => {
        onQrCode();
    }, 500);
};

const formattedDate = (dateString: any, formatString: any) => {
    try {
        const date = parseISO(dateString);
        return format(date, formatString);
    } catch (error) {
        return 'Invalid Date';
    }
};

const onQrCode = () => {
    if (!videoElement.value) {
        console.error('No video element found');
        return;
    }

    const codeReader = new BrowserMultiFormatReader();
    codeReader.decodeFromVideoDevice(undefined, videoElement.value, (decodedResult, error) => {
        if (decodedResult) {
            result.value = decodedResult.getText();
            isDialogScannerOpen.value = false;
            const scannedCode = parseInt(36);
            const findIndex = props?.machineries?.data.findIndex((machinery: any) => machinery.id === scannedCode);
            selectedItem.value = props?.machineries?.data[findIndex];
            isDialogViewOpen.value = true;
            codeReader.reset();
        }
        if (error) {
            console.warn('Scanning error:', error);
        }
    });
};

const saveMachinery = (e: Event) => {
    e.preventDefault();

    const formData = new FormData();

    Object.entries(form.data()).forEach(([key, value]) => {
        if (value !== null) {
            formData.append(key, value);
        }
    });

    if (form.image) {
        formData.append('image', form.image);
    }

    if (form.id) {
        router.patch(route('machinery.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        router.post(route('machinery.store'), formData, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    }
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDialogOpen.value = false;
};

const handleView = (item: any) => {
    selectedItem.value = item;
    console.log('item', item);
    isDialogViewOpen.value = true;
};

const handleEdit = (item: any) => {
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleDelete = (itemId: string) => {
    console.log('itemIditemId', itemId);
    if (!confirm('Are you sure you want to delete this machinery?')) {
        return;
    }

    router.delete(route('machinery.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Machinery deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.image = target.files[0]; // Store file object
    }
};

const handleDownloadQrCode = async (id: any) => {
    const qrDataURL = await QRCode.toDataURL(id.toString(), { width: 300 });

    const link = document.createElement('a');
    link.href = qrDataURL;
    link.download = 'qrcode.png';
    link.click();
};
</script>

<template>
    <Head title="Machinery" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Button @click="onScan()" class="mr-2">QR code scanner</Button>
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button>Add Machinery</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>Add New Machinery</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Machine Name</Label>
                                <Input required id="machine_name" v-model="form.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Type</Label>
                                <Input required id="type" v-model="form.type" placeholder="Enter machine type" />

                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>

                                <Label for="year_acquired">Year Acquired</Label>
                                <Input required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" />

                                <Label for="last_maintenance_date">Last Maintenance Date</Label>
                                <Input
                                    required
                                    type="date"
                                    id="last_maintenance_date"
                                    v-model="form.last_maintenance_date"
                                    placeholder="Enter Last Maintenance Date"
                                />

                                <Label for="next_scheduled_maintenance">Next Scheduled Maintenance</Label>
                                <Input
                                    required
                                    type="date"
                                    id="next_scheduled_maintenance"
                                    v-model="form.next_scheduled_maintenance"
                                    placeholder="Enter Next Scheduled Maintenance"
                                />
                            </div>

                            <DialogFooter class="mt-4 gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary" @click="closeModal">Cancel</Button>
                                </DialogClose>
                                <Button :disabled="form.processing">
                                    <button type="submit">Submit</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View Machinery</Button>
                    </DialogTrigger> -->
                    <DialogContent class="h-screen max-h-screen w-screen max-w-none overflow-y-auto">
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle class="mb-10">View Machinery</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Machine Name</Label>
                                <Input required id="machine_name" v-model="form.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Type</Label>
                                <Input required id="type" v-model="form.type" placeholder="Enter machine type" />

                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>

                                <Label for="year_acquired">Year Acquired</Label>
                                <Input required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" />

                                <Label for="last_maintenance_date">Last Maintenance Date</Label>
                                <Input
                                    required
                                    type="date"
                                    id="last_maintenance_date"
                                    v-model="form.last_maintenance_date"
                                    placeholder="Enter Last Maintenance Date"
                                />

                                <Label for="next_scheduled_maintenance">Next Scheduled Maintenance</Label>
                                <Input
                                    required
                                    type="date"
                                    id="next_scheduled_maintenance"
                                    v-model="form.next_scheduled_maintenance"
                                    placeholder="Enter Next Scheduled Maintenance"
                                />
                            </div>
                            <DialogTitle class="py-10">List of Maintainances</DialogTitle>

                            <Table
                                :headers="headersView"
                                :data="selectedItem?.maintainances"
                                :filterData="
                                    selectedItem?.maintainances?.map((maintainance) => ({
                                        id: maintainance.id,
                                        name: maintainance.user?.name,
                                        status: maintainance?.status,
                                        remarks: maintainance?.remarks,
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />

                            <DialogTitle class="py-10">List of Rentals</DialogTitle>
                            <Table
                                title="Rental"
                                :headers="headersViewRental"
                                :data="selectedItem?.rentals"
                                :filterData="
                                    selectedItem?.rentals?.map((rental) => ({
                                        id: rental.id,
                                        name: rental.user?.name,
                                        machine_name: rental?.machinery?.machine_name,
                                        created_at: formattedDate(rental.created_at, 'yyyy-MM-dd'),
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog :open="isDialogScannerOpen" @update:open="isDialogScannerOpen = $event">
                    <DialogContent>
                        <DialogHeader class="mb-3 space-y-3">
                            <DialogTitle>QR Code Scanner</DialogTitle>
                        </DialogHeader>
                        <video ref="videoElement" class="h-auto w-full"></video>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Machinery"
                :headers="headers"
                :data="props?.machineries?.data"
                :filterData="
                    props?.machineries?.data.map(
                        ({
                            id,
                            machine_name,
                            type,
                            status,
                            year_acquired,
                            last_maintenance_date,
                            next_scheduled_maintenance,
                            maintainances,
                            created_at,
                        }) => ({
                            id,
                            machine_name,
                            type,
                            status,
                            year_acquired,
                            last_maintenance_date,
                            next_scheduled_maintenance,
                            created_at: formattedDate(created_at, 'yyyy-MM-dd'),
                        }),
                    )
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                @downloadQrCode="handleDownloadQrCode"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
                :isHasViewBtn="true"
                :isHasDownloadQrCodeBtn="true"
            />
        </div>
    </AppLayout>
</template>
