<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('purchase.show', $requisition_id) }}">Back</x-back-link>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add items</h3>
            <h5 class="mb-4">Purchase Requisition ID: {{ $requisition_id }}</h5>
        </div>


        <form method="post" action="{{ route('purchase.add-items') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            <div class="mb-3">
                <label for="item" class="form-label"><strong>Item description</strong></label>
                <input type="text" class="form-control" id="item" name="item_description"
                    placeholder="Enter item description" required>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity"
                    required>
            </div>

            <x-form-button>Add</x-form-button>
        </form>
    </div>

</x-layout>