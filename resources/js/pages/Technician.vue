<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref, computed} from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';

import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Technician', href: '/technician' }];

const isDialogOpen = ref(false);

const props = defineProps<{
    name?: string;
    machineries: {};
    technicians: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

console.log('propspropsprops', props);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const action = ref('');

const headers = ['Id', 'Status', 'Start Date', 'Completed Date', 'Remarks'];
const form = useForm({
    name: '',
    remarks: '',
    status: '',
    completedDate: '',
    startDate: '',
    machinery_id: '',
    fields: '' // ← add this
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
const addTechnician = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');
    console.log('form.machinery', form);

    if (form.id) {
        // if (form.status === 'Completed') {
        //     router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        // }

        router.patch(route('technician.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        // router.patch(route('machinery.update', form.machinery_id), { status: 'Under Technician' });

        router.post(route('technician.store'), form, {
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
    if (!confirm('Are you sure you want to delete this technician?')) {
        return;
    }

    router.delete(route('technician.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Technician deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};


const filteredMaintainances = computed(() => {
  if (filter.value === 'All') {
    return props?.technicians?.data;
  } else {
    return props?.technicians?.data.filter(
      (maintainance) => maintainance.status === filter.value
    );
  }
});

const filteredMaintainancesForTable = computed(() => {
  return filteredMaintainances.value.map((technician) => ({
    id: technician.id,
    status: technician.status,
    starDate:  technician.startDate,
    completedDate: technician.completedDate,
    remarks: technician.remarks,
  }));
});

</script>

<template>
    <Head title="Routing checking" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add routine checking</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addTechnician">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Technician' : 'Edit Technician' }}</DialogTitle>
                            </DialogHeader>

                            <div class="mb-3">
                                <Label for="user">Machinery</Label>
                                <select style="background: white"  :disabled="action === 'edit'" id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option v-for="machinery in props.machineries.filter(d => d.status === 'Available')" :key="machinery.id" :value="machinery.id">
                                        {{ machinery?.machine_name }} ({{ machinery?.serial }})
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <Label for="completedDate">Start Date</Label>
                                <Input style="background: white" type="date" id="startDate" v-model="form.startDate" placeholder="Enter Completed Date" />
                            </div>

                            
                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="completedDate">Completed Date</Label>
                                <Input style="background: white" type="date" id="completedDate" v-model="form.completedDate" placeholder="Enter Completed Date" />
                            </div>

                            <div class="mb-3">
                                <Label for="status">Status</Label>
                                <select style="background: white" id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="item">Remarks</Label>
                                <Input  style="background: white"  id="item" v-model="form.remarks" placeholder="Item" />
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
                <select id="status" v-model="filter" class="ml-2" style="height: 37px">
                    <option value="All">All</option>
                    <option  value="Ongoing">Ongoing</option>
                    <option  value="Completed">Completed</option>
                </select>

                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Technician</DialogTitle>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <Label for="name">Item</Label>
                                <Input  style="background: white"  readonly required id="name" v-model="form.item" placeholder="Enter machine name" />

                                <Label for="machine_name">Stocks</Label>
                                <Input  style="background: white"  readonly required id="machine_name" v-model="form.stocks" placeholder="Enter machine name" />
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Technician"
                :headers="headers"
                :isHasFilter="true"
                :data="filteredMaintainances"
                :filterData="filteredMaintainancesForTable"
                :perPage="10"
                @editItem="handleEdit"
                @viewItem="handleView"
                @deleteItem="handleDelete"
                @markAsAvailableItem="handleMarkAsAvailable"
                :isHasViewBtn="false"
                :isHasMarkAsAvailableBtn="false"
                :isHasEditBtn="true"
                :isRowClickable="false"
                :isHasDeleteBtn="true"
            />
        </div>
    </AppLayout>
</template>
