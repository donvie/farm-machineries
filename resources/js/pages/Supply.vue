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

import { Link, usePage } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Supply', href: '/supply' }];

const isDialogOpen = ref(false);
const page = usePage();
const auth = computed(() => page.props.auth);
const role = computed(() => page.props.auth.user.role);


const props = defineProps<{
    name?: string;
    supplies: {
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

const headers = ['Id', 'Item', 'Type', 'Unit Price', 'Stocks'];
const form = useForm({
    item: '',
    stocks: '',
    unitPrice: '',
    type: '',
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
const addSupply = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');
    console.log('form.machinery', form);

    if (form.id) {
        // if (form.status === 'Completed') {
        //     router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        // }

        router.patch(route('supply.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });
    } else {
        // router.patch(route('machinery.update', form.machinery_id), { status: 'Under Supply' });

        router.post(route('supply.store'), form, {
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
    if (!confirm('Are you sure you want to delete this supply?')) {
        return;
    }

    router.delete(route('supply.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Supply deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};


const filteredMaintainances = computed(() => {
  if (filter.value === 'All') {
    return props?.supplies?.data;
  } else {
    return props?.supplies?.data.filter(
      (maintainance) => maintainance.type === filter.value
    );
  }
});

const filteredMaintainancesForTable = computed(() => {
  return filteredMaintainances.value.map((supply) => ({
    id: supply.id,
    item: supply.item,
    type: supply.type,
    unitPrice: supply.unitPrice,
    stocks: supply?.stocks < 0 ? 0 : supply.stocks,
  }));
});

</script>

<template>
    <Head title="Supply" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <!-- {{ form.completed_date }} -->
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button v-if="role !== 'management'" @click="action = 'add'">Add Supply</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addSupply">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Supply' : 'Edit Supply' }}</DialogTitle>
                            </DialogHeader>

                            <div class="mb-3">
                                <Label for="item">Item</Label>
                                <Input  style="background: white"  id="item" v-model="form.item" placeholder="Item" />
                            </div>

                            <div class="mb-3">
                                <Label for="unitPrice">Unit Price</Label>
                                <Input  style="background: white"  id="unitPrice" v-model="form.unitPrice" placeholder="Unit Price" />
                            </div>

                            <div class="mb-3">
                                <Label for="stocks">Stocks</Label>
                                <Input  style="background: white"  id="stocks" v-model="form.stocks" placeholder="Stocks" />
                            </div>
                            <div class="mb-3">
                                 <Label  for="type">Type</Label>
                                <select  style="background: white"  id="status" v-model="form.type" class="w-full rounded border px-3 py-2">
                                    <option  value="All">All</option>
                                    <option  value="Fertilizer">Fertilizer</option>
                                    <option  value="Insecticide">Insecticide</option>
                                </select>
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
                    <option  value="Fertilizer">Fertilizer</option>
                    <option  value="Insecticide">Insecticide</option>
                </select>

                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Supply</DialogTitle>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->
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
                title="Supply"
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
