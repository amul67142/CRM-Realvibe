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
    
    <!-- Auto-hide flash messages -->
    <script>
        setTimeout(function() {
            $('.flash-message').fadeOut('slow');
        }, 3000);
    </script>
</body>
</html>
