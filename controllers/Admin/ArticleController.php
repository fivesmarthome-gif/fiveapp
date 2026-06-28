<?php
/**
 * Admin Article Controller
 * HoanKiem LAB
 */

class ArticleController extends Controller
{
    public function index(): void
    {
        $articles = $this->db->fetchAll("SELECT a.*, u.name AS author_name FROM articles a LEFT JOIN users u ON u.id = a.created_by ORDER BY a.created_at DESC");
        $this->view('admin.articles.index', [
            'pageTitle' => 'Quản lý bài viết & tin tức',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Bài viết' => ''],
            'articles' => $articles
        ]);
    }

    public function create(): void
    {
        $this->view('admin.articles.create', [
            'pageTitle' => 'Viết bài mới',
            'breadcrumbs' => ['Bài viết' => url('/admin/articles'), 'Viết bài' => '']
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/articles/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/articles/create');
            return;
        }

        $coverImagePath = $this->uploadFile('cover_image', 'articles');

        $slug = slugify($this->input('title'));

        $id = $this->db->insert('articles', [
            'title' => $this->input('title'),
            'slug' => $slug,
            'type' => $this->input('type', 'news'),
            'content' => $this->input('content'),
            'cover_image' => $coverImagePath,
            'is_published' => $this->input('is_published', 0) ? 1 : 0,
            'show_hotline_button' => $this->input('show_hotline_button', 0) ? 1 : 0,
            'created_by' => Auth::getInstance()->id(),
            'published_at' => $this->input('is_published', 0) ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->logActivity('create_article', 'articles', $id, "Đã viết bài: {$this->input('title')}");
        $this->redirect('/admin/articles', ['success' => 'Đăng bài viết thành công']);
    }

    public function edit(string $id): void
    {
        $article = $this->db->fetch("SELECT * FROM articles WHERE id = ?", [(int)$id]);
        if (!$article) { $this->redirect('/admin/articles'); return; }

        $this->view('admin.articles.edit', [
            'pageTitle' => 'Chỉnh sửa bài viết',
            'breadcrumbs' => ['Bài viết' => url('/admin/articles'), 'Sửa' => ''],
            'article' => $article
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/articles/{$id}/edit"); return; }

        $article = $this->db->fetch("SELECT * FROM articles WHERE id = ?", [(int)$id]);
        if (!$article) { $this->redirect('/admin/articles'); return; }

        $coverImagePath = $this->uploadFile('cover_image', 'articles') ?: $article->cover_image;

        $slug = slugify($this->input('title'));

        $publishedAt = $article->published_at;
        if ($this->input('is_published', 0) && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $this->db->update('articles', [
            'title' => $this->input('title'),
            'slug' => $slug,
            'type' => $this->input('type', 'news'),
            'content' => $this->input('content'),
            'cover_image' => $coverImagePath,
            'is_published' => $this->input('is_published', 0) ? 1 : 0,
            'show_hotline_button' => $this->input('show_hotline_button', 0) ? 1 : 0,
            'published_at' => $publishedAt,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$article->id]);

        $this->redirect('/admin/articles', ['success' => 'Đã cập nhật bài viết']);
    }

    public function destroy(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/articles'); return; }

        $this->db->delete('articles', 'id = ?', [(int)$id]);
        $this->redirect('/admin/articles', ['success' => 'Đã xoá bài viết']);
    }
}
