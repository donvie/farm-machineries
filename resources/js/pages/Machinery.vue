<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { BrowserMultiFormatReader } from '@zxing/browser';
import QRCode from 'qrcode';

import { onMounted, ref, computed} from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';
import axios from 'axios';
import { Link, usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Machinery', href: '/machinery' }];

const page = usePage();
const auth = computed(() => page.props.auth);
const role = computed(() => page.props.auth.user.role);
const isDialogOpen = ref(false);
const isDialogScannerOpen = ref(false);
const isHide1 = ref(false);
const isHide2 = ref(false);
const isHide3 = ref(false);
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
    'Borrow Date','Work start date',
    'Completed Date',
    'Remarks',
];


const headers2323 =['Id', 'Machine Name', 'Brand', 'Serial', 'Status', 'Check fluid levels', 'Other Parts', 'Start Date', 'Completed Date', 'Remarks'];

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

const headers = ['Id', 'Machine Name', 'Brand', 'Serial', 'Status', 'Year Acquired'];
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
const filter = ref('All');

onMounted(() => {
    props?.machineries?.data.forEach(element => {
        const totalUsed = parseInt(
            element.rentals
                .filter(dd => dd.status === 'Returned')
                .reduce((sum, item) => sum + Number(item.numOfUsed || 0), 0)
            );

            if (totalUsed === 200  && element.count === '') {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }

            if (totalUsed === 400  && parseInt(element.count) === 200) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }



            if (totalUsed === 600 && parseInt(element.count) === 400) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }

            

            if (totalUsed === 800  && parseInt(element.count) ===600) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }

            

            if (totalUsed === 1000  && parseInt(element.count) === 800) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }

            if (totalUsed === 1200  && parseInt(element.count) === 1000) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }

            if (totalUsed === 1400  && parseInt(element.count) === 1200) {
                handleNotifySMS(element)
                element.count = totalUsed
        
                router.patch(route('machinery.update', element.id), element, {
                    preserveScroll: true,
                    onSuccess: () => closeModal(),
                    onError: (errors) => console.error('Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            }
    });
});

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

