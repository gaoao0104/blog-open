</main>
<footer class="site-footer">
    <div class="container">
        <p><?= !empty($settings['footer_copyright']) ? e($settings['footer_copyright']) : ('© ' . date('Y') . ' Gaoao Blog') ?></p>
        <p style="margin-top: 8px;">
            <a href="/rss.xml" target="_blank" title="RSS Subscription" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; color: var(--ink-secondary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                RSS
            </a>
        </p>
    </div>
</footer>

<!-- Highlight.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<!-- Custom Scripts -->
<script src="/assets/main.js"></script>
</body>
</html>
