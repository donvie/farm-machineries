<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
const headers = ['Id', 'Renter', 'Machine Name', 'Status', 'Remarks', 'Created At'];
const form = useForm({
    user_id: null,
    machinery_id: null,
    status: 'Ongoing',
    date_of_rent: null,
    user: {
        name: '',
    },
    machinery: {
        name: '',
    },
    remarks: '',
});

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
        if (form.status === 'Completed') {
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
                                <DialogTitle>{{ action === 'add' ? 'Add New Rental' : 'Edit Rental' }}</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new rental. </DialogDescription> -->
                            </DialogHeader>

                            <!-- <div class="grid mb-3">
                                <Label for="name">Name</Label>
                                <Input required id="name" v-model="form.name" placeholder="Enter Name" />
                            </div>
                             -->
                            <div class="mb-3">
                                <Label for="user">Lessee</Label>
                                <select id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <Label for="user">Machinery</Label>
                                <select id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option v-for="machinery in props.machineries" :key="machinery.id" :value="machinery.id">
                                        {{ machinery?.machine_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
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
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
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
                :data="props?.rentals?.data"
                :filterData="
                    props?.rentals?.data.map((rental) => ({
                        id: rental.id,
                        name: rental.user?.name,
                        machine_name: rental?.machinery?.machine_name,
                        status: rental.status,
                        remarks: rental.remarks,
                        created_at: formattedDate(rental.created_at, 'yyyy-MM-dd'),
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                :isHasViewBtn="true"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
            />
        </div>
    </AppLayout>
</template>
