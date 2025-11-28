<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link
        href="{{ route('returns.items.index', ['store_return_id' => $store_return_id, 'requisition_id' => $requisition_id]) }}">
        Back</x-back-link>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add items</h3>
        </div>

    <form method="post" action="{{ route('returns.items.store') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="store_return_id" name="store_return_id"
                    value="{{ $store_return_id }}" readonly hidden>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            <div class="mb-3">
                <label for="item" class="form-label"><strong>Item description</strong></label>
                <input type="text" class="form-control" id="item" name="item_name" placeholder="Enter item description"
                    required>
            </div>

            <div class="mb-3">
                <label for="item" class="form-label"><strong>Status</strong></label>
                <input type="text" class="form-control" id="status" name="status" placeholder="Enter item status"
                    required>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity"
                    value="1" min="1" required>
            </div>

            <div id="serialNumbersContainer">

            </div>
            <x-form-button>Add</x-form-button>
        </form>
    </div>


    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure jQuery is available before running
        if (typeof jQuery === "undefined") {
            return;
        }

        $(document).ready(function() {
            // Define the array of item descriptions that require serial numbers.
            const itemsRequiringSerial = ["powerbeam radios", "nanobeam radios", "cisco routers",
                "cisco switches", "mikrotik routers", "mikrotik switches", "rockets",
                "media converters",
                "cambium radios"
            ]; // Add or modify items as needed

            // Function to update the serial number fields based on item description and quantity
            function updateSerialFields() {
                let itemDesc = $('#item').val().toLowerCase().trim();
                let quantity = parseInt($('#quantity').val(), 10) || 1;
                let container = $('#serialNumbersContainer');
                container.empty();

                // Check if the item description contains any of the strings in the array
                if (itemsRequiringSerial.some(serialItem => itemDesc.includes(serialItem))) {
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

            // Trigger the update when item description or quantity changes
            $('#item, #quantity').on('input', updateSerialFields);

            // Trigger an initial update
            updateSerialFields();
        });
    });
    </script>
</x-layout>