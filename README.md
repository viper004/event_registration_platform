# 🎓 College Event Registration Platform (PHP & MySQL for XAMPP)

A simple, beginner-friendly PHP website where users can view upcoming college events (workshops, hackathons, seminars), register for an event, and receive a registration summary. Administrators can log in to view and delete student registrations and create/manage events.

---

## 💻 Tech Stack
- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript
- **Database:** MySQL

---

## 📂 Project Structure
```
php_project/
├── index.php              # Homepage: Lists upcoming events + "Register" button
├── signup.php             # User registration (Create account)
├── login.php              # User login
├── logout.php             # User logout handler
├── my_reservations.php    # User reservations tracking dashboard (view tickets, cancel)
├── cancel_reservation.php # User cancellation handler
├── register.php           # Event registration form (authenticated, pre-filled)
├── success.php            # Confirmation page with ticket details & print option
├── db.php                 # MySQL connection configured for XAMPP defaults (root, no password)
├── database.sql           # Database schema & sample events/admin/user credentials
├── style.css              # Clean, modern CSS styling
├── script.js              # Client-side form validation & utilities
├── admin_login.php        # Admin login page
├── admin_logout.php       # Admin logout handler
├── admin_dashboard.php    # Admin redirect to manage events
├── admin_view.php         # Admin view registration details
├── admin_delete.php       # Admin delete registration action
├── admin_events.php           # Admin manage events list
├── admin_event_attendees.php  # View attendee details for an event in a new tab
├── admin_create_event.php     # Admin create new event form
├── admin_delete_event.php # Admin delete event action
└── README.md              # Setup instructions
```

---

## 🚀 How to Run in XAMPP (Step-by-Step)

### Step 1: Copy Project Folder to `htdocs`
Copy this `php_project` folder directly into your XAMPP `htdocs` directory:
- **Windows:** `C:\xampp\htdocs\php_project`
- **Linux:** `/opt/lampp/htdocs/php_project`
- **Mac:** `/Applications/XAMPP/xamppfiles/htdocs/php_project`

### Step 2: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Open in Browser
Open your browser and navigate to:
```
http://localhost/php_project/
```

> **Note:** The database `event_db` and sample data are automatically created when you open the page! If you prefer importing manually, you can open phpMyAdmin at `http://localhost/phpmyadmin/` and import `database.sql`.

---

## 👤 User Login (Sample Account)
- **Login URL:** `http://localhost/php_project/login.php`
- **Email:** `rahul@gmail.com`
- **Password:** `user123`
*(Users can also create new accounts via `signup.php`)*

---

## 🔐 Administrator Login
- **Login URL:** `http://localhost/php_project/admin_login.php`
- **Username:** `admin`
- **Password:** `admin123`

---

## 📋 Features Checklist
- [x] **User Accounts & Authentication:** Sign up (`signup.php`), Login (`login.php`), Logout (`logout.php`) with secure password hashing (`password_hash`).
- [x] **Reservation Tracking:** Dedicated dashboard (`my_reservations.php`) where users can view all their event bookings, dates, venues, status, print tickets, and cancel reservations.
- [x] **Duplicate Booking Protection:** Prevents users from registering twice for the same event and links to their existing ticket.
- [x] **Homepage:** Lists upcoming events with name, date, venue, description, and "Register" button.
- [x] **Event Registration Form:** Pre-fills user details upon login and links bookings directly to the user's account.
- [x] **Success Page:** Confirmation message with event details, ticket number, and direct link to My Reservations.
- [x] **Admin Event Management & Registrations:** View all events, see full tabular lists of registered attendees in a new tab with the event name title, search attendees, and delete events/registrations.
