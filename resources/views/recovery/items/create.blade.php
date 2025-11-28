<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('recovery-items.index', $requisition_id) }}">Back</x-back-link>

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
            <h5 class="mb-4">Recovery Store Requisition ID: {{ $requisition_id }}</h5>
        </div>

        <form method="post" action="{{ route('recovery-items.store') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            <div class="form-group row">
                <div class="mb-3 col-6">
                    <label for="item" class="form-label"><strong>Item description</strong></label>
                    <input type="text" class="form-control" id="item" name="item_name" 
                           list="materials-list" placeholder="Select or type material name..." 
                           autocomplete="off" required>
                    <datalist id="materials-list">
                        @foreach($returnStores as $returnStore)
                            <option value="{{ $returnStore->item_name }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="mb-3 col-2">
                    <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" min="1" required>
                </div>

                <div class="mt-2 col-3">
                    <div class="form-check">
                        <label class="form-label fw-bold">Serial Numbers</label>
                        <input type="checkbox" class="custom-checkbox" id="serialCheckbox">
                    </div>
                </div>

                <div class="col-1 d-flex align-items-center">
                    <button type="button" class="btn btn-primary" id="addBtn">Add</button>
                </div>
            </div>

            <div id="serialNumbersContainer" class="form-group row"></div>
        </form>

        <!-- Items Table -->
        <div id="itemsTable" style="display: none;">
            <h5 class="mt-4">Added Items:</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Serial Numbers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Submit All Items Form -->
    <form method="post" action="{{ route('recovery-items.store') }}" id="submitAllForm" style="display: none;">
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
        const serialCheckbox = document.getElementById('serialCheckbox');
        const addBtn = document.getElementById('addBtn');
        const itemsTable = document.getElementById('itemsTable');
        const submitForm = document.getElementById('submitAllForm');
        
        let items = [];
        let editingIndex = -1;

        // Check for old input data (after validation error)
        @if(old('items'))
            items = {!! json_encode(json_decode(old('items'), true)) !!};
        @endif

        // Update this function to filter out already added items
        function updateDatalistOptions(inputValue = '') {
            datalist.innerHTML = '';
            
            const availableOptions = originalOptions.filter(option => 
                // Filter out already added items (except when editing)
                !items.some((item, index) => index !== editingIndex && item.item_name === option)
            );
            
            const filteredOptions = availableOptions.filter(option => 
                option.toLowerCase().includes(inputValue.toLowerCase())
            );
            
            filteredOptions.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                datalist.appendChild(optionElement);
            });
        }

        // Filter datalist options based on input
        itemInput.addEventListener('input', function() {
            updateDatalistOptions(this.value);
        });

        // Serial number checkbox handler
        serialCheckbox.addEventListener('change', updateSerialFields);
        document.getElementById('quantity').addEventListener('input', updateSerialFields);

        function updateSerialFields() {
            const container = document.getElementById('serialNumbersContainer');
            container.innerHTML = '';
            
            if (serialCheckbox.checked) {
                const quantity = parseInt(document.getElementById('quantity').value, 10) || 1;
                for (let i = 1; i <= quantity; i++) {
                    container.innerHTML += `
                        <div class="mb-3 col-3">
                            <label for="serialNumber${i}"><strong>Serial Number ${i}:</strong></label>
                            <input type="text" class="form-control" placeholder="Enter Serial Number" 
                                   id="serialNumber${i}" name="serialNumbers[]" required>
                        </div>
                    `;
                }
            }
        }

        function updateItemsDisplay() {
            const tbody = document.getElementById('itemsTableBody');
            tbody.innerHTML = '';
            
            items.forEach((item, index) => {
                const serialDisplay = item.serialNumbers && item.serialNumbers.length > 0 
                    ? item.serialNumbers.join(', ') 
                    : 'N/A';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>${serialDisplay}</td>
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
            
            if (!itemName || !quantity) {
                alert('Please fill in all required fields');
                return;
            }

            // Check if the item exists in the return stores list
            if (!originalOptions.includes(itemName)) {
                alert('Material "' + itemName + '" is not available in return stores. Please select from the available materials.');
                return;
            }

            // Check for duplicates (only when adding new items, not when editing)
            if (editingIndex === -1) {
                const existingItem = items.find(item => item.item_name === itemName);
                if (existingItem) {
                    alert('Material "' + itemName + '" is already added to the list. Please select a different material or edit the existing entry.');
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

            const itemData = {
                item_name: itemName,
                quantity: quantity,
                serialNumbers: serialNumbers
            };

            if (editingIndex === -1) {
                items.push(itemData);
            } else {
                items[editingIndex] = itemData;
                editingIndex = -1;
                addBtn.textContent = 'Add';
            }

            // Clear form and update displays
            document.getElementById('item').value = '';
            document.getElementById('quantity').value = '';
            serialCheckbox.checked = false;
            updateSerialFields();
            updateItemsDisplay();
            
            // Refresh datalist options
            updateDatalistOptions();
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
                items.splice(e.target.getAttribute('data-index'), 1);
                updateItemsDisplay();
                // Refresh datalist options after deletion
                updateDatalistOptions();
            }
        });

        // Initial datalist setup
        updateDatalistOptions();

        // Initialize display if there are old items
        if (items.length > 0) {
            updateItemsDisplay();
        }
    });
    </script>
</x-layout>
