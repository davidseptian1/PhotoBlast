## 🎯 Quick Start Guide - Test Tutorial

### Langkah Cepat untuk Test:

1. **Buka Test Page:**
   ```
   http://photoblast-rev.test/test-tutorial.html
   ```

2. **Lihat Console Log:**
   - Buka Developer Tools (F12)
   - Tab Console harus menunjukkan:
     ```
     Page loaded
     Driver.js loaded? true
     SweetAlert2 loaded? true
     TutorialHelper loaded? true
     Has seen tutorial? false
     Showing welcome popup...
     ```

3. **Popup Welcome Harus Muncul:**
   - Popup SweetAlert2 dengan 2 tombol
   - "Ya, Tunjukkan Panduan" atau "Tidak, Saya Sudah Paham"

4. **Klik "Ya":**
   - Tutorial akan mulai dengan highlight email input
   - Klik "Selanjutnya" untuk lanjut

5. **Test di Halaman Real:**
   
   **Reset dulu tutorial:**
   ```javascript
   localStorage.removeItem('photoblast_tutorial_seen');
   ```
   
   **Lalu akses halaman:**
   ```
   http://photoblast-rev.test/redeem
   ```

### ⚠️ Jika Tidak Muncul:

**Cek di Console Browser:**
```javascript
// Test 1: Cek library loaded
console.log('Driver:', typeof driver);
console.log('Swal:', typeof Swal);
console.log('TutorialHelper:', typeof TutorialHelper);

// Test 2: Manual trigger
if (typeof TutorialHelper !== 'undefined') {
    const helper = new TutorialHelper();
    helper.showWelcomePopup('Test Page');
}
```

### 🔄 Reset Tutorial:
```javascript
localStorage.removeItem('photoblast_tutorial_seen');
location.reload();
```

### 📋 Checklist:
- [ ] Test page bisa diakses
- [ ] Console log muncul
- [ ] Welcome popup muncul
- [ ] Bisa klik next/previous
- [ ] Tutorial selesai dan tersimpan

---

**Note:** Jika test page berhasil tapi halaman Laravel tidak, kemungkinan ada konflik dengan script lain atau timing issue. Lihat TUTORIAL_TROUBLESHOOTING.md untuk detail.
