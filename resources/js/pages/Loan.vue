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

console.log('dada', props);

const headers = ['Id', 'Name', 'Purpose', 'Loan Date', 'Repayment Date', 'Remarks'];
const form = useForm({
    user_id: {},
    amount: 0,
    purpose: '',
    status: '',
    loanDate: null,
    repaymentDate: null,
    remarks: '',
});

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
    console.log('Editing:', item);
    // Open a modal or navigate to an edit page
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
            </div>
            <Table
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
