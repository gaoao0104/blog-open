<?php
$title = '卡片管理';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card" style="margin-top: 24px;">
    <div class="card-management-layout">
        <!-- Sidebar / Dropdown for Post Selection -->
        <div class="card-manager-sidebar">
            <h3 class="card-title mb-4">选择文章</h3>
            <input type="text" id="post-search" placeholder="搜索文章标题..." class="form-control mb-4">
            <div id="post-list" class="post-list scrollable-list">
                <!-- Posts loaded via JS -->
                <div class="loading">加载中...</div>
            </div>
        </div>

        <!-- Main Content Area: Card Manager (hidden until post selected) -->
        <div id="card-manager-container" class="card-manager-container" style="display: none; flex: 1;">
            <section class="card-manager" style="margin-top: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 class="card-title">关联卡片</h3>
                    <button type="button" id="refresh-list-btn" class="btn btn-secondary btn-sm">⟳ 刷新</button>
                </div>
                
                <div id="card-list" class="card-list">
                    <!-- Cards loaded via JS -->
                </div>

                <div style="height: 1px; background: var(--admin-border); margin: 32px 0;"></div>

                <form id="card-form" class="card-manager-form">
                    <h4 style="font-size: 1rem; margin-bottom: 16px;">添加/编辑卡片</h4>
                    <input type="hidden" name="id" id="card-id">
                    <input type="hidden" name="post_id" id="current-post-id">
                    <div class="form-group">
                        <label class="form-label">标题</label>
                        <input type="text" name="title" id="card-title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">描述</label>
                        <input type="text" name="description" id="card-desc" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">链接</label>
                        <input type="url" name="link_url" id="card-link" class="form-control">
                    </div>
                    <div class="flex gap-4 mb-4">
                        <div style="flex: 1;">
                            <label class="form-label">排序 (大号在前)</label>
                            <input type="number" name="priority" id="card-priority" value="0" class="form-control">
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label">图片</label>
                            <input type="file" name="card_image" id="card-image" accept="image/*" class="form-control">
                        </div>
                    </div>
                    <div style="margin-top: 12px; display: flex; gap: 8px;">
                        <button type="submit" id="save-card-btn" class="btn btn-primary">保存卡片</button>
                        <button type="button" class="btn btn-secondary" id="reset-card-btn">重置</button>
                    </div>
                </form>
            </section>
        </div>
        
        <div id="empty-state" class="empty-state" style="flex: 1;">
            <p>请从左侧选择一篇文章以管理其卡片。</p>
        </div>
    </div>
</div>

<style>
.card-management-layout {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}
.card-manager-sidebar {
    width: 300px;
    flex-shrink: 0;
    border-right: 1px solid var(--admin-border);
    padding-right: 24px;
}
.scrollable-list {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    padding: 8px;
    background: var(--admin-bg);
}
.post-list-item {
    padding: 10px;
    margin-bottom: 4px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.post-list-item:hover {
    background: var(--admin-surface);
}
.post-list-item.active {
    background: var(--admin-primary);
    color: white !important;
}
.post-list-item.active div {
    color: white !important;
}
.empty-state {
    padding: 60px;
    text-align: center;
    color: var(--admin-text-secondary);
    background: var(--admin-bg);
    border-radius: 8px;
}
/* Card List Items */
.admin-card-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: var(--admin-bg);
    border-radius: 8px;
    margin-bottom: 12px;
    align-items: center;
}
.admin-card-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}
.admin-card-details {
    flex: 1;
}
.admin-card-title {
    font-weight: 600;
    margin-bottom: 4px;
}
.admin-card-meta {
    font-size: 0.85rem;
    color: var(--admin-text-secondary);
}
.admin-card-actions {
    display: flex;
    gap: 8px;
}
</style>

