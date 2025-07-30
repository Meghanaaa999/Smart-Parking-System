<?php
require '../config/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Real-time data
$total_lots = $conn->query("SELECT COUNT(*) as total FROM parking_lots")->fetch_assoc()['total'];
$available_slots = $conn->query("SELECT COUNT(*) as total FROM slots WHERE status = 'available'")->fetch_assoc()['total'];
$active_reservations = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            line-height: 1.6;
        }

        /* Dashboard container */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            background-color: #ffffff;
            width: 256px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease-in-out;
            position: relative;
            z-index: 10;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-title {
            font-size: 0.875rem;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            padding: 0.25rem;
            border-radius: 50%;
            cursor: pointer;
            color: #6b7280;
            transition: background-color 0.2s ease;
        }

        .sidebar-toggle:hover {
            background-color: #f3f4f6;
        }

        .sidebar.collapsed .sidebar-toggle i {
            transform: rotate(180deg);
        }

        /* Navigation styles */
        .sidebar-nav {
            padding: 1rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: #374151;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-link:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .nav-link.active {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .nav-link.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: #3b82f6;
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .nav-link.active .nav-icon {
            color: #1d4ed8;
        }

        .nav-text {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .content-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        /* Dashboard grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Dashboard cards */
        .dashboard-card {
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-info {
            flex: 1;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .card-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card-icon.emerald {
            background-color: #d1fae5;
            color: #059669;
        }

        .card-icon.blue {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .card-icon.purple {
            background-color: #e9d5ff;
            color: #7c3aed;
        }

        /* Loading animation */
        .loading {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .sidebar.collapsed {
                width: 100%;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .main-content {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar-header {
                padding: 0.75rem;
            }

            .sidebar-title {
                font-size: 1.125rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .dashboard-card {
                padding: 1rem;
            }

            .card-content {
                flex-direction: column;
                gap: 1rem;
            }

            .card-icon {
                align-self: flex-end;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #111827;
                color: #f9fafb;
            }

            .sidebar {
                background-color: #1f2937;
            }

            .sidebar-header {
                border-bottom-color: #374151;
            }

            .sidebar-title {
                color: #f9fafb;
            }

            .nav-link {
                color: #d1d5db;
            }

            .nav-link:hover {
                background-color: #374151;
                color: #f9fafb;
            }

            .dashboard-card {
                background-color: #1f2937;
                border-color: #374151;
            }

            .page-title {
                color: #f9fafb;
            }

            .card-title {
                color: #f9fafb;
            }

            .card-value {
                color: #f9fafb;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="sidebar-title" id="sidebarTitle">Admin Dashboard</h1>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-chevron-left" id="toggleIcon"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt nav-icon"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_lots.php" class="nav-link <?php echo ($current_page == 'manage_lots.php') ? 'active' : ''; ?>">
                            <i class="fas fa-parking nav-icon"></i>
                            <span class="nav-text">Manage Lots</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_slots.php" class="nav-link <?php echo ($current_page == 'manage_slots.php') ? 'active' : ''; ?>">
                            <i class="fas fa-th-large nav-icon"></i>
                            <span class="nav-text">Manage Slots</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="view_reservations.php" class="nav-link <?php echo ($current_page == 'view_reservations.php') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt nav-icon"></i>
                            <span class="nav-text">View Reservations</span>
                        </a>
                    </li>
                    <li class="nav-item" style="margin-top:2rem;">
                        <a href="../auth/logout.php" class="nav-link" style="color:#dc2626;">
                            <i class="fas fa-sign-out-alt nav-icon"></i>
                            <span class="nav-text">Log Out</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h2 class="page-title">Admin Dashboard</h2>
                <p class="page-subtitle">Welcome to your parking management system</p>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="card-info">
                            <h3 class="card-title">Parking Lots</h3>
                            <p class="card-description">Total parking lots</p>
                            <p class="card-value"><?php echo $total_lots; ?></p>
                        </div>
                        <div class="card-icon emerald">
                            <i class="fas fa-parking"></i>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="card-info">
                            <h3 class="card-title">Available Slots</h3>
                            <p class="card-description">Across all lots</p>
                            <p class="card-value"><?php echo $available_slots; ?></p>
                        </div>
                        <div class="card-icon blue">
                            <i class="fas fa-th-large"></i>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="card-info">
                            <h3 class="card-title">Reservations</h3>
                            <p class="card-description">Active reservations</p>
                            <p class="card-value"><?php echo $active_reservations; ?></p>
                        </div>
                        <div class="card-icon purple">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const sidebarTitle = document.getElementById("sidebarTitle");
            const toggleIcon = document.getElementById("toggleIcon");

            // Load saved sidebar state
            const savedState = localStorage.getItem("sidebarCollapsed");
            if (savedState === "true") {
                sidebar.classList.add("collapsed");
                sidebarTitle.textContent = "Admin";
                toggleIcon.classList.remove("fa-chevron-left");
                toggleIcon.classList.add("fa-chevron-right");
            }

            // Toggle sidebar
            sidebarToggle.addEventListener("click", function() {
                sidebar.classList.toggle("collapsed");

                if (sidebar.classList.contains("collapsed")) {
                    sidebarTitle.textContent = "Admin";
                    toggleIcon.classList.remove("fa-chevron-left");
                    toggleIcon.classList.add("fa-chevron-right");
                    localStorage.setItem("sidebarCollapsed", "true");
                } else {
                    sidebarTitle.textContent = "Admin Dashboard";
                    toggleIcon.classList.remove("fa-chevron-right");
                    toggleIcon.classList.add("fa-chevron-left");
                    localStorage.setItem("sidebarCollapsed", "false");
                }
            });

            // Add smooth loading effect for navigation links
            const navLinks = document.querySelectorAll(".nav-link");
            navLinks.forEach(function(link) {
                link.addEventListener("click", function(e) {
                    // Add loading state
                    this.classList.add("loading");
                    setTimeout(() => {
                        this.classList.remove("loading");
                    }, 200);
                });
            });

            // Add keyboard navigation (Ctrl+B to toggle sidebar)
            document.addEventListener("keydown", function(e) {
                if (e.ctrlKey && e.key === "b") {
                    e.preventDefault();
                    sidebarToggle.click();
                }
            });

            // Add responsive behavior
            function handleResize() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove("collapsed");
                    sidebarTitle.textContent = "Admin Dashboard";
                    toggleIcon.classList.remove("fa-chevron-right");
                    toggleIcon.classList.add("fa-chevron-left");
                }
            }

            window.addEventListener("resize", handleResize);
            handleResize(); // Call on initial load

            // Add smooth scroll behavior
            document.documentElement.style.scrollBehavior = 'smooth';

            // Add click animation to dashboard cards
            const dashboardCards = document.querySelectorAll(".dashboard-card");
            dashboardCards.forEach(function(card) {
                card.addEventListener("click", function() {
                    this.style.transform = "scale(0.98)";
                    setTimeout(() => {
                        this.style.transform = "translateY(-2px)";
                    }, 100);
                });
            });
        });
    </script>
</body>
</html>
