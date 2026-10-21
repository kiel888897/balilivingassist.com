<script>
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-password-toggle]');
        if (!toggle) return;

        var input = document.getElementById(toggle.getAttribute('data-password-toggle'));
        if (!input) return;

        var reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        toggle.setAttribute('aria-label', reveal ? 'Sembunyikan password' : 'Tampilkan password');
        toggle.innerHTML = reveal
            ? '<i class="fa-solid fa-eye-slash" aria-hidden="true"></i>'
            : '<i class="fa-solid fa-eye" aria-hidden="true"></i>';
    });
</script>
