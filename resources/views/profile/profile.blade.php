<x-layout>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card" style="background: rgba(255, 255, 255, 0.8);">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="custom-tabs-four-home-tab" data-toggle="pill"
                                            href="#custom-tabs-four-home" role="tab"
                                            aria-controls="custom-tabs-four-home" aria-selected="true">Profile</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-four-profile-tab" data-toggle="pill"
                                            href="{{ route('password.change') }}" role="tab"
                                            aria-controls="custom-tabs-four-profile" aria-selected="false">Change
                                            Password</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-four-profile-tab" data-toggle="pill"
                                            href="{{ route('profileImageCreate') }}" role="tab"
                                            aria-controls="custom-tabs-four-profile" aria-selected="false">Upload
                                            Profile Image</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-four-tabContent">
                                    <div class="tab-pane fade active show" id="custom-tabs-four-home" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-home-tab">
                                        <div class="row">
                                            <div class="col-sm-2 text-center">
                                                <div class="profile-container">
                                                    @if (Auth::user()->profile_image == null)
                                                        <img src="{{ asset('images/profile.png') }}" alt="Profile Image"
                                                            class="profile-image">
                                                    @else
                                                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}"
                                                            alt="Profile Image" class="profile-image">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <p><strong>Name:</strong> {{ Auth::user()->first_name }}
                                                    {{ Auth::user()->last_name }}
                                                </p>
                                                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                                <p><strong>Logged in since:</strong>
                                                    {{ Auth::user()->last_login_at->diffForHumans() }}</p>
                                                <p><strong>Date of Birth:</strong>
                                                    {{ \Carbon\Carbon::parse(Auth::user()->DOB)->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="custom-tabs-four-profile" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-profile-tab">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong>Change Password</strong></p>
                                                <div class="form-group">
                                                    <label class="text-gray">Current Password</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control"
                                                            placeholder="Enter current password" id="currentPassword">
                                                        <span class="toggle-visibility"
                                                            onclick="toggleVisibility('currentPassword', 'eyeCurrent')">
                                                            <i class="fa fa-eye" id="eyeCurrent"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="text-gray">New Password</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control"
                                                            placeholder="Enter new password" id="newPassword">
                                                        <span class="toggle-visibility"
                                                            onclick="toggleVisibility('newPassword', 'eyeNew')">
                                                            <i class="fa fa-eye" id="eyeNew"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="text-gray">Confirm New Password</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control"
                                                            placeholder="Confirm new password" id="confirmPassword">
                                                        <span class="toggle-visibility"
                                                            onclick="toggleVisibility('confirmPassword', 'eyeConfirm')">
                                                            <i class="fa fa-eye" id="eyeConfirm"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="mt-2 pl-4 pr-4 btn btn-custom">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        body {
            background: url('dist/img/yellow interior.jpg') no-repeat center center;
            background-size: cover;
        }

        .profile-container {
            position: relative;
            display: inline-block;
        }

        .profile-image {
            display: block;
            width: 130px;
            height: 130px;
            border-radius: 50%;
        }

        .btn-custom {
            background-color: rgb(255, 174, 0);
            color: black;
            font-size: 1.2em;
            padding: 10px 20px;
        }

        .btn-custom:hover {
            background-color: rgb(255, 150, 0);
        }

        .toggle-visibility {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
            z-index: 10;
        }

        input {
            border: 1px solid #ccc;
        }

        input:focus {
            border-color: rgb(255, 150, 0);
            box-shadow: 0 0 5px rgb(255, 150, 0);
        }

        input:focus:not(:placeholder-shown) {
            background-color: rgba(255, 150, 0, 0.1);
        }

        input:not(:placeholder-shown) {
            background-color: rgba(255, 150, 0, 0.1);
        }
    </style>

    <script>
        function toggleVisibility(elementId, eyeId) {
            const passwordField = document.getElementById(elementId);
            const eyeIcon = document.getElementById(eyeId);
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        }

        $(document).ready(function () {
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                $(e.target).tab('show');
            });
        });
    </script>
</x-layout>