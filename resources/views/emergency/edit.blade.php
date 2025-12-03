<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
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
            <h3 class="mb-2">Edit Emergency Requisition</h3>
            <h5 class="mb-4">ID: {{ $requisition_id }}</h5>
        </div>

        <form id="editForm" method="post" action="{{ route('emergency.update-all', $requisition_id) }}">
            @csrf

            <div class="mb-3">
                <label for="initiator" class="form-label"><strong>Initiator</strong></label>
                <input type="text" class="form-control" id="initiator" name="initiator"
                    value="{{ old('initiator', $requisition->initiator) }}" required>
            </div>

            <div class="mb-3">
                <label for="department" class="form-label"><strong>Department</strong></label>
                <input type="text" class="form-control" id="department" name="department"
                    value="{{ old('department', $requisition->department) }}" required>
            </div>

            <div class="mb-3">
                <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
                <input type="text" class="form-control" id="approvedBy" name="approved_by"
                    value="{{ old('approved_by', $requisition->approved_by) }}" required>
            </div>

            <div class="mb-3">
                <label for="requisitionDate" class="form-label"><strong>Requisition date</strong></label>
                <input type="date" class="form-control" id="requisitionDate" name="requisition_date"
                    value="{{ old('requisition_date', optional($requisition->requested_on)->format('Y-m-d')) }}"
                    required max="{{ date('Y-m-d') }}">
            </div>

            <hr/>

            <h4 class="mt-4 mb-3">Materials</h4>

            <div class="row ali">
                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label"><strong>From</strong></label>
                    <select id="from" class="form-control">
                        <option>Select source</option>
                        <option value="stores">Stores</option>
                        <option value="return stores">Return Stores</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label"><strong>Item description</strong></label>
                    <input type="text" class="form-control" id="item" placeholder="Item description" list="materials-list">
                </div>

                <div class="col-12 col-md-2 mb-3">
                    <label class="form-label"><strong>Quantity</strong></label>
                    <input type="number" class="form-control" id="quantity" placeholder="Quantity">
                </div>

                <div class="col-12 col-md-3 mb-3 d-flex align-items-center">
                    <label class="form-check-label mb-0 me-2"><strong>Serial Number?</strong></label>
                    <input type="checkbox" class="custom-checkbox" id="serialnumber">
                </div>

                <div class="col-12 col-md-3 mb-3 d-flex align-items-center">
                    <label class="form-check-label fw-bold">Same item(s) will be returned</label>
                    <input type="checkbox" class="custom-checkbox" id="will_return" name="will_return">
                </div>

                <div class="col-12 col-md-1 mb-3 d-flex align-items-center">
                    <button type="button" class="btn btn-primary" id="addItemBtn">Add</button>
                </div>
            </div>

            <div id="serialNumbersContainer" class="row mb-3"></div>

            <!-- Items Table -->
            <div id="itemsTableContainer" style="display: none; margin-top:10px">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>From</th>
                            <th>Serial Numbers</th>
                            <th>Same To Return</th>
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
                <a href="{{ route('emergencyIndex', $requisition_id) }}" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>

        @php
            $itemsData = $items->map(function ($item) {
                $serialNumbers = $item->serial_numbers->pluck('serial_number')->filter()->values()->all();

                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'from' => $item->from,
                    'same_to_return' => (int) ($item->same_to_return ?? 0),
                    'serialNumbers' => $serialNumbers,
                ];
            });
        @endphp

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let items = @json($itemsData);
        // Build datalist options from server-provided collections
        const storeOptions = [
            @foreach(($stores ?? collect()) as $store)
                '{{ addslashes($store->item_name) }}',
            @endforeach
        ];

        const returnOptions = [
            @foreach(($returnsStores ?? collect()) as $rs)
                '{{ addslashes($rs->item_name) }}',
            @endforeach
        ];

        // prepare datalist element for suggestions
        const datalistId = 'materials-list';
        let datalistEl = document.getElementById(datalistId);
        if (!datalistEl) {
            datalistEl = document.createElement('datalist');
            datalistEl.id = datalistId;
            document.body.appendChild(datalistEl);
        }

        // initialize combined options
        let originalOptions = [...storeOptions, ...returnOptions];

        function resetDatalist(options) {
            datalistEl.innerHTML = '';
            options.forEach(opt => {
                const optionElement = document.createElement('option');
                optionElement.value = opt;
                datalistEl.appendChild(optionElement);
            });
        }

        // set initial datalist
        resetDatalist(originalOptions);
        let deletedItemIds = [];
        let editingIndex = -1;

        const itemInput = document.getElementById('item');
        const quantityInput = document.getElementById('quantity');
        const fromSelect = document.getElementById('from');
        const serialCheckbox = document.getElementById('serialnumber');
        const itemsTable = document.getElementById('itemsTableContainer');
        const itemsBody = document.getElementById('itemsTableBody');
        const itemsDataInput = document.getElementById('itemsData');
        const deletedItemsInput = document.getElementById('deletedItems');

        function updateSerialFields() {
            const container = document.getElementById('serialNumbersContainer');
            container.innerHTML = '';
            const quantity = parseInt(quantityInput.value) || 0;
            if (serialCheckbox.checked && quantity > 0) {
                for (let i = 1; i <= quantity; i++) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-2';
                    col.innerHTML = `<input type="text" class="form-control" id="serialNumber${i}" placeholder="Serial Number ${i}">`;
                    container.appendChild(col);
                }
            }
        }

        // wire up datalist filtering behaviour (similar to create page)
        itemInput.addEventListener('input', function() {
            const inputValue = this.value.toLowerCase();
            const currentOptions = Array.from(datalistEl.options).map(opt => opt.value);

            const exactMatches = currentOptions.filter(option => option.toLowerCase().startsWith(inputValue));
            const partialMatches = currentOptions.filter(option => option.toLowerCase().includes(inputValue) && !option.toLowerCase().startsWith(inputValue));

            datalistEl.innerHTML = '';
            [...exactMatches, ...partialMatches].forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                datalistEl.appendChild(optionElement);
            });
        });

        fromSelect.addEventListener('change', function() {
            const v = this.value;
            if (v === 'stores') {
                resetDatalist(storeOptions);
            } else if (v === 'return stores') {
                resetDatalist(returnOptions);
            } else {
                resetDatalist([...storeOptions, ...returnOptions]);
            }
            // update serial fields in case from changed
            updateSerialFields();
        });

        function renderItems() {
            itemsBody.innerHTML = '';
            if (items.length === 0) { itemsTable.style.display = 'none'; itemsDataInput.value = JSON.stringify([]); deletedItemsInput.value = JSON.stringify(deletedItemIds); return; }
                items.forEach((it, idx) => {
                const serials = it.serialNumbers?.length ? it.serialNumbers.join(', ') : 'N/A';
                const sameReturn = it.same_to_return || it.will_return || it.sameToReturn ? true : false;
                itemsBody.insertAdjacentHTML('beforeend', `\n                    <tr>\n                        <td>${it.item_name}</td>\n                        <td>${it.quantity}</td>\n                        <td>${it.from || 'N/A'}</td>\n                        <td>${serials}</td>\n                        <td>${sameReturn ? 'Yes' : 'No'}</td>\n                        <td>\n                            <button type=\"button\" class=\"btn btn-sm btn-warning edit-btn\" data-index=\"${idx}\">Edit</button>\n                            <button type=\"button\" class=\"btn btn-sm btn-danger delete-btn\" data-index=\"${idx}\">Remove</button>\n                        </td>\n                    </tr>\n                `);
            });
            itemsTable.style.display = 'block';
            itemsDataInput.value = JSON.stringify(items);
            deletedItemsInput.value = JSON.stringify(deletedItemIds);
        }

        // initialize
        renderItems();
        updateSerialFields();

        document.getElementById('editForm').addEventListener('submit', function () {
            // nothing else to add for now
        });

        // Add/Edit logic
        document.getElementById('itemsTableContainer').addEventListener('click', function (e) {
            // handled below using event delegation
        });
            // Use the inline Add button (id="addItemBtn") and attach the Add/Save behavior
            function addSaveHandler() {
                const name = itemInput.value.trim();
                const qty = parseInt(quantityInput.value, 10) || 0;
                const from = fromSelect.value;
                if (!name || qty <= 0) { alert('Enter item and quantity'); return; }
                let serials = [];
                if (serialCheckbox.checked) {
                    for (let i = 1; i <= qty; i++) {
                        const el = document.getElementById(`serialNumber${i}`);
                        if (el && el.value.trim()) serials.push(el.value.trim());
                    }
                    if (serials.length !== qty) { alert('Please enter all serial numbers'); return; }
                }

                const willReturn = document.getElementById('will_return') ? document.getElementById('will_return').checked : false;
                const obj = { item_name: name, quantity: qty, from: from, same_to_return: willReturn ? 1 : 0, will_return: willReturn, serialNumbers: serials.length ? serials : null };
                const btn = document.getElementById('addItemBtn');
                if (editingIndex >= 0) {
                    const existing = items[editingIndex];
                    if (existing.id) obj.id = existing.id;
                    items[editingIndex] = obj;
                    editingIndex = -1;
                    if (btn) btn.textContent = 'Add';
                } else {
                    items.push(obj);
                }

                itemInput.value = '';
                quantityInput.value = '';
                serialCheckbox.checked = false;
                updateSerialFields();
                renderItems();
            }

            const addBtnElement = document.getElementById('addItemBtn');
            if (addBtnElement) {
                addBtnElement.addEventListener('click', addSaveHandler);
            }

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('edit-btn')) {
                const idx = parseInt(e.target.getAttribute('data-index'), 10);
                const it = items[idx];
                itemInput.value = it.item_name;
                quantityInput.value = it.quantity;
                fromSelect.value = it.from || 'stores';
                if (it.serialNumbers?.length) {
                    serialCheckbox.checked = true;
                    updateSerialFields();
                    setTimeout(() => it.serialNumbers.forEach((s, i) => {
                        const el = document.getElementById(`serialNumber${i+1}`);
                        if (el) el.value = s;
                    }), 50);
                } else {
                    serialCheckbox.checked = !!it.serialNumbers?.length;
                    updateSerialFields();
                }

                // populate the same-to-return checkbox if present on the item
                try {
                    const willReturnEl = document.getElementById('will_return');
                    if (willReturnEl) {
                        willReturnEl.checked = !!(it.same_to_return || it.will_return || it.sameToReturn);
                    }
                } catch (ex) {
                    // ignore
                }
                editingIndex = idx;
                // switch the inline button into Save state so the user can save their edits
                const inlineBtn = document.getElementById('addItemBtn');
                if (inlineBtn) inlineBtn.textContent = 'Save';
            }

            if (e.target.classList.contains('delete-btn')) {
                const idx = parseInt(e.target.getAttribute('data-index'), 10);
                const item = items[idx];
                if (item.id) deletedItemIds.push(item.id);
                items.splice(idx, 1);
                renderItems();
            }
        });

        // wire up serial fields
        serialCheckbox.addEventListener('change', updateSerialFields);
        quantityInput.addEventListener('input', updateSerialFields);
    });
    </script>
</x-layout>
