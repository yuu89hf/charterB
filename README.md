# charterB - Bulk Certificate Generator

charterB is a clean, lightweight, and modern Laravel web application built to automate bulk certificate generation with pixel-perfect precision and high customizability.

## 🚀 Key Features

- **Interactive Drag & Drop Editor**: Position the recipient's name on your certificate template dynamically.
- **Keyboard Precision Control**: Finely tune the text position using the **Arrow Keys** (1px movement) or **Shift + Arrow Keys** (10px movement) for pixel-perfect placement.
- **Smart Snap Guide (5%)**: Dragged elements automatically snap to every 5% coordinate interval for easy symmetry. Can be toggled on/off in the sidebar.
- **Google & System Fonts**: Choose from clean modern, elegant script, or classic serif fonts including:
  - Google Fonts: *Roboto*, *Montserrat*, *Playfair Display*, *Alex Brush*, *Cinzel*
  - System Fonts: *Comic Sans MS*, *Times New Roman*, *Arial*
- **Excel & CSV Reader**: 
  - Supports modern Excel files (`.xlsx`), legacy Excel files (`.xls`), and text-based (`.csv`, `.txt`) formats.
  - Automatically skips table headers (e.g. "Name", "Nama", "No").
  - Auto-detects delimiters (comma `,` or semicolon `;`) for CSVs.
  - **Longest Name Preview**: Scans the file in the browser and automatically displays the longest name as the preview text, allowing you to ensure names never clip or overflow.
- **Row Filtering & Exclusions**:
  - Filter specific ranges to process (e.g., generate rows 11 to 80 only).
  - Exclude individual rows from generation by specifying row numbers separated by semicolons (e.g. `22;33;44`).
- **Duplicate Detection**:
  - Detects duplicate names in the upload list and shows a warning on the UI.
  - Automatically filters duplicate rows on the backend to avoid redundant generation (*double building*). Matches names exactly.
- **Export to PDF, PNG, or JPG**:
  - Export certificates in vector-wrapped **PDF** format (built at high speed using optimized JPEG rendering in FPDF).
  - Also exports in standard **PNG** and **JPG** formats.
- **Preserved Filename Spaces**: Generates individual certificate files inside the ZIP exactly matching the recipient names (e.g. `Angel Leah.pdf` instead of `Angel_Leah.pdf`).
- **AJAX Progress Bar**: Live progress tracking of ZIP packaging for large-scale operations.

---

## 🛠️ Requirements & Installation

- **PHP**: ^8.2 (with `gd` and `zip` extensions enabled)
- **Composer**
- **Node.js & NPM** (for Vite assets compilation)

### Setup Steps

1. **Clone & Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment File**:
   Copy `.env.example` to `.env` and set your app name, URL, and database configuration.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Compile Assets**:
   ```bash
   npm run build
   ```

4. **Run Development Server**:
   ```bash
   php artisan serve
   ```
   Or use **Laragon** and access it via `http://charterB.test/workspace`.

---

## 💡 How to Use

1. **Upload Template**: Drag or select your certificate template image (PNG, JPG, JPEG, WebP) in the sidebar settings.
2. **Upload Data File**: Upload your recipient name list (Excel `.xlsx`/`.xls` or CSV) where the names are located in **Column A**.
3. **Configure Style & Position**:
   - Choose your preferred font and adjust the size.
   - Drag the name preview box on the canvas, or click it and use the **Arrow Keys** to position it.
4. **Generate**: Select output format (PDF/PNG/JPG) and resolution scale, then click **Generate & Download ZIP**.

---

## 📄 License
This project is open-sourced under the [MIT license](LICENSE).
