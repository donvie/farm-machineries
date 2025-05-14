<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import pdfMake from 'pdfmake/build/pdfmake';
import { onMounted, ref, computed} from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Rental', href: '/rental' }];

const isDialogOpen = ref(false);

const props = defineProps<{
    name?: string;
    rentals: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    users: {};
    machineries: {};
}>();

console.log('propspropsprops', props);

const selectedItem = ref({});
const action = ref('');
const isDialogViewOpen = ref(false);
const headers = [
    'Id',
    'Lessee',
    'Operator',
    'Machine Name',
    'Condition after use',
    'Rent',
    // 'Other Expenses',
    'Status',
    'Borrow Date',
    'Completed Date',
    'Remarks',
];
const form = useForm({
    user_id: null,
    operator_id: null,
    machinery_id: null,
    status: 'Active',
    attachment: '',
    rent: '',
    otherExpenses: '',
    completedDate: '',
    condition: '',
    date_of_rent: null,

    user: {
        name: '',
    },
    machinery: {
        name: '',
    },
    remarks: '',
});

const filter = ref('All');
const formattedDate = (dateString: any, formatString: any) => {
    try {
        const date = parseISO(dateString);
        return format(date, formatString);
    } catch (error) {
        return 'Invalid Date';
    }
};

const addRental = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');

    if (form.id) {
        if (form.status === 'Returned') {
            form.completedDate = format(new Date(), 'yyyy-MM-dd');
            router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        }
        router.patch(route('rental.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        router.patch(route('machinery.update', form.machinery_id), { status: 'In Use' });

        router.post(route('rental.store'), form, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
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

const handleEdit = (item: any) => {
    action.value = 'edit';
    selectedItem.value = item;
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleView = (item: any) => {
    selectedItem.value = item;
    console.log('item', item);
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
    isDialogViewOpen.value = true;
};

const generatePDF = (item: any) => {
    console.log('selectedItem.value', selectedItem.value);
    const docDefinition = {
        content: [
            { text: 'Receipt', style: 'header' },
            { text: `Machine: ${selectedItem.value.machinery?.machine_name}`, style: 'subheader' },
            { text: 'Thank you!', style: 'footer' },
        ],
        styles: {
            header: { fontSize: 18, bold: true, margin: [0, 0, 0, 10] },
            subheader: { fontSize: 14, margin: [0, 0, 0, 5] },
            footer: { fontSize: 12, italics: true, margin: [0, 10, 0, 0] },
        },
    };

    pdfMake.createPdf(docDefinition).download('receipt.pdf');
};

const handleDelete = (itemId: string) => {
    console.log('itemIditemId', itemId);
    if (!confirm('Are you sure you want to delete this rental?')) {
        return;
    }

    router.delete(route('rental.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Rental deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};

const filteredRentals = computed(() => {
  if (filter.value === 'All') {
    return props?.rentals?.data;
  } else {
    return props?.rentals?.data.filter(
      (rental) => rental.status === filter.value
    );
  }
});

const filteredRentalsForTable = computed(() => {
  return filteredRentals.value.map((rental) => ({
    id: rental.id || '',
    name: rental.user?.name || '',
    operator: rental.operator?.name || '',
    machine_name: rental?.machinery?.machine_name || '',
    condition: rental?.condition || '',
    rent: rental?.rent || '',
    // otherExpenses: rental?.otherExpenses || '',
    status: rental?.status || '',
    created_at: formattedDate(rental?.created_at, 'yyyy-MM-dd') || '',
    completed_date: rental.completedDate,
    remarks: rental?.remarks || '',
  }));
});

</script>

<template>
    <Head title="Rental" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">

                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add Rental</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addRental">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Rental' : 'Billing' }}</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new rental. </DialogDescription> -->
                            </DialogHeader>

                            <!-- <div class="grid mb-3">
                                <Label for="name">Name</Label>
                                <Input required id="name" v-model="form.name" placeholder="Enter Name" />
                            </div>
                             -->
                            <div class="mb-3">
                                <Label for="user">Lessee</Label>
                                <select :disabled="action === 'edit'" id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <Label for="user">Operator</Label>
                                <select :disabled="action === 'edit'" id="user" v-model="form.operator_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a operator</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3"  v-if="action === 'edit'">
                                <Label for="name">Machine Name</Label>
                                <Input readonly id="remarks" v-model="form.machinery.machine_name" placeholder="Enter Machine Name" />
                            </div>

                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Other Expenses</Label>
                                <Input id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Other Expenses" />
                            </div> -->

                            <div class="mb-3" v-if="action === 'edit' && form.machinery.machine_name === 'Harvester'">
                                <Label for="status">Attachment</Label>
                                <select id="status" v-model="form.attachment" class="w-full rounded border px-3 py-2">
                                    <option value="With Blade">With Blade</option>
                                    <option value="Without Blade">Without Blade</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>

                            
                            <div class="mb-3" v-if="action === 'edit'">
                                
                                <Label v-if="form.attachment === 'Without Blade'" for="otherExpenses">Number of sack/s</Label>
                                <Input  v-if="form.attachment === 'Without Blade'" id="otherExpenses" v-model="form.otherExpenses" placeholder="Number of sack/s" />
                                
                                <Label v-if="form.attachment === 'With Blade' || form.attachment === ''" for="otherExpenses">Number of sqm</Label>
                                <Input v-if="form.attachment === 'With Blade' || form.attachment === ''" id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Number of sqm" />
                            </div>

                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input id="otherExpenses" v-model="form.rentFee" placeholder="Enter rent fee" />
                            </div> -->


                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input id="otherExpenses" v-model="form.rent" placeholder="Enter Rent fee" />
                            </div>
    
                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Total Fee</Label>
                                
                                <Input readonly id="otherExpenses" :placeholder="form.otherExpenses * form.rent" />
                            </div>
    
                            <div class="mb-3" v-else>
                                <Label for="otherExpenses">Total number of harvested crop</Label>
                                
                                <Input readonly id="otherExpenses" :placeholder="form.otherExpenses / 10" />
                            </div>

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Machinery</Label>
                                <select id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option
                                        v-for="machinery in props.machineries.filter((machinery) => machinery.status === 'Available')"
                                        :key="machinery.id"
                                        :value="machinery.id"
                                    >
                                        {{ machinery?.machine_name }} ({{ machinery?.serial }})
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="condition">Condition</Label>
                                <Input id="condition" v-model="form.condition" placeholder="Enter Condition" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Returned">Returned</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>

                            <!-- <div class="mb-3">
                                <Label for="last_maintenance_date">Rent Date</Label>
                                <Input required type="date" id="rentDate" v-model="form.date_of_rent" placeholder="Enter Rent Date" />
                            </div> -->

                            <div class="mb-3">
                                <Label for="name">Remarks</Label>
                                <Input id="remarks" v-model="form.remarks" placeholder="Enter Remarks" />
                            </div>
                            <Button v-if="action === 'edit'" type="button" @click="generatePDF()">Generate receipt</Button>
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
                <select style="height: 37px"  id="status" v-model="filter"  class="ml-2">
                    <option value="All">All</option>
                    <option value="Active">Active</option>
                    <option value="Returned">Returned</option>
                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                </select>
                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View Machinery</Button>
                    </DialogTrigger> -->
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Rental</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->
                                <Label for="type">Renter</Label>
                                <Input readonly required id="type" v-model="form.user.name" placeholder="Enter machine type" />

                                <Label for="machine_name">Machine Name</Label>
                                <Input readonly required id="machine_name" v-model="form.machinery.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Type</Label>
                                <Input readonly required id="type" v-model="form.machinery.type" placeholder="Enter machine type" />

                                <Label for="status">Status</Label>
                                <select disabled id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Returned">Returned</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>

                                <!-- <Label for="year_acquired">Year Acquired</Label>
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
                                /> -->
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Rental"
                :headers="headers"
                :data="filteredRentals"
                :filterData="filteredRentalsForTable"
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                :isHasFilter="true"
                :isRowClickable="true"
                :isHasViewBtn="false"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
            />
        </div>
    </AppLayout>
</template>
