<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('emergencyItemsIndex', $requisition_id) }}">back</x-back-link>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add items</h3>
            <h5 class="mb-4">Emergency Requisition ID: {{ $requisition_id }}</h5>
        </div>

        <form method="post" action="{{ route('emergencyItemsStore', $requisition_id) }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            <div class="mb-3">
                <label for="item" class="form-label"><strong>Item description</strong></label>
                <input type="text" class="form-control" id="item" name="item_description"
                    placeholder="Enter item description" required value="{{ old('item_description') }}">
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity"
                    value="{{ old('quantity') }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label"><strong>From</strong></label>
                <select class="form-control" id="from" name="from" required>
                    <option></option>
                    <option value="stores">Stores</option>
                    <option value="return stores">Return Stores</option>
                </select>
            </div>

            <div class="mb-3 d-flex">
                <input type="checkbox" class="form-check-input" id="will_return" name="will_return">
                <label class="form-check fw-bold">Same item(s) will be returned</label>
            </div>

            <div id="serialNumbersContainer"></div>

            <x-form-button>Add</x-form-button>
        </form>

    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure jQuery is available before running
        if (typeof jQuery === "undefined") {
            return;
        }

        // Define the array of item descriptions that require serial numbers.
        const itemsRequiringSerial = [
            "powerbeam radios", "nanobeam radios", "cisco routers",
            "cisco switches", "mikrotik routers", "mikrotik switches",
            "rockets", "media converters", "cambium radios"
        ];

        // Define the "from" values that require serial numbers (in lowercase)
        const requiredFrom = ['return stores', 'stores'];

        // Function to update the serial number fields based on item description and quantity
        function updateSerialFields() {
            let itemDesc = $('#item').val().toLowerCase().trim();
            let from = $('#from').val().toLowerCase().trim();
            let quantity = parseInt($('#quantity').val(), 10) || 1;
            let container = $('#serialNumbersContainer');
            container.empty();

            // Only display serial fields if the item is in our array and the "from" value is required
            if (itemsRequiringSerial.includes(itemDesc) && requiredFrom.includes(from)) {
                for (let i = 1; i <= quantity; i++) {
                    container.append(`
                            <div class="mb-3">
                                <label for="serialNumber${i}"><strong>Serial Number ${i}:</strong></label>
                                <input type="text" class="form-control" placeholder="Enter Serial Number" id="serialNumber${i}" name="serialNumbers[]" required>
                            </div>
                        `);
                }
            }
        }

        // Attach updateSerialFields to input/change events on the item, quantity, and from fields
        $('#item, #quantity, #from').on('input change', updateSerialFields);

        // Trigger an initial update
        updateSerialFields();
    });
    </script>
</x-layout>