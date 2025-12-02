<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('emergencyItemsIndex', $requisition_id) }}">back</x-back-link>
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
            <h3 class="mb-2">Add items</h3>
            <h5 class="mb-4">Emergency Requisition ID: {{ $requisition_id }}</h5>
        </div>

        <form method="post" action="{{ route('emergencyItemsStore', $requisition_id) }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>
            <div class="row ali">
                <div class="col-12 col-md-3 mb-3">
                    <label class="form-label"><strong>From</strong></label>
                    <select class="form-control" id="from" name="from" required>
                        <option>Select source</option>
                        <option value="stores">Stores</option>
                        <option value="return stores">Return Stores</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label"><strong>Item description</strong></label>
                    <input type="text" class="form-control" id="item" name="item_name"
                        list="materials-list" placeholder="Select or type material name..."
                        autocomplete="off" required>
                    <datalist id="materials-list">
                        @foreach($stores as $store)
                            <option value="{{ $store->item_name }}">
                        @endforeach
                        @foreach($returnsStores as $rs)
                            <option value="{{ $rs->item_name }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label class="form-label"><strong>Quantity</strong></label>
                    <input type="number" class="form-control" id="quantity"
                        name="quantity" min="1" required>
                </div>

                <div class="col-12 col-md-3 mb-3 d-flex align-items-center">
                    <label class="form-label mb-0 me-2"><strong>Serial Number?</strong></label>
                    <input type="checkbox" class="custom-checkbox" id="serialnumber" name="serial_number">
                </div>

                <div class="col-12 col-md-3 mb-3 d-flex align-items-center">
                    <label class="form-label mb-0 me-2 fw-bold">Same item(s) will be returned</label>
                    <input type="checkbox" class="custom-checkbox" id="will_return" name="will_return">
                </div>

                <div class="col-12 col-md-1 mb-3 d-flex align-items-center">
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
                        <th>From</th>
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
<form method="post" action="{{ route('emergencyItemsStore', $requisition_id) }}" id="submitAllForm" style="display: none;">
    @csrf
    <input type="hidden" name="requisition_id" value="{{ $requisition_id }}">
    <input type="hidden" name="items" id="itemsData">
    <div class="text-center mt-4">
        <button type="submit" class="btn bg-warning btn-lg">Submit All Items</button>
    </div>
