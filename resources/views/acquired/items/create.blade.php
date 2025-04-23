<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-back-link href="{{ route('item.index', $acquired_id) }}">back</x-back-link>

    <div class="card p-5 bg-light bg-opacity-50">
        <h4 class="mb-4 text-center">Acquired Purchase Requisition</h4>
        <p class="text-center fw-bold">Add items</p>

        <form method="post" action="{{ route('item.store') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    placeholder="Enter Purchase requsition ID" required value="{{ $acquired_id }}" readonly hidden>
            </div>

            <div class="mb-3">
                <label for="item_description" class="form-label"><strong>Item description</strong></label>
                <input type="text" class="form-control" id="item_description" name="item_description"
                    placeholder="Enter Item description" required value="{{ old('item_description') }}">
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity"
                    min="1" required value="{{ old('quantity') }}">
            </div>
            <x-form-button>Add</x-form-button>
        </form>
    </div>
</x-layout>