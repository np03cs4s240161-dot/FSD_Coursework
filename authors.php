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
    <title>Manage Authors | LuminaLib LMS</title>
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
        <div class="content-container" style="padding: 0.75rem 2rem; max-width: 1400px; margin: 0 auto;">
            <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: flex-end; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <h1 class="page-title" style="margin: 0; font-size: 1.5rem;">Authors Management</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">View and manage authors in the library system.</p>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; max-width: 500px; justify-content: flex-end;">
                    <div class="main-search" style="position: relative; flex: 1; max-width: 350px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="globalSearch" placeholder="Search authors..." 
                               style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.8rem; border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text-main); font-size: 0.9375rem; outline: none;">
                    </div>
                    <?php if ($user_role === 'Admin'): ?>
                        <button id="addAuthorBtn" class="btn btn-primary" style="height: 44px; border-radius: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Add Author</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Author Name</th>
                            <th>Biography</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="authorsTableBody">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Author Modal -->
    <div id="authorModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Add New Author</h3>
                <button id="closeModal" class="close-btn">&times;</button>
            </div>
            <form id="authorForm" style="padding: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.4rem;">Author Name</label>
                    <input type="text" name="author_name" required style="width: 100%;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.4rem;">Biography</label>
                    <textarea name="biography" style="width: 100%; padding: 0.625rem; border: none; border-bottom: 1px solid var(--border); border-radius: 0; background: transparent; color: var(--text-main); height: 100px; font-family: inherit;"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelAuthor" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Author</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const authorsTableBody = document.getElementById('authorsTableBody');
            const authorModal = document.getElementById('authorModal');
            const authorForm = document.getElementById('authorForm');
            const toast = document.getElementById('toast');

            const showToast = (message, type = 'success') => {
                toast.textContent = message;
                toast.className = `toast ${type} show`;
                setTimeout(() => toast.classList.remove('show'), 3000);
            };

            const loadAuthors = async (keyword = '') => {
                const res = await fetch(`api/get_authors_detailed.php?keyword=${keyword}`);
                const data = await res.json();
                if (data.success) {
                    authorsTableBody.innerHTML = data.authors.map(a => `
                        <tr>
                            <td>${a.id}</td>
                            <td style="font-weight: 600;">${a.author_name}</td>
                            <td style="color: var(--text-muted); font-size: 0.875rem;">${a.biography || 'N/A'}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn edit-author" data-author='${JSON.stringify(a).replace(/'/g, "&apos;")}' title="Edit Author">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4L18.5 2.5z"></path></svg>
                                    </button>
                                    <button class="btn delete-author" data-id="${a.id}" title="Delete Author">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    document.querySelectorAll('.delete-author').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('Delete this author?')) return;
                            const fd = new FormData();
                            fd.append('id', btn.dataset.id);
                            const res = await fetch('api/delete_author.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                showToast(data.message);
                                loadAuthors();
                            } else {
                                showToast(data.message, 'error');
                            }
                        });
                    });
                }
            };

            document.getElementById('addAuthorBtn')?.addEventListener('click', () => {
                authorModal.classList.add('active');
            });

            document.getElementById('closeModal').addEventListener('click', () => {
                authorModal.classList.remove('active');
            });

            document.getElementById('cancelAuthor').addEventListener('click', () => {
                authorModal.classList.remove('active');
                authorForm.reset();
            });

            authorForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(authorForm);
                const res = await fetch('api/save_author.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    authorModal.classList.remove('active');
                    authorForm.reset();
                    loadAuthors();
                } else {
                    showToast(data.message, 'error');
                }
            });

            const globalSearch = document.getElementById('globalSearch');
            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    loadAuthors(e.target.value);
                });
            }

            loadAuthors();
        });
    </script>
</body>
</html>
