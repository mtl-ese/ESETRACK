
<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
   

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-4">Recovery Store Requisitions</h3>
        </div>

        <!-- Requisition Selection -->
        <div class="mb-4">
    <label for="requisition_select" class="form-label"><strong>Select Store Requisition</strong></label>
    <div class="input-group">
        @if(collect($requisitions)->isEmpty())
            <input type="text" class="form-control" id="requisition_select" name="requisition_id"
                   list="requisitions-list" placeholder="No store requisitions to recover from"
                   autocomplete="off" value="" disabled>
            <datalist id="requisitions-list"></datalist>
            <button class="btn btn-primary" type="button" id="load_materials" disabled>Load Materials</button>
            
        @else
            <input type="text" class="form-control" id="requisition_select" name="requisition_id" 
                   list="requisitions-list" placeholder="Select or enter requisition ID..." 
                   autocomplete="off" value="{{ old('requisition_id') }}">
            <datalist id="requisitions-list">
                @foreach($requisitions as $req)
                    <option value="{{ $req['requisition_id'] }}" label="{{ $req['clients_label'] }}"></option>
                @endforeach
            </datalist>
            <button class="btn btn-primary" type="button" id="load_materials">Load Materials</button>
        @endif
    </div>
</div>

        <!-- Materials Table (Hidden initially) -->
        <div id="materials_section" style="display: none;">
            <form method="post" action="{{ route('recovery.store') }}" id="recovery-form">
                @csrf
                <input type="hidden" name="requisition_id" id="selected_requisition_id" value="{{ old('requisition_id') }}">
                
                <div class="mb-3">
                    <label for="approved_by" class="form-label"><strong>Approved By</strong></label>
                    <input type="text" class="form-control" id="approved_by" name="approved_by" 
                           placeholder="Enter current approver" required value="{{ old('approved_by') }}">
                </div>

                <div class="mb-3">
                  <label for="recoveryDate" class="form-label"><strong>Recovery Date</strong></label>
                  <input type="date" class="form-control" id="recoveryDate" name="recovery_date"
                    placeholder="Enter Recovery Date" required max="{{ date('Y-m-d') }}" value="{{ old('recovery_date') }}">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Description</th>
                                <th>Issued Quantity</th>
                                <th>Already Recovered</th>
                                <th>Balance</th>
                                <th>Customer Name</th>
                                <th>Enter Recovered Quantity</th>
                                <th>Serial Number(s)</th>
                            </tr>
                        </thead>
                        <tbody id="materials_tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn bg-warning" id="submit_btn">Submit Recovery</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let materialsArray = [];
        let serialDropdownListenerAttached = false;
        const oldSerialSelections = @json(old('serials', []));
        const htmlEscapes = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => htmlEscapes[char] || char);
        }

        function closeSerialDropdowns() {
            document.querySelectorAll('.serial-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const requisitionInput = document.getElementById('requisition_select');
            const datalist = document.getElementById('requisitions-list');
            const originalOptions = Array.from(datalist.options).map(option => ({
                value: option.value,
                label: option.getAttribute('label')
            }));
            
            // Filter datalist options based on input
            requisitionInput.addEventListener('input', function() {
                const inputValue = this.value.toLowerCase();
                
                // Clear existing options
                datalist.innerHTML = '';
                
                // Filter and sort options - exact matches first, then partial matches
                const exactMatches = originalOptions.filter(option => 
                    option.value.toLowerCase().startsWith(inputValue)
                );
                const partialMatches = originalOptions.filter(option => 
                    option.value.toLowerCase().includes(inputValue) && 
                    !option.value.toLowerCase().startsWith(inputValue)
                );
                
                // Add filtered options back to datalist with labels
                [...exactMatches, ...partialMatches].forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.setAttribute('label', option.label);
                    datalist.appendChild(optionElement);
                });
            });

            const oldRequisitionId = '{{ old("requisition_id") }}';
            if (oldRequisitionId) {
                requisitionInput.value = oldRequisitionId;
                loadMaterialsForRequisition(oldRequisitionId);
            }
        });

        document.getElementById('load_materials').addEventListener('click', function() {
            const requisitionId = document.getElementById('requisition_select').value;
            
            if (!requisitionId) {
                alert('Please select a requisition first');
                return;
            }

            loadMaterialsForRequisition(requisitionId);
        });

        function loadMaterialsForRequisition(requisitionId) {
    document.getElementById('selected_requisition_id').value = requisitionId;
    
    fetch(`/recovery/load-materials/${requisitionId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            materialsArray = [...data.materials];
            renderMaterialsTable();
            document.getElementById('materials_section').style.display = 'block';
        } else {
            alert('Error loading materials');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading materials');
    });
}

function renderMaterialsTable() {
    const tbody = document.getElementById('materials_tbody');
    tbody.innerHTML = '';

    const oldQuantities = @json(old('quantities', []));
    const oldSerialSelections = @json(old('serials', []));

    const filteredMaterials = materialsArray.filter(m => m.quantity > 0);

    if (filteredMaterials.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    All materials are fully recovered or have zero balance.
                </td>
            </tr>
        `;
        return;
    }

    filteredMaterials.forEach(material => {
        const oldQuantity = oldQuantities[material.id] || '';
        const recovered = material.recovered ?? 0;
        const balance = material.balance ?? (material.quantity - recovered);

       const isFullyRecovered = balance <= 0;

        const serialNumbers = Array.isArray(material.serial_numbers)
            ? material.serial_numbers.filter(s => s)
            : [];

        // Prepare checkboxes for serials
        const oldSelectedSerials = oldSerialSelections[material.id] ?? [];
        const serialOptions = serialNumbers.map((serial, idx) => {
            const serialValue = String(serial);
            const isChecked = Array.isArray(oldSelectedSerials) && oldSelectedSerials.includes(serialValue) ? 'checked' : '';
            const checkboxId = `serial-${material.id}-${idx}`;
            return `
                <div class="form-check">
                    <input class="form-check-input serial-checkbox"
                           type="checkbox"
                           value="${serialValue}"
                           id="${checkboxId}"
                           name="serials[${material.id}][]"
                           ${isChecked} 
                           ${isFullyRecovered} ? 'disabled' : ''>
                    <label class="form-check-label" for="${checkboxId}">${escapeHtml(serialValue)}</label>
                </div>
            `;
        }).join('');

        const serialCellContent = serialNumbers.length
            ? `
                <div class="serial-selector position-relative" data-serial-container data-material-id="${material.id}">
                    <button type="button" class="btn btn-sm btn-outline-primary serial-toggle">Select Serial</button>
                    <div class="serial-dropdown card shadow-sm p-3" style="display: none; position: relative; z-index: 1000; top: 40px; left: 0; min-width: 220px; max-height: 240px; overflow-y: auto;">
                        ${serialOptions || '<p class="mb-0 text-muted">No serials available</p>' }
                    </div>
                </div>
            `
            : '<span class="text-muted">No Serials</span>';

        const row = `
            <tr>
                <td>${escapeHtml(material.item_name)}</td>
                <td>${material.quantity}</td>
                <td>${recovered}</td>
                <td class="fw-bold text-warning">${balance}</td>
                <td>${escapeHtml(material.destination_client)} - ${escapeHtml(material.destination_location)}</td>
                <td>
                    <input type="number"
                           class="form-control"
                           name="quantities[${material.id}]"
                           min="1"
                           max="${balance}"
                           value="${escapeHtml(oldQuantity)}"
                           placeholder="Enter recovered qty"
                           ${isFullyRecovered} ? 'disabled' : ''
                           >
                </td>
                <td>${serialCellContent}</td>
            </tr>
        `;

        tbody.innerHTML += row;
    });

    document.getElementById('materials_section').style.display = 'block';
    setupSerialSelection();
}

