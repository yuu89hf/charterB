# charterB - Bulk Certificate Generator

charterB is a clean, lightweight, and modern Laravel web application built to automate bulk certificate generation with pixel-perfect precision and high customizability.

## 🚀 Key Features

- **Interactive Drag & Drop Editor**: Position the recipient's name on your certificate template dynamically.
- **Keyboard Precision Control**: Finely tune the text position using the **Arrow Keys** (1px movement) or **Shift + Arrow Keys** (10px movement) for pixel-perfect placement.
- **Smart Snap Guide (5%)**: Dragged elements automatically snap to every 5% coordinate interval for easy symmetry. Can be toggled on/off in the sidebar.
- **Google & System Fonts**: Choose from clean modern, elegant script, or classic serif fonts including:
  - Google Fonts: *Roboto*, *Montserrat*, *Playfair Display*, *Alex Brush*, *Cinzel*
  - System Fonts: *Comic Sans MS*, *Times New Roman*, *Arial*
- **Smart CSV Parser**: 
  - Automatically skips table headers (e.g. "Name", "Nama", "No").
  - Auto-detects delimiters (comma `,` or semicolon `;`).
  - **Longest Name Preview**: Scans the CSV and automatically displays the longest name as the preview text, allowing you to ensure names never clip or overflow.
- **Preserved Filename Spaces**: Generates individual certificate files inside the ZIP exactly matching the recipient names (e.g. `Angel Leah.png` instead of `Angel_Leah.png`).
- **Flexible Formats & Resolution**: Export as PNG or JPG, with custom scale resolution (25% to 300%) to compress file sizes.
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
   Or use **Laragon** and access it via `http://charterB.test/`.

---

## 💡 How to Use

1. **Upload Template**: Drag or select your certificate template image (JPG, PNG, WebP, etc.) in the sidebar settings.
2. **Upload CSV**: Upload your recipient name list (CSV file) where the names are located in **Column A**.
3. **Configure Style & Position**:
   - Choose your preferred font and adjust the size.
   - Drag the name preview box on the canvas, or click it and use the **Arrow Keys** to position it.
4. **Generate**: Select output format (PNG/JPG) and resolution scale, then click **Generate & Download ZIP**.

---

## 📄 License
This project is open-sourced under the [MIT license](LICENSE).
