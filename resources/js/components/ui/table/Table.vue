<script setup>
import { ref, computed } from "vue";
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

// pdfMake.vfs = pdfFonts.pdfMake.vfs;

const props = defineProps({
  title: String,
  headers: Array,
  filterData: Array,
  data: Array,
  perPage: Number,
  isHasDeleteBtn: Boolean,
  isHasMarkAsAvailableBtn: Boolean,
  isHasEditBtn: Boolean,
  isHasViewBtn: Boolean,
  isHasDownloadQrCodeBtn: Boolean,
  isHasNotifySMSBtn: Boolean,
  noActions: Boolean,
  isHasFilter: Boolean,
  isRowClickable: Boolean
});

const emit = defineEmits([
  "viewEdit",
  "editItem",
  "deleteItem",
  "markAsAvailableItem",
  "viewItem",
  "downloadQrCode",
  "notifySMS",
]);

const rowsPerPage = props.perPage;
const currentPage = ref(1);
const searchQuery = ref("");
const startDate = ref(null);
const endDate = ref(null);

const filteredData = computed(() => {
  let filtered = props.filterData;
  console.log('filteredfiltered', filtered)

  if (startDate.value && endDate.value) {
    filtered = filtered.filter((row) => {
      const createdAt = new Date(row.created_at);
      const start = new Date(startDate.value);
      const end = new Date(endDate.value);
      end.setDate(end.getDate() + 1); // Include the entire end date

      return createdAt >= start && createdAt < end;
    });
  }

  if (searchQuery.value) {
    filtered = filtered.filter((row) =>
      Object.values(row).some((value) =>
        String(value).toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    );
  }

  return filtered;
});

const totalRows = computed(() => filteredData.value.length);
const totalPages = computed(() => Math.ceil(totalRows.value / rowsPerPage));

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage;
  return filteredData.value.slice(start, start + rowsPerPage);
});

const showingRange = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage + 1;
  const end = Math.min(start + rowsPerPage - 1, totalRows.value);
  return totalRows.value > 0
    ? `${start}-${end} of ${totalRows.value}`
    : "No results found";
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const ViewItem = (item) => {
  const index = props.data.findIndex((data) => data.id === item.id);
  emit("viewItem", props.data[index]);
};

const editItem = (item) => {
  const index = props.data.findIndex((data) => data.id === item.id);
  emit("editItem", props.data[index]);
};

const markAsAvailableItem = (item) => {
  const index = props.data.findIndex((data) => data.id === item.id);
  emit("markAsAvailableItem", props.data[index]);
};


const deleteItem = (itemId) => {
  if (confirm("Are you sure you want to delete this item?")) {
    emit("deleteItem", itemId);
  }
};

const downloadQrCode = (itemId) => {
  emit("downloadQrCode", itemId);
};

const notifySMS = (item) => {
    const index = props.data.findIndex((data) => data.id === item.id);
  // emit("editItem", props.data[index]);
  emit("notifySMS", props.data[index]);
};

