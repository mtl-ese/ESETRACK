<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('store.show', $requisition_id) }}">Back</x-back-link>

    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <style>
    .custom-checkbox {
        width: 20px;
        height: 20px;
        margin-left: 10px;
        cursor: pointer;
    }
    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
    }
    .form-check .form-label {
        margin: 0; 
        line-height: 5;
    }
    </style>
    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add Materials</h3>
            <h5 class="mb-4">Store Requisition ID: {{ $requisition_id }}</h5>
        </div>

        <form method="post" action="{{ route('store.add-items') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            @if(count($destinations) > 0)
            <div class="mb-3">
                <label for="destination" class="form-label"><strong>Select Destination</strong></label>
                <select class="form-select" id="destination" name="destination_link_id">
                    @foreach($destinations as $dest)
                        <option value="{{ $dest['id'] }}">{{ $dest['display'] }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Select a destination for this item</small>
            </div>
            @endif

            <div class="form-group row">
                <div class="mb-3 col-6">
                    <label for="item" class="form-label"><strong>Item description</strong></label>
                    <input type="text" class="form-control" id="item" name="item_name"
                           list="materials-list" placeholder="Select or type material name..."
                           autocomplete="off" required>
                    <datalist id="materials-list">
                        @foreach($stores as $store)
                            <option value="{{ $store->item_name }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="mb-3 col-2">
                    <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" min="1" required>
                </div>

                <div class="col-3">
                    <div class="form-check">
                        <label for="serialnumber" class="form-label"><strong>Serial Number?</strong></label>
                        <input type="checkbox" class="custom-checkbox" id="serialnumber" name="serial_number">
                    </div>
                </div>

                <div class="col-1 d-flex align-items-center">
                   <button type="button" class="btn bg-warning" id="addItemBtn">Add</button>
                </div>
                    
            </div>

            <div id="serialNumbersContainer" class="form-group row">   </div>

        </form>

         <!-- Items Table (Initially Hidden) -->
    <div id="itemsTableContainer" class="mt-4" style="display: none;">
        <h5 class="mb-2 text-center"><strong>Items to be Added</strong></h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Quantity</th>
                        <th>Destination</th>
                        <th>Serial Numbers</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">  </tbody>
            </table>
        </div>
    </div>
       
    </div>

     <!-- Submit All Items Form - Separate form for submission -->
<form method="post" action="{{ route('store.add-items') }}" id="submitAllForm" style="display: none;">
    @csrf
    <input type="hidden" name="requisition_id" value="{{ $requisition_id }}">
    <input type="hidden" name="items" id="itemsData">
    <div class="text-center mt-4">
        <button type="submit" class="btn bg-warning btn-lg">Submit All Items</button>
    </div>
    
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const itemInput = document.getElementById('item');
    const datalist = document.getElementById('materials-list');
    const originalOptions = Array.from(datalist.options).map(option => option.value);
    
    // Check for old input data (after validation error)
    let items = [];
    @if(old('items'))
        items = {!! json_encode(json_decode(old('items'), true)) !!};
    @endif
    
    let editingIndex = -1;
    const serialCheckbox = document.getElementById('serialnumber');
    const addBtn = document.getElementById('addItemBtn');
    const itemsTable = document.getElementById('itemsTableContainer');
    const submitForm = document.getElementById('submitAllForm');

    // Render items table on page load if there are existing items
        if (items.length > 0) {
            renderItemsTable();
        }

        // Filter datalist options based on input
        itemInput.addEventListener('input', function() {
            const inputValue = this.value.toLowerCase();
            
            // Clear existing options
            datalist.innerHTML = '';
            
            // Filter and sort options - exact matches first, then partial matches
            const exactMatches = originalOptions.filter(option => 
                option.toLowerCase().startsWith(inputValue)
            );
            const partialMatches = originalOptions.filter(option => 
                option.toLowerCase().includes(inputValue) && 
                !option.toLowerCase().startsWith(inputValue)
            );
            
            // Add filtered options back to datalist
            [...exactMatches, ...partialMatches].forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                datalist.appendChild(optionElement);
            });
        });

        // Function to update the serial number fields based on item quantity
        function updateSerialFields() {
            let quantity = parseInt(document.getElementById('quantity').value, 10) || 1;
            let container = document.getElementById('serialNumbersContainer');
            container.innerHTML = '';

            if (serialCheckbox.checked) {
                for (let i = 1; i <= quantity; i++) {
                    container.innerHTML += `
                        <div class="mb-3 col-3">
                            <label for="serialNumber${i}"><strong>Serial Number ${i}:</strong></label>
                            <input type="text" class="form-control" placeholder="Enter Serial Number" id="serialNumber${i}" name="serialNumbers[]" required>
                        </div>
                    `;
                }
            }
        }

        function clearForm() {
            document.getElementById('item').value = '';
            document.getElementById('quantity').value = '';
            serialCheckbox.checked = false;
            document.getElementById('serialNumbersContainer').innerHTML = '';
            editingIndex = -1;
            addBtn.textContent = 'Add';
            
            // Reset datalist to show all options
            datalist.innerHTML = '';
            originalOptions.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                datalist.appendChild(optionElement);
            });
        }

        function renderItemsTable() {
        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = '';

        items.forEach((item, index) => {

            // Get destination name from the dropdown (safe even if dropdown is hidden)
            let destinationName = 'N/A';
            const destinationSelect = document.getElementById('destination');
            if (destinationSelect) {
                const option = [...destinationSelect.options].find(opt => opt.value == item.destination_link_id);
                destinationName = option ? option.textContent : 'N/A';
            }

            const serialNumbers = item.serialNumbers ? item.serialNumbers.join(', ') : 'N/A';

            tbody.innerHTML += `
                <tr>
                    <td>${item.item_name}</td>
                    <td>${item.quantity}</td>
                    <td>${destinationName}</td>
                    <td>${serialNumbers}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-index="${index}">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-index="${index}">Delete</button>
                    </td>
                </tr>
            `;
        });

        if (items.length > 0) {
            itemsTable.style.display = 'block';
            submitForm.style.display = 'block';
            document.getElementById('itemsData').value = JSON.stringify(items);
        } else {
            itemsTable.style.display = 'none';
            submitForm.style.display = 'none';
        }
    }

    // Add/Save Item
    addBtn.addEventListener('click', function() {
        const itemName = document.getElementById('item').value.trim();
        const quantity = parseInt(document.getElementById('quantity').value, 10);
        const destinationSelect = document.getElementById('destination');
        const destinationLinkId = destinationSelect ? destinationSelect.value : null;

        if (!itemName || !quantity) {
            alert('Please fill in all required fields');
            return;
        }

        // Check if the item exists in the stores list
        if (!originalOptions.includes(itemName)) {
            alert('Material "' + itemName + '" is not available in stores. Please select from the available materials.');
            return;
        }

        // Check for duplicates (only when adding new items, not when editing)
       // Only when adding new items, not when editing
        if (editingIndex === -1) {
            const existingItem = items.find(item =>
                item.item_name === itemName &&
                item.destination_link_id === destinationLinkId // compare destination too
            );

            if (existingItem) {
                alert('Material "' + itemName + '" is already added for this destination. Edit if needed.');
                return;
            }
        }


        const serialNumbers = [];
        if (serialCheckbox.checked) {
            const serialInputs = document.querySelectorAll('input[name="serialNumbers[]"]');
            serialInputs.forEach(input => {
                const val = input.value.trim();
                if (val) serialNumbers.push(val);
            });

            if (serialNumbers.length !== quantity) {
                alert('Please enter all serial numbers');
                return;
            }
        }

        const item = {
            item_name: itemName,
            quantity: quantity,
            serialNumbers: serialNumbers.length > 0 ? serialNumbers : null,
            destination_link_id: destinationLinkId || null
        };

        if (editingIndex >= 0) {
            items[editingIndex] = item;
        } else {
            items.push(item);
        }

        clearForm();
        renderItemsTable();
    });

    // Edit Item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-btn')) {
            const index = e.target.getAttribute('data-index');
            const item = items[index];
            
            document.getElementById('item').value = item.item_name;
            document.getElementById('quantity').value = item.quantity;
            
            if (item.serialNumbers && item.serialNumbers.length > 0) {
                serialCheckbox.checked = true;
                updateSerialFields();
                
                setTimeout(() => {
                    item.serialNumbers.forEach((serial, i) => {
                        const input = document.getElementById(`serialNumber${i + 1}`);
                        if (input) input.value = serial;
                    });
                }, 100);
            }
            
            editingIndex = index;
            addBtn.textContent = 'Save';
        }
    });

    // Delete Item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            const index = e.target.getAttribute('data-index');
            if (confirm('Are you sure you want to delete this item?')) {
                items.splice(index, 1);
                renderItemsTable();
            }
        }
    });

    // Trigger the update when the serial number checkbox or quantity changes
    serialCheckbox.addEventListener('change', updateSerialFields);
    document.getElementById('quantity').addEventListener('input', updateSerialFields);

    // Trigger an initial update
    updateSerialFields();
});
</script>
</x-layout>
