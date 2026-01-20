# Tutorial System - Photoblast

Sistem tutorial interaktif telah berhasil diimplementasikan di seluruh halaman utama aplikasi Photoblast.

## 🎯 Fitur Tutorial

1. **Welcome Popup** - Popup pertama yang menanyakan apakah user ingin melihat tutorial
2. **Guided Tour** - Step-by-step guide dengan highlight pada elemen penting
3. **User-Friendly** - Desain menarik dengan gradient dan animasi
4. **Persistent State** - Tutorial hanya muncul sekali (disimpan di localStorage)
5. **Replay Button** - Tombol untuk mengulangi tutorial kapan saja

## 📄 Halaman yang Sudah Diimplementasi

### 1. **Halaman Redeem/Email** (`redeem.blade.php`)
- Tutorial input email
- Penjelasan tombol submit

### 2. **Halaman Template/Layout** (`template.blade.php`)
- Tutorial memilih layout foto
- Penjelasan jumlah foto per layout
- Cara preview frame (jika tersedia)

### 3. **Halaman Kamera** (`camera.blade.php`)
- Tutorial preview kamera
- Penjelasan tombol flip/mirror
- Cara memilih filter
- Penjelasan timer/countdown
- Tombol ambil foto
- Mode auto capture
- Daftar foto yang sudah diambil

### 4. **Halaman Print** (`print.blade.php`)
- Penjelasan proses printing
- Langkah-langkah yang harus dilakukan
- Reminder untuk tidak menutup halaman

## 🎨 Desain

- **Warna**: Gradient purple-blue (#667eea → #764ba2)
- **Animasi**: Smooth fade-in dan pulse effects
- **Responsive**: Support untuk desktop dan mobile
- **Accessibility**: ARIA labels dan keyboard navigation

## 🔧 Cara Menggunakan

### Reset Tutorial (untuk testing)
Buka browser console dan jalankan:
```javascript
localStorage.removeItem('photoblast_tutorial_seen');
location.reload();
```

### Atau klik tombol "❓ Lihat Tutorial Lagi" di pojok kanan bawah setiap halaman

## 📦 Library yang Digunakan

1. **Driver.js** - Untuk guided tour dengan highlight elements
2. **SweetAlert2** - Untuk beautiful popup dialogs
3. **Custom CSS** - Styling khusus untuk tema Photoblast

## 🚀 Next Steps (Opsional)

Jika ingin menambahkan tutorial di halaman lain:

1. Buka file blade yang ingin ditambahkan tutorial
2. Tambahkan script tutorial di bagian bawah sebelum `@endsection`
3. Gunakan format yang sama seperti contoh di atas
4. Sesuaikan `element` selector dan `popover` content

## 💡 Tips

- Tutorial menggunakan localStorage, jadi akan reset jika user clear browser data
- Tutorial hanya muncul sekali per session
- User bisa skip tutorial kapan saja dengan klik tombol close atau ESC
- Tombol replay tutorial selalu tersedia untuk user yang ingin mengulang

---

Selamat! Sistem tutorial sudah siap digunakan! 🎉
