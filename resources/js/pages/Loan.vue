<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
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
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Loan', href: '/loan' }];

const isDialogOpen = ref(false);

const props = defineProps<{
    name?: string;
    loans: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    users: {};
}>();
const selectedItem = ref({});
const isDialogViewOpen = ref(false);

console.log('dada', props);

const headers = ['Id', 'Name', 'Purpose', 'Loan Date', 'Repayment Date', 'Remarks', 'Created At'];
const form = useForm({
    user_id: {},
    amount: 0,
    purpose: '',
    status: '',
    loanDate: null,
    repaymentDate: null,
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

const addLoan = (e: Event) => {
    e.preventDefault();
    console.log('Submitting form...');

    router.post(route('loan.store'), form, {
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
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleDelete = (itemId: string) => {
    if (!confirm('Are you sure you want to delete this loan?')) {
        return;
    }

    router.delete(route('loan.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Loan deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};

const handleView = (item: any) => {
    selectedItem.value = item;
    console.log('item', item);
    isDialogViewOpen.value = true;
};

const handleNotifySMS = async (item) => {
    alert('Sending Email...');

    try {
        const response = await axios.post('/send-email', {
            // Change route
            email: 'dtagaban@unawa.asia', // Replace with dynamic email from item if needed
            subject: 'Notification Email',
            message: 'Hello, this is a notification email!', // Customize message
        });

        console.log('response', response.data);

        if (response.data.success) {
            alert('Email sent successfully!');
        } else {
            alert('Failed to send email.');
        }
    } catch (error) {
        console.log('error', error);
        alert('Error sending email.');
    }
    // alert('Sending SMS...');

    // try {
    //     const response = await axios.post('/send-sms', {
    //         phone: '09457518657',
    //         message: 'Hello from Semaphore!',
    //     });

    //     console.log('response', response.data);

    //     if (response.data.success) {
    //         alert('SMS sent successfully!');
    //     } else {
    //         alert('Failed to send SMS.');
    //     }
    // } catch (error) {
    //     console.log('error', error);
    //     alert('Error sending SMS.');
    // }
};
</script>

<template>
    <Head title="Loan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button>Add Loan</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="addLoan">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>Add New Loan</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new loan. </DialogDescription>
                            </DialogHeader>

                            <div class="mb-3">
                                <Label for="user">Lender </Label>
                                <select id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="amount">Amount</Label>
                                <Input required type="number" id="amount" v-model="form.amount" placeholder="Enter Amount" />
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
                                <Label for="remarks">Purpose</Label>
                                <Input required id="purpose" v-model="form.purpose" placeholder="Enter Remarks" />
                            </div>

                            <div class="mb-3">
                                <Label for="loanDate">Laon Date</Label>
                                <Input required type="date" id="loanDate" v-model="form.loanDate" placeholder="Enter Rent Date" />
                            </div>

                            <div class="mb-3">
                                <Label for="loanDate">Repayment Date</Label>
                                <Input required type="date" id="repaymentDate" v-model="form.repaymentDate" placeholder="Enter repaymentDate" />
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
                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View Machinery</Button>
                    </DialogTrigger> -->
                    <DialogContent>
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Machinery</DialogTitle>
                                <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <!-- <Label for="machine_name">Machine Name</Label>
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
                                /> -->
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Loan"
                :headers="headers"
                :data="props?.loans?.data"
                :filterData="
                    props?.loans?.data.map((loan) => ({
                        id: loan.id,
                        name: loan.user?.name,
                        purpose: loan.purpose,
                        loanDate: loan.loanDate,
                        repaymentDate: loan.repaymentDate,
                        remarks: loan.remarks,
                        created_at: formattedDate(loan.created_at, 'yyyy-MM-dd'),
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @notifySMS="handleNotifySMS"
                @viewItem="handleView"
                :isHasViewBtn="true"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
                :isHasNotifySMSBtn="true"
            />
        </div>
    </AppLayout>
</template>
