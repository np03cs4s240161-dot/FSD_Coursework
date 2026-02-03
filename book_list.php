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
    <title>Book List | LuminaLib</title>
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
                    <h1 class="page-title">Explore Library</h1>
                    <p class="page-subtitle">Browse all available books in our collection.</p>
                </div>
                <div class="admin-actions-group">
                    <div class="main-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="bookSearch" placeholder="Search by title, author or ISBN...">
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="bookListTable">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookListTableBody">
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
                <h3>Book Information</h3>
                <button id="closeDetailsModal" class="close-btn">&times;</button>
            </div>
            <div id="bookDetailsContent" style="padding: 1.5rem;">
                <!-- Populated via JS -->
            </div>
        </div>
    </div>

    <!-- Booking Request Modal -->
    <div id="bookingRequestModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3>Request Booking</h3>
                <button id="closeBookingModal" class="close-btn">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <p id="bookingBookTitle" style="font-weight: 600; font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--accent);"></p>
                <form id="bookingRequestForm">
                    <input type="hidden" name="book_id" id="bookingBookId">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Purpose of Borrowing</label>
                        <select name="purpose" required style="width: 100%;">
                            <option value="">Select Purpose</option>
                            <option value="Study/Research">Study/Research</option>
                            <option value="Personal Reading">Personal Reading</option>
                            <option value="Project Work">Project Work</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Expected Duration</label>
                        <select name="duration" required style="width: 100%;">
                            <option value="3 Days">3 Days</option>
                            <option value="1 Week">1 Week</option>
                            <option value="2 Weeks">2 Weeks</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Additional Message (Optional)</label>
                        <textarea name="message" rows="3" placeholder="Any special requests or instructions..." style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text-main); font-size: 0.875rem; outline: none;"></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" id="cancelBooking" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js?v=6"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('bookListTableBody');
            const searchInput = document.getElementById('bookSearch');
            
            const loadBooks = async (keyword = '') => {
                const res = await fetch(`api/search_books.php?keyword=${encodeURIComponent(keyword)}`);
                const data = await res.json();
                if (data.success) {
                    tableBody.innerHTML = data.books.map(b => `
                        <tr>
                            <td style="font-size: 0.8125rem;">${b.isbn}</td>
                            <td style="font-weight: 600;">${b.title}</td>
                            <td>${b.author_name}</td>
                            <td>${b.category_name}</td>
                            <td>${b.publish_year}</td>
                            <td><span class="status-tag ${b.status.toLowerCase()}">${b.status}</span></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <button class="btn view-details" data-book='${JSON.stringify(b).replace(/'/g, "&apos;")}' title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    ${b.status === 'Available' ? `
                                        <button class="btn btn-primary btn-sm book-now-btn" data-id="${b.id}" data-title="${b.title}" style="padding: 0.4rem 0.8rem; font-size: 0.8125rem; height: 34px;">Book</button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    // Details listener
                    document.querySelectorAll('.view-details').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const book = JSON.parse(btn.dataset.book);
                            showBookDetails(book);
                        });
                    });

                    // Book Now listener
                    document.querySelectorAll('.book-now-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            openBookingModal(btn.dataset.id, btn.dataset.title);
                        });
                    });
                }
            };

            // Details Modal Logic
            const detailsModal = document.getElementById('detailsModal');
            const closeDetailsModal = document.getElementById('closeDetailsModal');
            const bookDetailsContent = document.getElementById('bookDetailsContent');

            function showBookDetails(book) {
                bookDetailsContent.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        ${book.image_path ? `<div style="width: 100%; height: 200px; border-radius: 12px; background: url('${book.image_path}') center/cover no-repeat; margin-bottom: 0.5rem;"></div>` : ''}
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Title</strong><div style="font-size: 1.25rem; font-weight: 700; color: var(--accent);">${book.title}</div></div>
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Author</strong><div style="font-size: 1rem; font-weight: 500;">${book.author_name}</div></div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">ISBN</strong><div>${book.isbn}</div></div>
                            <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Category</strong><div>${book.category_name}</div></div>
                        </div>
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Publish Year</strong><div>${book.publish_year}</div></div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 0.5rem 0;">
                        <div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Status</strong><div><span class="status-tag ${book.status.toLowerCase()}">${book.status}</span></div></div>
                        ${book.biography ? `<div><strong style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">About Author</strong><p style="margin-top: 0.5rem; line-height: 1.5; color: var(--text-muted);">${book.biography}</p></div>` : ''}
                    </div>
                `;
                detailsModal.classList.add('modal-active');
            }

            // Booking Modal Logic
            const bookingRequestModal = document.getElementById('bookingRequestModal');
            const bookingRequestForm = document.getElementById('bookingRequestForm');
            const closeBookingModal = document.getElementById('closeBookingModal');
            const cancelBooking = document.getElementById('cancelBooking');

            function openBookingModal(bookId, bookTitle) {
                document.getElementById('bookingBookId').value = bookId;
                document.getElementById('bookingBookTitle').textContent = `Book: ${bookTitle}`;
                bookingRequestModal.classList.add('modal-active');
            }

            bookingRequestForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(bookingRequestForm);
                const res = await fetch('api/book_request.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    bookingRequestModal.classList.remove('modal-active');
                    bookingRequestForm.reset();
                    loadBooks(searchInput.value);
                } else {
                    showToast(data.message, 'error');
                }
            });

            closeDetailsModal.addEventListener('click', () => detailsModal.classList.remove('modal-active'));
            closeBookingModal.addEventListener('click', () => bookingRequestModal.classList.remove('modal-active'));
            cancelBooking.addEventListener('click', () => bookingRequestModal.classList.remove('modal-active'));

            // Search logic with debouncing
            let searchTimeout;
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const keyword = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadBooks(keyword);
                    }, 300);
                });
            }

            loadBooks();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
