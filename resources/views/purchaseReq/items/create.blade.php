<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    <x-back-link href="{{ route('purchase.show', $requisition_id) }}" class="mb-3">Back</x-back-link>

    <head>
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
    </head>
    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2">Add Materials</h3>
            <h5 class="mb-4">Purchase Requisition ID: {{ $requisition_id }}</h5>
        </div>

        <form method="post" action="{{ route('purchase.add-items') }}">
            @csrf
            <div class="mb-3">
                <input type="text" class="form-control" id="requisition_id" name="requisition_id"
                    value="{{ $requisition_id }}" readonly hidden>
            </div>

            <div class="form-group row">
                <div class="mb-3 col-6">
                    <label for="item" class="form-label"><strong>Item description</strong></label>
                    <input type="text" class="form-control" id="item" name="item_name" placeholder="Enter item description"
                        required>
                </div>

                <div class="mb-3 col-2">
                    <label for="quantity" class="form-label"><strong>Quantity</strong></label>
                    <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Enter quantity" min="1" required>
                </div>

                <div class="mt-3 col-1 d-flex align-items-center">
                   <button type="button" class="btn bg-warning" id="addItemBtn">Add</button>
                </div>
                    
            </div>

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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">  </tbody>
            </table>
        </div>
    </div>
       
    </div>

     <!-- Submit All Items Form - Separate form for submission -->
<form method="post" action="{{ route('purchase.add-items') }}" id="submitAllForm" style="display: none;">
    @csrf
    <input type="hidden" name="requisition_id" value="{{ $requisition_id }}">
    <input type="hidden" name="items" id="itemsData">
    <div class="text-center mt-4">
        <button type="submit" class="btn bg-warning btn-lg">Submit All Items</button>
    </div>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ensure jQuery is available before running
        if (typeof jQuery === "undefined") {
            return;
        }

        $(document).ready(function() {

            let items = [];
            let editingIndex = -1;
            const addBtn = $('#addItemBtn');
            const itemsTable = $('#itemsTableContainer');
            const submitForm = $('#submitAllForm');
            
        function clearForm() {
            $('#item').val('');
            $('#quantity').val('');
            editingIndex = -1;
            addBtn.text('Add');
        }

        
        function renderItemsTable() {
            const tbody = $('#itemsTableBody');
            tbody.empty();

            items.forEach((item, index) => {
                tbody.append(`
                    <tr>
                        <td>${item.item_name}</td>
                        <td>${item.quantity}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-index="${index}">Edit</button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-index="${index}">Remove</button>
                        </td>
                    </tr>
                `);
            });

            if (items.length > 0) {
                itemsTable.show();
                submitForm.show();
                $('#itemsData').val(JSON.stringify(items));
            } else {
                itemsTable.hide();
                submitForm.hide();
            }
        }

                // Add/Save Item
        addBtn.on('click', function() {
            const itemName = $('#item').val().trim();
            const quantity = parseInt($('#quantity').val(), 10);
            
            if (!itemName || !quantity) {
                alert('Please fill in all required fields');
                return;
            }

            const item = {
                item_name: itemName,
                quantity: quantity,
            };

            if (editingIndex >= 0) {
                const hasDuplicate = items.some((existing, idx) => idx !== editingIndex && existing.item_name.toLowerCase() === itemName.toLowerCase());
                if (hasDuplicate) {
                    alert('This material is already in the list. Please add it just once for the requisition.');
                    return;
                }
                items[editingIndex] = item;
            } else {
                const hasDuplicate = items.some(existing => existing.item_name.toLowerCase() === itemName.toLowerCase());
                if (hasDuplicate) {
                    alert('This material is already in the list. Please add it just once for the requisition.');
                    return;
                }
                items.push(item);
            }

            clearForm();
            renderItemsTable();
        });

          // Edit Item
        $(document).on('click', '.edit-btn', function() {
            const index = $(this).data('index');
            const item = items[index];
            
            $('#item').val(item.item_name);
            $('#quantity').val(item.quantity);
            
            editingIndex = index;
            addBtn.text('Save');
        });

        // Add delete functionality
        $(document).on('click', '.delete-btn', function() {
            const index = $(this).data('index');
                items.splice(index, 1);
                renderItemsTable();
        });
        });
    });
    </script>
</x-layout>
