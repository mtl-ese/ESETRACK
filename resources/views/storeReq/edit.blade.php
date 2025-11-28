<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Edit Store Requisition</h3>
            <h5 class="mb-4">ID: {{ $requisition_id }}</h5>
        </div>

        <form id="editForm" method="post" action="{{ route('store.update-all', $requisition_id) }}">
            @csrf

            <!-- Requisition Details -->
            <div class="mb-3">
                <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
                <input type="text" class="form-control" id="approvedBy" name="approved_by"
                    value="{{ $requisition->approved_by }}" required>
            </div>

            <div class="mb-3">
                <label for="requisitionDate" class="form-label"><strong>Requisition date</strong></label>
                <input type="date" class="form-control" id="requisitionDate" name="requisition_date"
                    value="{{ old('requisition_date', optional($requisition->requested_on)->format('Y-m-d')) }}"
                    required max="{{ date('Y-m-d') }}">
            </div>

            <div class=" bg-gray mb-3">
                <div class="form-check d-flex align-items-center">
                    <label class="form-check-label"><strong style="color:brown; font-size: large;">Any Item Diverted?</strong></label>
                    <input type="checkbox" class="form-check-input"
                           style="width: 30px; height: 30px; margin-left: 10px;" id="itemDiverted">
                </div>
            </div>

            <div id="itemDiversionNoteContainer" class="mx-3">
                <div class="mb-2" id="itemDiversionNoteWrapper" style="{{ $requisition->item_diversion_note ? '' : 'display:none;' }}">
                    <textarea class="form-control" name="item_diversion_note" id="itemDiversionNote" style="height: 150px;"
                     placeholder="Type Item Diversion Note including item name, quantity, serial number if any, new client and date.">{{ old('item_diversion_note', $requisition->item_diversion_note ?? '') }}</textarea>
                </div>
            </div>

             <hr style="color: black; ;"/>

            <!-- Multiple Destinations Section -->
            <div class="mb-4 p-3 border rounded bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Customer(s)</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="addDestination">
                        <i class="bi bi-plus-circle"></i> Add Customer
                    </button>
                </div>

                <div id="destinationsContainer">
                    @if(isset($destinations) && count($destinations) > 0)
                        @foreach($destinations as $index => $dest)
                        <div class="destination-item mb-3 p-3 border rounded" data-destination-id="{{ $dest['id'] }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Destination {{ $index + 1 }}</h6>
                                <button type="button" class="btn btn-sm btn-danger remove-destination">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Client</strong></label>
                                    <input type="text" class="form-control destination-client"
                                        value="{{ $dest['client'] }}" readonly>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label"><strong>Location</strong></label>
                                    <input type="text" class="form-control destination-location"
                                        value="{{ $dest['location'] }}" readonly>
                                </div>
                            </div>
                            <input type="hidden" name="existing_destinations[]" value="{{ $dest['id'] }}">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <hr style="color: black; ;"/>
            <!-- Materials Section -->
            <h4 class="mt-4 mb-3">Materials</h4>

            <!-- Add Item Form 
            <div class="mb-3">
                <label for="destination" class="form-label"><strong>Select Destination (Optional)</strong></label>
                <select class="form-select" id="destination" name="destination_link_id">
                    <option value="">-- No specific destination --</option>
                </select>
            </div>
            Destination Select for adding item -->
<div class="mb-3">
    <label for="destination" class="form-label"><strong>Select Destination (Optional)</strong></label>
    <select class="form-select" id="destination" name="destination_link_id">
        <option value="">-- No specific destination --</option>
        @foreach($destinations ?? [] as $dest)
            <option value="{{ $dest['id'] }}">{{ $dest['client'] }} - {{ $dest['location'] }}</option>
        @endforeach
    </select>
