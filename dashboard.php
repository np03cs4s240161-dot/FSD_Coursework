<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'] ?? 'Patron';
$username = $_SESSION['username'] ?? 'User';

$categories = [];
$totalBooks = 0;
$availableBooks = 0;
$totalAuthors = 0;

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection not established.");
    }

    // Fetch categories for filters
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
    if ($categoriesStmt) {
        $categories = $categoriesStmt->fetchAll();
    }

    // Fetch initial stats
    $totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 0;
    $availableBooks = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'Available'")->fetchColumn() ?: 0;
    $totalAuthors = $pdo->query("SELECT COUNT(*) FROM authors")->fetchColumn() ?: 0;
} catch (Exception $e) {
    // We'll catch and log, but let the page render with 0 stats/no categories
    // rather than a fatal blank page.
    error_log("Dashboard Initialization Error: " . $e->getMessage());
    $init_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Inspired | LuminaLib</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css?v=7">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="library-app">
    <?php include 'includes/navbar.php'; ?>
    
    <?php if (isset($init_error)): ?>
        <div class="alert danger" style="margin: 2rem; padding: 1rem; background: #fee2e2; color: #b91c1c; border-radius: 8px; border: 1px solid #fca5a5;">
            <strong>Database Error:</strong> <?= htmlspecialchars($init_error) ?>
            <p>Please check your database configuration in <code>config/db.php</code></p>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="app-main">
        <div class="content-container dashboard-container">
            <div class="dashboard-header-row">
                <div class="page-title-group">
                    <h1 class="page-title">Get inspired</h1>
                    <p class="page-subtitle">Explore the library resources</p>
                </div>
                <!-- Stats Row -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $totalBooks ?></div>
                            <div class="stat-label">Total Books</div>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $availableBooks ?></div>
                            <div class="stat-label">Available</div>
                        </div>
                    </div>
                    <div class="stat-card authors">
                        <div class="stat-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $totalAuthors ?></div>
                            <div class="stat-label">Authors</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Row -->
            <div class="controls-row">
                <div class="controls-left">
                    <!-- Category Pills -->
                    <div class="category-pills">
                        <button class="pill active" data-category="">All</button>
                        <?php 
                        $limit = 5;
                        foreach (array_slice($categories, 0, $limit) as $cat): ?>
                            <button class="pill" data-category="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="controls-right">
                    <div class="main-search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="globalSearch" placeholder="Search books, authors, ISBN...">
                    </div>
                    <button id="filterBtn" class="btn btn-secondary filter-toggle-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                        <span>Filters</span>
                    </button>
                    <?php if ($user_role === 'Admin'): ?>
                        <button id="addBookBtn" class="btn btn-primary add-book-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Add Book</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="books-grid" id="booksGrid">
                <!-- Populated via JavaScript -->
                <div class="loading-state">Loading books...</div>
            </div>
        </div>
    </main>
        </div>
    </main>

    <!-- Filter Sidebar -->
    <div id="filterSidebar" class="filter-sidebar">
        <div class="sidebar-header">
            <h3>Filters</h3>
            <button id="closeFilter" class="close-btn">&times;</button>
        </div>
        <div class="sidebar-content">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="searchInput" placeholder="Title or ISBN...">
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Issued">Issued</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Year Range</label>
                <input type="range" id="yearRange" min="1900" max="2025" value="2025">
                <div class="range-labels">
                    <span>1900</span>
                    <span id="yearValue">2025</span>
                </div>
            </div>
            <button id="applyFilters" class="btn btn-primary btn-block">Apply Filters</button>
            <button id="resetFilters" class="btn btn-secondary btn-block">Reset</button>
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

    <!-- Add/Edit Book Modal -->
    <div id="bookModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Book</h3>
                <button id="closeModal" class="close-btn">&times;</button>
            </div>
            <form id="bookForm" enctype="multipart/form-data">
                <input type="hidden" name="book_id" id="modalBookId">
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" placeholder="e.g. 978-0123456789" required>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Book Title" required>
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author_name" id="authorInput" placeholder="Author Name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="categorySelect" name="category_id" required></select>
                </div>
                <div class="form-group">
                    <label>Publish Year</label>
                    <input type="number" name="publish_year" value="2025" required>
                </div>

                <input type="file" id="bookImageInput" name="book_image" accept="image/*" style="display: none;">
                <div id="dropZone" class="upload-zone" style="position: relative; overflow: hidden; height: 160px; display: flex; align-items: center; justify-content: center; border: 2px dashed var(--border);">
                    <div id="uploadPrompt" class="upload-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <h4 style="margin: 0; font-size: 0.9rem;">Click or Drag Cover Image</h4>
                        <p style="margin: 0.25rem 0 0; font-size: 0.75rem; color: var(--text-muted);">PNG, JPG up to 5MB</p>
                    </div>
                    <div id="previewContainer" class="preview-container" style="display: none; position: absolute; inset: 0; background: var(--card-bg);">
                        <img id="imagePreviewEnhanced" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: contain; border-radius: 0;">
                        <div style="position: absolute; bottom: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.6); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; display: flex; align-items: center; gap: 0.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17 4 12"/></svg>
                            Image Selected
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelBook" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <!-- Custom Scripts -->
    <script src="assets/js/app.js?v=6"></script>
    <script>
        // Library App Specific JS
        document.addEventListener('DOMContentLoaded', () => {
            const booksGrid = document.getElementById('booksGrid');
            const filterBtn = document.getElementById('filterBtn');
            const filterSidebar = document.getElementById('filterSidebar');
            const closeFilter = document.getElementById('closeFilter');
            const categoryPills = document.querySelectorAll('.category-pills .pill');
            const applyFiltersBtn = document.getElementById('applyFilters');
            const resetFiltersBtn = document.getElementById('resetFilters');
            const globalSearch = document.getElementById('globalSearch');
            
            // Modal Elements
            const bookModal = document.getElementById('bookModal');
            const closeModal = document.getElementById('closeModal');
            const cancelBook = document.getElementById('cancelBook');
            const bookForm = document.getElementById('bookForm');
            const addBookBtn = document.getElementById('addBookBtn');
            const detailsModal = document.getElementById('detailsModal');
            const closeDetailsModal = document.getElementById('closeDetailsModal');
            const bookDetailsContent = document.getElementById('bookDetailsContent');
            const bookingRequestModal = document.getElementById('bookingRequestModal');
            const bookingRequestForm = document.getElementById('bookingRequestForm');
            const closeBookingModal = document.getElementById('closeBookingModal');
            const cancelBooking = document.getElementById('cancelBooking');
            
            // User context
            const userRole = <?= json_encode($user_role) ?>;
            const isAdmin = userRole === 'Admin';
            console.log("Logged in as:", userRole, "IsAdmin:", isAdmin);

            // Toggle filter sidebar
            if (filterBtn && filterSidebar) {
                filterBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log("Filter button clicked - Opening Sidebar");
                    filterSidebar.classList.add('open');
                });
                closeFilter.addEventListener('click', () => filterSidebar.classList.remove('open'));
            }

            // Category pill click
            categoryPills.forEach(pill => {
                pill.addEventListener('click', () => {
                    categoryPills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    loadBooks({ category: pill.dataset.category });
                });
            });

            // Global search with debouncing
            let searchTimeout;
            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    const keyword = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        console.log("AJAX Search triggering for:", keyword);
                        loadBooks({ keyword });
                    }, 300);
                });
            }

            // Apply filters
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => {
                    const keyword = document.getElementById('searchInput').value;
                    const category = document.getElementById('categoryFilter').value;
                    const status = document.getElementById('statusFilter').value;
                    const year = document.getElementById('yearRange').value;
                    loadBooks({ keyword, category, status, year });
                    filterSidebar.classList.remove('open');
                });
            }

            // Reset filters
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', () => {
                    document.getElementById('searchInput').value = '';
                    document.getElementById('categoryFilter').value = '';
                    document.getElementById('statusFilter').value = '';
                    document.getElementById('yearRange').value = '2025';
                    document.getElementById('yearValue').textContent = '2025';
                    categoryPills.forEach(p => p.classList.remove('active'));
                    if (categoryPills.length > 0) categoryPills[0].classList.add('active');
                    loadBooks({});
                    filterSidebar.classList.remove('open');
                });
            }

            // Year range display
            const yearRange = document.getElementById('yearRange');
            const yearValue = document.getElementById('yearValue');
            if (yearRange && yearValue) {
                yearRange.addEventListener('input', (e) => {
                    yearValue.textContent = e.target.value;
                });
            }

            // Load books function
            async function loadBooks(filters = {}) {
                const params = new URLSearchParams(filters);
                try {
                    const response = await fetch(`api/search_books.php?${params.toString()}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        renderBooksGrid(data.books);
                    }
                } catch (error) {
                    console.error('Failed to load books:', error);
                    if (booksGrid) booksGrid.innerHTML = '<div class="error-state">Failed to load books</div>';
                }
            }

            // Render books as cards
            function renderBooksGrid(books) {
                if (!booksGrid) return;
                
                if (books.length === 0) {
                    booksGrid.innerHTML = '<div class="empty-state"><h3>No books found</h3><p>Try adjusting your filters</p></div>';
                    return;
                }
                
                booksGrid.innerHTML = books.map(book => {
                    const statusClass = book.status.toLowerCase();
                    const colors = [
                        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        'linear-gradient(135deg, #2af598 0%, #009efd 100%)',
                        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
                    ];
                    const fallbackBg = colors[book.id % colors.length];
                    
                    // Log for debugging
                    if (book.image_path) console.log(`Book "${book.title}" image path: ${book.image_path}`);

                    return `
                        <div class="book-card" data-id="${book.id}">
                            <div class="card-image" style="background: ${fallbackBg};">
                                ${book.image_path ? `
                                    <img src="${book.image_path}" 
                                         alt="${book.title}" 
                                         style="width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                ` : ''}
                                <div class="card-title-overlay" style="${book.image_path ? 'display: none;' : 'display: flex; flex-direction: column; align-items: center; justify-content: center;'}">
                                    <div style="font-size: 0.8rem; opacity: 0.8; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 400;">Book Title</div>
                                    <div style="text-align: center; width: 100%;">${book.title}</div>
                                </div>
                                <span class="status-tag ${statusClass}">${book.status}</span>
                            </div>
                            <div class="card-info" style="padding: 1.25rem;">
                                <!-- Title Block -->
                                <div style="margin-bottom: 1rem;">
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem; font-weight: 600;">Title</div>
                                    <h3 class="card-book-title" style="margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--text-main); line-height: 1.4; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${book.title}</h3>
                                </div>
                                
                                <div class="card-meta" style="margin-bottom: 1rem; align-items: flex-end;">
                                    <div style="flex: 1; overflow: hidden;">
                                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem; font-weight: 600;">Author</div>
                                        <div class="card-author" style="font-weight: 500; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-main);">${book.author_name}</div>
                                    </div>
                                    <div class="card-actions" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <button class="icon-btn view-details" 
                                            data-book='${JSON.stringify(book).replace(/'/g, "&apos;")}' 
                                            title="View Details" style="border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                        ${isAdmin ? `
                                            <button class="icon-btn edit-book" 
                                                data-book='${JSON.stringify(book).replace(/'/g, "&apos;")}' 
                                                title="Edit" style="border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; color: var(--accent);">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4L18.5 2.5z"></path></svg>
                                            </button>
                                            <button class="icon-btn delete-book" data-id="${book.id}" title="Delete" style="border: 1px solid rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; color: #ef4444; background: rgba(239, 68, 68, 0.05);">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        ` : (book.status === 'Available' ? `
                                            <button class="btn btn-primary btn-sm book-now" 
                                                data-id="${book.id}" 
                                                data-title="${book.title.replace(/'/g, "&apos;")}"
                                                style="padding: 0.5rem 0.75rem; font-size: 0.75rem; border-radius: 8px;">Book Now</button>
                                        ` : '')}
                                    </div>
                                </div>
                                <div class="card-stats" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1rem;">
                                    <span class="card-category" style="background: var(--accent-glow); color: var(--accent); padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500;">${book.category_name}</span>
                                    <span class="card-year" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 500;">${book.publish_year}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Add event listeners using delegation for reliability (Attached once)
            // Add event listeners using delegation for reliability (Attached once)
            if (booksGrid) {
                booksGrid.onclick = async (e) => {
                    const btn = e.target.closest('button');
                    if (!btn) return;
                    e.stopPropagation();

                    if (btn.classList.contains('book-now')) {
                        console.log("Book Now Clicked", btn.dataset);
                        openBookingModal(btn.dataset.id, btn.dataset.title);
                    } else if (btn.classList.contains('view-details')) {
                        try {
                            const book = JSON.parse(btn.dataset.book);
                            showBookDetails(book);
                        } catch (err) {
                            console.error("Error showing details:", err);
                        }
                    } else if (btn.classList.contains('edit-book')) {
                        try {
                            const book = JSON.parse(btn.dataset.book);
                            openBookModal(book);
                        } catch (err) {
                            console.error("Error parsing book data", err);
                        }
                    } else if (btn.classList.contains('delete-book')) {
                        if (!confirm('Delete this book?')) return;
                        const fd = new FormData();
                        fd.append('book_id', btn.dataset.id);
                        const res = await fetch('api/delete_book.php', { method: 'POST', body: fd });
                        const result = await res.json();
                        if (result.success) {
                            showToast(result.message);
                            loadBooks({});
                        }
                    }
                };
            }

            // Details Modal Logic
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
                detailsModal.classList.add('active');
            }

            closeDetailsModal?.addEventListener('click', () => detailsModal.classList.remove('active'));

            // Booking Modal functions
            function openBookingModal(bookId, bookTitle) {
                if (!bookingRequestModal) return;
                document.getElementById('bookingBookId').value = bookId;
                document.getElementById('bookingBookTitle').textContent = `Book: ${bookTitle}`;
                bookingRequestModal.classList.add('modal-active');
            }

            closeBookingModal?.addEventListener('click', () => bookingRequestModal.classList.remove('modal-active'));
            cancelBooking?.addEventListener('click', () => bookingRequestModal.classList.remove('modal-active'));

            bookingRequestForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(bookingRequestForm);
                const res = await fetch('api/book_request.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message);
                    bookingRequestModal.classList.remove('modal-active');
                    bookingRequestForm.reset();
                    loadBooks({});
                } else {
                    showToast(data.message, 'error');
                }
            });

            // Modal functions
            window.openBookModal = async function(book = null) {
                if (!bookModal) {
                    console.error("CRITICAL: bookModal element not found in DOM!");
                    alert("System Error: Modal not found. Please refresh.");
                    return;
                }
                
                console.log("ACTIVATE MODAL: Forcing display...");
                // Force visibility using all possible methods
                bookModal.style.setProperty('display', 'flex', 'important');
                bookModal.style.opacity = '1';
                bookModal.style.visibility = 'visible';
                bookModal.classList.add('modal-active');
                
                // Show immediate loading state in category select
                const catSelect = document.getElementById('categorySelect');
                if (catSelect) catSelect.innerHTML = '<option value="">Loading...</option>';
                
                await populateSelects();

                const previewImg = document.getElementById('imagePreviewEnhanced');
                if (book) {
                    document.getElementById('modalTitle').textContent = 'Edit Book';
                    bookForm.book_id.value = book.id;
                    bookForm.isbn.value = book.isbn;
                    bookForm.title.value = book.title;
                    document.getElementById('authorInput').value = book.author_name || '';
                    bookForm.category_id.value = book.category_id;
                    bookForm.publish_year.value = book.publish_year;
                    
                    if (previewImg && book.image_path) {
                        previewImg.src = book.image_path;
                        document.getElementById('uploadPrompt').style.display = 'none';
                        document.getElementById('previewContainer').style.display = 'block';
                    } else {
                        if (previewImg) previewImg.src = '';
                        document.getElementById('uploadPrompt').style.display = 'block';
                        document.getElementById('previewContainer').style.display = 'none';
                    }
                } else {
                    document.getElementById('modalTitle').textContent = 'Add New Book';
                    bookForm.reset();
                    bookForm.book_id.value = '';
                    if (previewImg) {
                        previewImg.src = '';
                        document.getElementById('uploadPrompt').style.display = 'block';
                        document.getElementById('previewContainer').style.display = 'none';
                    }
                }
            };

            // Drag and Drop Logic
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('bookImageInput');
            const previewImgEnhanced = document.getElementById('imagePreviewEnhanced');

            if (dropZone && fileInput) {
                dropZone.addEventListener('click', () => fileInput.click());

                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('drag-over');
                });

                ['dragleave', 'dragend'].forEach(type => {
                    dropZone.addEventListener(type, () => dropZone.classList.remove('drag-over'));
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('drag-over');
                    const files = e.dataTransfer.files;
                    if (files.length) {
                        fileInput.files = files;
                        handlePreview(files[0]);
                    }
                });

                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length) handlePreview(fileInput.files[0]);
                });
            }

            function handlePreview(file) {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewImg = document.getElementById('imagePreviewEnhanced');
                        const uploadPrompt = document.getElementById('uploadPrompt');
                        const previewContainer = document.getElementById('previewContainer');
                        
                        if (previewImg) previewImg.src = e.target.result;
                        if (uploadPrompt) uploadPrompt.style.display = 'none';
                        if (previewContainer) previewContainer.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }

            async function populateSelects() {
                try {
                    const catRes = await fetch('api/get_categories.php').then(r => r.json());

                    if (catRes.success) {
                        document.getElementById('categorySelect').innerHTML = 
                            catRes.categories.map(c => `<option value="${c.id}">${c.category_name}</option>`).join('');
                    } else {
                        throw new Error("Categories failed to load");
                    }
                } catch (err) {
                    console.error("Populate selects error:", err);
                    document.getElementById('categorySelect').innerHTML = '<option value="">Error loading</option>';
                    showToast("Failed to load categories", "error");
                }
            }

            if (addBookBtn) {
                addBookBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log("Add Book button clicked - Opening Modal");
                    window.openBookModal();
                });
            }
            if (closeModal) closeModal.addEventListener('click', () => {
                bookModal.classList.remove('modal-active');
                bookModal.style.display = 'none';
                bookModal.style.opacity = '0';
                bookModal.style.visibility = 'hidden';
            });
            if (cancelBook) cancelBook.addEventListener('click', () => {
                bookModal.classList.remove('modal-active');
                bookModal.style.display = 'none';
                bookModal.style.opacity = '0';
                bookModal.style.visibility = 'hidden';
            });

            if (bookForm) {
                bookForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    console.log("Submitting book form...");
                    const formData = new FormData(bookForm);
                    const isEdit = !!bookForm.book_id.value;
                    const endpoint = isEdit ? 'api/update_book.php' : 'api/save_book.php';

                    try {
                        const response = await fetch(endpoint, { method: 'POST', body: formData });
                        const text = await response.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error("Invalid JSON response:", text);
                            throw new Error("Server returned an invalid response");
                        }

                        if (data.success) {
                            showToast(data.message);
                            bookModal.classList.remove('modal-active');
                            bookModal.style.display = 'none';
                            loadBooks({});
                        } else {
                            showToast(data.message || 'Action failed', 'error');
                        }
                    } catch (error) {
                        console.error("Submission error:", error);
                        showToast(error.message || 'Connection error', 'error');
                    }
                });
            }

            // Toast function
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                if (!toast) return;
                toast.textContent = message;
                toast.className = `toast ${type} show`;
                setTimeout(() => toast.classList.remove('show'), 3000);
            }
            window.showToast = showToast;

            // Initial load
            loadBooks({});
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