function setupSerialSelection() {
    const containers = document.querySelectorAll('[data-serial-container]');

    containers.forEach(container => {
        const toggle = container.querySelector('.serial-toggle');
        const dropdown = container.querySelector('.serial-dropdown');
        const materialId = container.getAttribute('data-material-id');
        const quantityInput = document.querySelector(`input[name="quantities[${materialId}]"]`);
        const checkboxes = container.querySelectorAll('.serial-checkbox');

        if (!quantityInput || checkboxes.length === 0) return;

        // Toggle dropdown
        if (toggle && dropdown) {
            toggle.addEventListener('click', e => {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';
                closeSerialDropdowns();
                dropdown.style.display = isOpen ? 'none' : 'block';
            });
        }

        // Prevent dropdown clicks from closing
        if (dropdown) {
            dropdown.addEventListener('click', e => e.stopPropagation());
        }

        // Enforce selection limit based on quantity input
        const enforceLimit = () => {
            let limit = parseInt(quantityInput.value, 10) || 0;
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    cb.disabled = false;
                } else {
                    cb.disabled = limit === 0 || checkedBoxes.length >= limit;
                }
            });
        };

        quantityInput.addEventListener('input', enforceLimit);
        quantityInput.addEventListener('change', enforceLimit);
        checkboxes.forEach(cb => cb.addEventListener('change', enforceLimit));

        enforceLimit(); // initialize correctly
    });

    // Close dropdown if clicked outside
    if (!serialDropdownListenerAttached) {
        document.addEventListener('click', e => {
            if (!e.target.closest('.serial-selector')) closeSerialDropdowns();
        });
        serialDropdownListenerAttached = true;
    }
}


        document.getElementById('recovery-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit_btn');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Recovery';
            }, 3000);
        });
    </script>
</x-layout>
