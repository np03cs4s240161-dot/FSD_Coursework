document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('searchBtn');
    const closeSearch = document.getElementById('closeSearch');
    const searchSidebar = document.getElementById('search-sidebar');
    const yearRange = document.getElementById('yearRange');
    const yearValue = document.getElementById('yearValue');
    const applyFilters = document.getElementById('applyFilters');
    const booksTableBody = document.querySelector('#booksTable tbody');
    const toast = document.getElementById('toast');
    const themeToggle = document.getElementById('themeToggle');

    // Utility: Debounce function to limit AJAX calls
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Theme Management
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);

    // Update theme icon based on current theme
    const updateThemeIcon = (theme) => {
        const themeToggles = document.querySelectorAll('#themeToggle, .theme-toggle');
        const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;
        const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;

        themeToggles.forEach(toggle => {
            if (toggle) {
                // Show moon when in light mode (to switch to dark)
                // Show sun when in dark mode (to switch to light)
                toggle.innerHTML = theme === 'light' ? moonIcon : sunIcon;
                toggle.setAttribute('title', theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode');
            }
        });
    };

    // Initialize icon on page load
    updateThemeIcon(currentTheme);

    // Theme toggle listeners
    const themeToggles = document.querySelectorAll('#themeToggle, .theme-toggle');
    themeToggles.forEach(toggle => {
        if (toggle) {
            toggle.addEventListener('click', () => {
                const newTheme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });
        }
    });

    // Mobile Menu Toggle
    const mobileMenuButtons = document.querySelectorAll('.mobile-menu-btn');
    const headerNav = document.querySelector('.header-nav');
    const landingNavLinks = document.querySelector('.landing-nav-links');

    if (mobileMenuButtons.length > 0) {
        mobileMenuButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isActive = (headerNav && headerNav.classList.contains('active')) ||
                    (landingNavLinks && landingNavLinks.classList.contains('active'));

                if (isActive) {
                    if (headerNav) headerNav.classList.remove('active');
                    if (landingNavLinks) landingNavLinks.classList.remove('active');
                    btn.classList.remove('menu-open');
                } else {
                    if (headerNav) headerNav.classList.add('active');
                    if (landingNavLinks) landingNavLinks.classList.add('active');
                    btn.classList.add('menu-open');
                }
            });
        });
    }

    // Close mobile menu when clicking a link
    const mobileLinks = document.querySelectorAll('.header-nav .nav-link, .landing-nav-links .nav-link, .landing-nav-links .btn');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (headerNav) headerNav.classList.remove('active');
            if (landingNavLinks) landingNavLinks.classList.remove('active');
            mobileMenuButtons.forEach(btn => btn.classList.remove('menu-open'));
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (headerNav && headerNav.classList.contains('active')) {
            if (!headerNav.contains(e.target) && ![...mobileMenuButtons].some(btn => btn.contains(e.target))) {
                headerNav.classList.remove('active');
                mobileMenuButtons.forEach(btn => btn.classList.remove('menu-open'));
            }
        }
        if (landingNavLinks && landingNavLinks.classList.contains('active')) {
            if (!landingNavLinks.contains(e.target) && ![...mobileMenuButtons].some(btn => btn.contains(e.target))) {
                landingNavLinks.classList.remove('active');
                mobileMenuButtons.forEach(btn => btn.classList.remove('menu-open'));
            }
        }
    });

    // Profile Dropdown Toggle
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    // Landing Page Navigation Scroll Effect
    const landingNav = document.querySelector('.landing-nav');
    if (landingNav && !document.body.classList.contains('auth-page')) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                landingNav.classList.add('scrolled');
            } else {
                landingNav.classList.remove('scrolled');
            }
        });
    }

    // Toggle Sidebar
    if (searchBtn && searchSidebar) {
        searchBtn.addEventListener('click', () => searchSidebar.classList.add('open'));
    }
    if (closeSearch && searchSidebar) {
        closeSearch.addEventListener('click', () => searchSidebar.classList.remove('open'));
    }

    // Year Range display
    if (yearRange && yearValue) {
        yearRange.addEventListener('input', (e) => {
            yearValue.textContent = e.target.value;
        });
    }

    // Toast Helper
    const showToast = (message, type = 'success') => {
        if (!toast) return;
        toast.textContent = message;
        toast.className = `toast ${type} show`;
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    };

    // Advanced Search Logic
    if (applyFilters && booksTableBody) {
        applyFilters.addEventListener('click', async () => {
            const keyword = document.getElementById('searchInput').value;
            const categorySelect = document.getElementById('categoryFilter');
            const category = categorySelect ? categorySelect.value : '';
            const year = yearRange ? yearRange.value : '';

            const params = new URLSearchParams({
                keyword,
                category,
                year
            });

            try {
                const response = await fetch(`api/search_books.php?${params.toString()}`);
                const data = await response.json();

                if (data.success) {
                    renderBooks(data.books);
                    if (searchSidebar) searchSidebar.classList.remove('open');
                    showToast(`Found ${data.books.length} books`);
                } else {
                    showToast(data.message || 'Error searching books', 'error');
                }
            } catch (error) {
                showToast('Network error', 'error');
            }
        });
    }

    const renderBooks = (books) => {
        if (!booksTableBody) return;
        if (books.length === 0) {
            booksTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No books found matching your criteria.</td></tr>';
            return;
        }

        const isAdmin = !!document.querySelector('#addBookBtn');

        booksTableBody.innerHTML = books.map(book => {
            let statusBtn = '';
            let actionButtons = `
                <button class="btn view-book" data-id="${book.id}" title="View Details">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>`;

            if (isAdmin) {
                actionButtons += `
                    <button class="btn edit-book" data-book='${JSON.stringify(book).replace(/'/g, "&apos;")}' title="Edit Book">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4L18.5 2.5z"></path></svg>
                    </button>
                    <button class="btn delete-book" data-id="${book.id}" title="Delete Book">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                `;

                if (book.status === 'Pending') {
                    statusBtn = `
                        <button class="btn update-status" data-id="${book.id}" data-status="Issued" style="font-size: 0.7rem; padding: 0.3rem 0.6rem; background: var(--success); color: white;">Approve</button>
                        <button class="btn update-status" data-id="${book.id}" data-status="Available" style="font-size: 0.7rem; padding: 0.3rem 0.6rem; background: var(--danger); color: white;">Reject</button>
                    `;
                } else {
                    const nextStatus = book.status === 'Available' ? 'Issued' : 'Available';
                    const btnLabel = book.status === 'Available' ? 'Mark Issued' : 'Mark Available';
                    statusBtn = `<button class="btn update-status" data-id="${book.id}" data-status="${nextStatus}" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; border: 1px solid var(--border);">${btnLabel}</button>`;
                }
            } else if (book.status === 'Available') {
                statusBtn = `<button class="btn update-status" data-id="${book.id}" data-status="Pending" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; background: var(--accent); color: white;">Book Now</button>`;
            } else if (book.status === 'Pending') {
                statusBtn = `<span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">Wait for approval</span>`;
            }

            return `
                <tr>
                    <td style="font-family: monospace;">${book.isbn}</td>
                    <td style="font-weight: 600;">${book.title}</td>
                    <td>${book.author_name}</td>
                    <td><span style="background: var(--sidebar-bg); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">${book.category_name}</span></td>
                    <td>
                        <span class="status-badge status-${book.status.toLowerCase()}">
                            ${book.status}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            ${actionButtons}
                            ${statusBtn}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    // Table Actions Delegation
    if (booksTableBody) {
        booksTableBody.addEventListener('click', async (e) => {
            const target = e.target.closest('button');
            if (!target) return;

            const id = target.dataset.id;

            // Status Update / Booking
            if (target.classList.contains('update-status')) {
                const status = target.dataset.status;
                const fd = new FormData();
                fd.append('book_id', id);
                fd.append('status', status);

                try {
                    const res = await fetch('api/update_status.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message);
                        refreshData();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (err) {
                    showToast('Connection error', 'error');
                }
            }

            // Delete Book
            if (target.classList.contains('delete-book')) {
                if (!confirm('Are you sure you want to delete this book?')) return;
                const fd = new FormData();
                fd.append('book_id', id);
                try {
                    const res = await fetch('api/delete_book.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message);
                        refreshData();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (err) { showToast('Delete failed', 'error'); }
            }

            // Edit Book Modal
            if (target.classList.contains('edit-book')) {
                const book = JSON.parse(target.dataset.book);
                openBookModal(book);
            }
        });
    }

    const refreshData = () => {
        if (applyFilters) {
            applyFilters.click();
        } else {
            // Basic fetch if search btn not present (e.g. catalog page)
            (async () => {
                const res = await fetch('api/search_books.php');
                const data = await res.json();
                if (data.success) renderBooks(data.books);
            })();
        }
        loadStats();
    };

    // Book Modal Management
    const addBookBtn = document.getElementById('addBookBtn');
    const bookModal = document.getElementById('bookModal');
    const cancelBook = document.getElementById('cancelBook');
    const bookForm = document.getElementById('bookForm');
    const modalTitle = document.getElementById('modalTitle');
    const modalBookId = document.getElementById('modalBookId');

    const openBookModal = async (book = null) => {
        if (!bookModal) return;
        bookModal.style.display = 'flex';
        await populateModalSelects();

        if (book) {
            modalTitle.textContent = 'Edit Book Details';
            modalBookId.value = book.id;
            bookForm.isbn.value = book.isbn;
            bookForm.title.value = book.title;
            bookForm.author_id.value = book.author_id;
            bookForm.category_id.value = book.category_id;
            bookForm.publish_year.value = book.publish_year;
        } else {
            modalTitle.textContent = 'Add New Book';
            modalBookId.value = '';
            bookForm.reset();
        }
    };

    if (addBookBtn) {
        addBookBtn.addEventListener('click', () => openBookModal());
    }

    if (cancelBook) {
        cancelBook.addEventListener('click', () => {
            bookModal.style.display = 'none';
            bookForm.reset();
        });
    }

    const populateModalSelects = async () => {
        const [catRes, authRes] = await Promise.all([
            fetch('api/get_categories.php'),
            fetch('api/get_authors.php')
        ]);
        const catData = await catRes.json();
        const authData = await authRes.json();

        if (catData.success) {
            const select = document.getElementById('categorySelect');
            if (select) select.innerHTML = catData.categories.map(c => `<option value="${c.id}">${c.category_name}</option>`).join('');
        }
        if (authData.success) {
            const select = document.getElementById('authorSelect');
            if (select) select.innerHTML = authData.authors.map(a => `<option value="${a.id}">${a.author_name}</option>`).join('');
        }
    };

    if (bookForm) {
        bookForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(bookForm);
            const isEdit = !!modalBookId.value;
            const endpoint = isEdit ? 'api/update_book.php' : 'api/save_book.php';

            try {
                const response = await fetch(endpoint, { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    showToast(data.message);
                    bookModal.style.display = 'none';
                    bookForm.reset();
                    refreshData();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Action failed', 'error');
            }
        });
    }

    // Fetch categories on load for search filter
    const loadCategories = async () => {
        const select = document.getElementById('categoryFilter');
        if (!select) return;
        const response = await fetch('api/get_categories.php');
        const data = await response.json();
        if (data.success) {
            select.innerHTML = '<option value="">All Categories</option>' +
                data.categories.map(cat => `<option value="${cat.id}">${cat.category_name}</option>`).join('');
        }
    };

    // Fetch analytics and render charts
    const loadStats = async () => {
        const chart = document.getElementById('genreChart');
        if (!chart) return;
        try {
            const response = await fetch('api/get_stats.php');
            const data = await response.json();

            if (data.success) {
                const maxVal = Math.max(...data.categories.map(c => c.count)) || 1;
                chart.innerHTML = data.categories.map(c => `
                    <div class="bar-row">
                        <div class="bar-label" title="${c.category_name}">${c.category_name}</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: ${(c.count / maxVal) * 100}%"></div>
                        </div>
                        <div class="bar-value">${c.count}</div>
                    </div>
                `).join('');

                if (data.availability) {
                    const total = data.availability.reduce((acc, curr) => acc + parseInt(curr.count), 0);
                    const available = data.availability.find(a => a.status === 'Available')?.count || 0;
                    const issued = data.availability.find(a => a.status === 'Issued')?.count || 0;
                    const authorsCount = data.categories.length; // Approximate from categories result for brevity

                    const card1 = document.querySelector('.stat-card:nth-child(1) .value');
                    const card2 = document.querySelector('.stat-card:nth-child(2) .value');
                    const card3 = document.querySelector('.stat-card:nth-child(3) .value');
                    const card4 = document.querySelector('.stat-card:nth-child(4) .value');
                    if (card1) card1.textContent = total;
                    if (card2) card2.textContent = available;
                    if (card3) card3.textContent = authorsCount;
                    if (card4) card4.textContent = issued;
                }
            }
        } catch (error) {
            console.error('Failed to load stats', error);
        }
    };

    loadCategories();
    loadStats();

    // Initial load
    if (booksTableBody) {
        refreshData();
    }
});
