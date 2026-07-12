<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ $storeSetting->name ?? 'Inventory App' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
        }

        * { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif; }
        
        body { 
            background: var(--light); 
            margin: 0; 
            color: #1f2937;
        }

        /* SIDEBAR MODERNISASI */
        .sidebar { 
            min-height: 100vh; 
            background: linear-gradient(180deg, var(--dark) 0%, #111827 100%);
            overflow-y: scroll; 
            box-sizing: border-box; 
            display: flex; 
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .sidebar a { 
            color: #cbd5e1;
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 0.875rem; 
            padding: 0.875rem 1.25rem; 
            border-radius: 8px; 
            transition: all 0.25s ease; 
            font-size: 0.95rem;
            font-weight: 500;
            margin: 0.25rem 0.5rem;
        }

        .sidebar a:hover { 
            background: rgba(99, 102, 241, 0.15);
            color: #fff; 
            transform: translateX(3px);
            padding-left: 1.5rem;
        }

        .sidebar a.active { 
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #fff; 
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .sidebar a i { 
            min-width: 20px; 
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar .brand { 
            color: #fff; 
            font-weight: 700; 
            padding: 1.5rem 1.25rem; 
            display: flex; 
            align-items: center; 
            gap: 0.875rem; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .sidebar .brand img { 
            width: 40px; 
            height: 40px; 
            object-fit: contain; 
            border-radius: 6px; 
            background: #fff; 
            padding: 2px;
        }

        .sidebar .brand span { 
            font-size: 1rem; 
            line-height: 1.3;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .sidebar .menu-section { 
            margin-top: 1.5rem; 
        }

        .sidebar .menu-label { 
            font-size: 0.7rem; 
            font-weight: 700; 
            color: #94a3b8;
            text-transform: uppercase; 
            padding: 1rem 1.5rem 0.75rem; 
            letter-spacing: 0.8px;
        }

        .sidebar hr { 
            margin: 1.5rem 0; 
            border-color: rgba(255,255,255,0.08);
        }

        /* MAIN LAYOUT */
        .main-wrapper { 
            display: flex; 
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .topbar h4 i {
            font-size: 1.75rem;
            color: var(--primary);
        }

        main.main-content {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        /* PROFILE AVATAR */
        .profile-avatar-btn { 
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
            gap: 1rem;
        }

        .profile-avatar-btn:hover { 
            transform: scale(1.05);
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 0.9rem;
        }

        .profile-info .name {
            font-weight: 600;
            color: var(--dark);
        }

        .profile-info .role {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .profile-avatar { 
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            border: 2px solid var(--primary);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .profile-avatar:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .dropdown-menu { 
            min-width: 280px;
            margin-top: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            padding: 0.5rem 0;
        }

        .dropdown-header { 
            padding: 1rem 1.25rem;
            background: var(--gray-50);
            border-radius: 6px 6px 0 0;
        }

        .dropdown-header strong {
            display: block;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .dropdown-header small {
            color: var(--gray-500);
        }

        .dropdown-item { 
            padding: 0.875rem 1.25rem;
            text-decoration: none;
            color: var(--gray-700);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dropdown-item:hover { 
            background-color: var(--gray-100);
            color: var(--primary);
        }

        .dropdown-item.text-danger:hover { 
            background-color: #fee2e2;
            color: var(--danger);
        }

        .dropdown-item i {
            font-size: 1rem;
            width: 20px;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid var(--gray-200);
        }

        /* ALERTS */
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert i {
            font-size: 1.25rem;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-warning {
            background: #fffbeb;
            color: #78350f;
            border-left: 4px solid var(--warning);
        }

        .alert-danger {
            background: #fef2f2;
            color: #7f1d1d;
            border-left: 4px solid var(--danger);
        }

        .alert-info {
            background: #ecf9ff;
            color: #0c4a6e;
            border-left: 4px solid var(--info);
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .alert li {
            margin-bottom: 0.25rem;
        }

        /* CARDS MODERN */
        .card {
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.25s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            padding: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            border-radius: 9px 9px 0 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header i {
            font-size: 1.25rem;
            color: var(--primary);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-stat {
            border: none;
            border-radius: 10px;
            background: #fff;
            border: 1px solid var(--gray-200);
            transition: all 0.25s ease;
        }

        .card-stat:hover {
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
        }

        .card-stat .card-body {
            padding: 1.5rem;
        }

        .card-stat .text-muted {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-600);
            margin-bottom: 0.75rem;
        }

        .card-stat .fs-3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .card-stat .fs-4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success);
        }

        /* BUTTONS MODERN */
        .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: #fff;
        }

        .btn-secondary:hover {
            background: #7c3aed;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-outline-primary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-sm {
            padding: 0.5rem 0.875rem;
            font-size: 0.85rem;
        }

        .btn i {
            margin-right: 0.375rem;
        }

        /* TABLES MODERN */
        .table {
            background: #fff;
        }

        .table thead {
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
        }

        .table thead th {
            color: var(--gray-700);
            font-weight: 700;
            padding: 1rem 1.25rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        .table-danger {
            background: #fee2e2 !important;
        }

        /* FORMS MODERN */
        .form-control, .form-select {
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        /* BADGES */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        .badge.bg-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.bg-danger {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .badge.bg-warning {
            background: #fef3c7;
            color: #78350f;
        }

        .badge.bg-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        /* FOOTER */
        footer {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        /* SIDEBAR TOGGLE */
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
            padding: 0.5rem;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            display: flex;
        }

        .sidebar-toggle:hover {
            color: var(--primary);
        }

        /* SIDEBAR COLLAPSED STATE */
        .sidebar.collapsed {
            width: 80px !important;
        }

        .sidebar.collapsed .brand {
            flex-direction: column;
            text-align: center;
            padding: 1rem 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar.collapsed .brand img {
            width: 35px;
            height: 35px;
        }

        .sidebar.collapsed .brand span {
            display: none;
        }

        .sidebar.collapsed a {
            justify-content: center;
            padding: 0.875rem 0.5rem;
            text-align: center;
            margin: 0.25rem 0;
        }

        .sidebar.collapsed a span {
            display: none;
        }

        .sidebar.collapsed .menu-label {
            display: none;
        }

        .sidebar.collapsed .menu-section {
            margin-top: 1rem;
        }

        .sidebar.collapsed hr {
            margin: 1rem 0;
        }

        /* MOBILE MENU TOGGLE */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark);
            padding: 0.5rem;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            color: var(--primary);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* RESPONSIVE - DESKTOP (992px and up) */
        @media (min-width: 992px) {
            .sidebar.collapsed {
                padding: 1rem 0.5rem !important;
            }

            .sidebar.collapsed .brand img {
                margin: 0 auto;
            }

            .sidebar.collapsed a {
                margin: 0.25rem auto;
            }

            .sidebar.collapsed i {
                font-size: 1.25rem;
                min-width: unset;
            }

            .sidebar.collapsed .menu-section {
                margin-top: 1.5rem;
            }
        }

        /* RESPONSIVE - TABLET (992px and down) */
        @media (max-width: 991px) {
            nav.sidebar {
                width: 260px;
            }

            main.main-content {
                padding: 1.5rem;
            }

            .topbar {
                padding: 0.75rem 1.5rem;
            }

            .topbar h4 {
                font-size: 1.35rem;
            }
        }

        /* RESPONSIVE - MOBILE (768px and down) */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }

            nav.sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 280px;
                height: 100vh;
                z-index: 1001;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 2px 0 8px rgba(0,0,0,0.15);
                overflow-y: auto;
            }

            nav.sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                flex-direction: column;
            }

            main.main-content {
                padding: 1rem;
                max-width: 100%;
            }

            .topbar {
                padding: 0.75rem 1rem;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .topbar h4 {
                font-size: 1.1rem;
                margin: 0;
            }

            .topbar h4 i {
                font-size: 1.35rem;
            }

            .profile-info {
                display: none;
            }

            .profile-avatar {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .card {
                border-radius: 8px;
            }

            .card-body {
                padding: 1rem;
            }

            .card-header {
                padding: 1rem;
                font-size: 0.95rem;
            }

            .btn {
                padding: 0.55rem 1rem;
                font-size: 0.85rem;
            }

            .btn-sm {
                padding: 0.4rem 0.7rem;
                font-size: 0.8rem;
            }

            /* Forms on mobile */
            .form-control, .form-select {
                padding: 0.7rem 0.875rem;
                font-size: 0.9rem;
            }

            .form-label {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }

            .input-group {
                flex-wrap: wrap;
            }

            /* Tables responsive */
            .table-responsive {
                margin: -1rem -1rem 0 -1rem;
            }

            .table {
                font-size: 0.85rem;
            }

            .table th {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }

            .table td {
                padding: 0.75rem 0.5rem;
            }

            .table code {
                font-size: 0.75rem !important;
            }

            /* Dashboard stats - stack on mobile */
            .row.g-4 {
                gap: 1rem !important;
            }

            /* Alert and badges */
            .alert {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 1rem;
                gap: 0.5rem;
            }

            .alert i {
                font-size: 1rem;
            }

            .badge {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            /* Dropdown menu */
            .dropdown-menu {
                min-width: 220px;
                max-width: 85vw;
            }

            .dropdown-item {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .dropdown-header {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            /* Modals */
            .modal-dialog {
                margin: 0.5rem auto;
            }

            .modal-content {
                border-radius: 8px;
            }

            .modal-header {
                padding: 1rem;
            }

            .modal-body {
                padding: 1rem;
                font-size: 0.9rem;
            }

            .modal-footer {
                padding: 1rem;
            }

            /* Flex direction on mobile */
            .d-flex {
                flex-direction: column;
            }

            .d-flex.gap-2, .d-flex.gap-3, .d-flex.gap-4 {
                gap: 0.5rem !important;
            }

            /* Adjust button widths */
            a.btn, button.btn {
                min-width: auto;
            }

            .btn-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            /* Text adjustments */
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.35rem; }
            h3 { font-size: 1.2rem; }
            h4 { font-size: 1.1rem; }
            h5 { font-size: 1rem; }
            h6 { font-size: 0.95rem; }

            /* Sidebar menu text visibility */
            .sidebar a span {
                display: inline;
            }

            /* Card stat adjustments */
            .card-stat {
                border-radius: 8px;
            }

            /* Profile section in mobile */
            .profile-avatar-btn {
                gap: 0.5rem;
            }

            /* Form filters mobile */
            .card-header > form {
                flex-direction: column;
            }

            .card-header > form > div {
                flex-direction: column;
                width: 100% !important;
            }

            .card-header > form > div > input,
            .card-header > form > div > select,
            .card-header > form > div > button,
            .card-header > form > div > a {
                width: 100%;
            }

            .form-check {
                display: flex;
                flex-wrap: wrap;
            }

            .form-check-label {
                white-space: nowrap;
            }

            /* Position relative adjustments for mobile */
            .position-relative {
                width: 100% !important;
                min-width: 100% !important;
            }

            .position-relative + select,
            .position-relative + button,
            .position-relative + a {
                width: 100%;
            }

            /* Button groups mobile friendly */
            .btn-group-sm > .btn {
                padding: 0.35rem 0.6rem;
                font-size: 0.75rem;
            }

            /* Allow wrapping of flex items in cards */
            div.d-flex[style*="justify-content: space-between"] {
                flex-wrap: wrap;
            }

            /* Make action buttons stack vertically on mobile */
            .btn-group.btn-group-sm {
                display: flex !important;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .btn-group.btn-group-sm > .btn {
                flex: 0 1 auto;
                width: auto;
                padding: 0.3rem 0.5rem;
            }

            /* Improve select dropdowns */
            select.form-select {
                width: 100%;
            }

            /* Input group mobile */
            .input-group {
                width: 100%;
            }

            .input-group > input,
            .input-group > select {
                min-width: 0;
            }

            /* Card footer mobile */
            .card-footer {
                padding: 0.75rem !important;
            }

            .card-footer .d-flex {
                flex-direction: column !important;
            }

            /* Pagination links mobile */
            nav[role="navigation"] {
                overflow-x: auto;
            }

            /* Improve text display in cells */
            td strong {
                display: block;
                word-wrap: break-word;
                word-break: break-word;
            }

            td small {
                display: block;
            }

            /* Make badges wrap properly */
            .badge {
                display: inline-block;
                word-wrap: break-word;
            }
        }

        /* EXTRA SMALL (576px and down) */
        @media (max-width: 576px) {
            nav.sidebar {
                width: 100%;
                max-width: 280px;
            }

            .topbar {
                padding: 0.5rem 0.75rem;
            }

            .topbar h4 {
                font-size: 1rem;
                font-weight: 600;
            }

            .topbar h4 i {
                font-size: 1.2rem;
            }

            main.main-content {
                padding: 0.75rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            .card-header {
                padding: 0.75rem;
                font-size: 0.9rem;
                border-radius: 6px 6px 0 0;
            }

            .btn {
                padding: 0.5rem 0.875rem;
                font-size: 0.8rem;
                width: 100%;
                white-space: nowrap;
            }

            .btn-group {
                gap: 0.25rem;
            }

            /* Grid adjustments for very small screens */
            .col-md-3, .col-md-6, .col-lg-6 {
                min-width: 100% !important;
            }

            .form-control, .form-select {
                padding: 0.6rem 0.75rem;
                font-size: 0.85rem;
            }

            .table {
                font-size: 0.8rem;
            }

            .table th {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
            }

            .table td {
                padding: 0.5rem 0.25rem;
            }

            /* Icons smaller on very small screens */
            i.bi {
                font-size: 0.95rem;
            }

            .sidebar a i {
                min-width: 16px;
                font-size: 0.95rem;
            }

            .alert {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
                gap: 0.25rem;
            }

            .alert i {
                font-size: 0.9rem;
            }

            .badge {
                padding: 0.35rem 0.6rem;
                font-size: 0.7rem;
            }

            h1 { font-size: 1.35rem; }
            h2 { font-size: 1.2rem; }
            h3 { font-size: 1.1rem; }
            h4 { font-size: 1rem; }
            h5 { font-size: 0.95rem; }
            h6 { font-size: 0.9rem; }

            /* Hide less important info on extra small screens */
            .text-muted.small {
                font-size: 0.8rem;
            }

            /* Adjust dropdown */
            .dropdown-menu {
                min-width: 200px;
                max-width: 90vw;
            }

            /* Responsive flex for dashboard items */
            div[style*="padding: 1.25rem; border-bottom"] {
                flex-wrap: wrap;
                padding: 0.75rem !important;
            }

            div[style*="padding: 1.25rem; border-bottom"] > div {
                margin-bottom: 0.5rem;
            }

            div[style*="padding: 1.25rem; border-bottom"] a.btn {
                width: auto;
            }

            /* Make inline stat items responsive */
            .d-flex.justify-content-between.align-items-start {
                flex-wrap: wrap;
            }

            /* Card stat responsive */
            .card-stat {
                border-radius: 8px;
            }

            /* Adjust inline width styles for mobile */
            div[style*="width: 50px"] {
                width: 40px !important;
                height: 40px !important;
            }

            div[style*="width: 50px"] i {
                font-size: 1.2rem !important;
            }
        }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="main-wrapper">
    <!-- SIDEBAR MODERN -->
    <nav class="sidebar p-3" id="mainSidebar" style="width: 280px; flex-shrink: 0; transition: width 0.3s ease, padding 0.3s ease;">
        <div class="brand">
            @if(!empty($storeSetting->logo_path))
                <img src="{{ asset($storeSetting->logo_path) }}" alt="Logo {{ $storeSetting->name }}">
            @else
                <i class="bi bi-box-seam"></i>
            @endif
            <span>{{ $storeSetting->name ?? 'Inventory' }}</span>
        </div>

        <!-- DASHBOARD -->
        @if(auth()->user()->canAccess('dashboard.view'))
            <div class="menu-section">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> 
                    <span>Dashboard</span>
                </a>
            </div>
        @endif

        <!-- MASTER DATA -->
        @if(auth()->user()->canAccess('items.view') || auth()->user()->canAccess('categories.manage') || auth()->user()->canAccess('suppliers.manage'))
            <div class="menu-section">
                <div class="menu-label">Master Data</div>
                @if(auth()->user()->canAccess('items.view'))
                    <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i> <span>Barang</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('categories.manage'))
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tag"></i> <span>Kategori</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('suppliers.manage'))
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i> <span>Supplier</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- TRANSAKSI -->
        @if(auth()->user()->canAccess('sales.view') || auth()->user()->canAccess('sales.create') || auth()->user()->canAccess('stock_movements.view') || auth()->user()->canAccess('purchase_orders.view') || auth()->user()->canAccess('store_settings.manage'))
            <div class="menu-section">
                <div class="menu-label">Transaksi</div>
                @if(auth()->user()->canAccess('sales.view') || auth()->user()->canAccess('sales.create'))
                    <a href="{{ auth()->user()->canAccess('sales.view') ? route('sales.index') : route('sales.create') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i> <span>Kasir/Penjualan</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('stock_movements.view'))
                    <a href="{{ route('stock-movements.index') }}" class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i> <span>Stok Masuk/Keluar</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('purchase_orders.view'))
                    <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check"></i> <span>Purchase Order</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('store_settings.manage'))
                    <a href="{{ route('print-files.index') }}" class="{{ request()->routeIs('print-files.*') ? 'active' : '' }}">
                        <i class="bi bi-printer"></i> <span>Cetak Struk</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- LAPORAN & INFORMASI -->
        @if(auth()->user()->canAccess('reports.view') || auth()->user()->canAccess('store_settings.manage'))
            <div class="menu-section">
                <div class="menu-label">Informasi</div>
                @if(auth()->user()->canAccess('reports.view'))
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-chart"></i> <span>Laporan</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('store_settings.manage'))
                    <a href="{{ route('store-settings.edit') }}" class="{{ request()->routeIs('store-settings.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i> <span>Pengaturan Toko</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- ADMIN -->
        @if(auth()->user()->canAccess('users.manage') || auth()->user()->canAccess('role_permissions.manage') || auth()->user()->canAccess('activity_logs.view'))
            <div class="menu-section">
                <div class="menu-label">Admin</div>
                @if(auth()->user()->canAccess('users.manage'))
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> <span>Manajemen User</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('role_permissions.manage'))
                    <a href="{{ route('role-permissions.edit') }}" class="{{ request()->routeIs('role-permissions.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i> <span>Akses Role</span>
                    </a>
                @endif
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i> <span>Manajemen Role</span>
                    </a>
                @endif
                @if(auth()->user()->canAccess('activity_logs.view'))
                    <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> <span>Riwayat Audit</span>
                    </a>
                @endif
            </div>
        @endif

        <hr class="mt-auto mb-0">
    </nav>

    <!-- MAIN CONTENT -->
    <div style="flex-grow: 1; display: flex; flex-direction: column;">
        <!-- TOPBAR -->
        <div class="topbar">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="mobile-menu-toggle" id="mobileMenuToggle" title="Buka Menu">
                    <i class="bi bi-list"></i>
                </button>
                <button class="sidebar-toggle" id="sidebarToggle" title="Sembunyikan Sidebar" style="display: none;">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <h4>
                    @if(request()->routeIs('dashboard'))
                        <i class="bi bi-speedometer2"></i> Dashboard
                    @elseif(request()->routeIs('items.*'))
                        <i class="bi bi-box-seam"></i> Master Barang
                    @elseif(request()->routeIs('categories.*'))
                        <i class="bi bi-tag"></i> Kategori
                    @elseif(request()->routeIs('suppliers.*'))
                        <i class="bi bi-truck"></i> Supplier
                    @elseif(request()->routeIs('sales.*'))
                        <i class="bi bi-cash-coin"></i> Kasir/Penjualan
                    @elseif(request()->routeIs('stock-movements.*'))
                        <i class="bi bi-arrow-left-right"></i> Stok Masuk/Keluar
                    @elseif(request()->routeIs('purchase-orders.*'))
                        <i class="bi bi-clipboard-check"></i> Purchase Order
                    @elseif(request()->routeIs('print-files.*'))
                        <i class="bi bi-printer"></i> Cetak Struk
                    @elseif(request()->routeIs('reports.*'))
                        <i class="bi bi-file-earmark-chart"></i> Laporan
                    @elseif(request()->routeIs('store-settings.*'))
                        <i class="bi bi-gear"></i> Pengaturan Toko
                    @elseif(request()->routeIs('users.*'))
                        <i class="bi bi-people-fill"></i> Manajemen User
                    @elseif(request()->routeIs('role-permissions.*'))
                        <i class="bi bi-shield-lock"></i> Akses Role
                    @elseif(request()->routeIs('activity-logs.*'))
                        <i class="bi bi-journal-text"></i> Riwayat Audit
                    @else
                        @yield('title', 'Dashboard')
                    @endif
                </h4>
            </div>
            
            <!-- USER PROFILE DROPDOWN -->
            <div class="dropdown">
                <button class="profile-avatar-btn" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="{{ auth()->user()->name }}">
                    <div class="profile-info">
                        <div class="name">{{ auth()->user()->name }}</div>
                        <div class="role">{{ auth()->user()->role->name ?? 'User' }}</div>
                    </div>
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <div class="dropdown-header">
                            <strong>{{ auth()->user()->name }}</strong>
                            <br>
                            <small class="text-muted">{{ auth()->user()->email ?? 'Admin' }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @if(auth()->user()->canAccess('profile.edit'))
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-pencil"></i> Edit Profil
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <div>{{ e(session('success')) }}</div>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>{{ e(session('warning')) }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i>
                    <div>{{ e(session('error')) }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <footer class="mt-5 pt-4 border-top">
                <p class="text-center text-muted small mb-0">
                    &copy; {{ date('Y') }} <strong>{{ $storeSetting->name ?? 'Inventory App' }}</strong> • Seluruh hak cipta dilindungi
                </p>
            </footer>
        </main>
    </div>
</div>

<!-- Modal konfirmasi modern, menggantikan popup confirm() bawaan browser -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width:56px;height:56px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                    </span>
                </div>
                <h6 class="mb-2">Konfirmasi Tindakan</h6>
                <p class="text-muted mb-0" id="confirmModalBody"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" id="confirmModalBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainSidebar = document.getElementById('mainSidebar');
    const sidebar = document.querySelector('nav.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Initialize sidebar toggle button visibility on desktop
    function updateToggleButtonVisibility() {
        if (window.innerWidth > 768) {
            sidebarToggle.style.display = 'flex';
        } else {
            sidebarToggle.style.display = 'none';
        }
    }

    // Restore sidebar state from localStorage
    function restoreSidebarState() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            mainSidebar.classList.add('collapsed');
            sidebarToggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
            sidebarToggle.title = 'Tampilkan Sidebar';
        }
    }

    // Toggle sidebar collapsed state
    function toggleCollapsed() {
        const isCollapsed = mainSidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        if (isCollapsed) {
            sidebarToggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
            sidebarToggle.title = 'Tampilkan Sidebar';
        } else {
            sidebarToggle.innerHTML = '<i class="bi bi-chevron-left"></i>';
            sidebarToggle.title = 'Sembunyikan Sidebar';
        }
    }

    // Initialize sidebar toggle
    updateToggleButtonVisibility();
    restoreSidebarState();
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleCollapsed);
    }

    // Update toggle button visibility on window resize
    window.addEventListener('resize', updateToggleButtonVisibility);

    function closeSidebar() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    }

    // Toggle menu button (mobile)
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    // Close sidebar when clicking on overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking on sidebar links
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Close sidebar after navigation on mobile
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    // Close sidebar when window is resized to desktop size
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});

// Ganti popup confirm() bawaan browser dengan modal modern.
// Pakai: <form data-confirm="Pesan konfirmasi di sini..."> alih-alih onsubmit="return confirm(...)"
document.addEventListener('DOMContentLoaded', function () {
    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    const confirmModalBody = document.getElementById('confirmModalBody');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            pendingForm = form;
            confirmModalBody.textContent = form.dataset.confirm;
            confirmModal.show();
        });
    });

    document.getElementById('confirmModalBtn').addEventListener('click', function () {
        confirmModal.hide();
        if (pendingForm) {
            pendingForm.submit();
        }
    });
});
</script>
</body>
</html>
