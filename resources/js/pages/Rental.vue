<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import pdfMake from 'pdfmake/build/pdfmake';
import { onMounted, ref, computed} from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table } from '@/components/ui/table';
import { format, parseISO } from 'date-fns';
import dayjs from 'dayjs'; // Make sure to install dayjs: npm install dayjs

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Rental', href: '/rental' }];

const isDialogOpen = ref(false);

const props = defineProps<{
    name?: string;
    rentals: {
        data: Array<{
            id: number;
            name: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    users: {};
    machineries: {};
    maintainances: {}
}>();

console.log('propspropsprops', props);

const selectedItem = ref({});
const action = ref('');
const isDialogViewOpen = ref(false);
const headers = [
    'Id',
    'Lessee',
    'Operator',
    'Machine Name',
    'Serial No.',
    'Condition after use',
    'Rent',
    // 'Other Expenses',
    'Status',
    'Borrow Date',
    'Completed Date',
    'Remarks',
];
const form = useForm({
    user_id: null,
    operator_id: null,
    machinery_id: null,
    status: 'Pending',
    attachment: '',
    rent: '',
    otherExpenses: '',
    completedDate: '',
    condition: '',
    date_of_rent: null,
    startDate: '',

    user: {
        name: '',
    },
    machinery: {
        name: '',
    },
    remarks: '',
});


const todayFormatted = computed(() => dayjs().format('YYYY-MM-DD'));
const isDateDisabled = ref(false);

const validateDate = () => {
  if (form.startDate) {
    isDateDisabled.value = props?.rentals?.data?.map(d => d.startDate).includes(form.startDate);
  } else {
    isDateDisabled.value = false;
  }
};

const filter = ref('All');
const formattedDate = (dateString: any, formatString: any) => {
    try {
        const date = parseISO(dateString);
        return format(date, formatString);
    } catch (error) {
        return 'Invalid Date';
    }
};

const addRental = (e: Event) => {

//   if (!isDateDisabled.value && form.startDate) {

    e.preventDefault();
    console.log('Submitting form...');

    if (form.id) {
        if (form.status === 'In use') {
            
            router.patch(route('machinery.update', form.machinery_id), { status: 'In Use' });
        }

        if (form.status === 'Returned') {
            form.completedDate = format(new Date(), 'yyyy-MM-dd');
            router.patch(route('machinery.update', form.machinery.id), { status: 'Available' });
        }
        router.patch(route('rental.update', form.id), form, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (errors) => console.error('Form errors:', errors),
            onFinish: () => closeModal(),
        });



    } else {
         if (props?.maintainances?.filter(dd  => dd.machinery_id === form.machinery_id).map(d => d.maintainance_date).includes(form.startDate)) {
            alert('The machine is currently unavailable.')
            return
         }


        if (props?.maintainances.filter(dd  => dd.user_id === form.operator_id).map(d => d.maintainance_date).includes(form.startDate)) {
            alert('Date is unavailable. Please select another work start date.');
            return
        }

        if (!props?.rentals?.data.filter(dd  => dd.operator.id === form.operator_id).map(d => d.startDate).includes(form.startDate)) {
            console.log('form.startDate', form.startDate)
            if (isToday(form.startDate)) {
                router.patch(route('machinery.update', form.machinery_id), { status: 'In Use' });
                form.status = 'In use'
            }

            // console.log('form',  )

            // console.log('dada', props?.rentals?.data.filter(dd  => dd.operator.id === form.operator_id).map(d => d.startDate))
            // console.log('aafaf', form.startDate)
            // console.log('aaad', props?.rentals?.data.filter(dd  => dd.operator.id === form.operator_id).map(d => d.startDate))
            // console.log('aaad', )
            // props?.rentals?.data.filter(dd  => dd.operator.id === form.operator_id).map(d => d.startDate).includes(form.startDate)

            router.post(route('rental.store'), form, {
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                },
                onError: (errors) => console.error('Form errors:', errors),
                onFinish: () => closeModal(),
            });
        } else {
            alert('Date is unavailable. Please select another work start date.');
        }
    }
};

