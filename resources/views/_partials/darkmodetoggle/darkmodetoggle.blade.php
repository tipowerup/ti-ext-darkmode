<button
    type="button"
    class="btn btn-link text-decoration-none"
    onclick="window.TiDarkmode && window.TiDarkmode.toggle()"
    title="{{ lang('tipowerup.darkmode::default.text_dark_mode') }}"
>
    <i id="ti-dm-toggle-icon" class="fa fa-moon"></i>
</button>
<script>
document.addEventListener('ti:darkmode-toggled', function(e) {
    var icon = document.getElementById('ti-dm-toggle-icon');
    if (icon) {
        icon.className = e.detail.enabled ? 'fa fa-sun' : 'fa fa-moon';
    }
});
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var icon = document.getElementById('ti-dm-toggle-icon');
        if (icon && window.TiDarkmode && window.TiDarkmode.isActive()) {
            icon.className = 'fa fa-sun';
        }
    }, 100);
});
</script>
