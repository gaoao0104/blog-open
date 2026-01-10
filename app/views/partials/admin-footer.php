        </div> <!-- End admin-content -->
    </main> <!-- End admin-main -->
</div> <!-- End admin-layout -->

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('theme-toggle');
        if (!toggleBtn) return;
        
        const sunIcon = toggleBtn.querySelector('.sun-icon');
        const moonIcon = toggleBtn.querySelector('.moon-icon');
        
        function updateIcons(isDark) {
            if (isDark) {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'inline';
            } else {
                sunIcon.style.display = 'inline';
                moonIcon.style.display = 'none';
            }
        }
        
        const currentTheme = document.documentElement.getAttribute('data-theme');
        updateIcons(currentTheme === 'dark');
        
        toggleBtn.addEventListener('click', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                updateIcons(false);
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                updateIcons(true);
            }
        });
        
        // Confirmation dialogs
        document.querySelectorAll('a[onclick]').forEach(link => {
            // Keep existing simple confirm for now, maybe upgrade later
        });
    });
</script>
</body>
</html>
