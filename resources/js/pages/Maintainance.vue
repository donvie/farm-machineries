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

const headers = ['Id', 'Name', 'Machine Name'];
const form = useForm({
    user_id: null,
    machinery_id: null,
    status: '',
    remarks: '',
});

const addMaintainance = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');

    router.post(route('maintainance.store'), form, {
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
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button>Add Maintainance</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addMaintainance">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>Add New Maintainance</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new maintainance. </DialogDescription>
                            </DialogHeader>
                            <div class="mb-3">
                                <Label for="user">Technician</Label>
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
                                <Label for="remarks">Remarks</Label>
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
                :data="props?.maintainances?.data"
                :filterData="
                    props?.maintainances?.data.map((maintainance) => ({
                        id: maintainance.id,
                        name: maintainance.user?.name,
                        maintainance: maintainance?.machinery?.machine_name,
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
