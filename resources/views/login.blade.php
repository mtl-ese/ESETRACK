<!DOCTYPE html>
<html lang="en">

<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/ionicons@5.5.2/dist/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <style>
    body {


        /font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        /*background: url('{{ asset('images/yellow interior.jpg') }}') no-repeat center center;
            background-size: cover;*/
        position: relative;
        /* Set position to relative */
        overflow: hidden;
        /* This will prevent the page from scrolling */


        background: url('{{ asset('images/yellow interior.jpg') }}') no-repeat center center;
        background-size: cover;
        background-attachment: fixed;
        overflow: auto !important;
        /* Ensure scrolling works if needed */
    }



    /* Add a separate background container */
    .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('{{ asset('images/yellow interior.jpg') }}') no-repeat center center;
        background-size: cover;
        z-index: -1;
        /* Ensure it stays in the background */
    }

    body.modal-open {
        z-index: 1050;
        /* Ensure the modal stays above the background */
    }




    /* Modal backdrop */
    .modal-backdrop {
        z-index: 999;
        /* Make sure the backdrop doesn't overlap the background */
    }



    .login-container {
        display: flex;
        flex-direction: column;
        /* Stack layout on small screens */
        width: 90%;
        /* Use more width on small screens */
        max-width: 1200px;
        background-color: rgb(32, 44, 95);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        position: fixed;
        /* Fixed position */
        top: 50%;
        /* Center vertically */
        left: 50%;
        /* Center horizontally */
        transform: translate(-50%, -50%);
        /* Adjust for centering */
        z-index: 1000;
        /* Make sure the login modal stays on top of other content */
    }

    @media (min-width: 768px) {
        .login-container {
            flex-direction: row;
            /* Row layout on larger screens */
        }
    }

    .login-left {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: url('{{ asset('images/MTL LOGO.jpg') }}') no-repeat center center;
        background-size: cover;
    }

    .login-left-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.62);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-left-text {
        color: #fff;
        text-align: center;
        padding: 20px;
    }

    .login-left-text h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .login-left-text p {
        font-size: 1.5rem;
        margin-top: 0;
    }

    .icon-container {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .login-right {
        flex: 1;
        padding: 40px;
        /* Reduced padding for better responsiveness */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-form-wrapper {
        width: 100%;
        max-width: 400px;
    }

    .login-form-wrapper h2 {
        text-align: center;
        font-weight: bold;
        margin-bottom: 30px;
        font-size: 1.75rem;
        color: #4a4a4a;
    }

    .form-control {
        margin-bottom: 20px;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
        width: 100%;
        /* Full width for inputs */
    }

    .btn.app-btn-primary {
        background-color: rgb(255, 237, 72);
        color: black;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        width: 100%;
        cursor: pointer;
    }

    .btn.app-btn-primary:hover {
        background-color: #b8a9c9;
    }

    footer {
        margin-top: 30px;
        text-align: center;
    }

    footer small {
        color: #aaa;
    }

    @media (max-width: 768px) {
        .login-left-text h1 {
            font-size: 2rem;
            /* Smaller heading size on mobile */
        }

        .login-left-text p {
            font-size: 1.2rem;
            /* Smaller paragraph size on mobile */
        }
    }

    .forgot-password {
        text-align: right;
        /* Aligns the forgot password link to the right */
    }

    /* Disable the password reveal button in Microsoft Edge */
    input[type="password"]::-ms-reveal {
        display: none;
    }
    </style>
</head>

<body>
    <!-- Background image container -->
    <div class="background-image"></div>
    <x-error></x-error>
    <div class="login-container">
        <div class="login-left">
            <div class="login-left-overlay">
                <div class="login-left-text">
                    <div class="icon-container">
                        <ion-icon name="school"></ion-icon>
                    </div>
                    <h1>Welcome To ESETRACK!</h1>
                    <p>Enter your Log in credentials </p>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-wrapper">
                <h2 class="text-white font-weight-bold">Login</h2>

                <form action="{{ route('login') }}" method="post" class="login-form">
                    @csrf
                    <div class="email mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email address" required
                            value="{{ old('email') }}">
                    </div>

                    <div class="password mb-3">
                        <div class="input-group">
                            <input type="password" name="password" id="signin-password" class="form-control"
                                placeholder="Password" required autocomplete="new-password">
                            <span class="input-group-text" id="toggle-password" style="max-height:38px;">
                                <i class="fa fa-eye" id="toggle-password-icon"></i>
                            </span>
                        </div>
                        <div class="text-sm text-danger"></div>
                        <div class="forgot-password mt-2">
                            <a href="#" class="text-white font-weight-light">Forgot password?</a>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn app-btn-primary">Log In</button>
                    </div>
                </form>

                <footer class="mt-4">
                    <small class="copyright">
                        Designed and developed by ESE CODING TEAM<a href="#" class="app-link" target="_blank"></a>
                    </small>
                </footer>
            </div>
        </div>
    </div>

    <script>
    const togglePassword = document.querySelector('#toggle-password');
    const passwordInput = document.querySelector('#signin-password');
    const toggleIcon = document.querySelector('#toggle-password-icon');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    });
    </script>

</body>

</html>