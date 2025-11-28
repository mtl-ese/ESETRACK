<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-4">Material Acquisition</h3>
        </div>

        <!-- Requisition Selection -->
        <div class="mb-4">
            <label for="requisition_select" class="form-label"><strong>Select Purchase Requisition</strong></label>
            <div class="input-group">
                <select class="form-select" id="requisition_select" name="requisition_id">
                    <option value="">Choose a requisition...</option>
                    @foreach($requisitions as $requisition)
                        <option value="{{ $requisition->requisition_id }}" 
                            {{ old('requisition_id') == $requisition->requisition_id ? 'selected' : '' }}>
                            {{ $requisition->requisition_id }} - {{ $requisition->project_description }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="button" id="load_materials">Load Materials</button>
            </div>
        </div>

        <!-- Materials Table (Hidden initially) -->
        <div id="materials_section" style="display: none;">
            <form method="post" action="{{ route('acquired.store') }}" id="acquisition_form">
                @csrf
                <input type="hidden" name="requisition_id" id="selected_requisition_id" value="{{ old('requisition_id') }}">
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Description</th>
                                <th>Requested Qty</th>
                                <th>Already Acquired</th>
                                <th>Balance</th>
                                <th>Enter Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="materials_tbody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-success" id="submit_btn">Submit All Materials</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we have old input (after validation error or redirect)
            const oldRequisitionId = '{{ old("requisition_id") }}';
            if (oldRequisitionId) {
                document.getElementById('requisition_select').value = oldRequisitionId;
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
            fetch('{{ route("acquired.loadMaterials") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ requisition_id: requisitionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('selected_requisition_id').value = requisitionId;
                    
                    const tbody = document.getElementById('materials_tbody');
                    tbody.innerHTML = '';
                    
                    const oldQuantities = @json(old('quantities', []));
                    
                    data.materials.forEach(material => {
                        const oldQuantity = oldQuantities[material.id] || '';
                        
                        const row = `
                            <tr>
                                <td>${material.description}</td>
                                <td>${material.requested}</td>
                                <td>${material.acquired}</td>
                                <td class="fw-bold text-warning">${material.balance}</td>
                                <td>
                                    <input type="number" 
                                           class="form-control" 
                                           name="quantities[${material.id}]" 
                                           min="1" 
                                           max="${material.balance}" 
                                           value="${oldQuantity}"
                                           placeholder="Enter qty">
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                    
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

        document.getElementById('acquisition_form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit_btn');
            
            // Disable button to prevent multiple submissions
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            // Re-enable after 3 seconds in case of errors
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit All Materials';
            }, 3000);
        });
    </script>
</x-layout>
