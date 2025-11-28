<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESE TRACK</title>
    <!-- <link rel="icon" type="image/png" href="{{ asset('images/ESETRACK.png') }}"> -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css"') }}"> -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style>
    body {
        display: flex;
        flex-direction: row;
        min-height: 100vh;
        margin: 0;
    }

    .top-bar {
        position: fixed;
        top: 0;
        left: 16.75%;
        width: calc(100% - 16.75%);
        height: 30px;
        background-color: #fff;
        z-index: 1000;
        transition: left 0.3s ease, width 0.3s ease;
    }

    .hover-scale {
        transition: transform 0.3s ease-in-out;
    }

    .hover-shadow:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
        transition: box-shadow 0.3s ease-in-out;
    }

    .hover-scale:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease-in-out;
    }

    .nav-item a {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        border: 2px solid transparent;
        border-radius: 6px;
    }

    .nav-item a:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-color: #0d6efd !important;
        color: #0d6efd;
        background-color: rgba(13, 202, 240, 0.25) !important;
    }

    .sidebar {
        width: 16.75%;
        max-width: 250px;
        min-height: 100vh;
        background-color: #001f3f !important;
        color: white !important;
        padding: 15px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        top: 0;
        transition: margin-left 0.3s ease;
    }

    .sidebar.hidden {
        margin-left: -250px;
    }

    .main-content {
        flex-grow: 1;
        padding: 15px;
        margin-left: 16.75%;
        margin-top: 50px;
        overflow-y: auto;
        background-image: url('{{ asset('images/yellow interior.jpg') }}');
        background-size: cover;
        background-position: center;
        transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 0;
    }

    .sidebar-header img {
        max-height: 45px;
        width: 100%;
        object-fit: contain;
    }

    .nav-item img.white-icon {
        max-height: 40px;
        filter: brightness(0) invert(1);
    }

    .nav-item img.original-icon {
        max-height: 40px;
        filter: none;
    }

    .dropdown-menu .dropdown-item {
        color: black !important;
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #003366 !important;
        color: yellow !important;
    }

    .nav-link.active {
        background-color: rgb(255, 174, 0) !important;
        color: black !important;
    }

    .nav-link.create-link {
        color: white !important;
    }

    .sidebar-header {
        margin-bottom: 15px;
    }

    /* DataTables language elements styling */
    .dataTables_wrapper .dataTables_filter input {
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .search-icon {
        margin-right: 8px;
        font-size: 1.1em;
    }

    .dataTables_wrapper .search-label {
        font-weight: 600;
        color: #495057;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em;
        margin: 0 2px;
        border: 1px solid transparent;
        border-radius: 4px;
        color: #3273dc;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8f9fa;
        border-color: #ddd;
        color: #23527c;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #6c757d;
        background: transparent;
        border-color: transparent;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3273dc;
        color: white !important;
        border-color: #3273dc;
    }
    </style>


</head>

<body>

    <!-- Top Bar -->
    <div class="top-bar">
        @include('components.top-bar')
    </div>

    <!-- Navigation Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header text-center">
            <a href="{{ route('dashboard') }}">
                <img class="rounded w-5 bg-transparent" src="{{ asset('images/ESETRACK.png') }}">
            </a>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item mb-2" title="Dashboard">
                <x-nav-link :active="request()->routeIs('dashboard')" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt rounded-5 bg-transparent white-icon me-1"
                        style="max-height: 40px;"></i>
                    Dashboard
                </x-nav-link>
            </li>
            <li class="nav-item mb-2" title="Create">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('create.store') || request()->routeIs('create.purchase') || request()->routeIs('recovery.create') || request()->routeIs('returns.create') ? 'active' : '' }} create-link"
                    href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-pen rounded-5 bg-transparent white-icon me-1" style="max-height: 40px;"></i>
                    Create
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li>
                        <x-nav-link :active="request()->routeIs('create.purchase')"
                            href="{{ route('create.purchase') }}" class="dropdown-item"
                            title="create a new purchase requisition">Purchase</x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :active="request()->routeIs('create.store')" href="{{ route('create.store') }}"
                            class="dropdown-item" title="create a new store requisition">Store</x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :active="request()->routeIs('recovery.create')"
                            href="{{ route('recovery.create') }}" class="dropdown-item"
                            title="create a new recovery requisition">Recovery</x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :active="request()->routeIs('returns.create')" href="{{ route('returns.create') }}"
                            class="dropdown-item" title="create a new store return">Store Return</x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :active="request()->routeIs('emergencyCreate')"
                            href="{{ route('emergencyCreate') }}" class="dropdown-item"
                            title="create a new emergency requisition">Emergency</x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :active="request()->routeIs('emergencyReturnCreate')"
                            href="{{ route('emergencyReturnCreate') }}" class="dropdown-item"
                            title="create a new emergency return">Emergency Return</x-nav-link>
                    </li>
                </ul>
            </li>
            <li class="nav-item mb-2" title="Stores">
                <x-nav-link :active="request()->routeIs('stores.index')" href="{{ route('stores.index') }}">
                    <i class="fas fa-database rounded-5 bg-transparent white-icon me-1" style="max-height: 40px;"></i>
                    Storage
                </x-nav-link>
            </li>
            <li class="nav-item mb-2" title="Recovery Storage">
                <x-nav-link :active="request()->routeIs('recovered.index')" href="{{ route('recovered.index') }}">
                    <img src="{{ asset('images/return-storage.gif') }}" alt="Recovered"
                        class="rounded-5 bg-transparent me-1" style="max-height: 21px;">
                    Recovery Storage
                </x-nav-link>
            </li>
            <li class="nav-item mb-2" title="Acquired Items">
                <x-nav-link :active="request()->routeIs('acquired.index')" href="{{ route('acquired.index') }}">
                    <i class="fas fa-check-circle rounded-5 bg-transparent white-icon me-1"
                        style="max-height: 40px;"></i>
                    Acquired
                </x-nav-link>
            </li>
            

            <li class="nav-item mb-2" title="Profile">
                <x-nav-link :active="request()->routeIs('profile', 'password.change', 'profileImageCreate')"
                    href="{{ route('profile') }}">
                    <i class="fas fa-user rounded-5 bg-transparent white-icon me-1" style="max-height: 40px;"></i>
                    Profile
                </x-nav-link>
            </li>

 
            @if (auth()->user()->isAdmin || auth()->user()->isSuperAdmin)
            <li class="nav-item mb-2" title="Users">
                <x-nav-link :active="request()->routeIs('usersIndex','usersShow')" href="{{ route('usersIndex') }}">
                    <i class="fas fa-users rounded-5 bg-transparent white-icon me-1" style="max-height: 40px;"></i>
                    Users
                </x-nav-link>
            </li>
            @endif

            <li class="nav-item" title="Logout">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <a href="#" id="logoutButton" class="nav-link text-white" style="cursor: pointer;">
                        <i class="fas fa-sign-out-alt rounded-5 bg-transparent white-icon me-1"
                            style="max-height: 40px;"></i>
                        Logout
                    </a>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content Section -->
    <div class="main-content">
    {{ $slot }}
    </div>

    <!-- SweetAlert Logout Confirmation Script -->
    <script>
    document.getElementById('logoutButton').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default anchor action

        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out and cannot return to this page.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(182, 11, 11)',
            cancelButtonColor: 'rgb(19, 146, 19)',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit(); // Submit the logout form
            }
        });
    });

    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.querySelector('[data-widget="pushmenu"]');
        const sidebar = document.querySelector('.sidebar');
        const topBar = document.querySelector('.top-bar');
        const mainContent = document.querySelector('.main-content');

        toggleButton.addEventListener('click', function(event) {
            event.preventDefault();
            sidebar.classList.toggle('hidden'); // Toggle sidebar visibility
            // Adjust the top bar and main content based on sidebar visibility
            if (sidebar.classList.contains('hidden')) {
                topBar.style.left = '0'; // Expand top bar to full width
                topBar.style.width = '100%'; // Full width
                mainContent.classList.add('expanded'); // Expand main content
            } else {
                topBar.style.left = '16.75%'; // Reset to initial position
                topBar.style.width = 'calc(100% - 16.75%)'; // Reset width
                mainContent.classList.remove('expanded'); // Reset main content
            }
        });
    });
    </script>
</body>

</html>