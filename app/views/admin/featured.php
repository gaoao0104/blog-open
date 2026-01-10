<?php
$title = '推荐管理';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">推荐管理</h2>
        <button id="add-recommendation-btn" class="btn btn-primary btn-sm">+ 新增推荐</button>
    </div>
    
    <div class="mb-4 text-sm" style="color: var(--admin-text-secondary);">
        拖拽调整顺序，首位展示在最前。
    </div>
    
    <div id="featured-list" class="sortable-list" style="min-height: 200px; border: 1px dashed var(--admin-border); border-radius: 8px; padding: 16px;">
        <!-- Loading -->
        <div class="loading">加载中...</div>
    </div>
    <button id="save-order-btn" class="btn btn-primary" style="margin-top: 15px; width: 100%; display: none;">保存排序</button>
</div>

<!-- Edit/Add Modal -->
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>编辑推荐信息</h3>
            <span class="close" id="modal-close">&times;</span>
        </div>
        <form id="edit-form">
            <!-- Article Select (Only for Add) -->
            <div class="form-group" id="article-select-group" style="display: none;">
                <label class="form-label">选择文章 (可选，不选则为自定义推荐)</label>
                <input type="text" id="article-search" placeholder="搜索文章标题..." class="form-control" autocomplete="off">
                <div id="article-dropdown" class="article-dropdown" style="display: none;"></div>
                <input type="hidden" id="selected-post-id">
                <div id="selected-post-display" style="display: none; padding: 10px; background: var(--admin-bg); border-radius: 6px; margin-top: 5px; align-items: center; justify-content: space-between;">
                    <span id="selected-post-title" style="font-weight: 500;"></span>
                    <button type="button" id="reselect-btn" class="btn btn-secondary btn-sm">重选</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">推荐标题 (自定义推荐必填)</label>
                <input type="text" id="edit-title" class="form-control" placeholder="输入显示的标题">
            </div>

            <div class="form-group">
                <label class="form-label">跳转链接 (留空则跳转文章详情)</label>
                <input type="text" id="edit-link-url" placeholder="https://..." class="form-control">
                <label class="checkbox-label flex items-center gap-2 mt-2" style="cursor: pointer; color: var(--admin-text-secondary);">
                    <input type="checkbox" id="clear-link-chk"> <span>清空链接 (强制跳转文章)</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">推荐图片 (卡片展示图)</label>
                <div id="image-preview" style="margin-bottom: 10px; background: var(--admin-bg); padding: 10px; border-radius: 8px; text-align: center; min-height: 100px; display: flex; align-items: center; justify-content: center; border: 1px dashed var(--admin-border);"></div>
                <input type="file" id="edit-image-file" accept="image/*" class="form-control" style="padding: 10px;">
                <label class="checkbox-label flex items-center gap-2 mt-2" style="cursor: pointer; color: var(--admin-text-secondary);">
                    <input type="checkbox" id="clear-image-chk"> <span>恢复使用文章封面</span>
                </label>
            </div>
            
            <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">保存</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1000; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.5); 
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }
    .modal-content {
        background-color: var(--admin-surface);
        margin: auto;
        padding: 24px;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        color: var(--admin-text);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--admin-border);
        padding-bottom: 16px;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--admin-text);
    }
    .close {
        color: var(--admin-text-secondary);
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        transition: color 0.2s;
    }
    .close:hover {
        color: var(--admin-text);
    }
    
    .article-dropdown {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        border-radius: 8px;
        position: absolute;
        width: calc(100% - 48px); /* Adjust based on padding */
        max-width: 450px;
        z-index: 10;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .article-option {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid var(--admin-border);
        color: var(--admin-text);
    }
    .article-option:hover {
        background: var(--admin-bg);
    }
    .post-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 8px;
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 8px;
        cursor: grab;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .post-item:active {
        cursor: grabbing;
    }
    .post-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--admin-primary);
    }
    .post-info {
        flex: 1;
    }
    .post-title {
        font-weight: 600;
        display: block;
        color: var(--admin-text);
        margin-bottom: 4px;
    }
    .post-date {
        font-size: 0.85rem;
        color: var(--admin-text-secondary);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const featuredListEl = document.getElementById('featured-list');
    const saveOrderBtn = document.getElementById('save-order-btn');
    const addRefBtn = document.getElementById('add-recommendation-btn');
    const csrfToken = '<?= $csrf_token ?>';

    let featuredPosts = [];
    let availablePosts = [];

    // Modals
    const modal = document.getElementById('edit-modal');
    const modalClose = document.getElementById('modal-close');
    const modalForm = document.getElementById('edit-form');
    const modalTitle = document.querySelector('.modal-header h3');
    const modalSubmitBtn = modalForm.querySelector('button[type="submit"]');
    
    // Add/Select Elements
    const articleSelectGroup = document.getElementById('article-select-group');
    const articleSearch = document.getElementById('article-search');
    const articleDropdown = document.getElementById('article-dropdown');
    const selectedPostIdInput = document.getElementById('selected-post-id');
    const selectedPostDisplay = document.getElementById('selected-post-display');
    const selectedPostTitle = document.getElementById('selected-post-title');
    const reselectBtn = document.getElementById('reselect-btn');

    let currentEditingCardId = 0; // Card ID (featured_cards.id)
    let isAddingNew = false;

    // Fetch Data
    function fetchData() {
        return fetch('/admin/featured-posts')
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON Parse Error:', text);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            featuredPosts = data.featured || [];
            availablePosts = data.available || [];
            render();
            initSortable();
        })
        .catch(err => {
            console.error(err);
            document.querySelector('.loading').innerText = '加载失败: ' + err.message;
        });
    }

    fetchData();

    function render() {
        // Render Featured
        featuredListEl.innerHTML = '';
        featuredPosts.forEach(card => {
            const el = createCardElement(card);
            featuredListEl.appendChild(el);
        });
        
        saveOrderBtn.style.display = featuredPosts.length > 0 ? 'block' : 'none';
        
        if (featuredPosts.length === 0) {
            featuredListEl.innerHTML = '<div style="text-align:center; color:var(--admin-text-secondary); padding:20px;">暂无推荐内容</div>';
        }
    }

    function createCardElement(card) {
        const div = document.createElement('div');
        div.className = 'post-item';
        div.dataset.id = card.id; // card_id
        
        let metaInfo = '';
        if (card.post_id) {
             metaInfo = `<span class="post-date">关联文章: ${escapeHtml(card.title)}</span>`;
        } else {
             metaInfo = `<span class="post-date" style="color:var(--admin-primary);">自定义推广 (无关联文章)</span>`; 
        }

        div.innerHTML = `
            <div class="post-info">
                <span class="post-title">${escapeHtml(card.title || '无标题')}</span>
                ${metaInfo}
            </div>
            <div class="flex gap-2">
                <button class="btn btn-secondary btn-sm action-edit-btn">编辑</button>
                <button class="btn btn-danger btn-sm action-del-btn">删除</button>
            </div>
        `;
        
        div.querySelector('.action-edit-btn').addEventListener('click', () => openEditModal(card, false));
        div.querySelector('.action-del-btn').addEventListener('click', () => deleteCard(card.id));
        
        return div;
    }

    function deleteCard(id) {
        if (!confirm('确定要删除这条推荐吗？')) return;
        
        fetch('/admin/featured-posts/remove', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                fetchData();
            } else {
                alert('删除失败: ' + (data.error || '未知错误'));
            }
        })
        .catch(console.error);
    }
    
    // Add New Button Click
    addRefBtn.addEventListener('click', () => {
        openEditModal(null, true);
    });
    
    function openEditModal(card, isNew) {
        isAddingNew = isNew;
        
        modalTitle.innerText = isNew ? '新增推荐' : '编辑推荐信息';
        modalSubmitBtn.innerText = isNew ? '确定推荐' : '保存';
        
        // Reset Inputs
        document.getElementById('edit-title').value = '';
        document.getElementById('edit-link-url').value = '';
        document.getElementById('edit-image-file').value = '';
        document.getElementById('clear-link-chk').checked = false;
        document.getElementById('clear-image-chk').checked = false;
        document.getElementById('image-preview').innerHTML = '<p style="color:var(--admin-text-secondary); font-size: 0.9rem;">暂无图片</p>';
        
        if (isNew) {
            currentEditingCardId = 0;
            articleSelectGroup.style.display = 'block';
            resetArticleSelection();
        } else {
            currentEditingCardId = card.id;
            articleSelectGroup.style.display = 'none';
             // Fill data
            document.getElementById('edit-title').value = card.title || '';
            document.getElementById('edit-link-url').value = card.link || '';
            if (card.image) {
                document.getElementById('image-preview').innerHTML = `<img src="${escapeHtml(card.image)}" style="max-width: 100%; max-height: 200px; border-radius: 4px;">`;
            }
        }
        
        modal.style.display = 'flex';
    }
    
    // Article Logic
    function resetArticleSelection() {
        articleSearch.value = '';
        articleSearch.placeholder = '搜索文章标题...';
        articleSearch.style.display = 'block';
        selectedPostIdInput.value = '';
        selectedPostDisplay.style.display = 'none';
        articleDropdown.style.display = 'none';
    }
    
    // ... article search input logic remains same ...

    articleSearch.addEventListener('input', () => {
        const query = articleSearch.value.toLowerCase().trim();
        if (query.length < 1) {
            articleDropdown.style.display = 'none';
            return;
        }
        
        const matches = availablePosts.filter(p => p.title.toLowerCase().includes(query));
        articleDropdown.innerHTML = '';
        if (matches.length > 0) {
            articleDropdown.style.display = 'block';
            matches.forEach(p => {
                const div = document.createElement('div');
                div.className = 'article-option';
                div.innerText = p.title;
                div.addEventListener('click', () => selectArticle(p));
                articleDropdown.appendChild(div);
            });
        } else {
             articleDropdown.style.display = 'none';
        }
    });

    function selectArticle(post) {
        selectedPostIdInput.value = post.id;
        selectedPostTitle.innerText = post.title;
        
        // Auto-fill title if empty
        const titleInput = document.getElementById('edit-title');
        if (!titleInput.value) {
            titleInput.value = post.title;
        }
        
        articleSearch.style.display = 'none';
        articleDropdown.style.display = 'none';
        selectedPostDisplay.style.display = 'flex'; 
        
        // Pre-fill image if available
         if (post.featured_image) {
            document.getElementById('image-preview').innerHTML = `<img src="${escapeHtml(post.featured_image)}" style="max-width: 100%; max-height: 200px; border-radius: 4px;">`;
        }
    }

    reselectBtn.addEventListener('click', resetArticleSelection);
    
    // Close Modal Logic
    modalClose.addEventListener('click', () => {
        modal.style.display = 'none';
        currentEditingCardId = 0;
    });
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Submit Logic
    modalForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('title', document.getElementById('edit-title').value);
        formData.append('link_url', document.getElementById('edit-link-url').value);
        
        const fileInput = document.getElementById('edit-image-file');
        if (fileInput.files.length > 0) {
            formData.append('featured_card_image', fileInput.files[0]);
        }
        
        if (document.getElementById('clear-link-chk').checked) formData.append('clear_link', 1);
        if (document.getElementById('clear-image-chk').checked) formData.append('clear_image', 1);

        const submitBtn = modalForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.disabled = true;
        submitBtn.innerText = '处理中...';
        
        let url = '';
        if (isAddingNew) {
            // ADD
            url = '/admin/featured-posts/add';
            const postId = selectedPostIdInput.value; 
            if (postId) {
                formData.append('post_id', postId);
            } else {
                 if (!document.getElementById('edit-title').value.trim()) {
                     alert('请输入标题');
                     submitBtn.disabled = false;
                     submitBtn.innerText = originalText;
                     return;
                 }
            }
            
        } else {
            // UPDATE
            url = '/admin/featured-posts/update';
            formData.append('card_id', currentEditingCardId);
        }

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                modal.style.display = 'none';
                return fetchData();
            } else {
                throw new Error(data.error || 'Failed');
            }
        })
        .catch(err => {
            console.error(err);
            alert('操作失败: ' + (err.message || '未知错误'));
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        });
    });

    function initSortable() {
        new Sortable(featuredListEl, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                saveOrderBtn.innerText = '保存排序'; 
                saveOrderBtn.classList.remove('btn-secondary'); // if ghost adds classes
                // Reset if needed
            }
        });
    }

    saveOrderBtn.addEventListener('click', () => {
        const itemIds = Array.from(featuredListEl.children).map(el => parseInt(el.dataset.id));
        fetch('/admin/featured-posts/reorder', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order: itemIds, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                saveOrderBtn.innerText = '已保存';
                setTimeout(() => {
                    saveOrderBtn.innerText = '保存排序';
                }, 2000);
            } else {
                alert('保存失败');
            }
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
