<nav class="main-header navbar layout-fixed app-header-content navbar-expand border-bottom-1"
    style="background-color: rgb(255, 174, 0) !important; color: black !important; height: 8vh !important;">
     
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                @if (Auth::user()->profile_image == null)
                    <img width="30" height="30" class="rounded-circle mr-2" src="{{ asset('images/profile.png') }}">
                @else
                    <img width="30" height="30" src="{{ asset('storage/' . Auth::user()->profile_image) }}"
                        class="rounded-circle mr-2">
                @endif                  
                <span class="ms-1">{{ ucwords(Auth::user()->first_name ?? 'Ian') }} {{ ucwords(Auth::user()->last_name ?? 'Chipinda') }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow"
                aria-labelledby="userDropdown"
                style="background-color: rgba(255, 255, 255, 0.8) !important; min-width: 300px;">
                <div class="text-center border-bottom py-2">
                    @if (Auth::user()->profile_image == null)
                        <img width="60" height="60" src="{{ asset('images/profile.png') }}" alt="Profile Image" class="rounded-circle mb-2">
                    @else
                        <img width="60" height="60" src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image"
                        class="rounded-circle mb-2">
                    @endif
                    <p class="mb-0 font-weight-bold">{{ ucwords(Auth::user()->first_name ?? 'Ian') }} {{ ucwords(Auth::user()->last_name ?? 'Chipinda') }}</p>
                    <small class="text-muted">Logged in since {{ Auth::user()->last_login_at->diffForHumans() }}</small>
                </div>
            </div>
        </li>
    </ul>
</nav>

<style>
    .navbar-nav.ml-auto {
        margin-left: auto !important;
    }

    .nav-link.dropdown-toggle {
        display: flex;
        align-items: center;
    }

    .dropdown-menu {
        border-radius: 6px;
      
    }
</style>