<script>
(function() {
    const csrfToken = <?= json_encode($csrf_token) ?>;
    // Serialize PHP posts to JS
    const allPosts = <?= json_encode($posts) ?>;
    
    const searchInput = document.getElementById('post-search');
    const postListEl = document.getElementById('post-list');
    const container = document.getElementById('card-manager-container');
    const emptyState = document.getElementById('empty-state');
    const listEl = document.getElementById('card-list');
    const formEl = document.getElementById('card-form');
    
    let currentPostId = null;

    // Initial Render
    renderPostList(allPosts);

    // Search Logic
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = allPosts.filter(p => p.title.toLowerCase().includes(query));
        renderPostList(filtered);
    });

    function renderPostList(posts) {
        if (posts.length === 0) {
            postListEl.innerHTML = '<div style="padding:10px; text-align:center; color:gray;">无匹配文章</div>';
            return;
        }
        postListEl.innerHTML = posts.map(p => `
            <div class="post-list-item ${currentPostId == p.id ? 'active' : ''}" onclick="window.selectPost(${p.id})">
                <div style="font-weight:bold;">${escapeHtml(p.title)}</div>
                <div style="font-size:0.8em; color:gray;">ID: ${p.id}</div>
            </div>
        `).join('');
    }

    // Load state from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const initialPostId = urlParams.get('post_id');
    if (initialPostId) {
        // Delay slightly to ensure list is rendered? No, sync render.
        window.selectPost(initialPostId);
    }

    window.selectPost = function(pid) {
        currentPostId = pid;
        document.getElementById('current-post-id').value = pid;
        
        // Update Active State
        const items = document.querySelectorAll('.post-list-item');
        items.forEach(el => el.classList.remove('active'));
        // Find specific item (might filtered out, but if visible)
        // Re-render to ensure active state is correct if we want strictly
        renderPostList(allPosts.filter(p => p.title.toLowerCase().includes(searchInput.value.toLowerCase()))); // Re-render to show active class properly? Or just loop simple.
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('post_id', pid);
        window.history.pushState({}, '', url);

        container.style.display = 'block';
        emptyState.style.display = 'none';
        loadCards();
    };

    function loadCards() {
        if (!currentPostId) return;
        
        listEl.innerHTML = '<p>加载中...</p>';
        fetch(`/admin/post-cards?post_id=${currentPostId}`)
            .then(res => res.json())
            .then(data => {
                if(data.cards) renderCards(data.cards);
                else listEl.innerHTML = '<p>暂无卡片</p>';
            })
            .catch(() => {
                listEl.innerHTML = '<p style="color:red">加载失败</p>';
            });
    }

    function renderCards(cards) {
        if (cards.length === 0) {
            listEl.innerHTML = '<p>暂无卡片</p>';
            return;
        }
        
        listEl.innerHTML = cards.map(card => `
            <div class="admin-card-item">
                <img src="${card.image_url || '/assets/placeholder.png'}" alt="">
                <div class="admin-card-details">
                    <div class="admin-card-title">${escapeHtml(card.title)}</div>
                    <div class="admin-card-meta">Link: ${escapeHtml(card.link_url)} | Pri: ${card.priority}</div>
                </div>
                <div class="admin-card-actions">
                    <button type="button" class="button small" onclick='editCard(${JSON.stringify(card)})'>编辑</button>
                    <button type="button" class="button small danger" onclick="deleteCard(${card.id})">删除</button>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    window.editCard = function(card) {
        document.getElementById('card-id').value = card.id;
        document.getElementById('card-title').value = card.title;
        document.getElementById('card-desc').value = card.description;
        document.getElementById('card-link').value = card.link_url;
        document.getElementById('card-priority').value = card.priority;
        document.getElementById('save-card-btn').textContent = '更新卡片';
        document.getElementById('card-form').scrollIntoView({ behavior: 'smooth' });
    };

    window.deleteCard = function(id) {
        if(!confirm('确定删除?')) return;
        const fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('id', id);
        
        fetch('/admin/post-cards/delete', { method: 'POST', body: fd })
            .then(() => loadCards());
    };
    
    document.getElementById('refresh-list-btn').addEventListener('click', loadCards);

    document.getElementById('reset-card-btn').addEventListener('click', () => {
        formEl.reset();
        document.getElementById('card-id').value = '';
        document.getElementById('current-post-id').value = currentPostId; // Preserve Post ID
        document.getElementById('save-card-btn').textContent = '保存卡片';
    });

    formEl.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(formEl);
        fd.append('csrf_token', csrfToken);
        fd.append('is_active', '1');

        if (!currentPostId) {
            alert('未选择文章');
            return;
        }

        const id = fd.get('id');
        const url = id ? '/admin/post-cards/update' : '/admin/post-cards/create';
        
        try {
            const res = await fetch(url, { method: 'POST', body: fd });
            const json = await res.json();
            if(json.success || json.id || json.status === 'ok') { // Check logic matches backend response
                formEl.reset();
                document.getElementById('card-id').value = '';
                document.getElementById('current-post-id').value = currentPostId;
                document.getElementById('save-card-btn').textContent = '保存卡片';
                loadCards();
            } else {
                alert(json.error || '操作失败');
            }
        } catch(err) {
            console.error(err);
            alert('网络错误');
        }
    });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
