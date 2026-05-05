================================================================
  ELEVEN ROOFING DASMA — Complete System v3
  Folder: Elevenroofingdasma
  Database: Elevenroofingdasmadatabase
================================================================

QUICK SETUP (XAMPP):
1. Copy this folder to: C:\xampp\htdocs\Elevenroofingdasma\
2. Start Apache & MySQL in XAMPP Control Panel
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Create database → Import: database/Elevenroofingdasmadatabase.sql
5. Visit: http://localhost/Elevenroofingdasma/public/index.php

DEMO CREDENTIALS (all password: admin123)
  👑 Owner:        owner@elevenroofingdasma.com
  🔵 System Admin: sysadmin@elevenroofingdasma.com
  🟡 Administrator:admin@elevenroofingdasma.com
  🟢 Staff:        staff@elevenroofingdasma.com
  ⚪ Customer:     juan@email.com

ROLE DASHBOARDS:
  Owner        → /owner/index.php
  System Admin → /sysadmin/index.php
  Administrator→ /admin/index.php
  Staff        → /staff/index.php
  Customer     → /user/dashboard.php

FOLDER STRUCTURE:
  /public/      — Public website (Home, Services, Products, About, Contact, Inquiry)
  /auth/        — Login, Register, Logout
  /admin/       — Full admin portal with inventory approval workflow
  /sysadmin/    — Locks, Backup/Restore, Logs, Inventory Monitor
  /owner/       — Full user control, permissions matrix
  /staff/       — Dashboard, Borrow Tools, REQUEST stock (not direct edit)
  /user/        — Customer dashboard, inquiry tracker, chat
  /assets/      — CSS, JS, uploaded images
  /config/      — database.php, session.php
  /includes/    — sidebar.php, helpers.php
  /database/    — SQL schema + backups/

NEW FEATURES (v3):
  ✅ INVENTORY APPROVAL WORKFLOW
     - Staff CANNOT directly edit stock — must submit a request
     - Staff goes to: staff/request_stock.php
     - Admin sees pending requests with badge in sidebar
     - Admin reviews, approves (auto-updates stock) or rejects
     - Full request history with status tracking
     - Sysadmin inventory monitor also shows pending requests

  ✅ REAL STOCK QUANTITIES ON PUBLIC PAGES
     - Products page shows actual unit count (e.g. "240 units")
     - Color-coded: Green = plenty, Yellow = limited, Red = very low
     - Mini stock bar showing % of minimum threshold
     - Homepage product preview also shows actual stock
     - "Out of Stock" shown when 0 units

  ✅ ADMIN TOOLS PAGE (tools_admin.php)
     - Add new tools to the system
     - Record borrows with condition tracking
     - Return tools with condition notes
     - Auto-mark overdue items
     - Tool availability table

ALL FEATURES:
  OWNER:    Full access, activate/deactivate/lock any user,
            change roles, delete accounts, permissions matrix
  SYSADMIN: Feature locks (6 features), DB backup/restore,
            activity logs, inventory monitor, user management
  ADMIN:    Inquiries (reply/approve), services + products
            (image upload), inventory APPROVAL, tools,
            chatbot Q&A, live chat, about/contact editor,
            reports, contact messages
  STAFF:    Dashboard, borrow tools, REQUEST stock changes
            (needs admin approval), live chat agent,
            view-only inventory & products
  CUSTOMER: Send inquiries, track replies, AI chatbot,
            human escalation live chat
================================================================
