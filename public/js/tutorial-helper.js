/**
 * Tutorial Helper - User-friendly guide untuk Photoblast
 * Menggunakan Driver.js dan SweetAlert2
 */

(function(window) {
    'use strict';

class TutorialHelper {
    constructor() {
        this.hasSeenTutorial = this.checkTutorialStatus();
    }

    /**
     * Check apakah user sudah pernah lihat tutorial
     */
    checkTutorialStatus() {
        return localStorage.getItem('photoblast_tutorial_seen') === 'true';
    }

    /**
     * Tandai tutorial sudah dilihat
     */
    markTutorialAsSeen() {
        localStorage.setItem('photoblast_tutorial_seen', 'true');
    }

    /**
     * Reset tutorial status (untuk testing atau user baru)
     */
    resetTutorial() {
        localStorage.removeItem('photoblast_tutorial_seen');
    }

    /**
     * Show welcome popup dengan opsi skip atau mulai tutorial
     */
    async showWelcomePopup(pageName) {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 not loaded');
            return false;
        }
        
        const result = await Swal.fire({
            title: '👋 Selamat Datang!',
            html: `
                <p>Apakah Anda membutuhkan <strong>panduan</strong> untuk menggunakan ${pageName}?</p>
                <p class="text-sm text-gray-600">Panduan akan membantu Anda memahami setiap langkah dengan mudah.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '📖 Ya, Tunjukkan Panduan',
            cancelButtonText: '❌ Tidak, Saya Sudah Paham',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            allowOutsideClick: false,
            customClass: {
                popup: 'swal-popup-tutorial',
                confirmButton: 'btn-confirm-tutorial',
                cancelButton: 'btn-cancel-tutorial'
            }
        });

        return result.isConfirmed;
    }

    /**
     * Start guided tour dengan Driver.js
     */
    startTour(steps, options = {}) {
        if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
            console.error('Driver.js not loaded properly');
            return;
        }
        
        const defaultOptions = {
            showProgress: true,
            showButtons: ['next', 'previous', 'close'],
            nextBtnText: 'Selanjutnya →',
            prevBtnText: '← Sebelumnya',
            doneBtnText: '✓ Selesai',
            closeBtnText: '×',
            progressText: '{{current}} dari {{total}}',
            allowClose: true,
            overlayClickNext: false,
            smoothScroll: true,
            disableActiveInteraction: false,
            ...options
        };

        const driverObj = window.driver.js.driver({
            ...defaultOptions,
            steps: steps,
            onDestroyStarted: () => {
                if (!driverObj.hasNextStep() || confirm('Apakah Anda yakin ingin melewati tutorial?')) {
                    driverObj.destroy();
                    this.markTutorialAsSeen();
                }
            }
        });

        driverObj.drive();
    }

    /**
     * Show single step popup/tooltip
     */
    showStep(element, title, description, position = 'bottom') {
        if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
            console.error('Driver.js not loaded properly');
            return;
        }
        
        const driverObj = window.driver.js.driver({
            showProgress: false,
            showButtons: ['close'],
            closeBtnText: '✓ Mengerti'
        });

        driverObj.highlight({
            element: element,
            popover: {
                title: title,
                description: description,
                position: position
            }
        });
    }

    /**
     * Show quick tip dengan timeout
     */
    async showQuickTip(message, type = 'info', duration = 3000) {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 not loaded');
            return;
        }
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        await Toast.fire({
            icon: type,
            title: message
        });
    }
}

// Tutorial configurations untuk setiap halaman
const TutorialConfigs = {
    // Halaman Redeem Code
    redeem: [
        {
            element: '#code-input',
            popover: {
                title: '🎫 Masukkan Kode Voucher',
                description: 'Ketik kode voucher yang Anda terima di sini. Kode biasanya terdiri dari huruf dan angka.',
                position: 'bottom'
            }
        },
        {
            element: '#submit-code-btn',
            popover: {
                title: '✅ Submit Kode',
                description: 'Klik tombol ini setelah memasukkan kode untuk memulai sesi photo booth.',
                position: 'top'
            }
        }
    ],

    // Halaman Template/Frame Selection
    template: [
        {
            element: '#frame-selection',
            popover: {
                title: '🖼️ Pilih Template Frame',
                description: 'Pilih salah satu template frame yang Anda sukai. Template akan digunakan untuk menghias foto Anda.',
                position: 'top'
            }
        },
        {
            element: '#layout-selection',
            popover: {
                title: '📐 Pilih Layout',
                description: 'Tentukan berapa banyak foto yang ingin Anda ambil (1, 2, 3, atau 4 foto) dengan memilih layout yang sesuai.',
                position: 'bottom'
            }
        },
        {
            element: '#next-btn',
            popover: {
                title: '➡️ Lanjut ke Kamera',
                description: 'Setelah memilih template dan layout, klik tombol ini untuk melanjutkan ke pengambilan foto.',
                position: 'top'
            }
        }
    ],

    // Halaman Camera
    camera: [
        {
            element: '#camera-preview',
            popover: {
                title: '📷 Preview Kamera',
                description: 'Posisikan diri Anda di depan kamera. Pastikan wajah Anda terlihat jelas dan pencahayaan cukup.',
                position: 'bottom'
            }
        },
        {
            element: '#countdown',
            popover: {
                title: '⏱️ Countdown',
                description: 'Perhatikan hitungan mundur sebelum foto diambil. Bersiaplah dan tersenyum! 😊',
                position: 'top'
            }
        },
        {
            element: '#capture-btn',
            popover: {
                title: '📸 Tombol Ambil Foto',
                description: 'Klik tombol ini untuk memulai pengambilan foto. Foto akan diambil secara otomatis setelah countdown.',
                position: 'top'
            }
        },
        {
            element: '#retake-btn',
            popover: {
                title: '🔄 Ulangi Foto',
                description: 'Jika hasil foto kurang memuaskan, gunakan tombol ini untuk mengambil foto ulang.',
                position: 'top'
            }
        },
        {
            element: '#next-btn',
            popover: {
                title: '✓ Selesai & Lanjutkan',
                description: 'Setelah semua foto selesai diambil dan hasilnya bagus, klik tombol ini untuk melanjutkan ke tahap berikutnya.',
                position: 'top'
            }
        }
    ],

    // Halaman List/Preview Photos
    preview: [
        {
            element: '#photo-list',
            popover: {
                title: '🖼️ Preview Foto-foto Anda',
                description: 'Ini adalah hasil foto yang sudah Anda ambil. Periksa apakah semua foto sudah sesuai keinginan.',
                position: 'top'
            }
        },
        {
            element: '#edit-btn',
            popover: {
                title: '✏️ Edit Foto',
                description: 'Anda bisa mengulangi pengambilan foto tertentu jika ada yang kurang memuaskan.',
                position: 'bottom'
            }
        },
        {
            element: '#continue-btn',
            popover: {
                title: '➡️ Lanjut ke Collage',
                description: 'Jika semua foto sudah OK, lanjutkan untuk membuat collage dan print.',
                position: 'top'
            }
        }
    ],

    // Halaman Print
    print: [
        {
            element: '#collage-preview',
            popover: {
                title: '🎨 Hasil Collage',
                description: 'Ini adalah hasil akhir collage foto Anda dengan frame yang telah dipilih. Keren kan? 😎',
                position: 'top'
            }
        },
        {
            element: '#email-input',
            popover: {
                title: '📧 Email (Opsional)',
                description: 'Masukkan email Anda jika ingin menerima salinan digital foto. Jika tidak, bisa dilewati.',
                position: 'bottom'
            }
        },
        {
            element: '#print-btn',
            popover: {
                title: '🖨️ Print Foto',
                description: 'Klik tombol ini untuk mencetak foto Anda. Proses printing akan dimulai dan foto akan keluar sebentar lagi!',
                position: 'top'
            }
        },
        {
            element: '#download-btn',
            popover: {
                title: '💾 Download Digital',
                description: 'Atau download versi digital untuk disimpan di ponsel/komputer Anda.',
                position: 'top'
            }
        }
    ]
};

// Export untuk digunakan di halaman lain
window.TutorialHelper = TutorialHelper;
window.TutorialConfigs = TutorialConfigs;

})(window);
