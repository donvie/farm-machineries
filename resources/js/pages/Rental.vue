<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
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

const headers = ['Id', 'Name', 'Machine Name'];
const form = useForm({
    user_id: null,
    machinery_id: null,
    status: '',
    date_of_rent: null,
    remarks: '',
});

const addRental = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');

    router.post(route('rental.store'), form, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => console.error('Form errors:', errors),
        onFinish: () => closeModal(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDialogOpen.value = false;
};

const handleEdit = (item: any) => {
    console.log('Editing:', item);
    // Open a modal or navigate to an edit page
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
                        <Button>Add Rental</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addRental">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>Add New Rental</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new rental. </DialogDescription>
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

                            <div class="mb-3">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <Label for="last_maintenance_date">Rent Date</Label>
                                <Input required type="date" id="rentDate" v-model="form.date_of_rent" placeholder="Enter Rent Date" />
                            </div>

                            <div class="mb-3">
                                <Label for="name">Remarks</Label>
                                <Input required id="remarks" v-model="form.remarks" placeholder="Enter Remarks" />
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
            </div>
            <Table
                :headers="headers"
                :data="props?.rentals?.data"
                :filterData="
                    props?.rentals?.data.map((rental) => ({
                        id: rental.id,
                        name: rental.user?.name,
                        machine_name: rental?.machinery?.machine_name,
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
            />
        </div>
    </AppLayout>
</template>
