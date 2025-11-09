<template>
  <div class="container">
    <div class="header">
      <h1>📄 CSV File upload system</h1>
      <p>Upload CSV files, the system will process them in the background</p>
    </div>

    <!-- Upload Area -->
    <div class="upload-section">
      <div
        class="upload-area"
        :class="{ dragover: isDragOver }"
        @click="triggerFileSelect"
        @dragover.prevent="isDragOver = true"
        @dragleave="isDragOver = false"
        @drop.prevent="handleDrop"
      >
        <div class="upload-icon">📤</div>
        <div class="upload-text">Click or drag files here to upload</div>
        <div class="upload-hint">Supports CSV files up to 40 MB</div>
        <input
          type="file"
          ref="fileInput"
          accept=".csv,.txt"
          @change="handleFileChange"
          class="hidden"
        />
      </div>
    </div>

    <!-- Upload History -->
    <div class="uploads-list">
      <h2>Upload History</h2>
      <div v-if="uploads.length === 0" class="empty-state">No upload records</div>
      <div v-else>
      <div
        class="upload-item"
        v-for="upload in uploads"
        :key="upload.id"
      >
        <div class="upload-row">
          <div class="upload-info-left">
            <div class="upload-filename">{{ upload.file_name }}</div>
            <div class="upload-time">Upload time: {{ formatDate(upload.created_at) }}</div>
          </div>
          <div class="upload-status" :class="'status-' + upload.status">
            {{ statusText(upload.status) }}
          </div>
        </div>

        <div v-if="upload.error_message" class="upload-error">
          Error: {{ upload.error_message }}
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

const uploads = ref([]);
const fileInput = ref(null);
const isDragOver = ref(false);
let polling = null;

// === Upload Logic ===
const triggerFileSelect = () => fileInput.value.click();

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) uploadFile(file);
};

const handleDrop = (e) => {
  isDragOver.value = false;
  const file = e.dataTransfer.files[0];
  if (file) uploadFile(file);
};

const uploadFile = async (file) => {
  const formData = new FormData();
  formData.append("file", file);

  try {

    await axios.post("/api/upload", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    await fetchUploads(); // Refresh the list after successful upload
  } catch (error) {
    console.error(error);
    alert("Upload failed: " + (error.response?.data?.message || error.message));
  }
};

// === Fetch Upload Records ===
const fetchUploads = async () => {
  try {
    const res = await axios.get("/api/status");
    uploads.value = res.data?.data || [];
  } catch (err) {
    console.error("Failed to load upload records:", err);
  }
};

// === Utility Functions ===
const statusText = (status) => {
  return {
    pending: "Pending",
    processing: "Processing",
    completed: "Completed",
    failed: "Failed",
  }[status] || status;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);

  // Convert to Malaysia time zone (+8)
  const malaysiaTime = new Date(
    date.toLocaleString("en-US", { timeZone: "Asia/Kuala_Lumpur" })
  );

  // Format as 12-hour time (AM/PM)
  const formatted = malaysiaTime.toLocaleString("en-MY", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });

  // Calculate time difference
  const diffText = timeAgo(malaysiaTime);

  return `${formatted} (${diffText})`;
};

const timeAgo = (date) => {
  const now = new Date();
  const diffMs = now - date;
  const diffMinutes = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMinutes / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMinutes < 1) return "Just now";
  if (diffMinutes < 60) return `${diffMinutes} minutes ago`;
  if (diffHours < 24) return `${diffHours} hours ago`;
  return `${diffDays} days ago`;
};

// === Auto Polling ===
const startPolling = () => {
  polling = setInterval(fetchUploads, 3000);
};
const stopPolling = () => {
  if (polling) clearInterval(polling);
};

onMounted(() => {
  fetchUploads();
  startPolling();

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) stopPolling();
    else startPolling();
  });
});

onUnmounted(stopPolling);
</script>

<style scoped>

</style>
