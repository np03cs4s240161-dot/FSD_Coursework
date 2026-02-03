<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | LuminaLib LMS</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=7">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="library-app">
    <?php include 'includes/navbar.php'; ?>

    <main class="app-main">
        <div class="content-container">
            <div class="admin-header-row">
                <div class="admin-title-group">
                    <h1 class="page-title">Categories Management</h1>
                    <p class="page-subtitle">Organize library assets by genres and categories.</p>
                </div>

                <div class="admin-actions-group">
                    <div class="main-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="globalSearch" placeholder="Search categories...">
                    </div>
                    <?php if ($user_role === 'Admin'): ?>
                        <button id="addCategoryBtn" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="btn-icon-left"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Add Category</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Category Modal -->
    <div id="categoryModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Category</h3>
                <button id="closeModal" class="close-btn">&times;</button>
            </div>
            <form id="categoryForm" style="padding: 1.5rem;">
                <input type="hidden" name="id" id="categoryId">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.4rem;">Category Name</label>
                    <input type="text" name="category_name" required style="width: 100%;">
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelCategory" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('categoriesTableBody');
            const modal = document.getElementById('categoryModal');
            const form = document.getElementById('categoryForm');
            const toast = document.getElementById('toast');

            const showToast = (message, type = 'success') => {
                toast.textContent = message;
                toast.className = `toast ${type} show`;
                setTimeout(() => toast.classList.remove('show'), 3000);
            };

            const loadCategories = async (keyword = '') => {
                const res = await fetch(`api/get_categories_detailed.php?keyword=${keyword}`);
                const data = await res.json();
                if (data.success) {
                    tableBody.innerHTML = data.categories.map(c => `
                        <tr>
                            <td>${c.id}</td>
                            <td style="font-weight: 600;">${c.category_name}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn edit-category" data-category='${JSON.stringify(c).replace(/'/g, "&apos;")}' title="Edit Category">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4L18.5 2.5z"></path></svg>
                                    </button>
                                    <button class="btn delete-category" data-id="${c.id}" title="Delete Category">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    document.querySelectorAll('.delete-category').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('Delete this category?')) return;
                            const fd = new FormData();
                            fd.append('id', btn.dataset.id);
                            const res = await fetch('api/delete_category.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                showToast(data.message);
                                loadCategories();
                            } else {
                                showToast(data.message, 'error');
                            }
                        });
                    });
                    document.querySelectorAll('.edit-category').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const category = JSON.parse(btn.dataset.category);
                            document.getElementById('modalTitle').textContent = 'Edit Category';
                            document.getElementById('categoryId').value = category.id;
                            form.elements['category_name'].value = category.category_name;
                            modal.classList.add('modal-active');
                            setTimeout(() => modal.classList.add('active'), 10);
                        });
                    });
                }
            };

            document.getElementById('addCategoryBtn')?.addEventListener('click', () => {
                document.getElementById('modalTitle').textContent = 'Add New Category';
                document.getElementById('categoryId').value = '';
                form.reset();
                modal.classList.add('modal-active');
                setTimeout(() => modal.classList.add('active'), 10);
            });

            document.getElementById('closeModal').addEventListener('click', () => {
                modal.classList.remove('active');
                setTimeout(() => modal.classList.remove('modal-active'), 300);
            });

            document.getElementById('cancelCategory').addEventListener('click', () => {
                modal.classList.remove('active');
                setTimeout(() => modal.classList.remove('modal-active'), 300);
                form.reset();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const res = await fetch('api/save_category.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    modal.classList.remove('active');
                    setTimeout(() => modal.classList.remove('modal-active'), 300);
                    form.reset();
                    loadCategories();
                } else {
                    showToast(data.message, 'error');
                }
            });

            let searchTimeout;
            const globalSearch = document.getElementById('globalSearch');
            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    const keyword = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadCategories(keyword);
                    }, 300);
                });
            }

            loadCategories();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
