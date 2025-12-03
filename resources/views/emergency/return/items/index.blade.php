<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    
    <div class="d-flex justify-content-between mb-5">
    <x-back-link href="{{ route('emergency.return.index') }}">Back</x-back-link>

    </div>

    @if ($items->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Emergency Return items: {{ $requisition_id }}</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Emergency Return items: {{ $requisition_id }}">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Item Description</th>
                                        <th style="color: white; background-color: #001f3f;">Return Quantity</th>
                                        <th style="color: white; background-color: #001f3f;">Serial Numbers</th>
                                        <th class="text-dark" style="background-color: rgb(255, 174, 0)">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($items as $item)
                                        <tr>
                                            <td  class="fw-bold text-decoration-none">{{ $number++ }}</td>
                                            <td class="fw-bold text-decoration-none">{{ $item->item_name }}</td>
                                            <td class="fw-bold text-decoration-none">{{ $item->quantity }}</td>
                                            <td class="fw-bold">
                                                @php
                                                    // Get serial numbers from the return item serial numbers
                                                    $serialNumbers = $item->serial_numbers
                                                        ->map(fn($link) => optional($link->itemSerialNumber)->serial_number)
                                                        ->filter()
                                                        ->toArray();
                                                @endphp
                                                {{ !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A' }}
                                            </td>
                                            <td class="fw-bold" style="background-color: rgba(255, 174, 0,0.75)">{{ $item->balance }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <h4 class="mb-4 text-center">Emergency Return items</h4>
        <h5 class="text-center">No items found on this emergency return</h5>

    @endif
</x-layout>
