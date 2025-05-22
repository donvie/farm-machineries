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
const filter = ref('All');

const headers = ['Id', 'Borrower', 'Status', 'Balance', 'Total Loan',  ];
const form = useForm({
    user_id: {},
    amount: 0,
    purpose: '',
    status: 'Active',
    loanDate: null,
    repaymentDate: null,
    isFullPayment: '',
    // loanGrantedDate: '',
    dateOfRelease: null,
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

// const dueDate = ref("April 14, 2025");
// const todayDate = ref('May 16, 2025'); // Today's date in milliseconds

// Optional: Format the today's date for display
const formattedTodayDateNow = computed(() => {
  return format(Date.now(), 'MMMM dd, yyyy'); // Example format
});


const calculatePenaltyMonthsFn1 = computed(() => {
    return (date1Str, date2Str) => {
        const date1 = new Date(date1Str);
        const date2 = new Date(date2Str);

        let months = (date2.getFullYear() - date1.getFullYear()) * 12;
        months -= date1.getMonth();
        months += date2.getMonth();

        return months <= 0 ? 0 : months;
        }
});


const calculatePenaltyMonthsFn = computed(() => {
    return (originalDueDateStr, todayStr) => {
        const originalDueDate = new Date(originalDueDateStr);
        const today = new Date(todayStr);

        // Grace date: one month after original
        const graceDate = new Date(originalDueDate);
        graceDate.setMonth(graceDate.getMonth() + 1);

        let fullMonths = 0;
        let checkDate = new Date(graceDate);

        // Start from the next month after the grace period
        checkDate.setMonth(checkDate.getMonth() + 1);

        while (true) {
            const dueThisMonth = new Date(checkDate.getFullYear(), checkDate.getMonth(), originalDueDate.getDate());

            if (dueThisMonth > today) break;

            fullMonths++;
            checkDate.setMonth(checkDate.getMonth() + 1);
        }

        return fullMonths;
    };
});


// const calculatePenaltyMonthsFn = computed(() => {
//     return (originalDueDateStr, todayStr) => {
//         const originalDueDate = new Date(originalDueDateStr);
//         const today = new Date(todayStr);

//         // Grace period: skip 1 month
//         const graceDate = new Date(originalDueDate);
//         graceDate.setMonth(graceDate.getMonth() + 1);

//         let fullMonths = 0;
//         let checkDate = new Date(graceDate);

//         while (true) {
//             // Create the 27th of the current check month
//             const dueDayThisMonth = new Date(checkDate.getFullYear(), checkDate.getMonth(), originalDueDate.getDate());

//             if (dueDayThisMonth > today) {
//                 break; // Stop if the current due date hasn’t occurred yet
//             }

//             fullMonths++;

//             // Move to next month
//             checkDate.setMonth(checkDate.getMonth() + 1);
//         }

//         return fullMonths;
//     };
// });

// const calculatePenaltyMonthsFn = computed(() => {
//   return (dueDateStr, timestampToday) => {
//     const dueDate = new Date(dueDateStr);
//     const todayDate = new Date(timestampToday);

//     const oneMonthAfterDue = new Date(dueDate);
//     oneMonthAfterDue.setMonth(dueDate.getMonth() + 1);

//     let monthDifference = (todayDate.getFullYear() - oneMonthAfterDue.getFullYear()) * 12;
//     monthDifference += todayDate.getMonth() - oneMonthAfterDue.getMonth();

//     return monthDifference > 0 ? monthDifference : 0;
//   };
// });

const calculatePenaltyDaysFn = computed(() => {
  return (dueDateStr, timestampToday) => {
    const toMidnightUTC = (date) => {
      const d = new Date(date);
      return Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate());
    };

    const dueDate = new Date(dueDateStr);
    const oneMonthAfterDue = new Date(dueDate);
    const originalMonth = oneMonthAfterDue.getMonth();
    oneMonthAfterDue.setMonth(originalMonth + 1);

    // Adjust if setMonth rolls into the next month
    if (oneMonthAfterDue.getMonth() !== (originalMonth + 1) % 12) {
      oneMonthAfterDue.setDate(0); // Set to last day of previous month
    }

    const startUTC = toMidnightUTC(oneMonthAfterDue);
    const endUTC = toMidnightUTC(timestampToday);

    const diffInDays = Math.floor((endUTC - startUTC) / (1000 * 60 * 60 * 24));
    return diffInDays > 0 ? diffInDays : 0;
  };
});

// function dateDifferenceInDays(date1, date2) {
//     // Parse the dates
//     const d1 = new Date(date1);
//     const d2 = new Date(date2);
    
//     // Calculate the difference in time (in milliseconds)
//     const timeDifference = Math.abs(d2 - d1);
    
//     // Convert time difference from milliseconds to days
//     const dayDifference = timeDifference / (1000 * 3600 * 24);
    
//     return dayDifference;
// }

const dateDifferenceInDays = computed(() => {
  return (date1Str, date2Str) => {
  // Parse the dates
    const d1 = new Date(date1Str);
    const d2 = new Date(date2Str);
    
    // Calculate the difference in time (in milliseconds)
    const timeDifference = Math.abs(d2 - d1);
    
    // Convert time difference from milliseconds to days
    const dayDifference = timeDifference / (1000 * 3600 * 24);
    
    return dayDifference;
}

});



const calculatePenaltyDaysFn1 = computed(() => {
  return (date1Str, date2Str) => {
  const date1 = new Date(date1Str);
  const date2 = new Date(date2Str);

  // Calculate the difference in milliseconds
  const differenceMs = date2.getTime() - date1.getTime();

  // Convert milliseconds to days
  const days = Math.ceil(differenceMs / (1000 * 60 * 60 * 24));

  return days;
}

});



// function calculatePenaltyDaysFn1(date1Str, date2Str) {
//   const date1 = new Date(date1Str);
//   const date2 = new Date(date2Str);

//   // Calculate the difference in milliseconds
//   const differenceMs = date2.getTime() - date1.getTime();

//   // Convert milliseconds to days
//   const days = Math.ceil(differenceMs / (1000 * 60 * 60 * 24));

//   return days;
// }

// const calculatePenaltyDaysFn1 = computed(() => {
//   return (dueDateStr, timestampToday) => {
//     const dueDate = new Date(dueDateStr);
//     const todayDate = new Date(timestampToday); // Create Date object from timestamp

//     const oneMonthAfterDue = new Date(dueDate);
//     oneMonthAfterDue.setMonth(dueDate.getMonth() + 1);

//     const differenceInMilliseconds = todayDate.getTime() - oneMonthAfterDue.getTime();
//     const differenceInDays = Math.floor(differenceInMilliseconds / (1000 * 60 * 60 * 24));

//     return differenceInDays > 0 ? differenceInDays : 0;
//   };
// });

const formattedDate = (dateString: any, formatString: any) => {
    try {
        const date = parseISO(dateString);
        return format(date, formatString);
    } catch (error) {
        return 'Invalid Date';
    }
};

const addLoan = (e: Event) => {
    if (form.loans[0].type === 'Cash' && form.loans[0].amount > 200000) {
        alert('The maximum allowed amount is ₱200,000.')
        return
    }
    if (form.loans[0].type === 'Loan Fertilizer' && form.loans[0].bags > 20) {
        alert('The maximum allowed bags is 20.')
        return
    }


// console.log('daad123', )
// console.log('daw231', form.user_id)

  if (form.loans.map(loan => {
    // Convert 'bags' to a number for comparison
    const bags = Number(loan.bags);
    const stocks = loan.purpose.stocks;
    const exceeds = bags > stocks;
    return exceeds; // Returns true if loan.bags > loan.purpose.stocks, false otherwise
  }).includes(true)) {
    return;
  }

  // Add the confirmation dialog here
  if (!confirm('Are you sure you want to save this loan?')) {
    return; // If the user cancels, exit the function
  }

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
                //  closeModal(); // Consider NOT closing modal here.  Close after ALL updates.
              },
              onError: (errors) => console.error('Form errors:', errors),
              //onFinish: () => closeModal(), //  Move this to after ALL updates
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



    if (totalAmount.value - selectedItem.value.histories?.reduce((total, loan) => {
                            const loanAmount = loan.amountPaid || 0;
                            return total + loanAmount;
                        }, 0) <= 0) {
                            form.status = "Paid"
    }


    router.patch(route('loan.update', form.id), form, {
      preserveScroll: true,
      onSuccess: () => {
        updateStocks(); // Call stock update function
        closeModal(); // Close modal AFTER loan update
      },
      onError: (errors) => console.error('Form errors:', errors),
      //onFinish: () => closeModal(),  Remove from here
    });
  } else {
    if (props?.loans?.data.filter(dad => dad.user.id === form.user_id).map(d => d.status).includes("Active") || props?.loans?.data.filter(dad => dad.user.id === form.user_id).map(d => d.status).includes("Overdue")) {
        alert('You currently have an active loan. Please settle it before applying for a new one. Only one loan is allowed at a time.')
        return
        
    }
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

const totalPenalty = computed(() => {
    console.log('formform', form)
  return form.loans.reduce((total, loan) => {
    console.log('formform', form)
    let penalty = 0;
    let interest = 0;
    const dueDate = loan.dueDate; // Assuming each loan has a dueDate

    // if (form.status === 'Overdue' && form.repaymentDate) {
      if (loan.type === 'Cash') {
        // interest = ((parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount))
        penalty = (parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
      } else {
        // interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)))
        penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
      }
    // }

    if (loan.type === 'Cash') {
        interest = ((parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount))
    // penalty = (parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
    } else {
        interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)))
    // penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
    }

    console.log('penaltypenalty', penalty)
    console.log('interestinterest', interest)


    const loanAmount = loan.amount || 0;
    const loanQty = loan.bags || 0;

    return total + penalty;
  }, 0);
});