// const disabledDate = (currentDate) => {
//   if (!currentDate) {
//     return false;
//   }

//   // Disable previous dates
//   const today = dayjs().startOf('day');
//   const isPast = currentDate.isBefore(today, 'day');
//   let dates = props?.rentals?.data.map(d => d.startDate)
//   console.log('dates', dates)

//   // Disable dates present in the 'dates' array
//   const formattedCurrentDate = currentDate.format('YYYY-MM-DD');
//   const isExisting = dates.value.includes(formattedCurrentDate);

//   return isPast || isExisting;
// };

const isToday = (input: string | number | Date): boolean => {
  const inputDate = new Date(input); // Ensure it's a Date
  const today = new Date();

  return (
    inputDate.getFullYear() === today.getFullYear() &&
    inputDate.getMonth() === today.getMonth() &&
    inputDate.getDate() === today.getDate()
  );
};


const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDialogOpen.value = false;
};

const handleEdit = (item: any) => {
    action.value = 'edit';
    selectedItem.value = item;
    isDialogOpen.value = true;
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
};

const handleView = (item: any) => {
    action.value = 'edit';
    selectedItem.value = item;
    console.log('item', item);
    Object.keys(item).forEach((key) => {
        form[key] = item[key] ?? '';
    });
    isDialogViewOpen.value = true;
};

const generatePDF = (item: any) => {
    console.log('item', item)
    console.log('selectedItem.value', selectedItem.value)
  const docDefinition = {
    content: [
      { text: 'MACHINERY RENTAL RECEIPT', style: 'header', alignment: 'center' },

      {
        style: 'detailsTable',
        table: {
          widths: ['30%', '70%'],
          body: [
            [{ text: 'Customer Name:', bold: true }, selectedItem.value.user.name || ''],
            [{ text: 'Machine Name:', bold: true }, selectedItem.value.machinery.machine_name || ''],
            [{ text: 'Serial Number:', bold: true }, selectedItem.value.machinery.serial || ''],
            [{ text: 'Condition after use:', bold: true }, selectedItem.value.condition || 'N/A'],
            [{ text: 'Rental Fee:', bold: true }, (form.rent * form.otherExpenses).toFixed(2) || 'N/A'],
            [{ text: 'Status:', bold: true }, selectedItem.value.status || ''],
            [{ text: 'Date Created:', bold: true }, selectedItem.value.created_at || ''],
            [{ text: 'Completed Date:', bold: true }, selectedItem.value.completedDate || 'Pending'],
            [{ text: 'Remarks:', bold: true }, selectedItem.value.remarks || 'None'],
          ],
        },
        layout: 'lightHorizontalLines',
        margin: [0, 20, 0, 20],
      },

      { text: 'Thank you for using our service!', style: 'footer', alignment: 'center' },
    ],

    styles: {
      header: {
        fontSize: 20,
        bold: true,
        margin: [0, 20, 0, 10],
      },
      detailsTable: {
        margin: [0, 5, 0, 15],
      },
      footer: {
        fontSize: 12,
        italics: true,
        margin: [0, 20, 0, 0],
      },
    },
    defaultStyle: {
    //   font: 'Helvetica',
    },
  };

  pdfMake.createPdf(docDefinition).download('machinery-receipt.pdf');
};


// const generatePDF = (item: any) => {
//     console.log('selectedItem.value', selectedItem.value);
//     const docDefinition = {
//         content: [
//             { text: 'Receipt', style: 'header' },
//             { text: `Machine: ${selectedItem.value.machinery?.machine_name}`, style: 'subheader' },
//             { text: 'Thank you!', style: 'footer' },
//         ],
//         styles: {
//             header: { fontSize: 18, bold: true, margin: [0, 0, 0, 10] },
//             subheader: { fontSize: 14, margin: [0, 0, 0, 5] },
//             footer: { fontSize: 12, italics: true, margin: [0, 10, 0, 0] },
//         },
//     };