</form>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const itemInput = document.getElementById('item');
        const datalist = document.getElementById('materials-list');

        // Build JS arrays from PHP-provided collections
        const storeOptions = [
            @foreach($stores as $store)
                '{{ addslashes($store->item_name) }}',
            @endforeach
        ];

        const returnOptions = [
            @foreach($returnsStores as $rs)
                '{{ addslashes($rs->item_name) }}',
            @endforeach
        ];

        // current options shown in datalist
        let originalOptions = [...storeOptions, ...returnOptions];

        // Serial number fields are only shown when the checkbox is checked

        const fromSelect = document.getElementById('from');
        const quantityInput = document.getElementById('quantity');
        const serialCheckbox = document.getElementById('serialnumber');
        const addBtn = document.getElementById('addItemBtn');

        // prepare items array (possibly coming from old input)
        let items = [];
        @if(old('items'))
            items = {!! json_encode(json_decode(old('items'), true)) !!};
        @endif

        let editingIndex = -1;
        const itemsTable = document.getElementById('itemsTableContainer');
        const submitForm = document.getElementById('submitAllForm');

        // initialize datalist with initial options
        function resetDatalist(options) {
            datalist.innerHTML = '';
            options.forEach(opt => {
                const optionElement = document.createElement('option');
                optionElement.value = opt;
                datalist.appendChild(optionElement);
            });
        }

        // Start with combined options
        resetDatalist(originalOptions);

        // filter datalist options based on input
        itemInput.addEventListener('input', function() {
            const inputValue = this.value.toLowerCase();
            const currentOptions = Array.from(datalist.options).map(opt => opt.value);

            // prefer startsWith matches first
            const exactMatches = currentOptions.filter(option => option.toLowerCase().startsWith(inputValue));
            const partialMatches = currentOptions.filter(option => option.toLowerCase().includes(inputValue) && !option.toLowerCase().startsWith(inputValue));

            datalist.innerHTML = '';
            [...exactMatches, ...partialMatches].forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                datalist.appendChild(optionElement);
            });
        });

        // When 'from' value changes, switch datalist options to only show items for that source
        fromSelect.addEventListener('change', function() {
            const v = this.value;
            if (v === 'stores') {
                resetDatalist(storeOptions);
            } else if (v === 'return stores') {
                resetDatalist(returnOptions);
            } else {
                resetDatalist([...storeOptions, ...returnOptions]);
            }
            // trigger serial update when from changes
            updateSerialFields();
        });

        // Function to update serial number fields based on item & quantity
        function updateSerialFields() {
            const itemDesc = (document.getElementById('item').value || '').toLowerCase().trim();
            const from = (document.getElementById('from').value || '').toLowerCase().trim();
            const quantity = parseInt(document.getElementById('quantity').value, 10) || 1;
            const container = document.getElementById('serialNumbersContainer');
            container.innerHTML = '';

            // show serial inputs only when the checkbox is checked
            const shouldShow = serialCheckbox.checked;

            if (shouldShow) {
                for (let i = 1; i <= quantity; i++) {
                    const div = document.createElement('div');
                    div.className = 'mb-3 col-3';
                    div.innerHTML = `
                        <label for="serialNumber${i}"><strong>Serial Number ${i}:</strong></label>
                        <input type="text" class="form-control" placeholder="Enter Serial Number" id="serialNumber${i}" name="serialNumbers[]" required>
                    `;
                    container.appendChild(div);
                }
                // checkbox controls whether serial inputs are shown
            } else {
                // if serials are not required, do not forcibly change the checkbox - let the user control it
            }
        }

        function clearForm() {
            document.getElementById('item').value = '';
            document.getElementById('quantity').value = '';
            document.getElementById('will_return').checked = false;
            serialCheckbox.checked = false;
            document.getElementById('serialNumbersContainer').innerHTML = '';
            editingIndex = -1;
            addBtn.textContent = 'Add';
            // reset to currently selected from's options
            const sel = fromSelect.value;
            if (sel === 'stores') resetDatalist(storeOptions);
            else if (sel === 'return stores') resetDatalist(returnOptions);
            else resetDatalist([...storeOptions, ...returnOptions]);
        }

        function renderItemsTable() {
            const tbody = document.getElementById('itemsTableBody');
            tbody.innerHTML = '';

            items.forEach((item, index) => {
                const serialNumbers = item.serialNumbers && item.serialNumbers.length ? item.serialNumbers.join(', ') : 'N/A';
                tbody.innerHTML += `
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.from || 'N/A'}</td>
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

        // populate items table if there are items from old input
        if (items.length > 0) renderItemsTable();

        // Add / Save item action
        addBtn.addEventListener('click', function() {
            const itemName = document.getElementById('item').value.trim();
            const quantity = parseInt(document.getElementById('quantity').value, 10);
            const fromVal = document.getElementById('from').value;
            const willReturn = document.getElementById('will_return').checked;

            if (!itemName || !quantity || !fromVal) {
                alert('Please fill in the required fields (From, Item and Quantity)');
                return;
            }

            // Validate that item exists in the selected source
            const listToCheck = fromVal === 'stores' ? storeOptions : (fromVal === 'return stores' ? returnOptions : [...storeOptions, ...returnOptions]);
            if (!listToCheck.includes(itemName)) {
                alert('Material "' + itemName + '" is not available in the selected source. Please choose from the available list.');
                return;
            }

            const serialNumbers = [];
            if (serialCheckbox.checked) {
                const serialInputs = document.querySelectorAll('input[name="serialNumbers[]"]');
                serialInputs.forEach(input => {
                    const val = input.value.trim();
                    if (val) serialNumbers.push(val);
                });

                if (serialNumbers.length !== quantity) {
                    alert('Please enter all serial numbers as per quantity');
                    return;
                }
            }

            const itemObj = {
                item_name: itemName,
                quantity: quantity,
                from: fromVal,
                serialNumbers: serialNumbers.length ? serialNumbers : null,
                will_return: willReturn ? 'on' : null
            };

            if (editingIndex >= 0) {
                items[editingIndex] = itemObj;
            } else {
                // Prevent duplicate item in the same requisition
                const dup = items.find(it => it.item_name === itemName && it.from === fromVal);
                if (dup) {
                    alert('This item from the selected source is already added. Edit it instead if needed.');
                    return;
                }
                items.push(itemObj);
            }

            clearForm();
            renderItemsTable();
        });

        // Edit and Delete handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-btn')) {
                const idx = parseInt(e.target.getAttribute('data-index'), 10);
                const itm = items[idx];
                document.getElementById('item').value = itm.item_name;
                document.getElementById('quantity').value = itm.quantity;
                document.getElementById('from').value = itm.from;
                document.getElementById('will_return').checked = itm.will_return === 'on' || itm.will_return === true;

                // Ensure datalist is showing correct source
                if (itm.from === 'stores') resetDatalist(storeOptions);
                else if (itm.from === 'return stores') resetDatalist(returnOptions);

                // Recreate serial inputs (if present)
                if (itm.serialNumbers && itm.serialNumbers.length) {
                    // indicate that serials are present so the serial checkbox is checked before we render inputs
                    serialCheckbox.checked = true;
                    setTimeout(() => {
                        updateSerialFields();
                        itm.serialNumbers.forEach((s, i) => {
                            const input = document.getElementById(`serialNumber${i+1}`);
                            if (input) input.value = s;
                        });
                    }, 10);
                } else {
                    updateSerialFields();
                }

                editingIndex = idx;
                addBtn.textContent = 'Save';
            }

            if (e.target.classList.contains('delete-btn')) {
                const idx = parseInt(e.target.getAttribute('data-index'), 10);
                if (confirm('Are you sure you want to delete this item?')) {
                    items.splice(idx, 1);
                    renderItemsTable();
                }
            }
        });

        // wire up serial/quantity changes
        serialCheckbox.addEventListener('change', updateSerialFields);
        quantityInput.addEventListener('input', updateSerialFields);
        itemInput.addEventListener('input', updateSerialFields);

        // initial state
        updateSerialFields();
    });
    </script>
</x-layout>