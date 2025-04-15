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

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Maintainance', href: '/maintainance' }];

const isDialogOpen = ref(false);

const props = defineProps<{
    name?: string;
    maintainances: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    machineries: {};
    users: {};
}>();

console.log('propspropsprops', props);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const action = ref('');

const headers = ['Id', 'Technician', 'Machine Name', 'Status', 'Maintainance Date', 'Completed Date', 'Remarks'];
const form = useForm({
    user_id: null,
    machinery_id: null,
    status: 'Pending',
    // completed_date: null,
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
const addMaintainance = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');
    console.log('form.machinery', form);

    if (form.id) {
        if (form.status === 'Completed') {
            router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        }

        router.patch(route('maintainance.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        router.patch(route('machinery.update', form.machinery_id), { status: 'Under Maintainance' });

        router.post(route('maintainance.store'), form, {
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
    console.log('Editing:', item);
    selectedItem.value = item;
    action.value = 'edit';
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleMarkAsAvailable = (item: any) => {
    console.log('Editing:', item);
    selectedItem.value = item;
    action.value = 'edit';
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleView = (item: any) => {
    selectedItem.value = item;
    console.log('item', item);
    isDialogViewOpen.value = true;
    console.log('formform', form);
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleDelete = (itemId: string) => {
    console.log('itemIditemId', itemId);
    if (!confirm('Are you sure you want to delete this maintainance?')) {
        return;
    }

    router.delete(route('maintainance.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Maintainance deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};
</script>

<template>
    <Head title="Maintainance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                {{ form.completed_date }}
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add Maintainance</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addMaintainance">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Maintainance' : 'Edit Maintainance' }}</DialogTitle>
                            </DialogHeader>
                            <div class="mb-3">
                                <Label for="user">Technician</Label>
                                <select id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users.filter((user) => user.role === 'technician')" :key="user.id" :value="user.id">
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

                            <Label for="year_acquired">Maintainance Date</Label>
                            <Input required type="date" id="year_acquired" v-model="form.maintainance_date" placeholder="Enter year acquired" />

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="completed_date">Completed Date</Label>
                                <Input required type="date" id="completed_date" v-model="form.completed_date" placeholder="Enter Completed Date" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Pending">Pending</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="remarks">Remarks</Label>
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
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Maintainance</DialogTitle>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->
                                <Label for="name">Technician</Label>
                                <Input readonly required id="name" v-model="form.user.name" placeholder="Enter machine name" />

                                <Label for="machine_name">Machine Name</Label>
                                <Input readonly required id="machine_name" v-model="form.machinery.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Machine Type</Label>
                                <Input readonly required id="type" v-model="form.machinery.type" placeholder="Enter machine type" />

                                <Label for="type">Status</Label>
                                <Input readonly required id="type" v-model="form.status" placeholder="Enter machine type" />

                                <Label for="type">Remarks</Label>
                                <Input readonly required id="type" v-model="form.remarks" placeholder="Enter machine type" />
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Maintainance"
                :headers="headers"
                :data="props?.maintainances?.data"
                :filterData="
                    props?.maintainances?.data.map((maintainance) => ({
                        id: maintainance.id,
                        name: maintainance.user?.name,
                        maintainance: maintainance?.machinery?.machine_name,
                        status: maintainance.status,
                        maintainance_date: formattedDate(maintainance.maintainance_date, 'yyyy-MM-dd'),
                        completed_date: formattedDate(maintainance.completed_date, 'yyyy-MM-dd'),
                        remarks: maintainance.remarks,
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @viewItem="handleView"
                @deleteItem="handleDelete"
                @markAsAvailableItem="handleMarkAsAvailable"
                :isHasViewBtn="true"
                :isHasMarkAsAvailableBtn="false"
                :isHasEditBtn="true"
                :isHasDeleteBtn="true"
            />
        </div>
    </AppLayout>
</template>
