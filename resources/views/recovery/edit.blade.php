<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>

    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2 text-bold">Edit Recovery Requisition</h3>
            <h5 class="text-muted d-block">Store Requisition ID: {{ $recovery->store_requisition_id }}</h5>
            <h5 class="text-muted d-block">Client(s):  
                @php
                    $requisition = $recovery->store_requisition;

                    // Collect unique client-location pairs
                    $clientLocations = $requisition->items
                        ->flatMap(function($item) {
                            return $item->destinationItems->map(function($di) {
                                $client = $di->link?->destination?->client ?? 'N/A';
                                $location = $di->link?->destination?->location ?? 'N/A';
                                return "$client - $location";
                            });
                        })
                        ->unique()
                        ->values()
                        ->implode(', ');
                    @endphp
             {{ $clientLocations ?: 'N/A' }}
        </h5>
        </div>

        <form method="post" action="{{ route('recovery.update-all', $recovery->recovery_requisition_id) }}" id="recovery-edit-form">
            @csrf

            <div class="mb-4">
                <div class="mb-3">
                    <label for="approved_by" class="form-label"><strong>Approved By</strong></label>
                    <input type="text"
                           class="form-control"
                           id="approved_by"
                           name="approved_by"
                           required
                           value="{{ old('approved_by', $recovery->approved_by) }}">
                </div>
                <div class="mb-3">
                    <label for="recoveryDate" class="form-label"><strong>Recovery Date</strong></label>
                    <input type="date"
                           class="form-control"
                           id="recoveryDate"
                           name="recovery_date"
                           required
                           max="{{ date('Y-m-d') }}"
                           value="{{ old('recovery_date', optional($recovery->recovered_on)->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="table-responsive mb-3">
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Item Description</th>
                <th>Issued Quantity</th>
                <th>Already Recovered</th>
                <th>Balance</th>
                <th>Client Name</th>
                <th>Update Recovered Quantity</th>
                <th>Serial Numbers</th>
            </tr>
        </thead>
        <tbody id="materials_tbody">
            @forelse ($items as $item)
                @php
                    // Each $item here is already split per destination in the controller
                    $currentSerials = collect($item['serial_numbers'] ?? []);
                    $availableSerialsForItem = collect($availableSerials[$item['item_name']] ?? [])->filter()->values();
                    $oldSerialSelection = collect(old("serials.{$item['id']}", []))->map(fn($v) => (string) $v);

                    $balance = $item['balance'];
                    $oldQuantity = old('items.' . $item['id'] . '.quantity', $item['quantity']);

                    $isDisabled = $balance <= 0 ? 'disabled' : '';
                @endphp

                <tr data-item-row data-item-id="{{ $item['id'] ?? $item['destination_item_id'] }}">
                    <td>{{ $item['item_name'] }}</td>
                    <td>{{ $item['issued_quantity'] }}</td>
                    <td>{{ $item['already_recovered'] }}</td>
                    <td class="fw-bold text-warning">{{ $item['balance'] }}</td>
                    <td>{{ $item['destination']['client'] ?? 'N/A' }} - {{ $item['destination']['location'] ?? 'N/A' }}</td>
                    <td>
                        <input type="number"
                               class="form-control serial-quantity-input"
                               name="items[{{ $item['id'] ?? 'new_' . $item['destination_item_id'] }}][quantity]"
                               min="0"
                               max="{{ $item['max_quantity'] }}"
                               value="{{ old('items.' . ($item['id'] ?? 'new_' . $item['destination_item_id']) . '.quantity', $item['quantity']) }}"
                               data-item-id="{{ $item['id'] ?? $item['destination_item_id'] }}"
                               data-max-quantity="{{ $item['max_quantity'] }}"
                               {{ $item['balance'] <= 0 && $item['quantity'] <= 0 ? 'disabled' : '' }}>
                        @if(!$item['id'])
                            <input type="hidden" name="items[new_{{ $item['destination_item_id'] }}][destination_item_id]" value="{{ $item['destination_item_id'] }}">
                            <input type="hidden" name="items[new_{{ $item['destination_item_id'] }}][store_item_id]" value="{{ $item['store_item_id'] }}">
                        @endif
                    </td>
                    <td>
                        @if($currentSerials->isEmpty() && $availableSerialsForItem->isEmpty())
                            <span class="text-muted">No Serials</span>
                        @else
                            <div class="serial-selector position-relative" data-serial-container data-item-id="{{ $item['id'] ?? $item['destination_item_id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-primary serial-toggle" {{ $isDisabled }}>Select Serials</button>
                                <div class="serial-dropdown card shadow-sm p-3"
                                     style="display:none; position:absolute; z-index:1000; top:40px; left:0; min-width:220px; max-height:240px; overflow-y:auto;">
                                    
                                    {{-- Currently attached serials --}}
                                    @if($currentSerials->isNotEmpty())
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Currently Attached</small>
                                            @foreach($currentSerials as $serial)
                                                @php
                                                    $serialValue = (string) $serial;
                                                    $isChecked = $oldSerialSelection->contains($serialValue) || $currentSerials->contains($serialValue);
                                                @endphp
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input serial-checkbox"
                                                           type="checkbox"
                                                           name="serials[{{ $item['id'] ?? $item['destination_item_id'] }}][]"
                                                           value="{{ $serialValue }}"
                                                           data-item-id="{{ $item['id'] ?? $item['destination_item_id'] }}"
                                                           {{ $isChecked ? 'checked' : '' }}
                                                           {{ $isDisabled }}>
                                                    <label class="form-check-label small">{{ $serialValue }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <hr class="my-2">
                                    @endif

                                    {{-- Available serials --}}
                                    @if($availableSerialsForItem->isNotEmpty())
                                        <div class="mb-1">
                                            <small class="text-muted d-block">Available Serials</small>
                                        </div>
                                        @foreach($availableSerialsForItem as $serial)
                                            @php
                                                $serialValue = (string) $serial;
                                                $isChecked = $oldSerialSelection->contains($serialValue);
                                            @endphp
                                            <div class="form-check">
                                                <input class="form-check-input serial-checkbox"
                                                       type="checkbox"
                                                       name="serials[{{ $item['id'] ?? $item['destination_item_id'] }}][]"
                                                       value="{{ $serialValue }}"
                                                       data-item-id="{{ $item['id'] ?? $item['destination_item_id'] }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       {{ $isDisabled }}>
                                                <label class="form-check-label">{{ $serialValue }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No materials recorded for this recovery.</td>
                </tr>
            @endforelse
        </tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn bg-warning">Update Recovery</button>
                <a href="{{ route('recovery.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const closeDropdowns = () => {
        document.querySelectorAll('.serial-dropdown').forEach(dd => dd.style.display = 'none');
    };

    document.addEventListener('click', closeDropdowns);

    // Serial dropdown toggles
    document.querySelectorAll('[data-serial-container]').forEach(container => {
        const toggle = container.querySelector('.serial-toggle');
        const dropdown = container.querySelector('.serial-dropdown');
        if (!toggle || !dropdown) return;

        toggle.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = dropdown.style.display === 'block';
            closeDropdowns();
            dropdown.style.display = isOpen ? 'none' : 'block';
        });

        dropdown.addEventListener('click', e => e.stopPropagation());
    });

    // Quantity validation
    const validateQuantity = (input) => {
        const value = parseInt(input.value, 10) || 0;
        const max = parseInt(input.dataset.maxQuantity, 10) || 0;
        
        if (value > max) {
            input.value = max;
            alert(`Maximum recoverable quantity is ${max}`);
        }
        
        if (value < 0) {
            input.value = 0;
        }
    };

    // Serial number limits enforcement
    const enforceSerialLimits = (itemId) => {
        const quantityInput = document.querySelector(`.serial-quantity-input[data-item-id="${CSS.escape(itemId)}"]`);
        const checkboxes = document.querySelectorAll(`.serial-checkbox[data-item-id="${CSS.escape(itemId)}"]`);
        if (!quantityInput || checkboxes.length === 0) return;

        const quantity = parseInt(quantityInput.value, 10) || 0;
        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

        // Disable unselected checkboxes if limit reached
        checkboxes.forEach(cb => {
            if (!cb.checked && selectedCount >= quantity) {
                cb.disabled = true;
            } else if (cb.disabled && selectedCount < quantity) {
                cb.disabled = false;
            }
        });

        // Auto-uncheck excess selections
        if (selectedCount > quantity) {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
            for (let i = quantity; i < checkedBoxes.length; i++) {
                checkedBoxes[i].checked = false;
            }
        }
    };

    // Attach event listeners
    document.querySelectorAll('.serial-quantity-input').forEach(input => {
        input.addEventListener('input', () => {
            validateQuantity(input);
            enforceSerialLimits(input.dataset.itemId);
        });
        
        input.addEventListener('blur', () => validateQuantity(input));
        
        // Initial validation
        enforceSerialLimits(input.dataset.itemId);
    });

    document.querySelectorAll('.serial-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => enforceSerialLimits(checkbox.dataset.itemId));
    });

    // Form submission validation
    document.getElementById('recovery-edit-form').addEventListener('submit', function(e) {
        let hasErrors = false;
        const errorMessages = [];

        document.querySelectorAll('.serial-quantity-input').forEach(input => {
            const value = parseInt(input.value, 10) || 0;
            const max = parseInt(input.dataset.maxQuantity, 10) || 0;
            
            if (value > max) {
                hasErrors = true;
                errorMessages.push(`Quantity for item cannot exceed ${max}`);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert('Please fix the following errors:\n' + errorMessages.join('\n'));
        }
    });
});
</script>
