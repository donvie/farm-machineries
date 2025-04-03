<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'User', href: '/user' }];

const isDialogOpen = ref(false);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const headersView = ['Id', 'Name', 'Status', 'Remarks'];
const headersViewRental = ['Id', 'Name', 'Machine Name', 'Remarks', 'Created At'];
const headersViewLoans = ['Id', 'Name', 'Amount', 'Purpose', 'Created At'];

const props = defineProps<{
    name?: string;
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            rentals: any;
            loans: any;
            maintainances: any;
        }>;
    };
}>();

console.log('propsprops', props);

const headersView1 = ['Id', 'Name', 'Status', 'Maintainance Date', 'Remarks'];
const headers = ['Id', 'Name', 'Email', 'Role', 'Created At'];
let form = useForm({
    name: '',
    email: '',
    role: '',
});

const result = ref('');
const videoElement = ref(null);

const formattedDate = (dateString: any, formatString: any) => {
    try {
        const date = parseISO(dateString);
        return format(date, formatString);
    } catch (error) {
        return 'Invalid Date';
    }
};

const saveUser = (e: Event) => {
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
        router.patch(route('user.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        router.post(route('user.store'), formData, {
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
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
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
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    router.delete(route('user.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('User deleted successfully');
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
</script>

<template>
    <Head title="User" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <!-- <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button>Add User</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="saveUser">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>Add New User</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new user. </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">

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
                </Dialog> -->

                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View User</Button>
                    </DialogTrigger> -->
                    <DialogContent class="h-screen max-h-screen w-screen max-w-none overflow-y-auto">
                        <form @submit.prevent="saveUser">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle class="mb-10">View User</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new user. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Name</Label>
                                <Input readonly required id="machine_name" v-model="selectedItem.name" placeholder="Name" />

                                <Label for="type">Email</Label>
                                <Input readonly required id="type" v-model="selectedItem.email" placeholder="Email" />
                            </div>

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
                                        remarks: rental?.remarks,
                                        created_at: formattedDate(rental.created_at, 'yyyy-MM-dd'),
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />

                            <DialogTitle class="py-10">List of Loans</DialogTitle>
                            <Table
                                title="Rental"
                                :headers="headersViewLoans"
                                :data="selectedItem?.loans"
                                :filterData="
                                    selectedItem?.loans?.map((rental) => ({
                                        id: rental.id,
                                        name: rental.user?.name,
                                        amount: rental?.amount,
                                        purpose: rental?.purpose,
                                        created_at: formattedDate(rental.created_at, 'yyyy-MM-dd'),
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />
                            <DialogTitle class="py-10">List of Maintainances</DialogTitle>

                            <Table
                                :headers="headersView1"
                                :data="selectedItem?.maintainances"
                                :filterData="
                                    selectedItem?.maintainances?.map((maintainance) => ({
                                        id: maintainance.id,
                                        name: maintainance.user?.name,
                                        status: maintainance?.status,
                                        maintainance_date: formattedDate(maintainance.maintainance_date, 'yyyy-MM-dd'),
                                        remarks: maintainance?.remarks,
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="User"
                :headers="headers"
                :data="props?.users?.data"
                :filterData="
                    props?.users?.data.map(({ id, name, email, role, created_at }) => ({
                        id,
                        name,
                        email,
                        role,
                        created_at: formattedDate(created_at, 'yyyy-MM-dd'),
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                :isHasDeleteBtn="true"
                :isHasEditBtn="false"
                :isHasViewBtn="true"
            />
        </div>
    </AppLayout>
</template>
