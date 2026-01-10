<?php
$title = $post ? '编辑文章' : '新建文章';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= e($title) ?></h2>
    </div>

    <form class="post-form" method="post" enctype="multipart/form-data" action="<?= $post ? '/admin/posts/update?id=' . e((string)$post['id']) : '/admin/posts/create' ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        
        <div class="form-group">
            <label class="form-label">标题</label>
            <input type="text" name="title" value="<?= e($post['title'] ?? '') ?>" class="form-control" style="font-size: 1.1rem; padding: 12px;" required>
        </div>

        <div class="flex gap-4 mb-4">
            <div style="flex: 1;">
                <label class="form-label">Slug (可选)</label>
                <input type="text" name="slug" value="<?= e($post['slug'] ?? '') ?>" class="form-control">
            </div>
            <div style="flex: 1;">
                <label class="form-label">分类</label>
                <select name="category_id" class="form-control">
                    <option value="">未分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e((string)$category['id']) ?>" <?= isset($post['category_id']) && (int)$post['category_id'] === (int)$category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex gap-4 mb-4">
            <div style="flex: 2;">
                <label class="form-label">标签 (使用逗号分隔)</label>
                <input type="text" name="tags" value="<?= e($tags) ?>" class="form-control">
            </div>
            <div style="flex: 1;">
                <label class="form-label">状态</label>
                <select name="status" class="form-control">
                    <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>发布</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4 mb-6 items-center">
            <label class="flex items-center gap-2" style="cursor: pointer;">
                <input type="checkbox" name="is_featured" <?= !empty($post['is_featured']) ? 'checked' : '' ?>>
                <span class="form-label" style="margin:0;">推荐置顶（最多三篇）</span>
            </label>
            
            <div style="flex: 1; margin-left: 24px;">
                <label class="form-label" style="display: inline-block; margin-right: 8px;">封面图</label>
                <input type="file" name="featured_image" accept="image/*" class="form-control" style="display: inline-block; width: auto;">
                <?php if (!empty($post['featured_image'])): ?>
                    <small style="margin-left: 8px; color: var(--admin-text-secondary);">当前: <?= e($post['featured_image']) ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">内容 (Markdown)</label>
            <div class="md-editor" style="border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden;">
                <textarea id="md-input" name="content" rows="20" required class="form-control" style="border: none; border-radius: 0; resize: vertical; min-height: 400px; font-family: monospace;"><?= e($post['content_md'] ?? '') ?></textarea>
                <div id="md-preview" class="md-preview" style="padding: 16px; background: var(--admin-bg); border-top: 1px solid var(--admin-border); max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">摘要</label>
            <textarea id="excerpt-input" name="excerpt" rows="3" class="form-control"><?= e($post['excerpt'] ?? '') ?></textarea>
            <div style="margin-top: 8px;">
                 <button type="button" class="btn btn-secondary btn-sm" id="generate-summary">✨ 自动生成摘要</button>
            </div>
        </div>

        <div style="position: sticky; bottom: 0; background: var(--admin-surface); padding: 16px 0; border-top: 1px solid var(--admin-border); z-index: 10;">
             <button type="submit" class="btn btn-primary">保存文章</button>
        </div>
    </form>
</div>

