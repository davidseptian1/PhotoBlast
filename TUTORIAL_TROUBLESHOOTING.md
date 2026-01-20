# 🔧 Tutorial Troubleshooting Guide

## ❗ Masalah: Tutorial Tidak Muncul

### Langkah-langkah Debugging:

### 1. **Test Page Sederhana**
Buka browser dan akses:
```
http://localhost/test-tutorial.html
atau
http://photoblast-rev.test/test-tutorial.html
```

Halaman ini akan menampilkan log console dan membantu mengidentifikasi masalah.

### 2. **Cek Console Browser**
Buka Developer Tools (F12) dan lihat tab Console:

**Yang harus muncul:**
```
✅ Driver.js loaded? true
✅ SweetAlert2 loaded? true  
✅ TutorialHelper loaded? true
✅ Has seen tutorial? false
```

**Jika ada yang false:**
- Periksa koneksi internet (CDN)
- Clear browser cache (Ctrl+Shift+Delete)
- Reload page (Ctrl+F5)

### 3. **Reset Tutorial Status**
Jika tutorial sudah pernah dilihat, reset dengan:

**Via Console Browser:**
```javascript
localStorage.removeItem('photoblast_tutorial_seen');
location.reload();
```

**Via Test Page:**
Klik tombol "Reset & Reload Tutorial" (merah di pojok kanan atas)

### 4. **Cek Network Tab**
Di Developer Tools → Network tab, pastikan file berikut ter-load:

✅ `driver.js.iife.js` - dari CDN jsdelivr
✅ `sweetalert2` - dari CDN jsdelivr  
✅ `tutorial-helper.js` - dari `/js/`
✅ `tutorial.css` - dari `/css/`
✅ `driver.css` - dari CDN jsdelivr

Jika ada yang gagal (merah/404), ada masalah loading file.

### 5. **Cek Error di Console**
Jika ada error merah di console:

**Error: "TutorialHelper is not defined"**
- File `tutorial-helper.js` tidak ter-load
- Solusi: Hard refresh (Ctrl+F5)

**Error: "driver is not defined"**
- CDN Driver.js tidak ter-load
- Solusi: Cek koneksi internet, atau ganti CDN

**Error: "Swal is not defined"**
- CDN SweetAlert2 tidak ter-load  
- Solusi: Cek koneksi internet

### 6. **Manual Test Tutorial**
Buka console browser dan jalankan:

```javascript
// Test TutorialHelper
const helper = new TutorialHelper();
console.log(helper);

// Test Welcome Popup
helper.showWelcomePopup('Test').then(result => {
    console.log('Result:', result);
});
```

Popup harus muncul!

### 7. **Cek Halaman Specific**

**Halaman Redeem/Email:**
```
http://photoblast-rev.test/redeem
```
Cek console apakah ada log debug yang saya tambahkan.

**Halaman Template:**
```
http://photoblast-rev.test/tempcollage
```

**Halaman Camera:**
```
http://photoblast-rev.test/camera
```

## 🛠️ Quick Fixes

### Fix 1: Clear All Cache
```javascript
// Di console browser
localStorage.clear();
sessionStorage.clear();
location.reload();
```

### Fix 2: Force Reload Assets
```
Ctrl + F5 (Windows)
Cmd + Shift + R (Mac)
```

### Fix 3: Disable Ad Blocker
Beberapa ad blocker memblokir CDN. Coba disable sementara.

### Fix 4: Test di Browser Lain
Coba Chrome, Firefox, atau Edge.

### Fix 5: Cek Laravel Asset
Pastikan Laravel bisa serve asset dengan benar:
```bash
php artisan storage:link
```

## 📱 Test di Different Devices

1. **Desktop Chrome** - Buka developer tools
2. **Mobile View** - Toggle device toolbar (Ctrl+Shift+M)
3. **Real Mobile** - Akses via IP lokal

## 🔍 Advanced Debugging

### Enable Verbose Logging
Edit `tutorial-helper.js`, tambahkan logging di setiap function:

```javascript
startTour(steps, options = {}) {
    console.log('startTour called with:', steps, options);
    // ... rest of code
}
```

### Check localStorage
```javascript
// Lihat semua localStorage
for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    console.log(key, '=', localStorage.getItem(key));
}
```

## ✅ Checklist Lengkap

- [ ] Test page ter-load dengan baik
- [ ] Console tidak ada error merah
- [ ] Driver.js loaded (typeof driver !== 'undefined')
- [ ] SweetAlert2 loaded (typeof Swal !== 'undefined')
- [ ] TutorialHelper loaded (typeof TutorialHelper !== 'undefined')
- [ ] localStorage 'photoblast_tutorial_seen' = 'false' atau tidak ada
- [ ] Welcome popup muncul
- [ ] Tutorial steps berjalan dengan baik
- [ ] Bisa klik Next/Previous
- [ ] Bisa close tutorial
- [ ] Tombol replay muncul

## 🆘 Still Not Working?

Jika masih tidak muncul setelah semua langkah di atas:

1. Screenshot console error
2. Screenshot network tab
3. Cek versi browser
4. Cek apakah JavaScript enabled di browser

## 📞 Support Commands

```bash
# Cek Laravel
php artisan --version

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart Laravel server
php artisan serve
```

---

**Pro Tip:** Gunakan test page (`test-tutorial.html`) untuk isolate masalah. Jika test page berfungsi tapi halaman asli tidak, masalahnya di integration Laravel.
