<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage} from '@inertiajs/vue3';

import { onMounted, ref, computed} from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Member', href: '/user' }];

const page =  usePage();
const auth = computed(() => page.props.auth);
const role = computed(() => page.props.auth.user.role);

const isDialogOpen = ref(false);
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const headersView = ['Id', 'Name', 'Status', 'Remarks'];
const isHide1 = ref(false);
const isHide2 = ref(false);
const isHide3 = ref(false);
const headersViewRental = [
    'Id',
    'Lessee',
    'Operator',
    'Machine Name',
    'Condition',
    'Rent',
    'Other Expenses',
    'Status',
    'Borrow Date',
    'Completed Date',
    'Remarks',
];
const headersViewLoans = ['Id', 'Loaner', 'Email', 'Status', 'Repayment Date', 'Loan Date'];

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

const headersView1 = ['Id', 'Technician', 'Machine Name', 'Work Done', 'Expenses', 'Status', 'Maintenance Date', 'Completed Date', 'Remarks'];
const headers = ['Id', 'Name', 'Email', 'Role', 'Created At'];
let form = useForm({
    name: '',
    email: '',
    role: 'user',
    password: '',
});

const filter = ref('All');
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

const filteredUsers = computed(() => {
  if (filter.value === 'All') {
    return props?.users?.data;
  } else {
    return props?.users?.data.filter(
      (user) => user.role === filter.value
    );
  }
});

const filteredUsersForTable = computed(() => {
  return filteredUsers.value.map(({ id, name, email, role, created_at }) => ({
    id,
    name,
    email,
    role: role === 'user' ? 'member' : role,
    created_at: formattedDate(created_at, 'yyyy-MM-dd'),
  }));
});

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
        console.log('dada', formData);
        console.log('forma', form);
        if (form.role === 'user') {
            formData.append('password', '123456');
        }
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
            console.log('Member deleted successfully');
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
    <Head title="Member" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button>Add Member</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="saveUser">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>Add New Member</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new user. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <Label for="name">Name</Label>
                                <Input  style="background: white"  required id="name" v-model="form.name" placeholder="Enter name" />

                                <Label for="email">Email</Label>
                                <Input  style="background: white"  required id="email" v-model="form.email" placeholder="Enter email" />

                                <Label for="status">Role</Label>
                                <select  style="background: white"  id="status" v-model="form.role" class="w-full rounded border px-3 py-2">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                    <option value="management">Management</option>
                                    <option value="technician">Technician</option>
                                </select>

                                <div class="grid gap-2" v-if="form.role !== 'user'">
                                    <Label for="password">Password</Label>
                                    <Input
                                         style="background: white" 
                                        id="password"
                                        type="password"
                                        required
                                        :tabindex="3"
                                        autocomplete="new-password"
                                        v-model="form.password"
                                        placeholder="Password"
                                    />
                                    <InputError :message="form.errors.password" />
                                </div>

                                <div class="grid gap-2" v-if="form.role !== 'user'">
                                    <Label for="password_confirmation">Confirm password</Label>
                                    <Input
                                         style="background: white" 
                                        id="password_confirmation"
                                        type="password"
                                        required
                                        :tabindex="4"
                                        autocomplete="new-password"
                                        v-model="form.password_confirmation"
                                        placeholder="Confirm password"
                                    />
                                </div>
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
                
                <select  id="status" v-model="filter" class="ml-2" style="height: 37px; background: white">
                    <option value="All">All</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="technician">Technician</option>
                </select>


                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View User</Button>
                    </DialogTrigger> -->
                    <DialogContent class="h-screen max-h-screen w-screen max-w-none overflow-y-auto">
                        <div >
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle class="mb-10">View Member</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new user. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Name</Label>
                                <Input 
                                         style="background: white"  readonly required id="machine_name" v-model="selectedItem.name" placeholder="Name" />

                                <Label for="type">Email</Label>
                                <Input 
                                         style="background: white"  readonly required id="type" v-model="selectedItem.email" placeholder="Email" />
                            </div>

                            <DialogTitle class="py-10">List of Rentals <button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide1 =! isHide1">{{isHide1 ? 'Hide details' : 'Show details'}}</button></DialogTitle>
                            <Table
                                v-if="isHide1"
                                title="Rental"
                                :headers="headersViewRental"
                                :data="selectedItem?.rentals"
                                :filterData="
                                    selectedItem?.rentals?.map((rental) => ({
                                        id: rental.id || '',
                                        name: rental.user?.name || '',
                                        operator: rental.operator?.name || '',
                                        machine_name: rental?.machinery?.machine_name || '',
                                        condition: rental?.condition || '',
                                        rent: rental?.rent || '',
                                        otherExpenses: rental?.otherExpenses || '',
                                        status: rental?.status || '',
                                        created_at: formattedDate(rental?.created_at, 'yyyy-MM-dd') || '',
                                        completed_date: rental.completedDate,
                                        remarks: rental?.remarks || '',
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />

                            <DialogTitle class="py-10">List of Loans <button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide2 =! isHide2">{{isHide2 ? 'Hide details' : 'Show details'}}</button></DialogTitle>
                            <Table
                                title="Rental"
                                v-if="isHide2"
                                :headers="headersViewLoans"
                                :data="selectedItem?.loans"
                                :filterData="
                                    selectedItem?.loans?.map((rental) => ({
                                        id: rental.id,
                                        name: rental.user?.name,
                                        email: rental.user?.email,
                                        status: rental.status,
                                        repaymentDate: rental.repaymentDate,
                                        created_at: formattedDate(rental.created_at, 'yyyy-MM-dd'),
                                    }))
                                "
                                :isHasFilter="true"
                                :noActions="true"
                                :perPage="10"
                            />
                            <DialogTitle class="py-10">List of Preventive Maintenance Schedules <button class="px-2 py-1 text-white bg-purple-500 rounded" @click="isHide3 =! isHide3">{{isHide3 ? 'Hide details' : 'Show details'}}</button></DialogTitle>

                            <Table
                                v-if="isHide3"
                                :headers="headersView1"
                                :data="selectedItem?.maintainances"
                                :filterData="
                                    selectedItem?.maintainances?.map((maintainance) => ({
                                        id: maintainance.id,
                                        name: maintainance.user?.name,
                                        maintainance: maintainance?.machinery?.machine_name,
                                        workDone: maintainance.workDone,
                                        expenses: maintainance.expenses,
                                        status: maintainance.status,
                                        maintainance_date: maintainance.maintainance_date,
                                        completed_date: maintainance.completed_date,
                                        remarks: maintainance.remarks,
                                    }))
                                "
                                :noActions="true"
                                :perPage="10"
                            />
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Member"
                :headers="headers"
                :data="filteredUsers"
                :filterData="filteredUsersForTable"
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                :isHasFilter="true"
                :isHasDeleteBtn="role !== 'management' ? true : false"
                :isHasEditBtn="false"
                :isHasViewBtn="true"
            />
        </div>
    </AppLayout>
</template>
