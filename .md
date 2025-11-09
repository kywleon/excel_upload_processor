# 📊 Excel Upload Processor

A Laravel + Vue 3 application that allows users to upload Excel or CSV files, process them asynchronously using background jobs, and update existing database records (UPSERT) intelligently. The frontend provides a real-time view of recent uploads and their processing statuses.

---

## 🚀 Features

- **File Uploads**
  - Supports Excel and CSV file formats.
  - Cleans up non-UTF-8 characters before processing.
  - Idempotent upload — duplicate files won't create multiple entries.
  
- **Background Processing**
  - Each upload is queued and processed by a Laravel **Job** in the background.
  - Ensures scalability and responsiveness during large file uploads.

- **UPSERT Logic**
  - Automatically updates existing rows based on a `UNIQUE_KEY`.
  - Example: if `PIECE_PRICE` is modified, the corresponding record is updated instead of duplicated.

- **Upload History Dashboard**
  - Displays a list of recent uploads with:
    - File name  
    - Upload time (Malaysia time, 12-hour format)  
    - Upload status (e.g., `Pending`, `Processing`, `Completed`, `Failed`)
  - Automatically refreshes upload status in real time using Vue.js reactivity.

- **API Transformers**
  - Uses Laravel **API Resources** (e.g., `UploadResource`) to format responses consistently.

---

## 🧱 Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend | Laravel Framework **12.37.0** |
| Frontend | Vue.js **v3** |
| Queue System | Laravel Queues / Jobs |
| Database | SQLite (development) / MySQL (production-ready) |
| Language | PHP 8+, JavaScript (ES6+) |

---

## ⚙️ How It Works

1. **User uploads** an Excel or CSV file.
2. The backend:
   - Validates and stores the file.
   - Generates a **hash** to ensure idempotency.
   - Dispatches a **background job** to process the file.
3. The job:
   - Parses and cleans up data.
   - Performs **UPSERT** operations based on `UNIQUE_KEY`.
   - Updates the upload record’s status accordingly.
4. The frontend (Vue.js):
   - Displays all uploads in a live-updating table.
   - Shows upload time in **Malaysia timezone (GMT+8)** and **12-hour format**.
   - Calculates the time difference between the upload and current time (e.g., “8 minutes ago”).

---

## 🧩 API Endpoints

| Method | Endpoint | Description |
|--------|-----------|-------------|
| `POST` | `/api/upload` | Upload a new Excel/CSV file |
| `GET` | `/api/status` | List all uploaded files with status and timestamps |

---

## 🖥️ Frontend Features

- Built with **Vue 3 Composition API**.
- Automatically updates upload statuses via reactive polling.
- Displays upload time in readable **localized format** (e.g., `08/11/2025, 11:59 PM (10 minutes ago)`).

---

## 🧠 Example Flow

```text
User → Upload Excel File → Laravel Controller → Store file + hash
          ↓
   Dispatch Job → Process rows → UPSERT into database
          ↓
 Update upload status → Vue frontend auto-refreshes → Show “Completed ✅”
