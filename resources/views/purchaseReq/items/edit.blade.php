<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <div class="text-center">
        <h3 class="mb-2">Edit item</h3>
        <h5 class="mb-4">Purchase Requisition ID: {{ $item->purchase_requisition_id }}</h5>
    </div>
    <div class="d-flex">
        <a class="btn btn-success mx-auto" href="{{ route('purchase.show', $item->purchase_requisition_id) }}">back</a>
    </div>

    <form method="post" action="{{ route('purchase.edit-items') }}">
        @csrf
        <div class="mb-3">
            <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                value="{{ $item->purchase_requisition_id}}" hidden readonly>
        </div>

        <div class="mb-3">
            <label for="item" class="form-label">Item description</label>
            <input type="text" class="form-control" id="item" name="item_description"
                placeholder="Enter item description" value="{{ $item->item_description }}" required>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter Quantity"
                value="{{ $item->quantity }}" required>
        </div>
        <input type="number" class="form-control" name="id" value="{{ $item->id }}" readonly hidden>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>

</x-layout>