<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-back-link href="{{ route('usersIndex') }}" class="mb-1">Back</x-back-link>


    <div class="card p-2 gy-2 bg-light bg-opacity-50">
        <h2 class="mb-2 card-header text-center fw-bold">{{ ucfirst(strtolower($user->first_name)) }}
            {{ ucfirst(strtolower($user->last_name)) }}
        </h2>

        <div class="container"></div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="profile-container">
                        @if ($user->profile_image == null)
                            <img src="{{ asset('images/profile.png') }}" alt="Profile Image"
                                class="profile-image w-75 h-50">
                        @else
                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile Image"
                                class="profile-image w-75 h-50">
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gap-2 p-4">
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Created On:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}
                        </p>
                        <p><strong>Status:</strong> {{ $user->isActivated ? 'Activated' : 'Deactivated' }}</p>
                        <p><strong>Role:</strong>
                            @if ($user->isSuperAdmin)
                                Super Admin
                            @else
                                {{ $user->isAdmin ? 'Admin' : 'User' }}
                            @endif
                        </p>
                        <p><strong>Activity:</strong>
                            @if ($user->is_active)
                                Active
                            @else
                                @if ($user->last_seen_at != null)
                                    Last seen: {{ \Carbon\Carbon::parse($user->last_seen_at)->diffForHumans() }}
                                @else
                                    Last seen: Not available
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @if ($user->isSuperAdmin)

        @else
            <div class="d-flex justify-content-center mt-4 card-footer gap-4">
                @if ($user->isActivated)
                    <form method="post" action="{{ route('usersDeactivate', $user->id) }}">
                        @csrf
                        <button class="btn mx-2" style="background-color: rgb(255, 174, 0);" type="submit">Deactivate</button>
                    </form>
                @else
                    <form method="post" action="{{ route('usersActivate', $user->id) }}">
                        @csrf
                        <button class="btn btn-success mx-2" type="submit">Activate</button>
                    </form>
                @endif

                <form method="post" action="{{ route('usersReset', $user->id) }}">
                    @csrf
                    <button class="btn btn-secondary mx-2" type="submit">Reset Password</button>
                </form>

                @if ($user->isAdmin)
                    <form method="post" action="{{ route('usersRevokeAdmin', $user->id) }}">
                        @csrf
                        <button class="btn btn-danger mx-2" type="submit">Revoke Admin</button>
                    </form>
                @else
                    <form method="post" action="{{ route('usersMakeAdmin', $user->id) }}">
                        @csrf
                        <button class="btn mx-2 text-white" type="submit" style="background-color: #003366">Make Admin</button>
                    </form>
                @endif
            </div>
        @endif


    </div>
</x-layout>