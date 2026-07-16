<h1 align="center">charterB 🎓</h1>

<p align="center">
  <strong>Bulk Certificate Generator — drag, drop, and download in seconds.</strong><br>
  A clean, modern Laravel web app for generating personalized certificates at scale.
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

**charterB** automates the tedious task of generating personalized certificates in bulk. Upload your template image, drop a CSV of recipient names, position the text with pixel-level precision on an interactive canvas — then download a ready-to-print ZIP file, all in your browser.

---

## 🚀 Features

### 📂 File & Data Input
- Upload certificate templates — JPG, PNG, WebP, GIF, BMP
- Import recipient names from **CSV, TXT, XLSX, or XLS**
- **Smart CSV Parser** — auto-detects `,` or `;` separator; skips header rows (`Name`, `Nama`, `No`)
- **Longest Name Preview** — automatically previews the longest name so text never clips
- **Row Filtering** — set start/end rows and exclude specific rows

### 🖊️ Text Positioning & Styling
- **Interactive Drag & Drop** — drag the name label anywhere on the canvas
- **Keyboard Precision** — `Arrow Keys` = 1px movement, `Shift + Arrow` = 10px movement
- **Smart Snap Guide (5%)** — snaps to 5% coordinate intervals; toggleable
- **8 Font Choices** — Roboto, Montserrat, Playfair Display, Alex Brush, Cinzel, Comic Sans, Times New Roman, Arial
- **Auto Font Shrink** — text auto-scales down to prevent clipping
- **Live Preview** — font family, size, and position update in real-time

### 📤 Export & Output
- Output formats: **PNG**, **JPG**, or **PDF** per certificate
- **Resolution Scaling** — 25% – 300% to control output file size
- **Paper Size Mode** — embed certificate on A4 or F4 white canvas
- **PDF Orientation** — Auto / Portrait / Landscape
- **Image Layout** — Full Page or Custom position within the paper canvas
- **Preserved Filenames** — ZIP uses original recipient names (e.g. `Angel Leah.png`)
- **AJAX Progress Bar** — live progress % for large batch generation

---

## 🛠️ Requirements

- **PHP** `^8.2` with `gd` and `zip` extensions enabled
- **Composer**
- **Node.js** & **npm** (for Vite asset compilation)

---

## ⚙️ Installation

```bash
git clone https://github.com/yuu89hf/charterB.git
cd charterB

composer install
npm install

cp .env.example .env
php artisan key:generate

npm run build
php artisan serve
```

> **Using Laragon?** Clone into your `www` directory and access at `http://charterB.test/`

---

## 💡 How to Use

1. **Upload Template** — select your certificate image in the sidebar (JPG, PNG, WebP, etc.)
2. **Upload CSV** — import your recipient list (names in **Column A**)
3. **Position Name** — drag the blue label on the canvas, or use **Arrow Keys** for pixel-perfect placement
4. **Set Style** — choose font and adjust size with the slider
5. **Configure Export** — select PNG / JPG / PDF, set resolution, optionally enable Paper Size mode
6. **Generate** — click **Generate & Download ZIP** and watch the progress bar fill up 🎉

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
