<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

if (!isAdmin()) {
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
    <title>Booking Requests | LuminaLib</title>
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
                    <h1 class="page-title">Booking Requests</h1>
                    <p class="page-subtitle">Approve or reject book reservations.</p>
                </div>
            </div>

            <div class="table-container">
                <table id="bookingsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Book Title</th>
                            <th>Request Details</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
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
            const bookingsTableBody = document.getElementById('bookingsTableBody');
            const toast = document.getElementById('toast');

            // Define showToast locally as it's not exposed globally by app.js
            const showToast = (message, type = 'success') => {
                if (!toast) return;
                toast.textContent = message;
                toast.className = `toast ${type} show`;
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            };
            
            // Event Delegation for Table Actions
            bookingsTableBody.addEventListener('click', async (e) => {
                const btn = e.target.closest('.btn'); 
                if (!btn) return;

                const id = btn.dataset.id;
                if (!id) return;

                // Stop propagation to prevent any row-click events if they exist
                e.stopPropagation();

                if (btn.classList.contains('approve-booking')) {
                    await updateBooking(id, 'Approved');
                } else if (btn.classList.contains('reject-booking')) {
                    if(confirm('Are you sure you want to reject this request?')) {
                        await updateBooking(id, 'Rejected');
                    }
                } else if (btn.classList.contains('approve-putback')) {
                    await updateBooking(id, 'Returned');
                }
            });

            const loadBookings = async () => {
                try {
                    const res = await fetch(`api/get_bookings.php?t=${Date.now()}`);
                    const data = await res.json();
                    
                    if (data.success) {
                        if (data.bookings.length === 0) {
                            bookingsTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem;">No booking requests found.</td></tr>';
                            return;
                        }

                        bookingsTableBody.innerHTML = data.bookings.map(b => `
                            <tr>
                                <td>${b.id}</td>
                                <td style="font-weight: 600;">${b.username}</td>
                                <td>${b.title}</td>
                                <td>
                                    <div style="font-size: 0.875rem;">
                                        <strong>${b.purpose || 'N/A'}</strong> (${b.duration || 'N/A'})
                                        <div style="color: var(--text-muted); margin-top: 0.25rem;">${b.message || 'No message'}</div>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.8125rem;">${new Date(b.booking_date).toLocaleString()}</td>
                                <td><span class="status-tag ${b.status.toLowerCase()}">${b.status}</span></td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 0.5rem; align-items: center;">
                                        ${getStatusButtons(b)}
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        console.error('Failed to load bookings:', data.message);
                    }
                } catch (err) {
                    console.error('Error loading bookings:', err);
                }
            };

            const getStatusButtons = (b) => {
                if (b.status === 'Pending') {
                    return `
                        <button class="btn approve-booking" data-id="${b.id}" title="Approve Request" style="color: #10b981;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </button>
                        <button class="btn reject-booking" data-id="${b.id}" title="Reject Request" style="color: #ef4444;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    `;
                } else if (b.status === 'Returning') {
                    return `
                        <button class="btn btn-primary approve-putback" data-id="${b.id}" title="Confirm Putback" style="padding: 0.4rem 0.8rem; font-size: 0.8125rem; height: 34px; display: flex; align-items: center; gap: 0.4rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"></path><path d="M4 9h12.5A5.5 5.5 0 0 1 22 14.5v0A5.5 5.5 0 0 1 16.5 20H11"></path></svg>
                            <span>Confirm Putback</span>
                        </button>
                    `;
                } else if (b.status === 'Approved') {
                    return `
                        <button class="btn btn-secondary approve-putback" data-id="${b.id}" title="Mark as Returned" style="padding: 0.4rem 0.8rem; font-size: 0.8125rem; height: 34px; display: flex; align-items: center; gap: 0.4rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"></path><path d="M4 9h12.5A5.5 5.5 0 0 1 22 14.5v0A5.5 5.5 0 0 1 16.5 20H11"></path></svg>
                            <span>Return Book</span>
                        </button>
                    `;
                }
                return '-';
            };

            const updateBooking = async (id, status) => {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('status', status);
                
                try {
                    const res = await fetch('api/update_booking_status.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    
                    if (data.success) {
                        showToast(data.message);
                        await loadBookings(); // Refresh table
                    } else {
                        showToast(data.message || 'Error updating status', 'error');
                    }
                } catch (err) {
                    console.error('Update failed:', err);
                    showToast('Connection error', 'error');
                }
            };

            // Initial Load
            loadBookings();
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
