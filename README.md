# ✨ LuminaLib LMS

**LuminaLib** is a premium, high-performance Library Management System built with a focus on modern aesthetics and a fully reactive user experience. It leverages a robust PHP/MySQL backend and a 100% AJAX-driven frontend to provide a seamless, Single-Page Application (SPA) feel.

---

## 🚀 Key Features

### 👤 Role-Based Access Control
- **Admin**: Full control over the library ecosystem. Manage books (Create, Read, Update, Delete), manage authors and categories, and view system analytics.
- **Patron (Visitor)**: Browse the library catalog, use advanced search filters, and "Book Now" (Issue) available assets.

### 📊 Modern Dashboard & Analytics
- **Live Stats**: Real-time counters for Total Books, Availability, Authors, and Active Borrowers.
- **Data Visualization**: Dynamic "Genre Distribution" bar chart that updates automatically as books are added or categorized.
- **Recent Acquisitions**: A live-updating table showing the latest assets added to the library.

### 🌗 Premium UI/UX
- **Dark & Light Mode**: A sleek theme system with smooth transitions and persistence (remembers your choice via LocalStorage).
- **Anti-Flicker Technology**: Optimized head-scripts prevent theme "flashing" on page reloads.
- **Glassmorphism Design**: Modern UI components with subtle transparency and professional shadows.

### 📚 Advanced Management
- **100% AJAX CRUD**: Add, Edit, and Delete books without ever refreshing the page.
- **Booking Validation**: Bookings made by Patrons enter a "Pending" state and must be manually **Approved** or **Rejected** by an Admin.
- **Intelligent Search**: Slide-out filtering sidebar for deep searches by Title, ISBN, Category, and Publication Year.
- **Auto-Initializing DB**: The system automatically creates the database, tables, and seeds default data on the first run.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 8.x (Modular API Architecture) |
| **Database** | MySQL / MariaDB (PDO Secure Connection) |
| **Frontend** | Vanilla JS (Fetch API), CSS3 (Variables & Grid) |
| **Aesthetics** | Google Fonts (Inter & Outfit), Dark Mode Support |

---

## 📥 Installation

1. **Prerequisites**: Ensure you have a running PHP server (Apache/Nginx) and a MySQL server.
2. **Configuration**: Edit `/config/db.php` if you need to change your MySQL credentials.
   ```php
   $db_host = 'localhost';
   $db_user = 'root'; // Your DB username
   $db_pass = 'YourPassword'; // Your DB password
   ```
3. **Automatic Setup**: Simply navigate to `index.php` in your browser. The system will automatically:
   - Create the `luminalib_db` database.
   - Create all necessary tables (`users`, `books`, `authors`, `categories`, `roles`).
   - Seed the default Admin and Patron accounts.

---

## 🔑 Demo Credentials

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `password123` |
| **Patron** | `patron` | `password123` |

---

## 📂 Project Structure

- `/api/`: JSON backend endpoints for AJAX operations.
- `/assets/`: Centralized CSS styling and the core JavaScript engine (`app.js`).
- `/config/db.php`: Database connection and auto-initialization logic.
- `/includes/`: Reusable helper functions (security, sessions, auth).
- `/index.php`: The main landing page.
- `/login.php`: Dedicated Login page.
- `/register.php`: User Registration page.
- `/dashboard.php`: The main control center.
- `/*.php`: Specific management modules (catalog, authors, categories, logs).

---

## 📜 License
Highly tailored for **Advanced Agentic Coding** workshops. Built with precision by **Antigravity**.
# FSD_Coursework
