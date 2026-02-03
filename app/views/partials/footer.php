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

<<<<<<< HEAD
=======
<!-- Mobile Bottom Toolbar -->
<div class="mobile-toolbar">
    <a href="/" class="mobile-toolbar-item <?= $current === '/' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        <span>首页</span>
    </a>
    <button type="button" class="mobile-toolbar-item" id="mobile-search-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <span>搜索</span>
    </button>
    <button type="button" class="mobile-toolbar-item" id="mobile-categories-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>分类</span>
    </button>
    <button type="button" class="mobile-toolbar-item" id="mobile-top-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
        <span>顶部</span>
    </button>
</div>

<!-- Categories Bottom Sheet -->
<div class="category-sheet-overlay" id="category-sheet-overlay"></div>
<div class="category-sheet" id="category-sheet">
    <div class="category-sheet-header">
        <h3>全部分类</h3>
        <button type="button" class="close-sheet-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <div class="category-sheet-content" id="category-sheet-list">
        <!-- Categories will be loaded here via JS -->
        <div class="loading-spinner">加载中...</div>
    </div>
</div>

<!-- Search Bottom Sheet -->
<div class="category-sheet-overlay" id="search-sheet-overlay"></div>
<div class="category-sheet" id="search-sheet">
    <div class="category-sheet-header">
        <h3>搜索文章</h3>
        <button type="button" class="close-sheet-btn" id="close-search-sheet">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <div class="category-sheet-content" style="display: block; padding-top: 10px;">
        <form action="/search" method="get" class="mobile-search-form">
            <input type="search" name="q" id="mobile-search-input" placeholder="输入关键词..." required autocomplete="off">
            <button type="submit">搜索</button>
        </form>
    </div>
</div>

>>>>>>> a3d11b8 (sync: update open-source release)
<!-- Highlight.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<!-- Custom Scripts -->
<script src="/assets/main.js"></script>
</body>
</html>
