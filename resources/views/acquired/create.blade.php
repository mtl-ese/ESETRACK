<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('acquired.index') }}">back</x-back-link>


    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add Acquired Purchase Requisitions</h3>
        </div>

        <form method="post" action="{{ route('acquired.store') }}">
            @csrf
            <div class="mb-3">
                <label for="requisition_id" class="form-label"><strong>Purchase Requisition ID</strong></label>
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    placeholder="Enter Purchase requsition ID" required value="{{ old('requisition_id') }}">
            </div>
        <x-form-button>Add</x-form-button>
        </form>

    </div>
</x-layout>