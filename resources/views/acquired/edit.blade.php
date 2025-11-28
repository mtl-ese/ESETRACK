<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-4">Edit Material Acquisition</h3>
            <h5 class="mb-2">Purchase Requisition ID: {{ $acquired->purchase_requisition_id }}</h5>
            <h5 class="mb-4">Project: {{ $acquired->requisition->project_description }}</h5>
        </div>

        @if($acquired->items->isEmpty())
            <div class="alert alert-warning text-center">
                <h4><i class="fas fa-exclamation-triangle"></i> No Items Found</h4>
                <p>This acquisition record has no items associated with it.</p>
                <p>You can either go back or delete this empty acquisition record.</p>
                
                <div class="mt-3">
                    <a href="{{ route('acquired.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                    
                    <form method="POST" action="{{ route('acquired.destroy', $acquired->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this empty acquisition record?')">
                            <i class="fas fa-trash"></i> Delete Empty Record
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Materials Table -->
            <div id="materials_section">
                <form method="post" action="{{ route('acquired.update-all', $acquired->id) }}" id="acquisition_form">
                    @csrf
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item Description</th>
                                    <th>Requested Quantity</th>
                                    <th>Current Acquired</th>
                                    <th>Available Balance</th>
                                    <th>New Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acquired->items as $item)
                                @php
                                    $totalAcquiredOthers = \App\Models\AcquiredItem::where('purchase_item_id', $item->purchase_item_id)
                                        ->where('id', '!=', $item->id)
                                        ->sum('quantity');
                                    $availableBalance = $item->purchaseItem ? $item->purchaseItem->quantity - $totalAcquiredOthers : 0;
                                @endphp
                                <tr>
                                    <td>{{ $item->item_description }}</td>
                                    <td>{{ $item->purchaseItem ? $item->purchaseItem->quantity : 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="fw-bold text-warning">{{ $availableBalance }}</td>
                                    <td>
                                        <input type="number" 
                                               class="form-control" 
                                               name="quantities[{{ $item->id }}]" 
                                               min="1" 
                                               max="{{ $availableBalance }}" 
                                               value="{{ old('quantities.' . $item->id, $item->quantity) }}"
                                               placeholder="Enter qty">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-warning" id="submit_btn">Save Changes</button>
                        <a href="{{ route('acquired.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('acquisition_form')?.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit_btn');
            
            // Disable button to prevent multiple submissions
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            // Re-enable after 3 seconds in case of errors
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update All Materials';
            }, 3000);
        });
    </script>
</x-layout>
