<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';
import { computed, ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import axios from 'axios';
import pdfMake from 'pdfmake/build/pdfmake';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Loan', href: '/loan' }];

const isDialogOpen = ref(false);
const headersView = ['Amount Paid', 'Date', 'Status'];

const props = defineProps<{
    name?: string;
    loans: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    supplies: {};
    users: {};
}>();
const selectedItem = ref({});
const isDialogViewOpen = ref(false);
const amountPaid = ref(0);
const action = ref('');

console.log('dadadad', props);

const headers = ['Id', 'Loaner', 'Email', 'Status', 'Repayment Date', 'Loan Date'];
const form = useForm({
    user_id: {},
    amount: 0,
    purpose: '',
    status: 'Active',
    loanDate: null,
    repaymentDate: null,
    remarks: '',
    loans: [
        {
            type: '',
            purpose: '',
            bags: 1,
            amount: '',
            areaha: '',
        },
    ],
    histories: [{ test: '' }],
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

    const formData = new FormData();

    Object.entries(form.data()).forEach(([key, value]) => {
        if (value !== null) {
            formData.append(key, value);
        }
    });

    if (form.image) {
        formData.append('image', form.image);
    }

    if (Array.isArray(form.loans)) {
        formData.append('loans', JSON.stringify(form.loans));
    }

    console.log('formformform', form);

    const updateStocks = () => {
        // Encapsulate stock update logic
        if (Array.isArray(form.loans)) {
            // Ensure form.loans is an array
            form.loans.forEach((element) => {
                if (element.type === 'Loan Fertilizer' && element.purpose?.id) {
                    // Important: Check if element.purpose.id exists
                    router.patch(
                        route('supply.update', element.purpose.id),
                        {
                            stocks: parseInt(element.purpose.stocks) - parseInt(element.bags),
                        },
                        {
                            preserveScroll: true,
                            onSuccess: () => {
                                //  closeModal(); // Consider NOT closing modal here.  Close after ALL updates.
                            },
                            onError: (errors) => console.error('Form errors:', errors),
                            //onFinish: () => closeModal(), //  Move this to after ALL updates
                        },
                    );
                }
            });
        }
    };

    if (form.id) {
        // Existing loan update
        if (typeof form.loans === 'string') {
            try {
                form.loans = JSON.parse(form.loans);
            } catch (e) {
                form.loans = [];
            }
        }
        console.log('form.histories', form.histories);

        if (typeof form.histories === 'string') {
            try {
                form.histories = JSON.parse(form.histories);
            } catch (error) {
                console.error('Error parsing form.histories string:', error);
                // If parsing fails, initialize it as an empty array to prevent further errors
                form.histories = [];
            }
        }

        form.histories = form.histories || [];
        form.histories.push({
            amountPaid: amountPaid.value,
            date: format(new Date(), 'yyyy-MM-dd'),
            status: form.status,
        });
        router.patch(route('loan.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => {
                updateStocks(); // Call stock update function
                closeModal(); // Close modal AFTER loan update
            },
            onError: (errors) => console.error('Form errors:', errors),
            //onFinish: () => closeModal(),  Remove from here
        });
    } else {
        // New loan creation
        updateStocks(); // Call stock update function *before* creating the loan.

        router.post(route('loan.store'), formData, {
            preserveScroll: true,
            onSuccess: () => closeModal(), // Close modal AFTER loan creation
            onError: (errors) => console.error('Form errors:', errors),
            // onFinish: () => closeModal(), // Remove from here
        });
    }
};

// const addLoan = (e: Event) => {
//     e.preventDefault();
//     console.log('Submitting form...');
//     console.log('adsss', form);

//     router.post(route('loan.store'), form, {
//         preserveScroll: true,
//         onSuccess: () => {
//             closeModal();
//         },
//         onError: (errors) => console.error('Form errors:', errors),
//         onFinish: () => closeModal(),
//     });
// };

const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDialogOpen.value = false;
};

const handleEdit = (item: any) => {
    console.log('itdadm', item);
    selectedItem.value = item;
    action.value = 'edit';
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
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
    isDialogViewOpen.value = true;
};