//     pdfMake.createPdf(docDefinition).download('receipt.pdf');
// };

const handleDelete = (itemId: string) => {
    console.log('itemIditemId', itemId);
    if (!confirm('Are you sure you want to delete this rental?')) {
        return;
    }

    router.delete(route('rental.destroy', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Rental deleted successfully');
        },
        onError: (errors) => console.error('Deletion error:', errors),
    });
};

const filteredRentals = computed(() => {
  if (filter.value === 'All') {
    return props?.rentals?.data;
  } else {
    return props?.rentals?.data.filter(
      (rental) => rental.status === filter.value
    );
  }
});

const filteredRentalsForTable = computed(() => {
  return filteredRentals.value.map((rental) => ({
    id: rental.id || '',
    name: rental.user?.name || '',
    operator: rental.operator?.name || '',
    machine_name: rental?.machinery?.machine_name || '',
    serial: rental.machinery?.serial || '',
    condition: rental?.condition || '',
    rent: rental?.rent || '',
    // otherExpenses: rental?.otherExpenses || '',
    status: rental?.status || '',
    created_at: formattedDate(rental?.created_at, 'yyyy-MM-dd') || '',
    completed_date: rental.completedDate,
    remarks: rental?.remarks || '',
  }));
});

console.log('props?.rentals?.data', props?.rentals?.data)

</script>

