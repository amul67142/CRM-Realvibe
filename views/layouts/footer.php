    </main> <!-- End Main Content -->

    <!-- Main JS -->
    <script>
        // Define base URL for JavaScript
        const baseUrl = '<?= BASE_URL ?>';
    </script>
    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/notifications.js') ?>"></script>
    
    <!-- Footer -->
    <footer class="footer footer-center p-6 bg-white text-base-content border-t border-gray-200 mt-auto">
        <div>
            <div class="flex items-center gap-2 mb-2 opacity-50">
                <span class="material-symbols-outlined text-2xl">rocket_launch</span>
                <span class="font-bold text-lg"><?= APP_NAME ?></span>
            </div>
            <p class="text-gray-500">© <?= date('Y') ?> Realvibe CRM. All rights reserved.</p>
            <div class="flex gap-4 justify-center mt-2">
                <a href="#" class="link link-hover text-sm text-gray-400">Privacy Policy</a>
                <span class="text-gray-300">•</span>
                <a href="#" class="link link-hover text-sm text-gray-400">Terms & Conditions</a>
            </div>
        </div>
    </footer>
    
    <!-- Auto-hide flash messages -->
    <script>
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 3000);
    </script>
</body>
</html>
