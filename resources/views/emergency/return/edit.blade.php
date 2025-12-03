<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-error-any></x-error-any>
    
    <div class="card p-5 bg-light bg-opacity-50">
        <div class="text-center">
            <h3 class="mb-2 text-bold">Edit Emergency Return</h3>
            <h5 class="mb-4">Emergency Requisition ID: {{ $requisitionId }}</h5>
        </div>

        <form method="post" action="{{ route('emergency.return.update-all', $requisitionId) }}">
            @csrf
            
            <div class="mb-3">
                <label for="approved_by" class="form-label"><strong>Approved By</strong></label>
                <input type="text" class="form-control" id="approved_by" name="approved_by" 
                       value="{{ old('approved_by', $emergencyReturn->approved_by) }}" required>
            </div>

            <div class="mb-3">
                <label for="return_date" class="form-label"><strong>Return Date</strong></label>
                <input type="date" class="form-control" id="return_date" name="return_date" 
                       value="{{ old('return_date', optional($emergencyReturn->returned_on)->format('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
            </div>

            <!-- Returned Items Section -->
            <h4 class="mt-4 mb-3">Returned Items</h4>

            @if($emergencyReturn->items->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Return Quantity</th>
                            <th>Current Return Balance</th>
                            <th>Update Return Quantity</th>
                            <th>Serial Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emergencyReturn->items as $item)
                            @php
                                $assignedSerials = collect($item->serial_numbers ?? [])
                                    ->map(fn($link) => optional($link->itemSerialNumber)->serial_number)
                                    ->filter()
                                    ->values();

                                $availableSerialsForItem = collect($availableSerials[$item->item_name] ?? [])->filter()->values();
                                $oldSerialSelection = collect(old("serials.{$item->item_name}", []))->map(fn($v) => (string) $v);
                            @endphp

                            <tr data-item-row data-item-name="{{ $item->item_name }}">
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>@php
                                    $requisitionItem = $emergencyReturn->requisition->items
                                        ->firstWhere('item_name', $item->item_name);
                                    $balance = $requisitionItem ? $requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0) : 0;
                                @endphp
                                {{ $balance }}</td>
                                <td>
                                    @php
                                        $maxReturnable = $item->quantity + max($item->balance, 0);
                                    @endphp
                                    <input type="number" class="form-control serial-quantity-input" required
                                           name="quantities[{{ $item->item_name }}]"
                                           value="{{ old("quantities.{$item->item_name}", $item->quantity) }}"
                                           min="1" max="{{ $maxReturnable }}"
                                           placeholder="Update return quantity"
                                           data-item-name="{{ $item->item_name }}">
                                </td>
                                <td>
                                    @php
                                        $allSerials = $assignedSerials->merge($availableSerialsForItem)->unique()->values();
                                    @endphp

                                    @if($allSerials->isEmpty())
                                        <span class="text-muted">No Serials</span>
                                    @else
                                        <div class="serial-selector position-relative" data-serial-container>
                                            <button type="button" class="btn btn-sm btn-outline-primary serial-toggle">Serials</button>
                                            <div class="serial-dropdown card shadow-sm p-3"
                                                 style="display:none; position:absolute; z-index:1000; top:40px; left:0; min-width:240px; max-height:260px; overflow-y:auto;">
                                                @foreach($allSerials as $serial)
                                                    @php
                                                        $isChecked = $oldSerialSelection->contains($serial) || $assignedSerials->contains($serial);
                                                    @endphp
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input serial-checkbox"
                                                               type="checkbox"
                                                               name="serials[{{ $item->item_name }}][]"
                                                               value="{{ $serial }}"
                                                               data-item-name="{{ $item->item_name }}"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label small">{{ $serial }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-warning">
                No items found for this return.
            </div>
            @endif

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg">Update Return</button>
                <a href="{{ route('emergency.return.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Close all dropdowns
                const closeDropdowns = () => {
                    document.querySelectorAll('.serial-dropdown').forEach(dropdown => dropdown.style.display = 'none');
                };
                document.addEventListener('click', closeDropdowns);

                // Toggle dropdowns
                document.querySelectorAll('[data-serial-container]').forEach(container => {
                    const toggle = container.querySelector('.serial-toggle');
                    const dropdown = container.querySelector('.serial-dropdown');
                    if (!toggle || !dropdown) return;

                    toggle.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const isOpen = dropdown.style.display === 'block';
                        closeDropdowns();
                        dropdown.style.display = isOpen ? 'none' : 'block';
                    });

                    dropdown.addEventListener('click', e => e.stopPropagation());
                });

                // Enforce serial selection limits
                const enforceSerialLimits = (itemName) => {
                    const quantityInput = document.querySelector(`.serial-quantity-input[data-item-name="${CSS.escape(itemName)}"]`);
                    const checkboxes = document.querySelectorAll(`.serial-checkbox[data-item-name="${CSS.escape(itemName)}"]`);
                    if (!quantityInput || checkboxes.length === 0) return;

                    const maxQty = parseInt(quantityInput.value, 10) || 0;
                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

                    checkboxes.forEach(cb => cb.disabled = !cb.checked && checkedCount >= maxQty);
                };

                document.querySelectorAll('.serial-quantity-input').forEach(input => {
                    input.addEventListener('input', () => enforceSerialLimits(input.dataset.itemName));
                    enforceSerialLimits(input.dataset.itemName);
                });

                document.querySelectorAll('.serial-checkbox').forEach(cb => {
                    cb.addEventListener('change', () => enforceSerialLimits(cb.dataset.itemName));
                });
            });
            </script>
        </form>
    </div>
</x-layout>
