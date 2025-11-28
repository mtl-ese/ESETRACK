<x-layout>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <h3 class="mb-2">Create a new User</h3>
        <form method="post" action="{{ route('usersStore') }}">
            @csrf
            <div class="mb-3">
                <label for="first_name" class="form-label"><strong>First name</strong></label>
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name"
                    value="{{ old('first_name') }}" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label"><strong>Last name</strong></label>
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name"
                    value="{{ old('last_name') }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email address</strong></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address"
                    value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label for="employee_number" class="form-label"><strong>Employee Number</strong></label>
                <input type="number" class="form-control" id="employee_number" name="employee_number" placeholder="Enter Employee Number"
                    value="{{ old('employee_number') }}" required min="1">
            </div>
            <x-form-button>Create</x-form-button>
        </form>
    </div>
</x-layout>