const handleNotifySMS = async (item) => {
    // console.log('itdadm', item);
    // selectedItem.value = item;
    // action.value = 'edit';
    // isDialogOpen.value = true;
    // Object.keys(item).forEach((key) => {
    //     form[key] = item[key] ?? '';
    // });
    const totalAmount = item.loans.reduce((total, loan) => {
        console.log('formform', item)
        let penalty = 0;
        let interest = 0;
        const dueDate = loan.dueDate; // Assuming each loan has a dueDate

        if (item.status === 'Overdue' && item.repaymentDate) {
        if (loan.type === 'Cash') {
            // interest = ((parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount))
            penalty = (parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, formattedTodayDateNow.value))
        } else {
            // interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)))
            penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, formattedTodayDateNow.value))
        }
        }

        if (loan.type === 'Cash') {
            interest = ((parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount))
        // penalty = (parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, itemattedTodayDateNow.value))
        } else {
            interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)))
        // penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, itemattedTodayDateNow.value))
        }

        console.log('penaltypenalty', penalty)
        console.log('interestinterest', interest)


        const loanAmount = loan.amount || 0;
        const loanQty = loan.bags || 0;

        return total + (loanAmount * loanQty) + penalty + interest;
    }, 0);


    const amount = totalAmount - item.histories.reduce((total, loan) => {
        const loanAmount = loan.amountPaid || 0;
        return total + loanAmount;
    }, 0)

    const penalty = item.loans.reduce((total, loan) => {
    console.log('itemitem', item)
    let penalty = 0;
    let interest = 0;
    const dueDate = loan.dueDate; // Assuming each loan has a dueDate

    // if (item.status === 'Overdue' && item.repaymentDate) {
      if (loan.type === 'Cash') {
        // interest = ((parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount))
        penalty = (parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, formattedTodayDateNow.value))
      } else {
        // interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)))
        penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, formattedTodayDateNow.value))
      }
    // }

    if (loan.type === 'Cash') {
        interest = ((parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount))
    // penalty = (parseFloat(calculatePenaltyMonthsFn1.value(item.dateOfRelease, item.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, itemattedTodayDateNow.value))
    } else {
        interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)))
    // penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(item.dateOfRelease, item.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(item.repaymentDate, itemattedTodayDateNow.value))
    }


    const loanAmount = loan.amount || 0;
    const loanQty = loan.bags || 0;

    return total + penalty;
  }, 0);

    console.log('penaltypenalty', penalty)
    console.log('amountamount', amount)

    // console.log('item', item);
    try {
        const response = await axios.post('/send-email', {
            // Change route
            email: item.user.email, // Replace with dynamic email from item if needed
            subject: 'Loan Repayment Reminder',
            message: `
            Loan Payment Reminder

                We would like to remind you that your loan payment is now past due. The total outstanding amount is ${amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').replace(/\d(?=(\d{3})+\.)/g, '$&,')}, which includes a penalty of ${penalty.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')} for the delay.

                We kindly ask that you settle the balance promptly to prevent any additional charges. If you have any questions or need assistance, feel free to reach out.

                Thank you for your attention to this matter.
            `
            // message: `
            // Gentle Reminder:

            //     This is a reminder that your loan repayment was due on ${item.repaymentDate}. We have not yet received your payment of PHP ${amount}.

            //     Please take a moment to make the payment at your earliest convenience.

            //     If you have already made the payment, please disregard this message. If you have any questions or need to discuss your account, please don't hesitate to contact us.

            //     Thank you for your prompt attention to this matter.`,
            
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
    alert('Sending SMS...');

};

const totalAmount = computed(() => {
    console.log('formform', form)
  return form.loans.reduce((total, loan) => {
    console.log('formform', form)
    let penalty = 0;
    let interest = 0;
    const dueDate = loan.dueDate; // Assuming each loan has a dueDate

    if (form.status === 'Overdue' && form.repaymentDate) {
      if (loan.type === 'Cash') {
        // interest = ((parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount))
        penalty = (parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
      } else {
        // interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)))
        penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
      }
    }

    if (loan.type === 'Cash') {
        interest = ((parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount))
    // penalty = (parseFloat(calculatePenaltyMonthsFn1.value(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
    } else {
        interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)))
    // penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
    }

    console.log('penaltypenalty', penalty)
    console.log('interestinterest', interest)


    const loanAmount = loan.amount || 0;
    const loanQty = loan.bags || 0;

    return total + (loanAmount * loanQty) + penalty + interest;
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

const validateDate1 = () => {
    form.loans[0].amount = 0
    // form.loans[0].purpose.unitPrice = 0
    // form.loans[0].bags = 0
}

const validateDate = () => {
  if (form.dateOfRelease) {
    if (form.loans[0].type === 'Loan Fertilizer') {
        const releaseDate = new Date(form.dateOfRelease)
        releaseDate.setDate(releaseDate.getDate() + 120)

        const formatted = releaseDate.toISOString().split('T')[0]
        form.repaymentDate = formatted
    } 
    if (form.loans[0].type === 'Cash') {
        
        const releaseDate = new Date(form.dateOfRelease)
        releaseDate.setMonth(releaseDate.getMonth() + 6)

        const formatted = releaseDate.toISOString().split('T')[0]
        form.repaymentDate = formatted

    }
  }
}


const generatePDF = (item: any) => {
    console.log('selectedItem.value', selectedItem.value);
    const user = selectedItem.value.user;
    const loan = selectedItem.value.loans[0]; // Assuming there's at least one loan entry

    const docDefinition = {
        content: [
            { text: 'Loan Receipt', style: 'header', alignment: 'center' },
            { text: '\nBorrower Information', style: 'subheader' },
            {
                columns: [
                    {
                        width: '50%',
                        stack: [
                            { text: `Name: ${user.name}` },
                            { text: `Email: ${user.email}` },
                        ],
                    },
                    {
                        width: '50%',
                        stack: [
                            { text: `Date of Release: ${selectedItem.value.dateOfRelease}` },
                            { text: `Repayment Date: ${selectedItem.value.repaymentDate}` },
                            { text: `Status: ${selectedItem.value.status}` },
                        ],
                    }
                ]
            },
            { text: '\nLoan Details', style: 'subheader' },
            {
                table: {
                    widths: loan.type === 'Cash' ? ['*', '*', '*', '*', '*'] : ['*', '*', '*', '*', '*', '*'],
                    body: [
                        loan.type === 'Cash' ? [
                            { text: 'Loan Type', bold: true },
                            { text: `${ loan.type === 'Cash' ? 'Purpose' : 'Bags'}`, bold: true },
                            { text: 'Amount (PHP)', bold: true },
                            { text: 'Interest', bold: true },
                            { text: 'Penalty', bold: true }
                        ] : [
                            { text: 'Loan Type', bold: true },
                            { text: 'Item Name', bold: true },
                            { text: `${ loan.type === 'Cash' ? 'Purpose' : 'Bags'}`, bold: true },
                            { text: 'Amount (PHP)', bold: true },
                            { text: 'Interest', bold: true },
                            { text: 'Penalty', bold: true }
                        ],
                        loan.type === 'Cash' ? 
                        [
                            loan.type,
                            loan.type === 'Cash' ? loan.purpose : loan.bags,
                            `₱${parseInt(loan.amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`  || 0,
                            ((parseFloat(calculatePenaltyMonthsFn1.value(selectedItem.value.dateOfRelease, selectedItem.value.repaymentDate)) * 0.01 * loan.amount)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') || 0,
                            ((parseFloat(calculatePenaltyMonthsFn1.value(selectedItem.value.dateOfRelease, selectedItem.value.repaymentDate)) * 0.01 * loan.amount) * calculatePenaltyMonthsFn.value(selectedItem.value.repaymentDate, formattedTodayDateNow.value)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')  || 0
                        ] : [
                            loan.type,
                            loan.purpose?.item || 'N/A',
                            loan.type === 'Cash' ? loan.purpose : loan.bags,
                            `₱${parseInt(loan.amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`  || 0,
                            (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(selectedItem.value.dateOfRelease, selectedItem.value.repaymentDate) * 0.12 / 365))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')  || 0,
                            (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(selectedItem.value.dateOfRelease, selectedItem.value.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(selectedItem.value.repaymentDate, formattedTodayDateNow.value))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')  || 0
                        ]
                    ]
                }
            },
            { text: '\nSummary', style: 'subheader' },
            {
                ul: [
                    `Total Loan: ₱${totalAmount.value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`,
                    `Balance:  ${ (totalAmount.value - selectedItem.value?.histories?.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')  === 'NaN' ? '0' : (totalAmount.value - selectedItem.value?.histories?.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`,
                    // `Status: ${selectedItem.value.status}`,
                ]
            },
            { text: '\nThank you for availing the loan service.', style: 'footer' }
        ],
        styles: {
            header: { fontSize: 20, bold: true, margin: [0, 0, 0, 10] },
            subheader: { fontSize: 14, bold: true, margin: [0, 10, 0, 5] },
            footer: { fontSize: 12, italics: true, alignment: 'center', margin: [0, 20, 0, 0] },
        }
    };

    pdfMake.createPdf(docDefinition).download('loan-receipt.pdf');
};


const filteredLoans = computed(() => {
  if (filter.value === 'All') {
    return props?.loans?.data;
  } else {
    return props?.loans?.data.filter(
      (loan) => loan.status === filter.value
    );
  }
});

const filteredLoansForTable = computed(() => {
    console.log('filteredLoans', filteredLoans.value)
  return filteredLoans.value.map((l) => {
    // Calculate the total amount for the current loan object
    const totalAmount = l.loans.reduce((total, loan) => {
        console.log('loandadad', loan)
        let penalty = 0;
        let interest = 0;
        const dueDate = loan.dueDate; // Assuming each loan has a dueDate

        if (l.status === 'Overdue' && l.repaymentDate) {
        if (loan.type === 'Cash') {
            penalty = (parseFloat(calculatePenaltyMonthsFn1.value(l.dateOfRelease, l.repaymentDate)) * 0.01 * loan.amount) * parseFloat(calculatePenaltyMonthsFn.value(l.repaymentDate, formattedTodayDateNow.value))
        } else {
            penalty = ((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(l.dateOfRelease, l.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn.value(form.repaymentDate, formattedTodayDateNow.value))
        }
        }

        if (loan.type === 'Cash') {
            interest = ((parseFloat(calculatePenaltyMonthsFn1.value(l.dateOfRelease, l.repaymentDate)) * 0.01 * loan.amount))
        } else {
            interest = (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays.value(l.dateOfRelease, l.repaymentDate) * 0.12 / 365)))
        }

        console.log('penaltypenalty', penalty)
        console.log('interestinterest', interest)


        const loanAmount = loan.amount || 0;
        const loanQty = loan.bags || 0;

        return total + (loanAmount * loanQty) + penalty + interest;
    }, 0);


 let remainingBalance1 = 0

    if (l && l.histories) {

    remainingBalance1 = l.histories.reduce((sum, singleLoan) => {
        console.log('loanloan', l)
      return sum + singleLoan.amountPaid
    }, 0);
    console.log('remainingBalance1', remainingBalance1)
    }



    return {
      id: l.id,
      name: l.user?.name,
      status: l.status,
    //   repaymentDate: l.repaymentDate,
      remainingBalance: (totalAmount - remainingBalance1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') === 'NaN' || (totalAmount - remainingBalance1) < 0  ? 0 : (totalAmount - remainingBalance1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'),
      totalAmount: totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') === 'NaN' || totalAmount < 0 ? 0 : totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'), // Assign the calculated totalAmount
    //   repaymentDate: loan.repaymentDate,
    //   created_at: formattedDate(loan.created_at, 'yyyy-MM-dd'),
    };
  });
});

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

                            <!-- <p>Today's Date: {{ formattedTodayDateNow }}</p>
                            <p>Penalty Days: {{ calculatePenaltyDaysFn(form.repaymentDate, formattedTodayDateNow) }} days</p>
                            <p>Penalty Months: {{ calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow) }} month(s)</p> -->

                            <div class="mb-3" v-if="action === 'add'">
                                <Label for="user">Borrower</Label>
                                <select style="background: white"  id="user" v-model="form.user_id" class="w-full rounded border px-3 py-2">
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
                                <select style="background: white"  id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Overdue">Overdue</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="loanDate">Date of Release</Label>
                                <Input @change="validateDate" style="background: white"  required type="date" id="dateOfRelease" v-model="form.dateOfRelease" placeholder="Enter date of release" />
                            </div>
                            <div class="mb-3">
                                <Label for="loanDate">Due Date</Label>
                                <Input style="background: white"  required type="date" id="repaymentDate" v-model="form.repaymentDate" placeholder="Enter repayment date" />
                            </div>
                            <!-- <div class="mb-3">
                                <Label for="loanDate">Date of Loan Granted</Label>
                                <Input @change="validateDate" style="background: white"  required type="date" id="loanGrantedDate" v-model="form.loanGrantedDate" placeholder="Enter date of loan granted" />
                            </div> -->
                            <div class="mt-4">
                                <h3 class="text-lg font-semibold">Loan Details:</h3>
                                <ul class="ml-6 list-disc">
                                    <!-- <pre>{{ form.loans }}</pre> -->
                                    <li v-for="(loan, index) in form.loans" :key="index">
                                        <!-- Input for Purpose -->
                                        <div class="mb-2">
                                            <Label for="status">Type</Label>
                                            <select  @change="validateDate1" style="background: white"  :disabled="action === 'edit'" :id="'type-' + index" v-model="loan.type" class="w-full rounded border px-3 py-2">
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
                                                style="background: white" 
                                                v-if="loan.type === 'Loan Fertilizer' && action === 'add'"
                                                id="user"
                                                v-model="loan.purpose"
                                                class="w-full rounded border px-3 py-2"
                                            >
                                                <option disabled value="">Select a item</option>
                                                <option :disabled="user.stocks <= 0" v-for="user in props.supplies.data" :key="user.id" :value="user">
                                                    {{ user.item }}  {{ user.stocks <= 0 ? '(Out of stocks)' : `(${user.stocks})` }} 
                                                </option>
                                            </select>
                                            <!-- <pre>{{ loan.purpose }}</pre> -->
                                            <select
                                                disabled
                                                style="background: white" 
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
                                                style="background: white" 
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
                                                style="background: white" 
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
                                                style="background: white" 
                                                required
                                                type="text"
                                                :id="'areaha-' + index"
                                                v-model="loan.purpose.unitPrice"
                                                placeholder="Enter Unit Price"
                                            />
                                        </div>

                                        <!-- Input for Purpose -->
                                        <!-- <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'areaha-' + index">AREA HA</Label>
                                            <Input style="background-color: white" required type="text" :id="'areaha-' + index" v-model="loan.areaha" placeholder="Enter Area Ha" />
                                        </div> -->

                                        <!-- Input for Bags -->
                                        <div v-if="loan.type === 'Loan Fertilizer'" class="mb-2">
                                            <Label :for="'bags-' + index">Qty</Label>
                                            <Input
                                                style="background: white" 
                                                :readonly="action === 'edit'"
                                                required
                                                user.stocks
                                                type="text"
                                                :id="'bags-' + index"
                                                v-model="loan.bags"
                                                placeholder="Enter Number of Bags"
                                            />
                                            <Label v-if="loan.bags > loan.purpose.stocks" class="mt-3" style="color: red" :for="'purpose-' + index">Insufficient Qty</Label>
                                        </div>

                                        <!-- Input for Amount -->
                                        <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'amount-' + index">{{loan.type === 'Loan Fertilizer' ? 'Principal amount fertlizer loan ' : 'Principal amount cash loan'}}</Label>
                                            <!-- <pre>{{loan}}</pre> -->
                                            <!-- <div style="margin-left: 10px" v-if="action === 'edit'" >{{loan.purpose.unitPrice * loan.bags}}</div> -->
                                            <Input v-if="action === 'edit'" readonly style="background-color: white"  type="text" :id="'amoudadnt-' + index"  :placeholder="loan.purpose.unitPrice * loan.bags" />
                                            <Input v-else readonly style="background-color: white" required type="text" :id="'amount-' + index" :value="loan.purpose.unitPrice && loan.bags ? loan.amount = loan.purpose.unitPrice * loan.bags : 0" placeholder="Enter Amount" />
                                        </div>
                                        <div class="mb-2" v-else>
                                            <Label :for="'amount-' + index">{{loan.type === 'Loan Fertilizer' ? 'Principal amount fertlizer loan ' : 'Principal amount cash loan'}}</Label>
                                            <Input style="background-color: white" required type="text" :id="'amount-' + index" v-model="loan.amount" placeholder="Enter Amount" />
                                        </div>
                                        <Label v-if="loan.type === 'Cash' && form.status === 'Overdue'" :for="'areaha-' + index">Penalty ({{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}} months)</Label>
                                       <!-- {{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}}dd -->
                                        <Input
                                            v-if="loan.type === 'Cash' && form.status === 'Overdue'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />


                                        <Label v-if="loan.type === 'Cash'" :for="'areaha-' + index">Interest ({{parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate))}} months)</Label>
                                        <!-- <div v-if="loan.type === 'Cash'">{{calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)}}</div> -->
                                        <Input
                                            v-if="loan.type === 'Cash'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />

                                        <!-- <Input
                                            v-if="loan.type === 'Cash'  && action === 'edit'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)) * 0.12 * loan.amount)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        /> -->


                                        <Label v-if="loan.type === 'Loan Fertilizer' && form.status === 'Overdue'" :for="'areaha-' + index">Penalty ({{ calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}} months)</Label>
                                        <!-- {{}} -->
                                        <!-- {{}}ddd -->
                                        <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer' && form.status === 'Overdue'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="(((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />
                                        <!-- {{form.repaymentDate}}
                                        {{formattedTodayDateNow}} -->
                                        <!-- {{parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow))}} -->
                                        <!-- {{parseFloat(calculatePenaltyDaysFn(form.repaymentDate, formattedTodayDateNow))}} -->
                                        <!-- {{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}}months
                                        {{dateDifferenceInDays(form.dateOfRelease, form.repaymentDate)}}dayss -->
                                        
                                        <Label v-if="loan.type === 'Loan Fertilizer'" :for="'areaha-' + index">Interest ({{dateDifferenceInDays(form.dateOfRelease, form.repaymentDate)}} days)</Label>
                                        <!-- <div v-if="loan.type === 'Loan Fertilizer'">{{calculatePenaltyDaysFn1(form.dateOfRelease, form.repaymentDate)}}</div> -->
                                        <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="loan.purpose.unitPrice && loan.bags ? (((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays(form.dateOfRelease, form.repaymentDate) * 0.12 / 365))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') : 0"
                                        />
                                        <!-- <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer' && action === 'edit'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="(((loan.purpose.unitPrice * loan.bags) * (calculatePenaltyDaysFn(form.repaymentDate, formattedTodayDateNow) * 0.12 / 365))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        /> -->
                                        <!-- <Button  v-if="index !== 0 && action === 'add'" class="rounded bg-blue-500 px-4 py-2 mt-3 text-white" @click="removeLoan(index)">
                                            Remove
                                        </Button> -->
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-2" v-if="action === 'edit'">
                                <Label :for="'amountadada-' + index">Amount Paid</Label>
                                <Input style="background: white"  required type="number" :id="'amodadaunt-' + index" v-model="amountPaid" placeholder="Enter Amount Paid" />
                            </div>
                            <!-- <div class="mt-4 text-right" v-if="action === 'add'">
                                <Button @click="pushLoan()">Add loan</Button>
                            </div> -->

                            <Button v-if="action === 'edit'" type="button" @click="generatePDF()">Generate receipt</Button>

                            <div class="mt-4">
                                <h3 class="text-md font-semibold">Total Loan: {{ totalAmount.toFixed(2) === 'NaN' || totalAmount < 0 ? 0 : totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}</h3>
                                <h3 v-if="selectedItem.loans && selectedItem.histories" class="text-md font-semibold">Balance: {{ 
                                    (totalAmount - selectedItem.histories.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') < 0 ? 0 : (totalAmount - selectedItem.histories.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}}</h3>
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
                <select id="filter" v-model="filter"  class="ml-2" style="height: 37px">
                    <option value="All">All</option>
                    <option value="Active">Active</option>
                    <option value="Paid">Paid</option>
                    <option value="Overdue">Overdue</option>
                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                </select>
                <Dialog :open="isDialogViewOpen" @update:open="isDialogViewOpen = $event">
                    <DialogContent class="max-h-[80vh] overflow-y-auto">
                        <form @submit.prevent="saveMachinery">
                            <DialogHeader class="mb-3 space-y-3">
                                <DialogTitle>View Loan</DialogTitle>
                            </DialogHeader>
                            <!-- <div class="mb-3">
                                <Label for="amount">Amount</Label>
                                <Input required type="number" id="amount" v-model="form.amount" placeholder="Enter Amount" />
                            </div> -->

                            <div class="mb-3" v-if="action === 'edit'">
                                <Label for="status">Status</Label>
                                <select disabled style="background: white"  id="status" v-model="form.status" class="w-full rounded border px-3 py-2">
                                    <option value="Active">Active</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Overdue">Overdue</option>
                                    <!-- <option value="Under Maintenance">Under Maintenance</option> -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <Label for="loanDate">Date of Release</Label>
                                <Input @change="validateDate" readonly style="background: white"  required type="date" id="dateOfRelease" v-model="form.dateOfRelease" placeholder="Enter date of release" />
                            </div>
                            <div class="mb-3">
                                <Label for="loanDate">Due Date</Label>
                                <Input readonly style="background: white"  required type="date" id="repaymentDate" v-model="form.repaymentDate" placeholder="Enter repayment date" />
                            </div>
                            <div class="mt-4">
                                <h3 class="text-lg font-semibold">Loan Details:</h3>
                                <ul class="ml-6 list-disc">
                                    <!-- <pre>{{ form.loans }}</pre> -->
                                    <li v-for="(loan, index) in form.loans" :key="index">
                                        <!-- Input for Purpose -->
                                        <div class="mb-2">
                                            <Label for="status">Type</Label>
                                            <select style="background: white"  disabled :id="'type-' + index" v-model="loan.type" class="w-full rounded border px-3 py-2">
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
                                                style="background: white" 
                                                v-if="loan.type === 'Loan Fertilizer' && action === 'add'"
                                                id="user"
                                                v-model="loan.purpose"
                                                class="w-full rounded border px-3 py-2"
                                            >
                                                <option disabled value="">Select a item</option>
                                                <option :disabled="user.stocks <= 0" v-for="user in props.supplies.data" :key="user.id" :value="user">
                                                    {{ user.item }}  {{ user.stocks <= 0 ? '(Out of stocks)' : `(${user.stocks})` }} 
                                                </option>
                                            </select>
                                            <!-- <pre>{{ loan.purpose }}</pre> -->
                                            <select
                                                disabled
                                                style="background: white" 
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
                                                style="background: white" 
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
                                                style="background: white" 
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
                                                style="background: white" 
                                                required
                                                type="text"
                                                :id="'areaha-' + index"
                                                v-model="loan.purpose.unitPrice"
                                                placeholder="Enter Unit Price"
                                            />
                                        </div>

                                        <!-- Input for Purpose -->
                                        <!-- <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'areaha-' + index">AREA HA</Label>
                                            <Input style="background-color: white" required type="text" :id="'areaha-' + index" v-model="loan.areaha" placeholder="Enter Area Ha" />
                                        </div> -->

                                        <!-- Input for Bags -->
                                        <div v-if="loan.type === 'Loan Fertilizer'" class="mb-2">
                                            <Label :for="'bags-' + index">Qty</Label>
                                            <Input
                                                style="background: white" 
                                                :readonly="action === 'edit'"
                                                required
                                                user.stocks
                                                type="text"
                                                :id="'bags-' + index"
                                                v-model="loan.bags"
                                                placeholder="Enter Number of Bags"
                                            />
                                            <Label v-if="loan.bags > loan.purpose.stocks" class="mt-3" style="color: red" :for="'purpose-' + index">Insufficient Qty</Label>
                                        </div>

                                        <!-- Input for Amount -->
                                        <div class="mb-2" v-if="loan.type === 'Loan Fertilizer'">
                                            <Label :for="'amount-' + index">{{loan.type === 'Loan Fertilizer' ? 'Principal amount fertlizer loan ' : 'Principal amount cash loan'}}</Label>
                                            <!-- <pre>{{loan}}</pre> -->
                                            <!-- <div style="margin-left: 10px" v-if="action === 'edit'" >{{loan.purpose.unitPrice * loan.bags}}</div> -->
                                            <Input v-if="action === 'edit'" readonly style="background-color: white"  type="text" :id="'amoudadnt-' + index"  :placeholder="loan.purpose.unitPrice * loan.bags" />
                                            <Input v-else readonly style="background-color: white" required type="text" :id="'amount-' + index" :value="loan.amount = loan.purpose.unitPrice * loan.bags" placeholder="Enter Amount" />
                                        </div>
                                        <div class="mb-2" v-else>
                                            <Label :for="'amount-' + index">{{loan.type === 'Loan Fertilizer' ? 'Principal amount fertlizer loan ' : 'Principal amount cash loan'}}</Label>
                                            <Input style="background-color: white" required type="text" :id="'amount-' + index" v-model="loan.amount" placeholder="Enter Amount" />
                                        </div>
                                        <Label v-if="loan.type === 'Cash' && form.status === 'Overdue'" :for="'areaha-' + index">Penalty ({{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}} months)</Label>
                                       <!-- {{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}}dd -->
                                        <Input
                                            v-if="loan.type === 'Cash' && form.status === 'Overdue'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount) * calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />


                                        <Label v-if="loan.type === 'Cash'" :for="'areaha-' + index">Interest ({{parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate))}} months)</Label>
                                        <!-- <div v-if="loan.type === 'Cash'">{{calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)}}</div> -->
                                        <Input
                                            v-if="loan.type === 'Cash'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn1(form.dateOfRelease, form.repaymentDate)) * 0.01 * loan.amount)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />

                                        <!-- <Input
                                            v-if="loan.type === 'Cash'  && action === 'edit'"
                                            readonly
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="((parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)) * 0.12 * loan.amount)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        /> -->


                                        <Label v-if="loan.type === 'Loan Fertilizer' && form.status === 'Overdue'" :for="'areaha-' + index">Penalty ({{ calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}} months)</Label>
                                        <!-- {{}} -->
                                        <!-- {{}}ddd -->
                                        <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer' && form.status === 'Overdue'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="(((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays(form.dateOfRelease, form.repaymentDate) * 0.12 / 365)) * parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />
                                        <!-- {{form.repaymentDate}}
                                        {{formattedTodayDateNow}} -->
                                        <!-- {{parseFloat(calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow))}} -->
                                        <!-- {{parseFloat(calculatePenaltyDaysFn(form.repaymentDate, formattedTodayDateNow))}} -->
                                        <!-- {{calculatePenaltyMonthsFn(form.repaymentDate, formattedTodayDateNow)}}months
                                        {{dateDifferenceInDays(form.dateOfRelease, form.repaymentDate)}}dayss -->
                                        
                                        <Label v-if="loan.type === 'Loan Fertilizer'" :for="'areaha-' + index">Interest ({{dateDifferenceInDays(form.dateOfRelease, form.repaymentDate)}} days)</Label>
                                        <!-- <div v-if="loan.type === 'Loan Fertilizer'">{{calculatePenaltyDaysFn1(form.dateOfRelease, form.repaymentDate)}}</div> -->
                                        <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="(((loan.purpose.unitPrice * loan.bags) * (dateDifferenceInDays(form.dateOfRelease, form.repaymentDate) * 0.12 / 365))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        />
                                        <!-- <Input
                                            readonly
                                            v-if="loan.type === 'Loan Fertilizer' && action === 'edit'"
                                            style="background: white" 
                                            required
                                            type="text"
                                            :id="'areaha-' + index"
                                            :placeholder="(((loan.purpose.unitPrice * loan.bags) * (calculatePenaltyDaysFn(form.repaymentDate, formattedTodayDateNow) * 0.12 / 365))).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')"
                                        /> -->
                                        <!-- <Button  v-if="index !== 0 && action === 'add'" class="rounded bg-blue-500 px-4 py-2 mt-3 text-white" @click="removeLoan(index)">
                                            Remove
                                        </Button> -->
                                    </li>
                                </ul>
                            </div>
                            <!-- <div class="mb-2" v-if="action === 'edit'">
                                <Label :for="'amountadada-' + index">Amount Paid</Label>
                                <Input style="background: white"  required type="number" :id="'amodadaunt-' + index" v-model="amountPaid" placeholder="Enter Amount Paid" />
                            </div> -->
                            <!-- <div class="mt-4 text-right" v-if="action === 'add'">
                                <Button @click="pushLoan()">Add loan</Button>
                            </div> -->

                            <Button class="mt-5" v-if="action === 'edit'" type="button" @click="generatePDF()">Generate receipt</Button>

                            <div class="mt-4">
                                <h3 class="text-md font-semibold">Total Loan: {{ totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}</h3>
                                <h3 v-if="selectedItem.loans && selectedItem.histories" class="text-md font-semibold">Balance: {{ 
                                    (totalAmount - selectedItem.histories.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}</h3>
                            </div>

                            <h3 class="mt-8 text-lg font-semibold">Payment History</h3>
                            <h3 v-if="!selectedItem.histories" class="mt-8 text-lg text-center font-semibold">No data</h3>
                            
                            <Table
                                class="mt-4"
                                v-if="selectedItem.histories"
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
                            <div class="mt-4">
                                <h3 class="text-md font-semibold">Total Loan: {{ totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}</h3>
                                <h3 v-if="selectedItem.loans && selectedItem.histories" class="text-md font-semibold">Balance: {{ 
                                selectedItem.loans.reduce((total, loan) => {
                                    const loanAmount = loan.amount || 0;
                                    const loanQty = loan.bags || 0;
                                    return total + loanAmount * loanQty; // Multiply amount by qty (bags)
                                }, 0) - selectedItem.histories.reduce((total, loan) => {
                                    const loanAmount = loan.amountPaid || 0;
                                    return total + loanAmount;
                                }, 0) }}</h3>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <Table
                title="Loan"
                :headers="headers"
                :data="filteredLoans"
                :filterData="filteredLoansForTable"
                :perPage="10"
                @editItem="handleEdit"
                @deleteItem="handleDelete"
                @notifySMS="handleNotifySMS"
                @viewItem="handleView"
                :isHasViewBtn="true"
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
