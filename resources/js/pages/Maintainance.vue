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

const headers = ['Id', 'Technician', 'Machine Name', 'Work Done', 'Expenses', 'Status', 'Maintenance Date', 'Completed Date', 'Work to do'];
const form = useForm({
    user_id: null,
    machinery_id: null,
    status: 'Pending',
    // completed_date: null,
    remarks: '',
    sourceOfFund: '',
    condition: '',
    workDone: '',
    expenses: '',
});

const filter = ref('All');

const isToday = (input: string | number | Date): boolean => {
  const inputDate = new Date(input); // Ensure it's a Date
  const today = new Date();

  return (
    inputDate.getFullYear() === today.getFullYear() &&
    inputDate.getMonth() === today.getMonth() &&
    inputDate.getDate() === today.getDate()
  );
};

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
    console.log('form.machinery111', form.condition);
    console.log('form.machinery22', form.workDone);
    console.log('form.machinery333', form.expenses);

    if (form.id) {
        if (form.status === 'Completed') {
            router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        }

        if (form.status === 'Under Maintenance') {
            
        router.patch(route('machinery.update', form.machinery_id), { status: 'Under Maintenance' });
        }

        router.patch(route('maintainance.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        // router.patch(route('machinery.update', form.machinery_id), { status: 'Pending' });
         if (props?.maintainances?.data.filter(dd  => dd.machinery.id === form.machinery_id).map(d => d.maintainance_date).includes(form.maintainance_date)) {
            alert('The machine is currently unavailable.')
            return
         }

        if (!props?.maintainances?.data.filter(dd  => dd.user.id === form.user_id).map(d => d.maintainance_date).includes(form.maintainance_date)) {

        if (isToday(form.maintainance_date)) {
            form.status = 'Under Maintenance'
        router.patch(route('machinery.update', form.machinery_id), { status: 'Under Maintenance' });
        }

        // if (props?.maintainances?.data.filter(dad => dad.user.id === form.user_id).map(d => d.status).includes("Active")) {
        //     alert('You currently have an active loan. Please settle it before applying for a new one. Only one loan is allowed at a time.')
        //     return
            
        // }


        router.post(route('maintainance.store'), form, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
        } else {
            alert('Date is unavailable. Please select another maintenance date.');
        }
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

const filteredMaintainances = computed(() => {
  if (filter.value === 'All') {
    return props?.maintainances?.data;
  } else {
    return props?.maintainances?.data.filter(
      (maintainance) => maintainance.status === filter.value
    );
  }
});

const filteredMaintainancesForTable = computed(() => {
  return filteredMaintainances.value.map((maintainance) => ({
    id: maintainance.id,
    name: maintainance.user?.name,
    maintainance: maintainance?.machinery?.machine_name,
    workDone: maintainance.workDone,
    expenses: maintainance.expenses,
    status: maintainance.status,
    maintainance_date: maintainance.maintainance_date,
    completed_date: maintainance.completed_date,
    remarks: maintainance.remarks,
  }));
});

</script>

<template>
    <Head title="Maintainance" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <!-- {{ form.completed_date }} -->
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add Maintainance</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addMaintainance">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Maintainance' : 'Edit Status' }}</DialogTitle>
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
                                <Label for="user">Technician</Label>
                                <select style="background: white" :disabled="action === 'edit'" id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users.filter((user) => user.role === 'technician')" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="condition">Condition</Label>
                                <Input style="background: white" id="condition" v-model="form.condition" placeholder="Enter Condition" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="workDone">Source of fund</Label>
                                <Input style="background: white" id="workDone" v-model="form.sourceOfFund" placeholder="Enter Source of fund" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="workDone">Work Done</Label>
                                <Input style="background: white" id="workDone" v-model="form.workDone" placeholder="Enter Work Done" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="expenses">Expenses</Label>
                                <Input style="background: white" id="expenses" v-model="form.expenses" placeholder="Enter Expenses" />
                            </div>

                            <Label for="year_acquired">Maintenance Date</Label>
                            <Input style="background: white" type="date" id="year_acquired" v-model="form.maintainance_date" placeholder="Enter year acquired" />

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="completed_date">Completed Date</Label>
                                <Input style="background: white" type="date" id="completed_date" v-model="form.completed_date" placeholder="Enter Completed Date" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select style="background: white" id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Pending">Pending</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="remarks">Work to do</Label>
                                <Input style="background: white" id="remarks" v-model="form.remarks" placeholder="Enter Work to do" />
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
                                <Input style="background: white" readonly required id="name" v-model="form.user.name" placeholder="Enter machine name" />

                                <Label for="machine_name">Machine Name</Label>
                                <Input style="background: white" readonly required id="machine_name" v-model="form.machinery.machine_name" placeholder="Enter machine name" />

                                <Label for="type">Machine Type</Label>
                                <Input style="background: white" readonly required id="type" v-model="form.machinery.type" placeholder="Enter machine type" />

                                <Label for="type">Status</Label>
                                <Input style="background: white" readonly required id="type" v-model="form.status" placeholder="Enter machine type" />

                                <Label for="type">Work to do</Label>
                                <Input style="background: white" readonly required id="type" v-model="form.remarks" placeholder="Enter machine type" />
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
                <select id="status" v-model="filter" class="ml-2" style="height: 37px">
                    <option value="All">All</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <Table
                title="Maintainance"
                :headers="headers"
                :data="filteredMaintainances"
                :filterData="filteredMaintainancesForTable"
                :perPage="10"
                @editItem="handleEdit"
                @viewItem="handleView"
                @deleteItem="handleDelete"
                @markAsAvailableItem="handleMarkAsAvailable"
                :isHasFilter="true"
                :isRowClickable="false"
                :isHasViewBtn="false"
                :isHasMarkAsAvailableBtn="false"
                :isHasEditBtn="true"
                :isHasDeleteBtn="true"
            />
        </div>
    </AppLayout>
</template>
