# 🖨️ Panduan Print A6 (105mm x 148mm) - Tanpa Terpotong

## ⚠️ Masalah: Hasil Print Terpotong di Sisi Kiri & Kanan

### ✅ Solusi yang Sudah Diterapkan:

1. **CSS Print A6** (`print-a6.css`)
   - Margin: 0 (no margins)
   - Size: A6 portrait (105mm x 148mm)
   - Full bleed support

2. **JavaScript Print Padding**
   - Padding dikurangi dari 40px ke 10px minimal
   - Maksimalkan area print

3. **@page Rule**
   - Hapus semua default browser margins
   - Force A6 size

## 🔧 Setting Printer yang Benar

### Di Browser Print Dialog:

1. **Paper Size**: Pilih **A6 (105 x 148 mm)**
2. **Margins**: Pilih **None** atau **Minimum**
3. **Scale**: **100%** (jangan di-scale)
4. **Headers & Footers**: **OFF** (matikan)
5. **Background graphics**: **ON** (aktifkan)

### Di Windows Printer Settings:

```
Preferences > Paper/Quality:
- Paper Size: A6 (105 x 148 mm)
- Paper Type: Photo Paper (jika tersedia)

Advanced:
- Paper Size: A6 (105 x 148 mm)
- Borderless Printing: ON (jika tersedia)
```

### Di Printer Thermal (Seperti DNP DS620):

```
- Paper Size: 4x6" atau A6
- Print Quality: High/Fine
- Borderless: YES
- Auto Cut: ON
```

## 🎯 Tips Agar Tidak Terpotong:

### 1. **Enable Borderless Printing**
Jika printer support borderless:
- Aktifkan "Borderless" di printer settings
- Biasanya ada di Advanced settings

### 2. **Check Printer Margins**
Kebanyakan printer punya minimum margin 2-5mm:
- Cek spec printer Anda
- Sesuaikan design jika perlu

### 3. **Use Correct Paper Type**
- Gunakan kertas A6 yang sesuai
- Jangan paksa A4 dipotong
- Photo paper biasanya support borderless

### 4. **Test Print**
```javascript
// Di browser console, test print tanpa tutorial
localStorage.setItem('photoblast_tutorial_seen', 'true');
```

## 📐 Cek Actual Print Size

### Cara Mengukur:
1. Print 1 lembar test
2. Ukur dengan penggaris:
   - Width: harus ≈ 105mm
   - Height: harus ≈ 148mm
3. Jika lebih kecil, ada masalah scaling

### Jika Ukuran Salah:
- Cek printer setting (jangan auto-scale)
- Pastikan paper size = A6
- Print scale harus 100%

## 🖨️ Printer-Specific Settings

### Canon SELPHY:
```
Paper Size: Postcard (4x6" / A6)
Borderless: ON
Quality: Fine
```

### Epson Photo Printers:
```
Paper Size: A6 (105 x 148mm)
Borderless: Full bleed
Media Type: Premium Glossy Photo Paper
Quality: Best Photo
```

### HP Photo Printers:
```
Paper Size: A6
Margins: Borderless
Paper Type: Advanced Photo Paper
Quality: Best
```

### DNP/Mitsubishi Thermal:
```
Paper: 4R (4x6")
Cut: Auto
Borderless: Yes
```

## 🐛 Troubleshooting

### Masalah: Masih Terpotong

**1. Cek Browser Print Settings:**
```
- Chrome: Print > More settings > Margins: None
- Edge: Print > More settings > Margins: None
- Firefox: Print > Page Setup > Margins: 0
```

**2. Cek CSS Sudah Load:**
```javascript
// Di browser console
console.log(document.styleSheets);
// Cari print-a6.css
```

**3. Clear Cache:**
```
Ctrl + Shift + Delete
Atau
Ctrl + F5 (hard refresh)
```

**4. Test dengan PDF:**
- Print to PDF dulu
- Cek hasil PDF apakah terpotong
- Jika PDF OK, masalah di printer setting

### Masalah: Gambar Terlalu Kecil

**Penyebab:** Padding terlalu besar

**Solusi:** Edit `print.js`
```javascript
function getBasePadding(canvasWidth, canvasHeight) {
    return 5; // Kurangi dari 10 ke 5
}
```

### Masalah: Ada White Space di Pinggir

**Penyebab:** Printer margin default

**Solusi:** 
1. Enable borderless printing
2. Atau accept 2-3mm margin (normal untuk non-borderless printer)

## ✅ Checklist Before Print

- [ ] Paper size = A6 (105 x 148mm)
- [ ] Margins = None/Minimum
- [ ] Borderless ON (if available)
- [ ] Scale = 100%
- [ ] Headers/Footers OFF
- [ ] Correct paper loaded in printer
- [ ] Test print first
- [ ] Measure result

## 📞 Support

Jika masih terpotong setelah setting di atas:
1. Cek spec printer (apakah support borderless A6)
2. Beberapa printer punya minimum margin 2-5mm (tidak bisa dihindari)
3. Consider upgrade ke printer yang support full borderless

---

**Note:** Beberapa printer tidak support 100% borderless. Jika printer Anda punya minimum margin 2-3mm, itu adalah limitasi hardware dan tidak bisa di-fix via software.
