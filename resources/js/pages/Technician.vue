<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, ref, computed} from 'vue';
import dayjs from 'dayjs';

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

const availableFields = ref([
    'Engine oil',
    'Coolant',
    'Hydraulic fluid',
]);

const availableFields1 = ref([
    'Tires',
    'Belts',
    'Hoses'
]);

const toggleField1 = (field: string) => {
    const index = form.field1.indexOf(field);
    if (index > -1) {
        form.field1.splice(index, 1); // remove
    } else {
        form.field1.push(field); // add
    }
};

const toggleField = (field: string) => {
    const index = form.fields.indexOf(field);
    if (index > -1) {
        form.fields.splice(index, 1); // remove
    } else {
        form.fields.push(field); // add
    }
};

const headers = ['Id', 'Machine Name', 'Brand', 'Serial', 'Status', 'Year acquired', 'Check fluid levels', 'Other Parts', 'Start Date', 'Completed Date', 'Remarks'];
const form = useForm({
    name: '',
    remarks: '',
    status: 'Ongoing',
    completedDate: '',
    startDate: '',
    machinery_id: '',
    field1: [] as string[], // ← update to array
    fields: [] as string[], // ← update to array
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

onMounted(() => {
    // if (props?.technicians?.data.length === 0) {

    addRoutingChecking()
    // } 
});

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

const getAllMondays = (start: string) => {
    const startDate = dayjs(start);
    const today = dayjs();
    const mondays: string[] = [];

    // Find the first Monday on or after the start date
    let current = startDate.day() === 1 ? startDate : startDate.add((8 - startDate.day()) % 7, 'day');

    while (current.isBefore(today) || current.isSame(today, 'day')) {
        mondays.push(current.format('YYYY-MM-DD'));
        current = current.add(1, 'week');
    }

    return mondays;
};

const addRoutingChecking = () => {
    console.log('props?.technicians?.data', props?.technicians?.data)
    // Extract existing combinations of startDate and machineId
    const existing = props?.technicians?.data.map(dd => ({
        startDate: dd.startDate,
        machineId: dd.machinery.id,
    }));
    props.machineries.forEach((machinery) => {
        console.log('machinerymachinery', machinery)
        

        const mondays = getAllMondays(machinery.year_acquired); // Generate all Mondays from May 4, 2025
        console.log('Generated Mondays:', mondays);

        mondays.forEach((monday) => {
            // Check if this machinery already has a record for the given Monday
            const exists = existing.some(entry =>
                entry.machineId === machinery.id &&
                dayjs(entry.startDate).isSame(monday, 'day')
            );

            if (!exists) {
                const form = {
                    name: '',
                    remarks: '',
                    status: 'Ongoing',
                    completedDate: '',
                    startDate: monday,
                    machinery_id: machinery.id,
                    field1: [] as string[],
                    fields: [] as string[],
                };

                router.post(route('technician.store'), form, {
                    preserveScroll: true,
                    onSuccess: () => {
                        console.log(`✅ Created routing for machinery ID: ${machinery.id} on ${monday}`);
                    },
                    onError: (errors) => console.error('❌ Form errors:', errors),
                    onFinish: () => closeModal(),
                });
            } else {
                console.log(`⚠️ Skipped: routing already exists for machinery ID: ${machinery.id} on ${monday}`);
            }
        });
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
    console.log('filteredMadd', filteredMaintainances.value)
  return filteredMaintainances.value.map((technician) => ({
    id: technician.id,
    machineName: technician.machinery?.machine_name,
    brand: technician.machinery?.brand,
    serial: technician.machinery?.serial,
    status: technician.status,
    year_acquired: technician.machinery.year_acquired,
    fields: technician.fields.toString(),
    field1: technician.field1.toString(),
    starDate:  technician.startDate,
    completedDate: technician.completedDate,
    remarks: technician.remarks,
  }));
});

</script>

<template>
    <Head title="Routine checking" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <!-- <Button @click="addRoutingChecking()">Add routine checking</Button> -->
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <!-- <Button @click="action = 'add'">Add routine checking</Button> -->
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addTechnician">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Routine Checking' : 'View Routing Checking' }}</DialogTitle>
                            </DialogHeader>
<div class="mb-3">
    <Label>Check fluid levels</Label>
    <div class="space-y-2">
        <div v-for="field in availableFields" :key="field" class="flex items-center gap-2">
            <input
                type="checkbox"
                :id="field"
                :value="field"
                :checked="form.fields.includes(field)"
                @change="toggleField(field)"
            />
            <label :for="field">{{ field }}</label>
        </div>
    </div>
</div><div class="mb-3">
    <Label>Other parts</Label>
    <div class="space-y-2">
        <div v-for="field in availableFields1" :key="field" class="flex items-center gap-2">
            <input
                type="checkbox"
                :id="field"
                :value="field"
                :checked="form.field1.includes(field)"
                @change="toggleField1(field)"
            />
            <label :for="field">{{ field }}</label>
        </div>
    </div>
</div>

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Machinery</Label>
                                <select style="background: white"  :disabled="action === 'edit'" id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option v-for="machinery in props.machineries.filter(d => d.status === 'Available')" :key="machinery.id" :value="machinery.id">
                                        {{ machinery?.machine_name }} ({{ machinery?.serial }})
                                    </option>
                                </select>
                            </div>

                            
                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="user">Machinery</Label>
                                <Input style="background: white" readonly id="startDate" :placeholder="selectedItem.machinery?.machine_name" />
                            </div>
                            
                            <div class="mb-3">
                                <Label for="completedDate">Start Date</Label>
                                <Input :readonly="action === 'edit'" style="background: white" type="date" id="startDate" v-model="form.startDate" placeholder="Enter Completed Date" />
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
                                <Input  style="background: white"  id="item" v-model="form.remarks" placeholder="Remarks" />
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
                                <DialogTitle>View Routing Checking</DialogTitle>
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
