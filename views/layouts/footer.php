            </div>
        </div>
        
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>
    </div>
    
    <!-- Main JS -->
    <script>
        // Define base URL for JavaScript
        const baseUrl = '<?= BASE_URL ?>';
    </script>
    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/notifications.js') ?>"></script>
    
    
    <!-- Footer -->
    <footer class="footer footer-center p-4 bg-base-300 text-base-content mt-8">
        <div>
            <p>© <?= date('Y') ?> Realvibe CRM. All rights reserved.</p>
            <div class="flex gap-4 justify-center mt-2">
                <a href="/public/privacy-policy.php" class="link link-hover" target="_blank">Privacy Policy</a>
                <span>•</span>
                <a href="/public/terms-and-conditions.php" class="link link-hover" target="_blank">Terms & Conditions</a>
            </div>
        </div>
    </footer>
    
    <!-- Auto-hide flash messages -->
    <script>
        setTimeout(function() {
            $('.flash-message').fadeOut('slow');
        }, 3000);
    </script>
</body>
</html>
