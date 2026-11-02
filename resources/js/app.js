document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-show-all').forEach(function (button) {
        if (button.disabled) return;
        var expanded = false;
        // Optional custom labels; default matches the regional pages' toggle wording.
        var allLabel = button.getAttribute('data-all-label') || 'Lihat Semua Skema';
        var lessLabel = button.getAttribute('data-less-label') || 'Tampilkan Lebih Sedikit';
        button.addEventListener('click', function () {
            // Each [data-show-group] container toggles independently;
            // falls back to section scope for the regional pages' original behavior.
            var scope = button.closest('[data-show-group]') || button.closest('section');
            var extras = scope ? scope.querySelectorAll('.scheme-extra') : [];
            expanded = !expanded;
            extras.forEach(function (el) {
                if (expanded) {
                    el.removeAttribute('hidden');
                } else {
                    el.setAttribute('hidden', '');
                }
            });
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            button.textContent = expanded ? lessLabel : allLabel;
        });
    });
});