</div>


            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="item" placeholder="Item description">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" id="quantity" placeholder="Quantity">
                </div>
                <div class="col-md-3">
                    <div class="form-check d-flex align-items-center mx">
                        <label class="form-check-label"><strong>Has Serial Numbers</strong></label>
                        <input type="checkbox" class="form-check-input"
                            style="width: 30px; height: 30px; margin-left: 10px;" id="serialnumber">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="addItemBtn">Add</button>
                </div>
            </div>

            <div id="serialNumbersContainer" class="row mb-3"></div>

            <!-- Items Table -->
            <div id="itemsTableContainer" style="display: none;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Destination</th>
                            <th>Serial Numbers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody"></tbody>
                </table>
            </div>

            <input type="hidden" name="items" id="itemsData">
            <input type="hidden" name="deleted_items" id="deletedItems">

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg">Save Changes</button>
                <a href="{{ route('store.index', $requisition_id) }}" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>

        @php
      $itemsData = $items->map(function ($item) {
      $serialNumbers = $item->serial_numbers
        ->pluck('serial_number')
        ->filter()
        ->values()
        ->all();

    // Only grab the first (active) destination link for display
      $activeLink = $item->destinationItems->first()?->link;

    return [
        'id' => $item->id,
        'item_name' => $item->item_name,
        'quantity' => $item->quantity,
        'serialNumbers' => $serialNumbers,
        'destination_link_id' => $activeLink?->id ?? null, // send actual link ID
    ];
});

$destinationsLookup = isset($destinations)
    ? collect($destinations)->keyBy('id')->toArray()
    : [];
       @endphp