<template>
    <Head title="Rental" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="my-4">

                <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
                    <DialogTrigger as-child>
                        <Button @click="action = 'add'">Add Rental</Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="addRental">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>{{ action === 'add' ? 'Add New Rental' : 'Billing' }}</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new rental. </DialogDescription> -->
                            </DialogHeader>

                            <!-- <div class="grid mb-3">
                                <Label for="name">Name</Label>
                                <Input required id="name" v-model="form.name" placeholder="Enter Name" />
                            </div>
                             -->
                            <div class="mb-3">
                                <Label for="user">Lessee</Label>
                                <!-- <pre>{{props.users.find(t => t.role === 'technician')}}</pre> -->
                                <select style="background: white" :disabled="action === 'edit'" id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <Label for="user">Operator</Label>
                                <select style="background: white" :disabled="action === 'edit'" id="user" v-model="form.operator_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a operator</option>
                                    <option v-for="user in props.users.filter(t => t.role === 'technician')" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3"  v-if="action === 'edit'">
                                <Label for="name">Machine Name</Label>
                                <Input style="background: white" readonly id="remarks" v-model="form.machinery.machine_name" placeholder="Enter Machine Name" />
                            </div>

                            <div class="mb-3"  v-if="action === 'edit'">
                                <!-- <pre>{{form.machinery}}</pre> -->
                                <Label for="name">Serial No.</Label>
                                <Input style="background: white" readonly id="remarks" v-model="form.machinery.serial" placeholder="Enter Serial No." />
                            </div>

                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Other Expenses</Label>
                                <Input id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Other Expenses" />
                            </div> -->

                            <div class="mb-3" v-if="action === 'edit' && form.machinery.machine_name === 'Multi-crop Combine Harvester'">
                                <Label for="status">Attachment</Label>
                                <select style="background: white" id="status" v-model="form.attachment" class="w-full rounded border px-3 py-2">
                                    <option value="With Blade">With Blade</option>
                                    <option value="Without Blade">Without Blade</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>

                            
                            <div class="mb-3" v-if="action === 'edit'">
                                
                                <Label v-if="form.attachment === 'Without Blade'" for="otherExpenses">Number of sack/s</Label>
                                <Input style="background: white" v-if="form.attachment === 'Without Blade'" id="otherExpenses" v-model="form.otherExpenses" placeholder="Number of sack/s" />
                                
                                <Label v-if="form.attachment === 'With Blade' || form.attachment === ''" for="otherExpenses">Number of sqm</Label>
                                <Input style="background: white" v-if="form.attachment === 'With Blade' || form.attachment === ''" id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Number of sqm" />
                            </div>

                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input id="otherExpenses" v-model="form.rentFee" placeholder="Enter rent fee" />
                            </div> -->


                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input style="background: white" id="otherExpenses" v-model="form.rent" placeholder="Enter Rent fee" />
                            </div>
    
                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Total Fee</Label>
                                
                                <Input style="background: white" readonly id="otherExpenses" :placeholder="form.otherExpenses * form.rent" />
                            </div>
    
                            <div class="mb-3" v-else>
                                <Label v-if="action === 'edit'" for="otherExpenses">Total number of harvested crop</Label>
                                
                                <Input style="background: white" v-if="action === 'edit'" readonly id="otherExpenses" :placeholder="form.otherExpenses / 10" />
                            </div>

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Machinery</Label>
                                <select style="background: white" id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option
                                        v-for="machinery in props.machineries.filter((machinery) => machinery.status === 'Available')"
                                        :key="machinery.id"
                                        :value="machinery.id"
                                    >
                                        {{ machinery?.machine_name }} ({{ machinery?.serial }})
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="condition">Condition after use</Label>
                                <Input style="background: white" id="condition" v-model="form.condition" placeholder="Enter Condition after use" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Pending">Pending</option>
                                    <option value="In use">In use</option>
                                    <option value="Returned">Returned</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <Label for="loanDate">Work start date</Label>
                                <!-- <pre>{{props?.rentals?.data.map(d => d.startDate)}}</pre> -->
                                <Input 
                                @change="validateDate"
                                :min="todayFormatted"
                                style="background: white"
                                :disabled="action === 'edit'"
                                required
                                type="date" id="repaymentDate" v-model="form.startDate" placeholder="Enter Work start date" />
                            </div>

                            <!-- <div class="mb-3">
                                <Label for="last_maintenance_date">Rent Date</Label>
                                <Input required type="date" id="rentDate" v-model="form.date_of_rent" placeholder="Enter Rent Date" />
                            </div> -->

                            <div class="mb-3">
                                <Label for="name">Remarks</Label>
                                <Input style="background: white" id="remarks" v-model="form.remarks" placeholder="Enter Remarks" />
                            </div>
                            <Button v-if="action === 'edit'" type="button" @click="generatePDF(form)">Generate receipt</Button>
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
                <select style="height: 37px"  id="status" v-model="filter"  class="ml-2">
                    <option value="All">All</option>
                    <option value="In use">In use</option>
                    <option value="Returned">Returned</option>
                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                </select>
                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <!-- <DialogTrigger as-child>
                        <Button>View Machinery</Button>
                    </DialogTrigger> -->
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Rental</DialogTitle>
                                <!-- <DialogDescription> Fill in the details below to add a new machinery. </DialogDescription> -->
                            </DialogHeader>
                            
                              <div class="mb-3">
                                <Label for="user">Lessee</Label>
                                <select style="background: white" disabled id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a user</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <Label for="user">Operator</Label>
                                <select style="background: white" disabled id="user" v-model="form.operator_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a operator</option>
                                    <option v-for="user in props.users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3"  v-if="action === 'edit'">
                                <Label for="name">Machine Name</Label>
                                <Input style="background: white" readonly id="remarks" v-model="form.machinery.machine_name" placeholder="Enter Machine Name" />
                            </div>

                            <div class="mb-3"  v-if="action === 'edit'">
                                <!-- <pre>{{form.machinery}}</pre> -->
                                <Label for="name">Serial No.</Label>
                                <Input style="background: white" readonly id="remarks" v-model="form.machinery.serial" placeholder="Enter Serial No." />
                            </div>


                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Other Expenses</Label>
                                <Input id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Other Expenses" />
                            </div> -->

                            <div class="mb-3" v-if="action === 'edit' && form.machinery.machine_name === 'Multi-crop Combine Harvester'">
                                <Label for="status">Attachment</Label>
                                <select style="background: white" disabled id="status" v-model="form.attachment" class="w-full rounded border px-3 py-2">
                                    <option value="With Blade">With Blade</option>
                                    <option value="Without Blade">Without Blade</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>

                            
                            <div class="mb-3" v-if="action === 'edit'">
                                
                                <Label v-if="form.attachment === 'Without Blade'" for="otherExpenses">Number of sack/s</Label>
                                <Input style="background: white" readonly v-if="form.attachment === 'Without Blade'" id="otherExpenses" v-model="form.otherExpenses" placeholder="Number of sack/s" />
                                
                                <Label v-if="form.attachment === 'With Blade' || form.attachment === ''" for="otherExpenses">Number of sqm</Label>
                                <Input style="background: white" readonly v-if="form.attachment === 'With Blade' || form.attachment === ''" id="otherExpenses" v-model="form.otherExpenses" placeholder="Enter Number of sqm" />
                            </div>

                            <!-- <div class="mb-3" v-if="action === 'edit'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input id="otherExpenses" v-model="form.rentFee" placeholder="Enter rent fee" />
                            </div> -->


                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Rent fee</Label>
                                <Input style="background: white" readonly id="otherExpenses" v-model="form.rent" placeholder="Enter Rent fee" />
                            </div>
    
                            <div class="mb-3" v-if="action === 'edit' && form.attachment !== 'Without Blade'">
                                <Label for="otherExpenses">Total Fee</Label>
                                
                                <Input style="background: white" readonly id="otherExpenses" :placeholder="form.otherExpenses * form.rent" />
                            </div>
    
                            <div class="mb-3" v-else>
                                <Label v-if="action === 'edit'" for="otherExpenses">Total number of harvested crop</Label>
                                
                                <Input style="background: white" v-if="action === 'edit'" readonly id="otherExpenses" :placeholder="form.otherExpenses / 10" />
                            </div>

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Machinery</Label>
                                <select style="background: white" disabled id="user" v-model="form.machinery_id" class="w-full rounded border px-3 py-2">
                                    <option disabled value="">Select a machinery</option>
                                    <option
                                        v-for="machinery in props.machineries.filter((machinery) => machinery.status === 'Available')"
                                        :key="machinery.id"
                                        :value="machinery.id"
                                    >
                                        {{ machinery?.machine_name }} ({{ machinery?.serial }})
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="condition">Condition after use</Label>
                                <Input style="background: white" readonly id="condition" v-model="form.condition" placeholder="Enter Condition after use" />
                            </div>

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select style="background: white" disabled id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="In use">In use</option>
                                    <option value="Returned">Returned</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>

                            <!-- <div class="mb-3">
                                <Label for="last_maintenance_date">Rent Date</Label>
                                <Input required type="date" id="rentDate" v-model="form.date_of_rent" placeholder="Enter Rent Date" />
                            </div> -->

                            <div class="mb-3">
                                <Label for="name">Remarks</Label>
                                <Input style="background: white"  readonly id="remarks" v-model="form.remarks" placeholder="Enter Remarks" />
                            </div>
                            <Button v-if="action === 'edit'" type="button" @click="generatePDF()">Generate receipt</Button>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Rental"
                :headers="headers"
                :data="filteredRentals"
                :filterData="filteredRentalsForTable"
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @viewItem="handleView"
                :isHasFilter="true"
                :isRowClickable="true"
                :isHasViewBtn="true"
                :isHasDeleteBtn="true"
                :isHasEditBtn="true"
            />
        </div>
    </AppLayout>
</template>