<script>
    const mdInput = document.getElementById('md-input');
    const mdPreview = document.getElementById('md-preview');
    const csrfToken = '<?= e($csrf_token) ?>';

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function renderMarkdown(text) {
        let html = escapeHtml(text);

        // Pre-process code blocks
        const codeBlocks = [];
        html = html.replace(/```([\s\S]*?)```/g, (_, code) => {
            codeBlocks.push(`<div class="code-wrapper"><pre><code>${code.trim()}</code></pre></div>`);
            return `__CODE_BLOCK_${codeBlocks.length - 1}__`;
        });

        const lines = html.split(/\n/);
        let out = '';
        
        // State
        let inList = false;
        let listType = null;
        let inQuote = false;
        let quoteBuffer = [];

        const formatInline = (str) => {
            str = str.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            str = str.replace(/\*(.+?)\*/g, '<em>$1</em>');
            str = str.replace(/`([^`]+)`/g, '<code>$1</code>');
            str = str.replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, '<img src="$2" alt="$1" style="max-width:100%; border-radius:6px;">');
            str = str.replace(/\[(.+?)\]\((https?:\/\/[^\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
            return str;
        };

        const closeList = () => {
            if (inList) {
                out += listType === 'ul' ? '</ul>' : '</ol>';
                inList = false;
                listType = null;
            }
        };

        const closeQuote = () => {
            if (inQuote) {
                const quotedContent = quoteBuffer.map(formatInline).join('<br>');
                out += `<blockquote style="border-left: 3px solid var(--admin-primary); padding-left: 12px; margin: 8px 0; color: var(--admin-text-secondary);">${quotedContent}</blockquote>`;
                inQuote = false;
                quoteBuffer = [];
            }
        };

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            const trim = line.trim();

            if (trim === '') {
                closeList();
                closeQuote();
                continue;
            }

            // Headers
            if (line.match(/^#{1,6}\s/)) {
                closeList();
                closeQuote();
                const level = line.match(/^(#{1,6})/)[1].length;
                const content = line.replace(/^(#{1,6})\s+/, '');
                out += `<h${level} style="font-weight:600; margin-top: 1em; margin-bottom: 0.5em;">${formatInline(content)}</h${level}>`;
                continue;
            }

            // HR
            if (line.match(/^(-{3,}|\*{3,})$/)) {
                closeList();
                closeQuote();
                out += '<hr style="border: 0; border-top: 1px solid var(--admin-border); margin: 16px 0;">';
                continue;
            }

            // Blockquote
            if (line.match(/^\s*>/)) {
                closeList();
                if (!inQuote) {
                    inQuote = true;
                }
                quoteBuffer.push(line.replace(/^\s*>\s?/, ''));
                continue;
            } else {
                 if (inQuote) {
                     closeQuote();
                 }
            }

            // Unordered List
            const ulMatch = line.match(/^\s*[-*+]\s+(.*)$/);
            if (ulMatch) {
                if (inList && listType !== 'ul') closeList();
                if (!inList) {
                    inList = true;
                    listType = 'ul';
                    out += '<ul style="padding-left: 20px;">';
                }
                out += '<li>' + formatInline(ulMatch[1]) + '</li>';
                continue;
            }

            // Ordered List
            const olMatch = line.match(/^\s*\d+\.\s+(.*)$/);
            if (olMatch) {
                if (inList && listType !== 'ol') closeList();
                if (!inList) {
                    inList = true;
                    listType = 'ol';
                    out += '<ol style="padding-left: 20px;">';
                }
                out += '<li>' + formatInline(olMatch[1]) + '</li>';
                continue;
            }

            // Normal Text
            closeList(); 
            if (line.trim().match(/^__CODE_BLOCK_\d+__$/)) {
                out += line;
            } else {
                out += '<p style="margin-bottom: 0.8em;">' + formatInline(line) + '</p>';
            }
        }

        closeList();
        closeQuote();

        out = out.replace(/__CODE_BLOCK_(\d+)__/g, (_, index) => codeBlocks[index]);

        return out;
    }

    const excerptInput = document.getElementById('excerpt-input');
    const generateButton = document.getElementById('generate-summary');

    function updatePreview() {
        mdPreview.innerHTML = renderMarkdown(mdInput.value);
    }

    mdInput.addEventListener('input', updatePreview);
    updatePreview();

    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? textarea.value.length;
        textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
        updatePreview();
    }

    async function uploadEditorImage(file) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('image', file, file.name || 'image.png');

        const response = await fetch('/admin/uploads/editor', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        if (!response.ok || !data.url) {
            throw new Error(data.error || 'upload_failed');
        }
        return data.url;
    }

    mdInput.addEventListener('paste', async (event) => {
        const items = Array.from(event.clipboardData?.items || []);
        const imageItem = items.find((item) => item.type.startsWith('image/'));
        if (!imageItem) {
            return;
        }
        event.preventDefault();
        const file = imageItem.getAsFile();
        if (!file) {
            return;
        }
        try {
            const url = await uploadEditorImage(file);
            insertAtCursor(mdInput, `![image](${url})`);
        } catch (error) {
            alert('图片上传失败');
        }
    });

    mdInput.addEventListener('dragover', (event) => {
        event.preventDefault();
        mdInput.classList.add('dragging');
    });

    mdInput.addEventListener('dragleave', () => {
        mdInput.classList.remove('dragging');
    });

    mdInput.addEventListener('drop', async (event) => {
        event.preventDefault();
        mdInput.classList.remove('dragging');
        const file = event.dataTransfer?.files?.[0];
        if (!file || !file.type.startsWith('image/')) {
            return;
        }
        try {
            const url = await uploadEditorImage(file);
            insertAtCursor(mdInput, `![image](${url})`);
        } catch (error) {
            alert('图片上传失败');
        }
    });

    generateButton.addEventListener('click', async () => {
        generateButton.disabled = true;
        generateButton.textContent = '生成中...';
        try {
            const response = await fetch('/admin/posts/summary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    content: mdInput.value,
                }),
            });
            const data = await response.json();
            if (data.summary) {
                excerptInput.value = data.summary;
            }
            if (data.error) {
                alert('摘要生成失败：' + data.error);
            }
        } catch (error) {
            console.error(error);
        } finally {
            generateButton.disabled = false;
            generateButton.textContent = '✨ 自动生成摘要';
        }
    });
</script>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
