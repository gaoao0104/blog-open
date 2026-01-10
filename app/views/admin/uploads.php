<?php
$title = '素材上传';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">素材上传</h2>
        <form class="inline-form flex gap-2" method="post" enctype="multipart/form-data" action="/admin/uploads/create" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="file" name="featured_image" accept="image/*" required class="form-control" style="width: auto; padding: 6px;">
            <button type="submit" class="btn btn-primary btn-sm">上传图片</button>
        </form>
    </div>

    <div class="flex gap-4 mb-4 items-center" style="background: var(--admin-bg); padding: 12px; border-radius: 8px;">
        <button type="button" class="btn btn-secondary btn-sm" id="generate-markdown">生成 Markdown</button>
        <button type="button" class="btn btn-secondary btn-sm" id="copy-markdown">复制 Markdown</button>
        <span class="hint" style="font-size: 0.85rem; color: var(--admin-text-secondary);">勾选图片后可批量插入 Markdown</span>
    </div>
    
    <textarea id="markdown-output" rows="4" class="markdown-output form-control mb-6" readonly style="font-family: monospace; font-size: 0.85rem;"></textarea>

    <div class="upload-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
        <?php foreach ($uploads as $upload): ?>
            <div class="upload-card" style="background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden;">
                <div style="height: 150px; overflow: hidden; position: relative; background: #eee;">
                     <img src="<?= e($upload['file_path']) ?>" alt="<?= e($upload['file_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                     <label class="upload-select" style="position: absolute; top: 8px; left: 8px; background: rgba(0,0,0,0.5); padding: 4px; border-radius: 4px;">
                        <input type="checkbox" data-path="<?= e($upload['file_path']) ?>" data-caption="<?= e($upload['caption'] ?? $upload['file_name']) ?>">
                    </label>
                </div>
                
                <div style="padding: 12px;">
                    <code style="display: block; font-size: 0.75rem; color: var(--admin-text-secondary); margin-bottom: 8px; word-break: break-all;"><?= e($upload['file_path']) ?></code>
                    
                    <form class="caption-form" method="post" action="/admin/uploads/update">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                        <input type="hidden" name="id" value="<?= e((string)$upload['id']) ?>">
                        <div class="flex gap-2">
                             <input type="text" name="caption" placeholder="图片说明" value="<?= e($upload['caption'] ?? '') ?>" class="form-control" style="padding: 4px 8px; font-size: 0.85rem;">
                             <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">🆗</button>
                        </div>
                        <div class="text-right mt-2" style="margin-top: 8px;">
                             <a class="btn btn-danger btn-sm" style="padding: 4px 8px; font-size: 0.75rem;" href="/admin/uploads/delete?id=<?= e((string)$upload['id']) ?>" onclick="return confirm('确定删除?');">删除</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const generateButton = document.getElementById('generate-markdown');
    const copyButton = document.getElementById('copy-markdown');
    const output = document.getElementById('markdown-output');

    function buildMarkdown() {
        const selected = Array.from(document.querySelectorAll('.upload-select input:checked'));
        const lines = selected.map((input) => {
            const caption = input.dataset.caption || 'image';
            const path = input.dataset.path;
            return `![${caption}](${path})`;
        });
        output.value = lines.join('\\n');
    }

    generateButton.addEventListener('click', buildMarkdown);
    copyButton.addEventListener('click', async () => {
        buildMarkdown();
        if (output.value.trim() === '') {
            return;
        }
        try {
            await navigator.clipboard.writeText(output.value);
            alert('复制成功');
        } catch (error) {
            output.select();
            document.execCommand('copy');
            alert('已尝试复制');
        }
    });
</script>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
