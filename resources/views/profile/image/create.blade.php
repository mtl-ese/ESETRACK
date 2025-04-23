<x-layout>
    <x-error></x-error>
    <x-success></x-success>
    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <!-- Optional content header -->
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-success card-outline card-outline-tabs">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile') }}" role="tab">Profile</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('password.change') }}" role="tab">Change
                                            Password</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="{{ route('profileImageCreate') }}"
                                            role="tab">Upload profile image</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body">
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
                                    <div class="col-sm-10">
                                        <p><strong>Upload Profile Image</strong></p>
                                        <form action="{{ route('profileImageStore') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">

                                                <div class="mb-3">
                                                    <input type="text" class="form-control" id="requisition_id"
                                                        name="user_id" required value="{{ $id }}" readonly hidden>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="item_description" class="form-label">Image</label>
                                                    <input type="file" class="form-control" id="image" name="image"
                                                        placeholder="Upload image" required>
                                                </div>

                                                <button type="submit" class="btn" style="background-color: rgb(255, 174, 0)">Add</button>

                                        </form>
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
            /* Set background image */
            background-size: cover;
            /* Cover the entire area */
        }

        .card {
            background: rgba(255, 255, 255, 0.8) !important;
            /* Semi-transparent white for readability */
        }

        .profile-container {
            position: relative;
            display: inline-block;
        }

        .profile-image {
            display: block;
            width: 130px;
            /* Adjust the width as necessary */
            height: 130px;
            /* Adjust the height as necessary */
            border-radius: 50%;
            /* This makes the image round */
        }

        .btn-custom {
            background-color: rgb(255, 174, 0);
            /* Custom button color */
            color: black;
            /* Button text color */
            font-size: 1.2em;
            /* Larger font size */
            padding: 10px 20px;
            /* Increased padding for larger button size */
        }

        .btn-custom:hover {
            background-color: rgb(255, 150, 0);
            /* Change color on hover */
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
            /* Default border */
        }

        input:focus {
            border-color: rgb(255, 150, 0);
            /* Change border color on focus */
            box-shadow: 0 0 5px rgb(255, 150, 0);
            /* Optional: add shadow effect */
        }

        input:focus:not(:placeholder-shown) {
            background-color: rgba(255, 150, 0, 0.1);
            /* Light background for filled fields */
        }

        input:not(:placeholder-shown) {
            background-color: rgba(255, 150, 0, 0.1);
            /* Light background for filled fields */
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
    </script>
</x-layout>