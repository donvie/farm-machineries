<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BrowserMultiFormatReader } from '@zxing/browser';
import QRCode from 'qrcode';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Machinery', href: '/machinery' }];

const isDialogOpen = ref(false);
const isDialogScannerOpen = ref(false);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const action = ref('');
const headersView = ['Id', 'Technician', 'Work Done', 'Expenses', 'Status', 'Maintenance Date', 'Completed Date', 'Remarks'];
const headersViewRental = [
    'Id',
    'Lessee',
    'Operator',
    'Machine Name',
    'Condition',
    'Rent',
    'Other Expenses',
    'Status',
    'Borrow Date',
    'Completed Date',
    'Remarks',
];

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

const headers = ['Id', 'Machine Name', 'Brand', 'Status', 'Year Acquired'];
let form = useForm({
    machine_name: '',
    brand: '',
    type: '',
    status: 'Available',
    year_acquired: '',
    last_maintenance_date: '',
    next_scheduled_maintenance: '',
    image: null,
    // brand: '',
    serial: '',
    capacity: '',
    accessories: '',
    supplier: '',
    branchAddress: '',
    costPerMachine: '',
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
            alert(decodedResult.getText());

            const urlObj = new URL(decodedResult.getText());
            const pathSegments = urlObj.pathname.split('/');
            const id = pathSegments[pathSegments.length - 1];

            isDialogScannerOpen.value = false;
            const scannedCode = parseInt(id);
            const findIndex = props?.machineries?.data.findIndex((machinery: any) => machinery.id === scannedCode);

            if (findIndex !== -1) {
                selectedItem.value = props?.machineries?.data[findIndex];
                isDialogViewOpen.value = true;
            }

            codeReader.reset();
        }

        // Optional: log only unexpected errors
        if (error && error.name !== 'NotFoundException') {
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
        console.log('dadFormData', FormData);
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
    action.value = 'edit';
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

const handleDownloadQrCode = async (id: number) => {
    const valueToEncode = `https://farmmachineriesmanagementsystem.com/records/${id}`;

    const qrDataURL = await QRCode.toDataURL(valueToEncode, {
        width: 300,
        margin: 4,
        errorCorrectionLevel: 'H',
        color: {
            dark: '#000000',
            light: '#ffffff',
        },
    });

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
                        <Button @click="action = 'add'">Add Machinery</Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Machinery' : 'Edit Machinery' }}</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Machine Name</Label>
                                <Input required id="machine_name" v-model="form.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Brand</Label>
                                <Input required id="type" v-model="form.brand" placeholder="Enter brand" />

                                <Label for="type">Serial</Label>
                                <Input required id="serial" v-model="form.serial" placeholder="Enter Serial" />
                                <Label for="type">Capacity</Label>
                                <Input required id="capacity" v-model="form.capacity" placeholder="Enter Capacity" />
                                <Label for="type">Fee</Label>
                                <Input required id="cost" v-model="form.costPerMachine" placeholder="Enter Fee" />
                                <Label for="type">Attachments/Accesories</Label>
                                <Input required id="accessories" v-model="form.accessories" placeholder="Enter Attachment/Accessories" />
                                <Label for="type">Supplier</Label>
                                <Input required id="supplier" v-model="form.supplier" placeholder="Enter Supplier" />
                                <Label for="type">Branch Address</Label>
                                <Input required id="branchAddress" v-model="form.branchAddress" placeholder="Enter branch Address" />

                                <Label for="year_acquired">Year Acquired</Label>
                                <Input required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" />

                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option disabled value="Available">Available</option>
                                    <option disabled value="In Use">In Use</option>
                                    <option disabled value="Under Maintenance">Under Maintenance</option>
                                    <!-- <option value="Deactivate">Deactivate</option> -->
                                </select>

                                <!-- <Label for="last_maintenance_date">Last Maintenance Date</Label>
                                <Input
                                    required
                                    type="date"
                                    id="last_maintenance_date"
                                    v-model="form.last_maintenance_date"
                                    placeholder="Enter Last Maintenance Date"
                                /> -->

                                <!-- <Label for="next_scheduled_maintenance">Next Scheduled Maintenance</Label>
                                <Input
                                    required
                                    type="date"
                                    id="next_scheduled_maintenance"
                                    v-model="form.next_scheduled_maintenance"
                                    placeholder="Enter Next Scheduled Maintenance"
                                /> -->
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
                                <Input readonly required id="machine_name" v-model="selectedItem.machine_name" placeholder="Enter machine name" />

                                <!-- <Label for="type">Type</Label>
                                <Input readonly required id="type" v-model="selectedItem.type" placeholder="Enter machine type" /> -->

                                <Label for="status">Status</Label>
                                <select disabled id="status" v-model="selectedItem.status" class="w-full rounded border px-3 py-2">
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
                            </div>
                            <DialogTitle class="py-10">List of Maintainances</DialogTitle>

                            <Table
                                :headers="headersView"
                                :data="selectedItem?.maintainances"
                                :filterData="
                                    selectedItem?.maintainances?.map((maintainance) => ({
                                        id: maintainance.id,
                                        name: maintainance.user?.name,
                                        workDone: maintainance.workDone,
                                        expenses: maintainance.expenses,
                                        status: maintainance?.status,
                                        maintainance_date: maintainance.maintainance_date,
                                        completed_date: maintainance.completed_date,
                                        remarks: maintainance?.remarks,
                                    }))
                                "
                                :isHasFilter="true"
                                :isRowClickable="true"
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
                                        id: rental.id || '',
                                        name: rental.user?.name || '',
                                        operator: rental.operator?.name || '',
                                        machine_name: rental?.machinery?.machine_name || '',
                                        condition: rental?.condition || '',
                                        rent: rental?.rent || '',
                                        otherExpenses: rental?.otherExpenses || '',
                                        status: rental?.status || '',
                                        created_at: formattedDate(rental?.created_at, 'yyyy-MM-dd') || '',
                                        completed_date: rental.completedDate,
                                        remarks: rental?.remarks || '',
                                    }))
                                "
                                :isHasFilter="true"
                                :noActions="true"
                                :isRowClickable="true"
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
                    props?.machineries?.data.map(({ id, machine_name, brand, status, year_acquired, maintainances, created_at }) => ({
                        id,
                        machine_name,
                        brand,
                        status,
                        year_acquired,
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                @downloadQrCode="handleDownloadQrCode"
                :isRowClickable="true"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
                :isHasFilter="true"
                :isHasViewBtn="true"
                :isHasDownloadQrCodeBtn="true"
            />
        </div>
    </AppLayout>
</template>
