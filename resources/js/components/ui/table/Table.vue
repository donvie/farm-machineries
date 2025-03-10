<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  headers: Array,
  filterData: Array,
  data: Array,
  perPage: Number,
  isHasDeleteBtn: Boolean,
  isHasEditBtn: Boolean,
  isHasViewBtn: Boolean,
  isHasDownloadQrCodeBtn: Boolean,
  noActions: Boolean
});

const emit = defineEmits(["viewEdit", "editItem", "deleteItem", "viewItem", "downloadQrCode"]);

const rowsPerPage = props.perPage;
const currentPage = ref(1);
const searchQuery = ref(""); // Search input value

// Compute total rows based on filtered filterData
const filteredData = computed(() => {
  if (!searchQuery.value) return props.filterData;
  
  return props.filterData.filter(row =>
    Object.values(row).some(value =>
      String(value).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  );
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
  return totalRows.value > 0 ? `${start}-${end} of ${totalRows.value}` : "No results found";
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const ViewItem = (item) => {
  const index = props.data.findIndex(data => data.id === item.id)
  emit("viewItem", props.data[index]);
};

const editItem = (item) => {
  emit("editItem", item);
};

const deleteItem = (itemId) => {
  if (confirm("Are you sure you want to delete this item?")) {
    emit("deleteItem", itemId);
  }
};

const downloadQrCode = (itemId) => {
    emit("downloadQrCode", itemId);
};
</script>

<template>
  <div class="overflow-x-auto rounded-md border border-gray-200">
    <!-- Search Input -->
    <div class="p-4">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search..." 
        class="px-3 py-2 border rounded"
      />
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
        <tr v-for="(row, rowIndex) in paginatedData" :key="rowIndex" class="border-b">
          <td v-for="(value, colIndex) in row" :key="colIndex" class="px-4 py-2">
            {{ value }}
          </td>
          <td class="px-4 py-2 flex gap-2">
            <button v-if="props.isHasDownloadQrCodeBtn" @click="downloadQrCode(row.id)" class="px-2 py-1 text-white bg-purple-500 rounded">QRcode</button>
            <button v-if="props.isHasEditBtn" @click="editItem(row)" class="px-2 py-1 text-white bg-green-500 rounded">Edit</button>
            <button v-if="props.isHasDeleteBtn" @click="deleteItem(row.id)" class="px-2 py-1 text-white bg-red-500 rounded">Delete</button>
            <button v-if="props.isHasViewBtn" @click="ViewItem(row)" class="px-2 py-1 text-white bg-blue-500 rounded">View</button>
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
