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
    <title>Books Catalog | LuminaLib LMS</title>
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
                    <h1 class="page-title" style="margin: 0; font-size: 1.5rem;">Full Book Catalog</h1>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Browse and manage all available assets.</p>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; max-width: 500px; justify-content: flex-end;">
                    <div class="main-search" style="position: relative; flex: 1; max-width: 350px;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">🔍</span>
                        <input type="text" id="globalSearch" placeholder="Search by title, isbn, author..." 
                               style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border-radius: 12px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text-main); font-size: 0.9375rem; outline: none;">
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="booksTable">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Re-using globalSearch from navbar for catalog filtering
            const globalSearch = document.getElementById('globalSearch');
            
            const loadBooks = async (keyword = '') => {
                const res = await fetch(`api/search_books.php?keyword=${keyword}`);
                const data = await res.json();
                if (data.success) {
                    const tbody = document.querySelector('#booksTable tbody');
                    tbody.innerHTML = data.books.map(book => `
                        <tr>
                            <td style="font-family: monospace;">${book.isbn}</td>
                            <td style="font-weight: 600;">${book.title}</td>
                            <td>${book.author_name}</td>
                            <td><span class="badge">${book.category_name}</span></td>
                            <td><span class="status-tag ${book.status.toLowerCase()}">${book.status}</span></td>
                            <td>
                                <button class="icon-btn" title="View Detail">👁️</button>
                            </td>
                        </tr>
                    `).join('');
                }
            };

            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    loadBooks(e.target.value);
                });
            }

            loadBooks();
        });
    </script>
</body>
</html>