<script>
document.addEventListener("DOMContentLoaded", function () {
    let items = @json($itemsData); 
    let deletedItemIds = [];
    let destinationsLookup = @json($destinationsLookup);
    let editingIndex = -1;
    let destinationCount = {{ isset($destinations) ? count($destinations) : 0 }};
    let newDestinations = []; // Track newly added destinations

    const destinationsContainer = $('#destinationsContainer');
    const destinationSelect = $('#destination');
    const addDestinationBtn = $('#addDestination');
    const addItemBtn = $('#addItemBtn');
    const serialCheckbox = $('#serialnumber');
    const itemDivertedCheckbox = $('#itemDiverted');

    // Initial UI setup
    updateItemsDisplay();
    updateDestinationDropdown();
    toggleItemDiversionNote();
    updateSerialFields();

    // ---------------- Functions ----------------
    function toggleItemDiversionNote() {
        $('#itemDiversionNoteWrapper').toggle(itemDivertedCheckbox.is(':checked'));
    }

    function updateItemsDisplay() {
        const tableContainer = $('#itemsTableContainer');
        const tableBody = $('#itemsTableBody');

        if (items.length === 0) { tableContainer.hide(); return; }

        tableBody.empty();
        items.forEach((item, index) => {
            const serialNumbers = item.serialNumbers?.length ? item.serialNumbers.join(', ') : 'N/A';
            const dest = item.destination_link_id != null
                ? (destinationsLookup[item.destination_link_id]?.client ?? 
                   newDestinations.find(d => d.tempId === item.destination_link_id)?.client ?? 'N/A') + 
                  ' - ' + 
                  (destinationsLookup[item.destination_link_id]?.location ?? 
                   newDestinations.find(d => d.tempId === item.destination_link_id)?.location ?? '')
                : 'N/A';

            tableBody.append(`
                <tr>
                    <td>${item.item_name}</td>
                    <td>${item.quantity}</td>
                    <td>${dest}</td>
                    <td>${serialNumbers}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-index="${index}">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-index="${index}">Remove</button>
                    </td>
                </tr>
            `);
        });
        tableContainer.show();
        $('#itemsData').val(JSON.stringify(items));
    }

    function updateDestinationDropdown() {
        destinationSelect.empty();
        destinationSelect.append('<option value="">-- No specific destination --</option>');

        // Existing destinations
        Object.entries(destinationsLookup).forEach(([id, dest]) => {
            destinationSelect.append(`<option value="${id}">${dest.client} - ${dest.location}</option>`);
        });

        // New destinations (temp IDs) - only if both client & location filled
        newDestinations.forEach(d => {
            if (d.client && d.location) {
                destinationSelect.append(`<option value="${d.tempId}">${d.client} - ${d.location}</option>`);
            }
        });
    }

    function updateSerialFields() {
        const quantity = parseInt($('#quantity').val()) || 0;
        const container = $('#serialNumbersContainer');

        if (serialCheckbox.is(':checked') && quantity > 0) {
            container.empty();
            for (let i = 1; i <= quantity; i++) {
                container.append(`
                    <div class="col-md-3 mb-2">
                        <input type="text" class="form-control" id="serialNumber${i}" placeholder="Serial Number ${i}">
                    </div>
                `);
            }
            container.show();
        } else container.hide().empty();
    }

    // ---------------- Event Handlers ----------------
    serialCheckbox.on('change', updateSerialFields);
    $('#quantity').on('input', updateSerialFields);
    itemDivertedCheckbox.on('change', toggleItemDiversionNote);

    // Add new destination
    addDestinationBtn.on('click', function () {
        const index = destinationCount++;
        const newDest = { tempId: `new_${index}`, client: '', location: '' };
        newDestinations.push(newDest);

        const html = `
            <div class="destination-item mb-3 p-3 border rounded d-flex flex-column" data-temp-id="${newDest.tempId}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Destination ${index+1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-destination">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control new-destination-client" placeholder="Client Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control new-destination-location" placeholder="Location" required>
                    </div>
                </div>
            </div>
        `;
        destinationsContainer.append(html);

        // Listen for changes on the inputs to update the dropdown live
        const container = destinationsContainer.find(`[data-temp-id="${newDest.tempId}"]`);
        container.find('.new-destination-client, .new-destination-location').on('input', function () {
            newDest.client = container.find('.new-destination-client').val().trim();
            newDest.location = container.find('.new-destination-location').val().trim();
            updateDestinationDropdown();
        });
    });

    // Remove destination
    $(document).on('click', '.remove-destination', function(){
        const parentDiv = $(this).closest('.destination-item');
        const tempId = parentDiv.data('temp-id');
        newDestinations = newDestinations.filter(d => d.tempId !== tempId);
        parentDiv.remove();
        updateDestinationDropdown();
    });

    // Add/Edit item
    addItemBtn.on('click', function () {
        const itemName = $('#item').val().trim();
        const quantity = parseInt($('#quantity').val());
        let destinationLinkId = $('#destination').val();

        if (!itemName || !quantity || quantity <= 0) {
            alert('Enter valid item name and quantity');
            return;
        }

        // Validate new destination selection
        if (destinationLinkId && destinationLinkId.startsWith('new_')) {
            const selectedNew = newDestinations.find(d => d.tempId === destinationLinkId);
            if (!selectedNew.client || !selectedNew.location) {
                alert('Please enter client and location for the new destination before assigning item.');
                return;
            }
        } else if (destinationLinkId) {
            destinationLinkId = Number(destinationLinkId);
        } else destinationLinkId = null;

        // Collect serial numbers
        const serialNumbers = [];
        if (serialCheckbox.is(':checked')) {
            for (let i = 1; i <= quantity; i++) {
                const serial = $(`#serialNumber${i}`).val().trim();
                if (serial) serialNumbers.push(serial);
            }
        }

        const newItem = { item_name: itemName, quantity, serialNumbers, destination_link_id: destinationLinkId };

        if (editingIndex >= 0) {
            items[editingIndex] = newItem;
            editingIndex = -1;
            addItemBtn.text('Add');
        } else items.push(newItem);

        // Reset form
        $('#item').val('');
        $('#quantity').val('');
        serialCheckbox.prop('checked', false);
        $('#destination').val('');
        updateSerialFields();
        updateItemsDisplay();
    });

    // Edit/Delete item (same as before)
    $(document).on('click', '.edit-btn', function () {
        const index = $(this).data('index');
        const item = items[index];

        $('#item').val(item.item_name);
        $('#quantity').val(item.quantity);
        $('#destination').val(item.destination_link_id);

        if (item.serialNumbers?.length > 0) {
            $('#serialnumber').prop('checked', true);
            updateSerialFields();
            setTimeout(() => item.serialNumbers.forEach((serial, i) => $(`#serialNumber${i + 1}`).val(serial)), 100);
        }

        editingIndex = index;
        addItemBtn.text('Save');
    });

    $(document).on('click', '.delete-btn', function () {
        const index = $(this).data('index');
        const item = items[index];

        if (item.id) {
            deletedItemIds.push(item.id);
            $('#deletedItems').val(JSON.stringify(deletedItemIds));
        }

        items.splice(index, 1);
        updateItemsDisplay();
    });

    // On form submit, set hidden inputs for new destinations
    $('#editForm').on('submit', function () {
        newDestinations.forEach((d, i) => {
            $('<input>').attr({
                type:'hidden',
                name:`new_destinations[${i}][client]`,
                value:d.client
            }).appendTo('#editForm');

            $('<input>').attr({
                type:'hidden',
                name:`new_destinations[${i}][location]`,
                value:d.location
            }).appendTo('#editForm');
        });
    });
});
</script>

</x-layout>
