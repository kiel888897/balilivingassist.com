<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | BLA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.css">
    <style>
        :root {
            --ink: #26343d;
            --muted: #71808a;
            --teal: #087e79;
            --teal-dark: #066963;
            --sidebar: #173d46;
            --canvas: #f3f6f5;
            --line: #e1e8e5;
            --white: #fff;
            --danger: #b42318;
            --green: #16805d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--canvas);
            color: var(--ink);
            font: 14px 'DM Sans', sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .admin-shell {
            --sidebar-width: 248px;
            min-height: 100vh;
        }

        .admin-shell.is-sidebar-collapsed {
            --sidebar-width: 76px;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 5;
            width: var(--sidebar-width);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 22px 16px 18px;
            background: var(--sidebar);
            color: #eaf2f0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-color: rgba(234, 242, 240, .35) transparent;
            scrollbar-width: thin;
            transition: width .2s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(234, 242, 240, .35);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-bottom: 18px;
        }

        .brand {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 11px;
            padding: 4px 4px 4px 8px;
            font: 800 16px 'Manrope', sans-serif;
        }

        .brand-name {
            overflow: hidden;
            white-space: nowrap;
        }

        .brand-mark {
            width: 36px;
            flex: 0 0 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 8px;
        }

        .sidebar-toggle {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 7px;
            background: transparent;
            color: #eaf2f0;
            cursor: pointer;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, .12);
        }

        .nav-label {
            margin: 12px 10px 9px;
            color: #9eb9b5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .side-nav {
            display: grid;
            gap: 4px;
        }

        .side-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 7px;
            color: #c4d5d2;
            font-weight: 600;
        }

        .side-nav a i {
            flex: 0 0 20px;
        }

        .nav-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .side-nav a:hover,
        .side-nav a.active {
            background: rgba(255, 255, 255, .12);
            color: white;
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, .16);
            padding-top: 14px;
        }

        .sidebar-user {
            padding: 8px 10px 12px;
            color: #c4d5d2;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .sidebar-user strong {
            display: block;
            color: white;
            font-size: 13px;
        }

        .logout-button {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 10px;
            border-radius: 7px;
            color: #c4d5d2;
            text-align: left;
            cursor: pointer;
        }

        .logout-button:hover {
            background: rgba(255, 255, 255, .12);
            color: white;
        }

        .admin-main {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            transition: margin-left .2s ease;
        }

        .topbar {
            min-height: 66px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 0 32px;
            border-bottom: 1px solid var(--line);
            background: var(--white);
        }

        .breadcrumb {
            color: var(--muted);
        }

        .topbar-link {
            color: var(--teal);
            font-weight: 700;
        }

        .page-content {
            width: min(1440px, 100%);
            margin: 0 auto;
            padding: 30px 32px 54px;
        }

        .page-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-heading h1 {
            margin: 0;
            color: #1d3039;
            font: 800 25px 'Manrope', sans-serif;
        }

        .page-description {
            margin: 7px 0 0;
            color: var(--muted);
        }

        .page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .panel {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: var(--white);
        }

        .panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
        }

        .panel-heading h2 {
            margin: 0;
            font: 700 15px 'Manrope', sans-serif;
        }

        .panel-body {
            padding: 18px 20px;
        }

        .data-wrap {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .data-table th {
            background: #f8faf9;
            color: #667780;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .data-table th,
        .data-table td {
            padding: 13px 15px;
            border-bottom: 1px solid #edf1ef;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }

        .data-table tr:last-child td {
            border-bottom: 0;
        }

        .data-table .subtext {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 400;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            padding: 18px 20px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: white;
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .stat-value {
            margin-top: 9px;
            color: #1d3039;
            font: 800 27px 'Manrope', sans-serif;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 18px;
        }

        .quotation-editor-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .quotation-summary {
            grid-template-columns: minmax(250px, .8fr) minmax(0, 1.2fr);
            align-items: start;
        }

        .quotation-payment-link {
            grid-column: 1 / -1;
        }

        .badge {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 5px;
            background: #e9f4f1;
            color: #21705a;
            font-size: 11px;
            font-weight: 700;
        }

        .muted {
            color: var(--muted);
        }

        .status-message {
            margin-bottom: 18px;
            padding: 11px 14px;
            border: 1px solid #b9e2d1;
            border-radius: 7px;
            background: #effaf5;
            color: #176b4b;
        }

        .status-error {
            margin-bottom: 18px;
            padding: 11px 14px;
            border: 1px solid #f0c5c0;
            border-radius: 7px;
            background: #fff4f2;
            color: #9e2f25;
        }

        .btn {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            padding: 8px 13px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: var(--teal);
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--teal-dark);
        }

        .btn-light {
            border-color: var(--line);
            background: white;
            color: var(--ink);
        }

        .btn-light:hover {
            background: #f7faf9;
        }

        .btn-danger {
            background: #b42318;
            color: white;
        }

        .btn-danger:hover {
            background: #911d14;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .form-panel {
            max-width: 940px;
            padding: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: grid;
            align-content: start;
            gap: 7px;
        }

        .field-wide {
            grid-column: 1 / -1;
        }

        .field label {
            color: #3d4d56;
            font-size: 13px;
            font-weight: 700;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            min-height: 41px;
            padding: 9px 11px;
            border: 1px solid #cbd6d2;
            border-radius: 6px;
            background: white;
            color: var(--ink);
            font: inherit;
        }

        .field input[readonly] {
            border-color: #dce5e2;
            background: #f4f7f6;
            color: #657780;
            cursor: default;
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: 3px solid rgba(8, 126, 121, .14);
            border-color: var(--teal);
        }

        .check-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            padding: 4px 0;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #45565f;
            font-size: 13px;
        }

        .check input {
            width: 16px;
            height: 16px;
            accent-color: var(--teal);
        }

        .field-error {
            color: var(--danger);
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
        }

        .detail-panel {
            margin-top: 20px;
        }

        .detail-panel .panel-heading {
            align-items: flex-start;
        }

        .detail-panel .panel-heading p {
            margin: 6px 0 0;
        }

        .detail-form {
            padding: 20px;
        }

        .detail-list {
            display: grid;
            gap: 14px;
        }

        .detail-card {
            min-width: 0;
            margin: 0;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfc;
        }

        .detail-card > .btn {
            margin-top: 14px;
        }

        .quote-item-list {
            display: grid;
            gap: 10px;
        }

        .quote-item-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            min-width: 0;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: white;
            transition: opacity .15s ease, background .15s ease;
        }

        .quote-item-row.is-marked-remove {
            background: #fff7f6;
            opacity: .58;
        }

        .quote-item-description {
            display: grid;
            min-width: 0;
            gap: 7px;
        }

        .quote-item-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .quote-item-sku,
        .quote-item-options {
            color: var(--muted);
            font-size: 12px;
        }

        .quote-item-options {
            margin: 0;
        }

        .quote-description-input,
        .quote-item-control input {
            width: 100%;
            min-width: 0;
            min-height: 38px;
            padding: 8px 10px;
            border: 1px solid #cbd6d2;
            border-radius: 6px;
            background: white;
            color: var(--ink);
            font: inherit;
        }

        .quote-item-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: flex-end;
            gap: 9px;
        }

        .quote-item-control {
            display: grid;
            gap: 5px;
            min-width: 94px;
        }

        .quote-item-control label {
            color: #52616a;
            font-size: 11px;
            font-weight: 700;
        }

        .quote-price-control {
            width: 152px;
        }

        .quote-quantity-control {
            display: inline-flex;
            height: 38px;
            overflow: hidden;
            border: 1px solid #cbd6d2;
            border-radius: 6px;
            background: white;
        }

        .quote-quantity-control button {
            width: 34px;
            flex: 0 0 34px;
            border: 0;
            background: white;
            color: var(--teal);
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
        }

        .quote-quantity-control button:hover:not(:disabled) {
            background: #eef7f4;
        }

        .quote-quantity-control button:disabled {
            color: #aebbb6;
            cursor: not-allowed;
        }

        .quote-quantity-control input {
            width: 48px;
            min-height: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            text-align: center;
            font-weight: 700;
        }

        .quote-line-total {
            min-width: 110px;
            padding-bottom: 9px;
            color: var(--ink);
            font-size: 13px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .quote-remove-button {
            min-height: 38px;
            color: var(--danger);
        }

        .quote-item-dialog {
            width: min(540px, calc(100% - 28px));
            max-width: none;
            max-height: min(90vh, 720px);
            padding: 0;
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: white;
            color: var(--ink);
            box-shadow: 0 24px 70px rgba(20, 40, 35, .28);
        }

        .quote-item-dialog::backdrop {
            background: rgba(22, 39, 35, .55);
        }

        .quote-dialog-content {
            padding: 22px;
        }

        .quote-dialog-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 20px;
        }

        .quote-dialog-heading h2 {
            margin: 0;
            font: 700 18px 'Manrope', sans-serif;
        }

        .quote-dialog-heading p {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .quote-dialog-close {
            min-width: 38px;
            padding: 4px;
            font-size: 20px;
        }

        .quote-dialog-fields {
            display: grid;
            gap: 15px;
        }

        .quote-item-dialog .field[hidden] {
            display: none !important;
        }

        .quote-dialog-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .gallery-card {
            display: grid;
            align-content: start;
            gap: 12px;
        }

        .gallery-card img {
            width: 100%;
            height: 150px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: white;
            object-fit: contain;
        }

        .gallery-delete {
            color: var(--danger);
        }

        .detail-upload {
            max-width: 540px;
            margin-top: 18px;
        }

        .detail-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 9px;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        .tier-list {
            display: grid;
            gap: 8px;
            margin-bottom: 10px;
        }

        .tier-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
            gap: 8px;
        }

        .recommendation-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .recommendation-row {
            display: flex;
            min-width: 0;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 7px;
        }

        .recommendation-row .check {
            min-width: 0;
        }

        .recommendation-row .check span {
            overflow-wrap: anywhere;
        }

        .recommendation-row small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
        }

        .recommendation-order {
            width: 110px;
            flex: 0 0 110px;
        }

        .recommendation-order > span {
            color: var(--muted);
            font-size: 11px;
        }

        .error-list {
            margin: 7px 0 0;
            padding-left: 20px;
        }

        .is-sidebar-collapsed .sidebar-header {
            flex-direction: column;
        }

        .is-sidebar-collapsed .brand {
            padding: 4px 0;
        }

        .is-sidebar-collapsed .brand-name,
        .is-sidebar-collapsed .nav-label,
        .is-sidebar-collapsed .nav-text,
        .is-sidebar-collapsed .sidebar-user {
            display: none;
        }

        .is-sidebar-collapsed .side-nav a,
        .is-sidebar-collapsed .logout-button {
            justify-content: center;
            padding-right: 8px;
            padding-left: 8px;
        }

        .is-sidebar-collapsed .logout-button i {
            margin-right: 0;
        }

        .is-sidebar-collapsed .sidebar-bottom {
            margin-top: auto;
        }

        .is-sidebar-collapsed .logout-button .logout-text {
            display: none;
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .quotation-summary {
                grid-template-columns: 1fr;
            }

            .quotation-payment-link {
                grid-column: auto;
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: auto;
                height: auto;
                padding: 12px 14px;
                overflow: visible;
            }

            .sidebar-header {
                justify-content: flex-start;
                padding-bottom: 12px;
            }

            .brand {
                padding: 0 4px;
            }

            .sidebar-toggle {
                display: none;
            }

            .nav-label,
            .sidebar-bottom {
                display: none;
            }

            .side-nav {
                display: flex;
                overflow-x: auto;
                gap: 5px;
            }

            .side-nav a {
                white-space: nowrap;
                padding: 9px 10px;
            }

            .admin-main {
                margin-left: 0;
            }

            .is-sidebar-collapsed .sidebar-header {
                flex-direction: row;
            }

            .is-sidebar-collapsed .brand-name,
            .is-sidebar-collapsed .nav-text {
                display: inline;
            }

            .is-sidebar-collapsed .side-nav a {
                justify-content: flex-start;
            }

            .topbar {
                min-height: 54px;
                padding: 0 18px;
            }

            .page-content {
                padding: 22px 16px 40px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-value {
                font-size: 22px;
            }

            .form-panel {
                padding: 16px;
            }

            .detail-form {
                padding: 14px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field-wide {
                grid-column: auto;
            }

            .recommendation-list {
                grid-template-columns: 1fr;
            }

            .recommendation-row {
                align-items: flex-start;
            }

            .tier-row {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }

            .tier-row .btn {
                grid-column: 1 / -1;
            }

            .quote-item-row {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .quote-item-controls {
                justify-content: flex-start;
            }

            .quote-line-total {
                min-width: 0;
                padding-bottom: 10px;
                text-align: left;
            }

            .quote-dialog-content {
                padding: 17px;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
</head>

<body>
    <div class="admin-shell" data-admin-shell>
        <aside class="sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <a class="brand" href="{{ route('admin') }}" aria-label="BLA Admin dashboard">
                    <span class="brand-mark"><i class="fa-solid fa-house-chimney" aria-hidden="true"></i></span>
                    <span class="brand-name">BLA Admin</span>
                </a>
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="admin-sidebar-navigation" aria-expanded="true" aria-label="Minimize sidebar" title="Minimize sidebar">
                    <i class="fa-solid fa-angles-left" aria-hidden="true" data-sidebar-toggle-icon></i>
                </button>
            </div>
            <div class="nav-label">Workspace</div>
            <nav class="side-nav" id="admin-sidebar-navigation" aria-label="Admin navigation">
                <a class="{{ request()->routeIs('admin') ? 'active' : '' }}" href="{{ route('admin') }}" aria-label="Dashboard" title="Dashboard"><i class="fa-solid fa-gauge-high w-5 text-center" aria-hidden="true"></i><span class="nav-text">Dashboard</span></a>
                @if (Auth::user()->hasPermissionTo('catalog.view'))
                <a class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}" href="{{ route('admin.products') }}" aria-label="Products" title="Products"><i class="fa-solid fa-boxes-stacked w-5 text-center" aria-hidden="true"></i><span class="nav-text">Products</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('categories.manage'))
                <a class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories') }}" aria-label="Categories" title="Categories"><i class="fa-solid fa-tags w-5 text-center" aria-hidden="true"></i><span class="nav-text">Categories</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('services.manage'))
                <a class="{{ request()->routeIs('admin.services*') ? 'active' : '' }}" href="{{ route('admin.services') }}" aria-label="Services" title="Services"><i class="fa-solid fa-screwdriver-wrench w-5 text-center" aria-hidden="true"></i><span class="nav-text">Services</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('projects.manage'))
                <a class="{{ request()->routeIs('admin.portfolio*') ? 'active' : '' }}" href="{{ route('admin.portfolio') }}" aria-label="Portfolio" title="Portfolio"><i class="fa-solid fa-images w-5 text-center" aria-hidden="true"></i><span class="nav-text">Portfolio</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('rental.manage'))
                <a class="{{ request()->routeIs('admin.rental-categories*') ? 'active' : '' }}" href="{{ route('admin.rental-categories') }}" aria-label="Rental categories" title="Rental categories"><i class="fa-solid fa-truck-ramp-box w-5 text-center" aria-hidden="true"></i><span class="nav-text">Rental categories</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('delivery.manage'))
                <a class="{{ request()->routeIs('admin.delivery*') ? 'active' : '' }}" href="{{ route('admin.delivery') }}" aria-label="Delivery" title="Delivery"><i class="fa-solid fa-truck-fast w-5 text-center" aria-hidden="true"></i><span class="nav-text">Delivery</span></a>
                @endif
                @if (Auth::user()->hasPermissionTo('quotes.manage'))
                <a class="{{ request()->routeIs('admin.quotations*') ? 'active' : '' }}" href="{{ route('admin.quotations') }}" aria-label="Quotations" title="Quotations">
                    <i class="fa-solid fa-file-invoice w-5 text-center" aria-hidden="true"></i>
                    <span class="nav-text">Quotations</span>
                    @php($newQuotationCount = \App\Models\Quotation::where('status', 'new')->count())
                    @if ($newQuotationCount > 0)
                    <span class="ml-auto rounded-full bg-orange-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $newQuotationCount }}</span>
                    @endif
                </a>
                @endif
                @if (Auth::user()->hasPermissionTo('users.manage'))
                <a class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}" aria-label="Users" title="Users"><i class="fa-solid fa-users w-5 text-center" aria-hidden="true"></i><span class="nav-text">Users</span></a>
                @endif
            </nav>
            <div class="sidebar-bottom">
                <div class="sidebar-user"><strong>{{ Auth::user()->name }}</strong>{{ Auth::user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" aria-label="Sign out" title="Sign out"><i class="fa-solid fa-arrow-right-from-bracket mr-2" aria-hidden="true"></i><span class="logout-text">Sign out</span></button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="topbar">
                <div class="breadcrumb">BLA <span aria-hidden="true">/</span> Admin</div>
                <a class="topbar-link" href="{{ route('home') }}" target="_blank" rel="noopener">View public site</a>
            </header>

            <main class="page-content">
                <div class="page-heading">
                    <div>
                        <h1>@yield('page_title')</h1>
                        @hasSection('page_description')
                        <p class="page-description">@yield('page_description')</p>
                        @endif
                    </div>
                    <div class="page-actions">@yield('page_actions')</div>
                </div>

                @if (session('status'))
                <div class="status-message" role="status">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                <div class="status-error" role="alert">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.umd.min.js"></script>
    @stack('scripts')
    <script>
        (function () {
            var shell = document.querySelector('[data-admin-shell]');
            var toggle = document.querySelector('[data-sidebar-toggle]');
            var icon = document.querySelector('[data-sidebar-toggle-icon]');

            if (!shell || !toggle || !icon) return;

            toggle.addEventListener('click', function () {
                var collapsed = shell.classList.toggle('is-sidebar-collapsed');
                toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
                toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
                icon.className = collapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left';
                icon.setAttribute('aria-hidden', 'true');
            });
        })();
    </script>
</body>

</html>