const handleNotifySMS = async (item) => {
    console.log('item', item);
    try {
        const response = await axios.post('/send-email', {
            // Change route
            email: item.user.email, // Replace with dynamic email from item if needed
            subject: 'Loan Repayment Reminder',
            message: `
            Gentle Reminder:

                This is a reminder that your loan repayment was due on ${item.repaymentDate}. We have not yet received your payment of PHP ${item.amount}.

                Please take a moment to make the payment at your earliest convenience.

                If you have already made the payment, please disregard this message. If you have any questions or need to discuss your account, please don't hesitate to contact us.

                Thank you for your prompt attention to this matter.`,
            
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

};

const totalAmount = computed(() => {
    return form.loans.reduce((total, loan) => {
        const loanAmount = loan.amount || 0;
        const loanQty = loan.bags || 0;
        return total + loanAmount * loanQty; // Multiply amount by qty (bags)
    }, 0);
});

const removeLoan = (index: number) => {
    if (form.loans.length > 1) {
        form.loans.splice(index, 1); // This removes the loan at the given index
    }
};

const pushLoan = (index: number) => {
    form.loans.push({
        purpose: '',
        bags: 1,
        amount: '',
        areaha: '',
    });
};

const generatePDF = (item: any) => {
    console.log('selectedItem.value', selectedItem.value);
    const docDefinition = {
        content: [
            { text: 'Receipt', style: 'header' },
            // { text: `Machine: ${selectedItem.value.machinery?.machine_name}`, style: 'subheader' },
            { text: 'Thank you!', style: 'footer' },
        ],
        styles: {
            header: { fontSize: 18, bold: true, margin: [0, 0, 0, 10] },
            subheader: { fontSize: 14, margin: [0, 0, 0, 5] },
            footer: { fontSize: 12, italics: true, margin: [0, 10, 0, 0] },
        },
    };

    pdfMake.createPdf(docDefinition).download('receipt.pdf');
};
</script>

<template>
    <Head title="Loan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">
                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add Loan</Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="addLoan">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Loan' : 'Billing' }}</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new loan. </DialogDescription> -->
                            </DialogHeader>

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Borrower</Label>
                                <select id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                            <!-- <div class="mb-3">
                                <Label for="amount">Amount</Label>
                                <Input required type="number" id="amount" v-model="form.amount" placeholder="Enter Amount" />
                            </div> -->

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Overdue">Overdue</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="loanDate">Repayment Date</Label>
                                <Input required type="date" id="repaymentDate" v-model="form.repaymentDate" placeholder="Enter repaymentDate" />
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-semibold">Loan Details:</h3>
                                <ul class="ml-6 list-disc">
                                    <!-- <pre>{{ form.loans }}</pre> -->
                                    <li v-for="(loan, index) in form.loans" :key="index">
                                        <!-- Input for Purpose -->
                                        <div class="mb-2">
                                            <Label for="status">Type</Label>
                                            <select :id="'type-' + index" v-model="loan.type" class="w-full rounded border px-3 py-2">
                                                <option value="Loan Fertilizer">Loan Fertilizer</option>
                                                <option value="Cash">Cash</option>
                                            </select>
                                            <!-- <Input required type="text" :id="'type-' + index" v-model="loan.type" placeholder="Enter Type" /> -->
                                        </div>

                                        <!-- Input for Purpose -->
                                        <div class="mb-2">
                                            <Label v-if="loan.type === 'Loan Fertilizer'" :for="'purpose-' + index">Item</Label>
                                            <Label v-else :for="'purpose-' + index">Purpose</Label>
                                            <select
                                                v-if="loan.type === 'Loan Fertilizer' && action === 'add'"
                                                id="user"
                                                v-model="loan.purpose"
                                                class="w-full rounded border px-3 py-2"
                                            >
                                                <option disabled value="">Select a item</option>
                                                <option v-for="user in props.supplies.data" :key="user.id" :value="user">
                                                    {{ user.item }}
                                                </option>
                                            </select>
                                            <!-- <pre>{{ loan.purpose }}</pre> -->
                                            <select
                                                disabled
                                                v-if="loan.type === 'Loan Fertilizer' && action === 'edit'"
                                                id="user"
                                                v-model="loan.purpose.id"
                                                class="w-full rounded border px-3 py-2"
                                            >
                                                <option disabled value="">Select a item</option>
                                                <option v-for="user in props.supplies.data" :key="user.id" :value="loan.purpose.id">
                                                    {{ user.item }}
                                                </option>
                                            </select>
                                            <Input
                                                v-if="loan.type === 'Cash'"
                                                required
                                                type="text"
                                                :id="'purpose-' + index"
                                                v-model="loan.purpose"
                                                placeholder="Enter Purpose"
                                            />
                                        </div>

                                        <!-- Input for Purpose -->
                                        <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'areaha-' + index">Available Stocks</Label>
                                            <Input
                                                readonly
                                                required
                                                type="text"
                                                :id="'areaha-' + index"
                                                v-model="loan.purpose.stocks"
                                                placeholder="Enter Available Stocks"
                                            />
                                        </div>

                                        <!-- Input for Purpose -->
                                        <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'areaha-' + index">Unit Price</Label>
                                            <Input
                                                readonly
                                                required
                                                type="text"
                                                :id="'areaha-' + index"
                                                v-model="loan.purpose.unitPrice"
                                                placeholder="Enter Unit Price"
                                            />
                                        </div>

                                        <!-- Input for Purpose -->
                                        <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'areaha-' + index">AREA HA</Label>
                                            <Input required type="text" :id="'areaha-' + index" v-model="loan.areaha" placeholder="Enter Area Ha" />
                                        </div>

                                        <!-- Input for Bags -->
                                        <div v-if="loan.type === 'Loan Fertilizer'" class="mb-2">
                                            <Label :for="'bags-' + index">Qty</Label>
                                            <Input
                                                required
                                                type="number"
                                                :id="'bags-' + index"
                                                v-model="loan.bags"
                                                placeholder="Enter Number of Bags"
                                            />
                                        </div>

                                        <!-- Input for Amount -->
                                        <div class="mb-2">
                                            <Label :for="'amount-' + index">Amount</Label>
                                            <Input required type="number" :id="'amount-' + index" v-model="loan.amount" placeholder="Enter Amount" />
                                        </div>

                                        <Button v-if="index !== 0" class="rounded bg-blue-500 px-4 py-2 text-white" @click="removeLoan(index)">
                                            Remove
                                        </Button>
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-2" v-if="action === 'edit'">
                                <Label :for="'amount-' + index">Amount Paid</Label>
                                <Input required type="number" :id="'amount-' + index" v-model="amountPaid" placeholder="Enter Amount Paid" />
                            </div>
                            <div class="mt-4 text-right">
                                <Button @click="pushLoan()">Add loan</Button>
                            </div>

                            <Button v-if="action === 'edit'" type="button" @click="generatePDF()">Generate receipt</Button>

                            <div class="mt-4">
                                <h3 class="text-md font-semibold">Total Amount: {{ totalAmount.toFixed(2) }}</h3>
                            </div>

                            <h3 v-if="action === 'edit' && selectedItem.histories" class="mt-8 text-lg font-semibold">Payment History</h3>
                            <Table
                                class="mt-4"
                                v-if="action === 'edit' && selectedItem.histories"
                                :headers="headersView"
                                :data="selectedItem.histories"
                                :filterData="
                                    selectedItem.histories?.map((maintainance) => ({
                                        amountPaid: maintainance.amountPaid,
                                        date: maintainance.date,
                                        status: maintainance.status,
                                    }))
                                "
                                :isRowClickable="true"
                                :noActions="true"
                                :isHasFilter="false"
                                :perPage="10"
                            />

                            <!-- <div class="mb-3">
                                <Label for="remarks">Purpose</Label>
                                <Input required id="purpose" v-model="form.purpose" placeholder="Enter purpose" />
                            </div> -->

                            <!-- <div class="mb-3">
                                <Label for="loanDate">Laon Date</Label>
                                <Input required type="date" id="loanDate" v-model="form.loanDate" placeholder="Enter Rent Date" />
                            </div> -->

                            <!-- <div class="mb-3">
                                <Label for="name">Remarks</Label>
                                <Input required id="remarks" v-model="form.remarks" placeholder="Enter Remarks" />
                            </div> -->

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
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Loan</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>

                            <div class="grid gap-4">
                                <!-- <Label for="image">Upload Image</Label>
                                <Input id="image" type="file" accept="image/*" @change="handleFileUpload" /> -->

                                <Label for="machine_name">Name</Label>
                                <Input readonly required id="machine_name" v-model="form.user.name" placeholder="Enter machine name" />
                                <!-- 
                                <Label for="type">Amount</Label>
                                <Input readonly required id="type" v-model="form.amount" placeholder="Enter machine type" /> -->

                                <!-- <Label for="type">Purpose</Label>
                                <Input readonly required id="type" v-model="form.purpose" placeholder="Enter machine type" /> -->

                                <Label for="type">Repayment Date</Label>
                                <Input readonly required id="type" v-model="form.repaymentDate" placeholder="Enter machine type" />

                                <Label for="status">Status</Label>
                                <select disabled id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Overdue">Overdue</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>

                                <div class="mt-4">
                                    <h3 class="text-lg font-semibold">Loan Details:</h3>
                                    <ul class="ml-6 list-disc">
                                        <li v-for="(loan, index) in form.loans" :key="index">
                                            <div class="mb-2">
                                                <Label for="status">Type</Label>
                                                <select disabled :id="'type-' + index" v-model="loan.type" class="w-full rounded border px-3 py-2">
                                                    <option value="Loan Fertilizer">Loan Fertilizer</option>
                                                    <option value="Cash">Cash</option>
                                                </select>
                                                <!-- <Input required type="text" :id="'type-' + index" v-model="loan.type" placeholder="Enter Type" /> -->
                                            </div>

                                            <!-- Input for Purpose -->
                                            <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                                <Label :for="'areaha-' + index">AREA HA</Label>
                                                <Input
                                                    readonly
                                                    required
                                                    type="text"
                                                    :id="'areaha-' + index"
                                                    v-model="loan.areaha"
                                                    placeholder="Enter Area Ha"
                                                />
                                            </div>

                                            <!-- Input for Purpose -->
                                            <div class="mb-2">
                                                <Label :for="'purpose-' + index">Purpose</Label>
                                                <Input
                                                    required
                                                    readonly
                                                    type="text"
                                                    :id="'purpose-' + index"
                                                    v-model="loan.purpose"
                                                    placeholder="Enter Purpose"
                                                />
                                            </div>

                                            <!-- Input for Amount -->
                                            <div class="mb-2">
                                                <Label :for="'amount-' + index">Amount</Label>
                                                <Input
                                                    readonly
                                                    required
                                                    type="number"
                                                    :id="'amount-' + index"
                                                    v-model="loan.amount"
                                                    placeholder="Enter Amount"
                                                />
                                            </div>

                                            <!-- Input for Bags -->
                                            <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                                <Label :for="'bags-' + index">Qty</Label>
                                                <Input
                                                    readonly
                                                    required
                                                    type="number"
                                                    :id="'bags-' + index"
                                                    v-model="loan.bags"
                                                    placeholder="Enter Number of Bags"
                                                />
                                            </div>
                                            <!-- <Button v-if="index !== 0" class="rounded bg-blue-500 px-4 py-2 text-white" @click="removeLoan(index)">
                                                Remove
                                            </Button> -->
                                        </li>
                                    </ul>
                                </div>
                                <!-- <div class="mt-4 text-right">
                                    <Button @click="pushLoan()">Add loan</Button>
                                </div> -->

                                <div class="mt-4">
                                    <h3 class="text-md font-semibold">Total Amount: {{ totalAmount.toFixed(2) }}</h3>
                                </div>

                                <!-- <Label for="year_acquired">Year Acquired</Label>
                                <Input required type="date" id="year_acquired" v-model="form.year_acquired" placeholder="Enter year acquired" /> -->

                                <!-- <Label for="last_maintenance_date">Last Maintenance Date</Label>
                                <Input
                                    required
                                    type="date"
                                    id="last_maintenance_date"
                                    v-model="form.last_maintenance_date"
                                    placeholder="Enter Last Maintenance Date"
                                /> -->

                                <!-- <Label for="next_scheduled_maintenance">Next Scheduled Maintenance</Label>
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
                        email: loan.user?.email,
                        status: loan.status,
                        repaymentDate: loan.repaymentDate,
                        created_at: formattedDate(loan.created_at, 'yyyy-MM-dd'),
                    }))
                "
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @notifySMS="handleNotifySMS"
                @viewItem="handleView"
                :isHasViewBtn="false"
                :isHasDeleteBtn="true"
                :isRowClickable="false"
                :isHasEditBtn="true"
                :isHasFilter="true"
                :isHasNotifySMSBtn="true"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.dialog-content {
    max-height: 80vh; /* Adjust the height as per your design */
    overflow-y: auto;
}
</style>