const handleNotifySMS = async (item) => {
    // console.log('item', item);
    try {
    // alert('Sending SMS...');
        const response = await axios.post('/send-email', {
            // Change route
            email: 'hannalykaramos0805+technician@gmail.com', // Replace with dynamic email from item if needed
            subject: 'Maintenance Required for Machinery',
            message: `
            I hope this message finds you well—please be informed that Machine: ${item.machine_name} requires maintenance; kindly prioritize this request and let us know your earliest availability for inspection and repair—feel free to reach out if any tools or parts are needed.
           `
            
        });

        if (response.data.success) {
            // alert('Email sent successfully!');
        } else {
            // alert('Failed to send email.');
        }
    } catch (error) {
        console.log('error', error);
        alert('Error sending email.');
    }

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
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
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

const filteredMachineries = computed(() => {
  if (filter.value === 'All') {
    return props?.machineries?.data;
  } else if (filter.value === 'Under Maintenance') {
    return props?.machineries?.data.filter(
      (machinery) => machinery.status === 'Under Maintenance'
    );
  } else {
    return props?.machineries?.data.filter((machinery) => machinery.status === filter.value);
  }
});

// Computed property for the filterData prop of the Table component
const filteredMachineriesForTable = computed(() => {
  return filteredMachineries.value.map(({ id, machine_name, brand, serial, status, year_acquired }) => ({
    id,
    machine_name,
    brand,
    serial,
    status,
    year_acquired,
  }));
});

</script>

<template>
    <Head title="Machinery" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Button @click="onScan()" class="mr-2">QR code scanner</Button>
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button v-if="role !== 'management'"  @click="action = 'add'">Add Machinery</Button>
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
                                <Input style="background: white" required id="machine_name" v-model="form.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Brand</Label>
                                <Input style="background: white" required id="type" v-model="form.brand" placeholder="Enter brand" />

                                <Label for="type">Serial</Label>
                                <Input style="background: white" required id="serial" v-model="form.serial" placeholder="Enter Serial" />
                                <Label for="type">Capacity</Label>
                                <Input style="background: white" required id="capacity" v-model="form.capacity" placeholder="Enter Capacity" />
                                <!-- <Label for="type">Fee</Label> -->
                                <!-- <Input required id="cost" v-model="form.costPerMachine" placeholder="Enter Fee" /> -->
                                <!-- <Label for="type">Attachments/Accesories</Label>
                                <Input required id="accessories" v-model="form.accessories" placeholder="Enter Attachment/Accessories" /> -->
                                <Label for="type">Supplier</Label>
                                <Input style="background: white" required id="supplier" v-model="form.supplier" placeholder="Enter Supplier" />
                                <Label for="type">Branch Address</Label>
                                <Input style="background: white" required id="branchAddress" v-model="form.branchAddress" placeholder="Enter branch Address" />

                                <Label for="year_acquired">Year Acquired</Label>
                                <Input style="background: white" required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" />

                                <Label v-if="action === 'edit'" for="status">Status</Label>
                                <select style="background: white" v-if="action === 'edit'" id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
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
                                    <Button type="submit">Submit</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
                <select id="status" v-model="filter" class="ml-2" style="height: 37px">
                    <option value="All">All</option>
                    <option value="Available">Available</option>
                    <option value="In Use">In Use</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                </select>

                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View Machinery</Button>
                    </DialogTrigger> -->
                    <DialogContent class="h-screen max-h-screen w-screen max-w-none overflow-y-auto">
                        <div>
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle class="mb-10">View Machinery</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <Label for="machine_name">Machine Name</Label>
    <Input readonly style="background: white" required id="machine_name" v-model="form.machine_name" placeholder="Enter machine name" />
  </div>

  <div>
    <Label for="status">Status</Label>
    <select disabled id="status" v-model="selectedItem.status" class="w-full rounded border px-3 py-2">
      <option value="Available">Available</option>
      <option value="In Use">In Use</option>
      <option value="Under Maintenance">Under Maintenance</option>
    </select>
  </div>

  <div>
    <Label for="type">Brand</Label>
    <Input readonly style="background: white" required id="type" v-model="form.brand" placeholder="Enter brand" />
  </div>

  <div>
    <Label for="serial">Serial</Label>
    <Input readonly style="background: white" required id="serial" v-model="form.serial" placeholder="Enter Serial" />
  </div>

  <div>
    <Label for="capacity">Capacity</Label>
    <Input readonly style="background: white" required id="capacity" v-model="form.capacity" placeholder="Enter Capacity" />
  </div>

  <div>
    <Label for="supplier">Supplier</Label>
    <Input readonly style="background: white" required id="supplier" v-model="form.supplier" placeholder="Enter Supplier" />
  </div>

  <div>
    <Label for="branchAddress">Branch Address</Label>
    <Input readonly style="background: white" required id="branchAddress" v-model="form.branchAddress" placeholder="Enter branch Address" />
  </div>

  <div>
    <Label for="year_acquired">Year Acquired</Label>
    <Input readonly style="background: white" required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" />
  </div>
  
  <div>
    <Label for="year_acquired">Number of hours used</Label>
    <Input v-if="selectedItem && selectedItem.rentals" readonly style="background: white" required id="branchAddress" :placeholder="selectedItem.rentals.filter(dd => dd.status === 'Returned').reduce((sum, item) => sum + Number(item.numOfUsed || 0), 0)" />
  </div>
</div>

                            <DialogTitle class="py-10">List of Preventive Maintenance Schedules 
                            <Button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide1 =! isHide1">{{isHide1 ? 'Hide details' : 'Show details'}}</button></DialogTitle>

                            <Table
                                v-if="isHide1"
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

                            <DialogTitle class="py-10">List of Rentals  <Button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide2 =! isHide2">{{isHide1 ? 'Hide details' : 'Show details'}}</button></DialogTitle>
                           
                            <Table
                                title="Rental"
                                :headers="headersViewRental"
                                :data="selectedItem?.rentals"
                                v-if="isHide2"
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
                                        startDate: rental.startDate,
                                        completed_date: rental.completedDate,
                                        remarks: rental?.remarks || '',
                                    }))
                                "
                                :isHasFilter="true"
                                :noActions="true"
                                :isRowClickable="true"
                                :perPage="10"
                            />

                            <DialogTitle class="py-10">List of technician routine checking every week  <Button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide3 =! isHide3">{{isHide1 ? 'Hide details' : 'Show details'}}</button></DialogTitle>
                           
                            <Table
                                title="List of technician routine checking every week"
                                :headers="headers2323"
                                :data="selectedItem?.technicians"
                                v-if="isHide3"
                                :filterData="
                                    selectedItem?.technicians?.map((rental) => ({
    id: rental.id,
    machineName: rental.machinery?.machine_name,
    brand: rental.machinery?.brand,
    serial: rental.machinery?.serial,
    status: rental.status,
    fields: rental.fields.toString(),
    field1: rental.field1.toString(),
    starDate:  rental.startDate,
    completedDate: rental.completedDate,
    remarks: rental.remarks,
                                    }))
                                "
                                :isHasFilter="true"
                                :noActions="true"
                                :isRowClickable="true"
                                :perPage="10"
                            />
                        </div>
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
                :data="filteredMachineries"
                :filterData="filteredMachineriesForTable"
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                @downloadQrCode="handleDownloadQrCode"
                :isRowClickable="true"
                @notifySMS="handleNotifySMS"
                :isHasDeleteBtn="true"
                :isHasNotifySMSBtn="true"
                :isHasEditBtn="role !== 'management' ? true : false"
                :isHasFilter="true"
                :isHasViewBtn="true"
                :isHasDownloadQrCodeBtn="true"
            />
        </div>
    </AppLayout>
</template>
