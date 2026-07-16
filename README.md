<h1 align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=28&duration=3000&pause=1000&color=3B82F6&center=true&vCenter=true&width=600&lines=charterB+%F0%9F%8E%93;Bulk+Certificate+Generator;Fast+%E2%80%A2+Flexible+%E2%80%A2+Pixel-Perfect" alt="charterB Typing SVG" />
</h1>

<p align="center">
  <strong>A clean, modern Laravel web app for bulk certificate generation — drag, drop, and download in seconds.</strong>
</p>

<p align="center">
  <a href="https://github.com/yuu89hf/charterB/releases">
    <img src="https://img.shields.io/github/v/release/yuu89hf/charterB?style=flat-square&color=3B82F6&label=Release" alt="Latest Release">
  </a>
  <a href="https://github.com/yuu89hf/charterB/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/yuu89hf/charterB?style=flat-square&color=10B981" alt="License">
  </a>
  <img src="https://img.shields.io/badge/PHP-%5E8.2-8892BF?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
</p>

---

## ✨ What is charterB?

**charterB** is a lightweight yet powerful Laravel application that automates the tedious task of generating personalized certificates in bulk. Upload your template image, drop a CSV of recipient names, position the text with pixel-level precision on an interactive canvas, and download a ready-to-print ZIP file — all in your browser.

---

## 🚀 Features

### 📂 File & Data Input
| Feature | Description |
|---|---|
| **Template Upload** | Accepts JPG, PNG, WebP, GIF, BMP certificate templates |
| **CSV / Excel Support** | Upload `.csv`, `.txt`, `.xlsx`, `.xls` name lists |
| **Smart CSV Parser** | Auto-detects `,` or `;` separators; skips common header rows (`Name`, `Nama`, `No`) |
| **Longest Name Preview** | Automatically finds and previews the longest name so text never clips |
| **Row Filtering** | Set start/end rows and exclude specific rows with precise control |

### 🖊️ Text Positioning & Styling
| Feature | Description |
|---|---|
| **Interactive Drag & Drop** | Drag the name label anywhere on the canvas |
| **Keyboard Precision** | `↑↓←→` moves 1px; `Shift + Arrow` moves 10px |
| **Smart Snap Guide (5%)** | Snaps to 5% coordinate intervals for easy centering; toggle on/off |
| **Font Selection** | Choose from 8 fonts: Roboto, Montserrat, Playfair Display, Alex Brush, Cinzel, Comic Sans, Times New Roman, Arial |
| **Auto Font Shrink** | Font automatically shrinks if text risks overflowing the template |
| **Live Preview** | Font family, size, and position all update in real-time on the canvas |

### 📤 Export & Output
| Feature | Description |
|---|---|
| **Output Formats** | PNG, JPG, or PDF per certificate |
| **Resolution Scale** | 25% – 300% resolution scaling to control file size |
| **Paper Size Mode** | Embed certificate on A4 or F4 white paper canvas |
| **PDF Paper** | Generate print-ready PDFs with custom paper orientation (Auto/Portrait/Landscape) |
| **Image Layout** | Full Page or Custom position within the paper canvas |
| **Preserved Filenames** | ZIP files use the original recipient names (e.g. `Angel Leah.png`, not `Angel_Leah.png`) |
| **AJAX Progress Bar** | Live progress % during ZIP generation for large batches |

---

## 🛠️ Requirements

- **PHP** `^8.2` with `gd` and `zip` extensions enabled
- **Composer**
- **Node.js** & **npm** (for Vite asset compilation)

---

## ⚙️ Installation

```bash
# 1. Clone the repository
git clone https://github.com/yuu89hf/charterB.git
cd charterB

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Configure your environment
cp .env.example .env
php artisan key:generate

# 5. Build front-end assets
npm run build

# 6. Start the development server
php artisan serve
```

> **Using Laragon?** Simply clone into your `www` directory and access the app at `http://charterB.test/`.

---

## 💡 How to Use

1. **Upload Template** — Drag or select your certificate image in the sidebar (JPG, PNG, WebP, etc.)
2. **Upload CSV** — Select your recipient list. Names should be in **Column A**.
3. **Position Name** — Drag the blue label on the canvas, or click it and use **Arrow Keys** for pixel-perfect placement.
4. **Set Style** — Choose font, adjust font size with the slider.
5. **Configure Export** — Select PNG / JPG / PDF, set resolution, optionally enable Paper Size mode.
6. **Generate** — Click **Generate & Download ZIP** and watch the progress bar fill up!

---

## 🗂️ Project Structure

```
charterB/
├── app/
│   └── Http/
│       └── Controllers/
│           └── CertificateController.php  # Core generation logic
├── public/
│   └── fonts/                             # TTF fonts used for rendering
├── resources/
│   └── views/
│       └── certificate/
│           └── index.blade.php            # Main interactive workspace UI
└── routes/
    └── web.php                            # Application routes
```

---

## 🔤 Available Fonts

| Font | Style | Source |
|---|---|---|
| **Roboto Bold** | Modern Sans-Serif | Google Fonts |
| **Montserrat Bold** | Sleek Sans-Serif | Google Fonts |
| **Playfair Display Bold** | Elegant Serif | Google Fonts |
| **Alex Brush** | Signature Script | Google Fonts |
| **Cinzel Bold** | Classic Serif | Google Fonts |
| **Comic Sans MS** | Casual | System |
| **Times New Roman** | Classic Serif | System |
| **Arial** | Clean Sans-Serif | System |

---

## 🧑‍💻 Tech Stack

- **Backend**: [Laravel 12](https://laravel.com/) (PHP 8.2+)
- **Image Processing**: [Intervention Image v4](https://image.intervention.io/) with GD driver
- **PDF Generation**: [FPDF](http://www.fpdf.org/)
- **Frontend**: [TailwindCSS 3](https://tailwindcss.com/) + Vanilla JS + [Vite](https://vitejs.dev/)
- **Excel Parsing**: [SheetJS (xlsx)](https://sheetjs.com/) (client-side)

---

## 📄 License

This project is open-sourced under the [MIT License](LICENSE).

---

<p align="center">
  Made with ❤️ using Laravel &nbsp;·&nbsp; <a href="https://github.com/yuu89hf/charterB/issues">Report a Bug</a> &nbsp;·&nbsp; <a href="https://github.com/yuu89hf/charterB/issues">Request a Feature</a>
</p>
