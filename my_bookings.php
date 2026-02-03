<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('dashboard.php');
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | LuminaLib</title>
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
                    <h1 class="page-title">My Bookings</h1>
                    <p class="page-subtitle">View status of your book requests and borrowing history.</p>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Request Details</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="myBookingsTableBody">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Book Details Modal -->
    <div id="detailsModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Book Details</h3>
                <button id="closeDetailsModal" class="close-btn">&times;</button>
            </div>
            <div id="bookDetailsContent" style="padding: 1.5rem;">
                <!-- Populated via JS -->
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('myBookingsTableBody');
            
            const loadMyBookings = async () => {
                const res = await fetch(`api/get_my_bookings.php`);
                const data = await res.json();
                if (data.success) {
                    if (data.bookings.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No bookings found.</td></tr>';
                        return;
                    }
                    tableBody.innerHTML = data.bookings.map(b => `
                        <tr>
                            <td style="font-weight: 600;">${b.title}</td>
                            <td>${b.author_name}</td>
                            <td>
                                <div style="font-size: 0.8125rem;">
                                    <strong>${b.purpose || 'N/A'}</strong> (${b.duration || 'N/A'})
                                </div>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.8125rem;">${new Date(b.booking_date).toLocaleDateString()}</td>
                            <td><span class="status-tag ${b.status.toLowerCase()}">${b.status}</span></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <button class="btn view-details" data-book='${JSON.stringify(b).replace(/'/g, "&apos;")}' title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    ${b.status === 'Approved' ? `
                                        <button class="btn btn-secondary putback-btn" data-id="${b.id}" title="Putback Book" style="padding: 0.4rem 0.8rem; font-size: 0.8125rem; height: 34px; display: flex; align-items: center; gap: 0.4rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"></path><path d="M4 9h12.5A5.5 5.5 0 0 1 22 14.5v0A5.5 5.5 0 0 1 16.5 20H11"></path></svg>
                                            <span>Putback</span>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    document.querySelectorAll('.view-details').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const book = JSON.parse(btn.dataset.book);
                            showBookDetails(book);
                        });
                    });

                    document.querySelectorAll('.putback-btn').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('Are you sure you want to request a putback for this book?')) return;
                            const fd = new FormData();
                            fd.append('id', btn.dataset.id);
                            const res = await fetch('api/request_putback.php', { method: 'POST', body: fd });
                            const data = await res.json();
                            if (data.success) {
                                showToast(data.message);
                                loadMyBookings();
                            } else {
                                showToast(data.message, 'error');
                            }
                        });
                    });
                }
            };

            const detailsModal = document.getElementById('detailsModal');
            const closeDetailsModal = document.getElementById('closeDetailsModal');
            const bookDetailsContent = document.getElementById('bookDetailsContent');

            const showBookDetails = (book) => {
                bookDetailsContent.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        ${book.image_path ? `<div style="width: 100%; height: 200px; border-radius: 12px; background: url('${book.image_path}') center/cover no-repeat; margin-bottom: 0.5rem;"></div>` : ''}
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Title</strong><div style="font-size: 1.1rem; font-weight: 600;">${book.title}</div></div>
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Author</strong><div>${book.author_name}</div></div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">ISBN</strong><div>${book.isbn}</div></div>
                            <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Category</strong><div>${book.category_name}</div></div>
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 0.5rem 0;">
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Your Request Purpose</strong><div>${book.purpose || 'N/A'}</div></div>
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Requested Duration</strong><div>${book.duration || 'N/A'}</div></div>
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Your Message</strong><div style="font-style: italic; color: var(--text-muted);">${book.message || 'No additional message provided.'}</div></div>
                    </div>
                `;
                detailsModal.classList.add('active');
            };

            closeDetailsModal.addEventListener('click', () => detailsModal.classList.remove('active'));
            
            loadMyBookings();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
