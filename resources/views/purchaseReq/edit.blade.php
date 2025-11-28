<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    
    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Edit Purchase Requisition</h3>
            <h5 class="mb-4">ID: {{ $requisition_id }}</h5>
        </div>

        <form id="editForm" method="post" action="{{ route('purchase.update-all', $requisition_id) }}">
            @csrf
            
            <!-- Requisition Details -->
       <div class="mb-3">
        <label for="project" class="form-label"><strong>Project Description</strong></label>
        <input type="text" class="form-control" id="project" name="project_description" placeholder="Enter project description"
          required value="{{ $requisition->project_description }}">
       </div>
       <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by" placeholder="Enter Approver's Name"
          value="{{ $requisition->approved_by }}" required>
       </div>
       <div class="mb-3">
       <label for="requisitionDate" class="form-label"><strong>Requisition date</strong></label>
        <input type="date" class="form-control" id="requisitionDate" name="requisition_date"
          placeholder="Enter Requisition Date" required max="{{ date('Y-m-d') }}" value="{{ old('requisition_date', optional($requisition->requested_on)->format('Y-m-d')) }}">
       </div>

            <!-- Materials Section -->
            <h4 class="mt-4 mb-3">Materials</h4>
            
            <!-- Add Item Form -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="item" placeholder="Item description">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" id="quantity" placeholder="Quantity">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="addItemBtn">Add</button>
                </div>
            </div>

            <!-- Items Table -->
            <div id="itemsTableContainer" style="display: none;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody"></tbody>
                </table>
            </div>

            <input type="hidden" name="items" id="itemsData">
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg">Save Changes</button>
                <a href="{{ route('purchase.index', $requisition_id) }}" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>

    @php
        $itemsData = $items->map(function($item) {
            return [
                'item_description' => $item->item_description,
                'quantity' => $item->quantity,
            ];
        });
    @endphp

    <script>
        // Initialize with existing items
        let items = @json($itemsData);
        
        let editingIndex = -1;
        
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof jQuery === "undefined") {
                return;
            }

            const addBtn = $('#addItemBtn');
            
            // Display existing items on page load
            updateItemsDisplay();

            function updateItemsDisplay() {
                const tableContainer = $('#itemsTableContainer');
                const tableBody = $('#itemsTableBody');
                
                if (items.length === 0) {
                    tableContainer.hide();
                    return;
                }
                
                tableBody.empty();
                items.forEach((item, index) => {
                    tableBody.append(`
                        <tr>
                            <td>${item.item_description}</td>
                            <td>${item.quantity}</td>
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

            // Add/Update Item
            addBtn.on('click', function() {
                const itemName = $('#item').val().trim();
                const quantity = parseInt($('#quantity').val());
                
                if (!itemName || !quantity || quantity <= 0) {
                    alert('Please enter valid item name and quantity');
                    return;
                }
                
                const newItem = {
                    item_description: itemName,
                    quantity: quantity,
                };
                
                if (editingIndex >= 0) {
                    items[editingIndex] = newItem;
                    editingIndex = -1;
                    addBtn.text('Add');
                } else {
                    items.push(newItem);
                }
                
                // Clear form
                $('#item').val('');
                $('#quantity').val('');
                updateItemsDisplay();
            });

            // Edit Item
            $(document).on('click', '.edit-btn', function() {
                const index = $(this).data('index');
                const item = items[index];
                
                $('#item').val(item.item_description);
                $('#quantity').val(item.quantity);
                
                editingIndex = index;
                addBtn.text('Save');
            });

            // Delete Item
            $(document).on('click', '.delete-btn', function() {
                const index = $(this).data('index');
                if (confirm('Are you sure you want to delete this item?')) {
                    items.splice(index, 1);
                    updateItemsDisplay();
                }
            });
        });
    </script>
</x-layout>

