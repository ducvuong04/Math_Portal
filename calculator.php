<?php
include 'includes/header.php';
?>

<div class="calculator-wrapper">
    <div style="position: absolute; top: 100px; left: 50%; transform: translateX(-50%); z-index: 5;">
        <button id="enable-floating" class="btn btn-primary"
            style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem 2rem; box-shadow: 0 10px 20px rgba(0,0,0,0.3);">
            <span class="material-icons-round">open_in_new</span> Bật máy tính nổi sử dụng cho các trang khác
        </button>
    </div>

    <div id="main-calc-container" style="margin-top: 60px;">
        <!-- Injected by JS -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('main-calc-container');
        container.innerHTML = Calculator.getHTML(false);
        Calculator.init('calc-display', 'calc-history');
        Calculator.attachEvents('#main-calc-container');

        document.getElementById('enable-floating').onclick = function () {
            localStorage.setItem('showFloatingCalc', 'true');
            // Effect: Grow out the floating calc and jump to home
            window.location.href = 'index.php';
        };
    });
</script>

<?php include 'includes/footer.php'; ?>