const downloadPDF = () => {
  const tableBody = [];
  const clonedPropsHeader = [...props.headers];
  const formattedHeaderRow = clonedPropsHeader.map(text => ({ text, bold: true }));
  tableBody.push(formattedHeaderRow);
 const dataToProcess = [...filteredData.value];

  const processedData = dataToProcess.map(row => {
    const newRow = {...row};
    // Example: Add soft hyphens to long strings
    Object.keys(newRow).forEach(key => {
      if (typeof newRow[key] === 'string' && newRow[key].length > 30) {
        newRow[key] = newRow[key].replace(/([^\s-]{10})/g, '$1\u00AD');
      }
    });

    return Object.values(newRow);
  });

  processedData.forEach(rowData => {
    tableBody.push(rowData);
  });

  const columnWidth = `${100 / props.headers.length}%`; // Calculate column width

  const docDefinition = {
    pageSize: 'A4',
    pageOrientation: 'landscape',
    content: [
      { text: props.title, style: 'header', alignment: 'center' },
      {
        table: {
          widths: props.headers.map(() => columnWidth), // Force even column widths
          body: tableBody,
          layout: {
            hLineWidth: function (i, node) {
              return 0.5;
            },
            vLineWidth: function (i, node) {
              return 0.5;
            },
            hLineColor: function (i, node) {
              return '#aaa';
            },
            vLineColor: function (i, node) {
              return '#aaa';
            },
            paddingLeft: function (i, node) {
              return 1;
            },
            paddingRight: function (i, node) {
              return 1;
            },
            paddingTop: function (i, node) {
              return 1;
            },
            paddingBottom: function (i, node) {
              return 1;
            },
          },
        },
        style: {
          fontSize: 11,
          lineHeight: 0.8,
        },
      },
    ],
    styles: {
      header: {
        fontSize: 18,
        bold: true,
        margin: [0, 0, 0, 10],
      },
    },
  };

  pdfMake.createPdf(docDefinition).download(`${props.title}.pdf`);
};
</script>

<template>
  <div class="overflow-x-auto rounded-md border border-gray-200">
    <div v-if="props.isHasFilter" class="p-4 flex flex-wrap gap-2">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search..."
        class="px-3 py-2 border rounded"
      />
      <input
        v-model="startDate"
        type="date"
        class="px-3 py-2 border rounded"
      />
      <input
        v-model="endDate"
        type="date"
        class="px-3 py-2 border rounded"
      />
      <button @click="downloadPDF" class="px-4 py-2 bg-green-500 text-white rounded">Download PDF</button>
    </div>

    <table class="w-full text-left text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th v-for="(header, index) in headers" :key="index" class="px-4 py-2">
            {{ header }}
          </th>
          <th v-if="!noActions" class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr @click="props.isRowClickable ? ViewItem(row) : null" v-for="(row, rowIndex) in paginatedData" :key="rowIndex" class="border-b">
          <td v-for="(value, colIndex) in row" :key="colIndex" class="px-4 py-2">
            <span v-if="value === 'Overdue'" style="color: red;">
              {{ value }}
            </span>
            <span v-else>
              {{ value }}
            </span>
          </td>
          <td class="px-4 py-2 flex gap-2">
            <button v-if="props.isHasDownloadQrCodeBtn" @click.stop="downloadQrCode(row.id)" class="px-2 py-1 text-white bg-purple-500 rounded">QRcode</button>
            <button v-if="props.isHasDeleteBtn" @click.stop="deleteItem(row.id)" class="px-2 py-1 text-white bg-red-500 rounded">Delete</button>
            <button v-if="props.isHasMarkAsAvailableBtn" @click.stop="markAsAvailableItem(row)" class="px-2 py-1 text-white bg-pink-500 rounded">Mark as Available</button>
            <button v-if="props.isHasEditBtn && row.status !== 'Returned'" @click.stop="editItem(row)" class="px-2 py-1 text-white bg-green-500 rounded">
              <span v-if="title === 'Maintainance'">
                Update status
              </span>
              <span v-else>
                {{title === 'Loan' || title === 'Rental' ? 'Billing' : 'Edit' }}
              </span>
            </button>
            <button v-if="props.isHasViewBtn" @click.stop="ViewItem(row)" class="px-2 py-1 text-white bg-blue-500 rounded">View</button>
            <button v-if="props.isHasNotifySMSBtn && row.status === 'Overdue'" @click.stop="notifySMS(row)" class="px-2 py-1 text-white bg-amber-500 rounded">Notify</button>
          </td>
        </tr>
      </tbody>
      </table>
      <div class="flex justify-between items-center p-4">
        <span class="text-sm">{{ showingRange }}</span>

        <div class="flex gap-2">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 border rounded disabled:opacity-50">
            Prev
          </button>
          <span class="mt-1">Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 border rounded disabled:opacity-50">
            Next
          </button>
        </div>
      </div>
    </div>
</template>