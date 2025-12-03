<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-4">Store Return</h3>
        </div>

        <!-- Requisition Selection -->
        <div class="mb-4">
            <label for="requisition_select" class="form-label"><strong>Reference Store Requisition</strong></label>
            <div class="input-group">
                @php $hasRequisitions = count($requisitions ?? []) > 0; @endphp
                <input type="text" class="form-control" id="requisition_select" name="requisition_id" 
                       list="requisitions-list" placeholder="Select or enter requisition ID..." 
                       autocomplete="off" value="{{ old('store_requisition_id') }}">
                <datalist id="requisitions-list">
                    @foreach($requisitions as $requisition)
                        @php
                            // Collect unique client-location pairs safely
                            $clients = $requisition->items
                                ->map(fn($item) => optional($item->destinationLink)->destination
                                                ? optional($item->destinationLink->destination)->client
                                                    . ' - ' .
                                                    optional($item->destinationLink->destination)->location
                                                : null)
                                ->filter() // remove nulls
                                ->unique()
                                ->implode(', ');

                            $label = $clients ? " ($clients)" : '(N/A)';
                        @endphp
                        <option value="{{ $requisition->store_requisition_id }}" label="{{ $label }}"></option>
                    @endforeach
                </datalist>
                <button class="btn btn-primary" type="button" id="load_materials">Load Materials</button>
            </div>
        </div>
        @if(!$hasRequisitions)
            <div class="mt-2 alert alert-info">There are no recovery requisitions yet.</div>
        @endif

        <!-- Materials Table (Hidden initially) -->
        <div id="materials_section" style="display: none;">
            <form method="post" action="{{ route('returns.store') }}" id="return-form">
                @csrf
                <input type="hidden" name="requisition_id" id="selected_requisition_id" value="{{ old('requisition_id') }}">
                
                <div class="mb-3">
                    <label for="approved_by" class="form-label"><strong>Approved By</strong></label>
                    <input type="text" class="form-control" id="approved_by" name="approved_by" 
                           placeholder="Enter current approver" required value="{{ old('approved_by') }}">
                </div>

                <div class="mb-3">
                  <label for="returnDate" class="form-label"><strong>Return Date</strong></label>
                  <input type="date" class="form-control" id="returnDate" name="return_date"
                    placeholder="Enter Return Date" required max="{{ date('Y-m-d') }}" value="{{ old('return_date') }}">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Description</th>
                                <th>Recovered Quantity</th>
                                <th>Already Returned</th>
                                <th>Balance</th>
                                <th>Enter Return Quantity</th>
                                <th>Serial Number(s)</th>
                            </tr>
                        </thead>
                        <tbody id="materials_tbody">
                            <!-- Dynamic content loaded via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn bg-warning" id="submit_btn">Submit Return</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let materialsArray = [];
        let serialDropdownListenerAttached = false;
        const oldSerialSelections = @json(old('serials', []));
        const htmlEscapes = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        function escapeHtml(value) { return String(value ?? '').replace(/[&<>"']/g, c => htmlEscapes[c] || c); }
        function closeSerialDropdowns() {
            document.querySelectorAll('.serial-dropdown').forEach(d => d.style.display = 'none');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const requisitionInput = document.getElementById('requisition_select');
            const datalist = document.getElementById('requisitions-list');
            const originalOptions = Array.from(datalist.options).map(option => ({ value: option.value, label: option.getAttribute('label') }));
            
            requisitionInput.addEventListener('input', function() {
                const inputValue = this.value.toLowerCase();
                datalist.innerHTML = '';
                const exact = originalOptions.filter(o => o.value.toLowerCase().startsWith(inputValue));
                const partial = originalOptions.filter(o => o.value.toLowerCase().includes(inputValue) && !o.value.toLowerCase().startsWith(inputValue));
                [...exact, ...partial].forEach(o => {
                    const el = document.createElement('option');
                    el.value = o.value; el.setAttribute('label', o.label); datalist.appendChild(el);
                });
            });

            const oldRequisitionId = '{{ old("requisition_id") }}';
            if (oldRequisitionId) { requisitionInput.value = oldRequisitionId; loadMaterialsForRequisition(oldRequisitionId); }
        });

        document.getElementById('load_materials').addEventListener('click', function() {
            const requisitionInput = document.getElementById('requisition_select');
            const requisitionId = requisitionInput.value;
            const datalist = document.getElementById('requisitions-list');

            if (!requisitionId) {
                if (datalist && datalist.options.length === 0) {
                    alert('There are no recovery requisitions yet');
                    return;
                }
                alert('Please select a requisition first');
                return;
            }
            loadMaterialsForRequisition(requisitionId);
        });

        function loadMaterialsForRequisition(requisitionId) {
            document.getElementById('selected_requisition_id').value = requisitionId;
            fetch(`/returns/load-materials/${requisitionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        materialsArray = [...data.materials];
                        renderMaterialsTable();
                        document.getElementById('materials_section').style.display = 'block';
                    } else {
                        document.getElementById('materials_section').style.display = 'none';
                        alert(data.message || 'Error loading materials');
                    }
                })
                .catch(error => { console.error('Error:', error); alert('Error loading materials'); });
        }

        function renderMaterialsTable() {
            const tbody = document.getElementById('materials_tbody');
            tbody.innerHTML = '';
            const oldQuantities = @json(old('quantities', []));
            const oldSerialSelections = @json(old('serials', []));
            const filteredMaterials = materialsArray.filter(m => m.balance > 0);

            if (filteredMaterials.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">All materials are fully recovered or have zero balance.</td></tr>`;
                return;
            }

            filteredMaterials.forEach(material => {
                const oldQuantity = oldQuantities[material.item_name] || '';
                const returned = material.returned ?? 0;
                const recovered = material.recovered ?? 0;
                const balance = material.balance ?? Math.max(recovered - returned, 0);
                if (balance <= 0) return;

                // serials comes from the backend as an array of objects with serial_number and returned properties
                const serialNumbers = Array.isArray(material.serials)
                    ? material.serials.filter(s => !s.returned || s.returned === 0)
                    : [];

                const oldSelectionSource = oldSerialSelections[material.item_name] || [];
                const normalizedSelection = Array.isArray(oldSelectionSource) ? oldSelectionSource.map(String) : [String(oldSelectionSource)];

                const serialOptions = serialNumbers.map((s, idx) => {
                    // Each entry is an object with serial_number property
                    const serialValue = String(s.serial_number || s);
                    const isChecked = normalizedSelection.includes(serialValue) ? 'checked' : '';
                    const safeSerial = escapeHtml(serialValue);
                    const checkboxId = `serial-${material.item_name}-${idx}`;
                    return `<div class="form-check">
                                <input class="form-check-input serial-checkbox" type="checkbox" value="${safeSerial}" id="${checkboxId}" name="serials[${material.item_name}][]" ${isChecked}>
                                <label class="form-check-label" for="${checkboxId}">${safeSerial}</label>
                            </div>`;
                }).join('');

                const serialCellContent = serialNumbers.length
                    ? `<div class="serial-selector position-relative" data-serial-container data-material-key="${escapeHtml(material.item_name)}">
                           <button type="button" class="btn btn-sm btn-outline-primary serial-toggle">Select Serial</button>
                           <div class="serial-dropdown card shadow-sm p-3" style="display: none; position: absolute; z-index: 1000; top: 40px; left: 0; min-width: 220px; max-height: 240px; overflow-y: auto;">
                               ${serialOptions || '<p class="mb-0 text-muted">No serials available</p>'}
                           </div>
                       </div>`
                    : '<span class="text-muted">No Serials</span>';

                tbody.innerHTML += `<tr>
                    <td>${escapeHtml(material.item_name)}</td>
                    <td>${recovered}</td>
                    <td>${returned}</td>
                    <td class="fw-bold text-warning">${balance}</td>
                    <td><input type="number" class="form-control" name="quantities[${material.item_name}]" min="1" max="${balance}" value="${escapeHtml(oldQuantity)}" placeholder="Enter return qty"></td>
                    <td>${serialCellContent}</td>
                </tr>`;
            });

            document.getElementById('materials_section').style.display = 'block';
            setupSerialSelection();
        }

        function setupSerialSelection() {
            const containers = document.querySelectorAll('[data-serial-container]');
            containers.forEach(container => {
                const toggle = container.querySelector('.serial-toggle');
                const dropdown = container.querySelector('.serial-dropdown');
                const materialKey = container.getAttribute('data-material-key');
                const quantityInput = document.querySelector(`input[name="quantities[${CSS.escape(materialKey)}]"]`);
                const checkboxes = container.querySelectorAll('.serial-checkbox');

                if (dropdown) dropdown.addEventListener('click', e => e.stopPropagation());
                if (toggle && dropdown) {
                    toggle.addEventListener('click', function (event) {
                        event.stopPropagation();
                        const isOpen = dropdown.style.display === 'block';
                        closeSerialDropdowns();
                        dropdown.style.display = isOpen ? 'none' : 'block';
                    });
                }

                const enforceLimit = () => {
                    const limitValue = quantityInput ? parseInt(quantityInput.value, 10) : 0;
                    const limit = Number.isFinite(limitValue) && limitValue > 0 ? limitValue : 0;

                    if (limit === 0) {
                        checkboxes.forEach(cb => { cb.checked = false; cb.disabled = true; });
                        if (dropdown) dropdown.style.display = 'none';
                        return;
                    }

                    let checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
                    if (checkedBoxes.length > limit) checkedBoxes.slice(limit).forEach(cb => cb.checked = false);
                    checkboxes.forEach(cb => { cb.disabled = !cb.checked && checkedBoxes.length >= limit; });
                };

                if (quantityInput) {
                    quantityInput.addEventListener('input', enforceLimit);
                    quantityInput.addEventListener('change', enforceLimit);
                }
                checkboxes.forEach(cb => cb.addEventListener('change', enforceLimit));
                enforceLimit();
            });

            if (!serialDropdownListenerAttached) {
                document.addEventListener('click', function (event) {
                    if (!event.target.closest('.serial-selector')) closeSerialDropdowns();
                });
                serialDropdownListenerAttached = true;
            }
        }

        document.getElementById('return-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit_btn');
            submitBtn.disabled = true; 
            submitBtn.textContent = 'Processing...';
            setTimeout(function() { submitBtn.disabled = false; submitBtn.textContent = 'Submit Return'; }, 3000);
        });
    </script>
</x-layout>
