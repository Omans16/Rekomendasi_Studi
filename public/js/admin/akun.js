(function () {
    'use strict';

    const page = document.querySelector('.js-account-page');
    const accountModal = document.getElementById('accountModal');

    const roleInput = document.getElementById('roleInput');
    const roleSiswa = document.getElementById('roleSiswa');
    const roleGuru = document.getElementById('roleGuru');

    const usernameLabel = document.getElementById('usernameLabel');
    const usernameHint = document.getElementById('usernameHint');
    const kelasGroup = document.getElementById('kelasGroup');

    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const passwordLabel = document.getElementById('passwordLabel');
    const passwordHint = document.getElementById('passwordHint');

    if (!page || !accountModal || !roleInput) {
        return;
    }

    function openAccountModal() {
        accountModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeAccountModal() {
        accountModal.hidden = true;
        document.body.style.overflow = '';
    }

    function setAccountRole(role) {
        roleInput.value = role;

        if (roleSiswa) {
            roleSiswa.classList.toggle('active', role === 'siswa');
        }

        if (roleGuru) {
            roleGuru.classList.toggle('active', role === 'guru_bk');
        }

        if (role === 'guru_bk') {
            setGuruBkForm();
            return;
        }

        setSiswaForm();
    }

    function setGuruBkForm() {
        usernameLabel.textContent = 'NIP / Username Guru BK';
        usernameHint.textContent = 'Gunakan NIP atau username khusus untuk login Guru BK.';

        kelasGroup.style.display = 'none';

        passwordLabel.textContent = 'Password';
        password.placeholder = 'Masukkan password Guru BK';
        password.required = true;

        passwordConfirmation.required = true;
        passwordConfirmation.placeholder = 'Ulangi password Guru BK';

        passwordHint.textContent = 'Password wajib diisi untuk akun Guru BK.';
    }

    function setSiswaForm() {
        usernameLabel.textContent = 'NISN';
        usernameHint.textContent = 'Untuk siswa, gunakan NISN. Password awal otomatis memakai NISN jika password dikosongkan.';

        kelasGroup.style.display = '';

        passwordLabel.textContent = 'Password';
        password.placeholder = 'Kosongkan untuk siswa agar otomatis memakai NISN';
        password.required = false;

        passwordConfirmation.required = false;
        passwordConfirmation.placeholder = 'Ulangi password jika diisi';

        passwordHint.textContent = 'Untuk siswa, jika password dikosongkan maka password awal sama dengan NISN.';
    }

    document.querySelectorAll('[data-account-modal-open]').forEach(function (button) {
        button.addEventListener('click', openAccountModal);
    });

    document.querySelectorAll('[data-account-modal-close]').forEach(function (button) {
        button.addEventListener('click', closeAccountModal);
    });

    document.querySelectorAll('[data-role-option]').forEach(function (button) {
        button.addEventListener('click', function () {
            setAccountRole(button.dataset.roleOption);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !accountModal.hidden) {
            closeAccountModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const initialRole = page.dataset.initialRole || 'siswa';
        const hasErrors = page.dataset.hasErrors === '1';

        setAccountRole(initialRole);

        if (hasErrors) {
            openAccountModal();
        }
    });
})();