<?php
/**
 * Footer Template
 * 
 * Common footer for all pages in the Geez Restaurant application
 */
?>
            </main>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between">
                <span class="text-muted">&copy; <?php echo date('Y'); ?> Geez Restaurant</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo ASSET_URL; ?>/js/custom.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($page_js)): ?>
    <script src="<?php echo ASSET_URL; ?>/js/<?php echo $page_js; ?>"></script>
    <?php endif; ?>
</body>
</html>
