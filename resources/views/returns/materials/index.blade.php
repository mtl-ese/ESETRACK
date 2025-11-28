<x-layout>
    <x-success></x-success>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('returns.index') }}">back</x-back-link>
    </div>

    <!-- show this when no items are found -->
    <h5 class=" mb-4 text-center">
            @if ($storeReturnItems->isNotEmpty())
                {{-- Show nothing when items exist --}}
            @else
                No items are available yet.
            @endif
            </h5>

            <!-- Show this when items are found -->
            @if ($storeReturnItems->isNotEmpty())
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light bg-opacity-50">
                            <div class="card-header border-bottom-1">
                                <h3 class="card-title mt-2 text-lg"><strong>Returned Materials</strong></h3>
                            </div>
                            <div class="card-body bg-light bg-opacity-50">
                                <div class="table-responsive">
                                    <table id="example1" class="datatable table table-bordered table-striped table-hover"
                                        data-title="Recovery Requisition items">
                                        <thead>
                                            <tr>
                                                <th style="color: white; background-color: #001f3f ;">No.</th>
                                                <th style="color: white; background-color: #001f3f ;">Store Requisition ID
                                                </th>
                                                <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                                <th style="color: white; background-color: #001f3f ;">Customer Name
                                                </th>
                                                <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                                <th style="color: white; background-color: #001f3f ;">Return Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $number = 1
                                            @endphp
                                            @foreach ($storeReturnItems as $item)
                                                <tr>
                                                    <td class="text-decoration-none fw-bold" style="color: #007bff;">
                                                        {{ $number++ }}</td>
                                                    <td class="fw-bold" style="color: #007bff;">
                                                        {{ $item->store_return?->recovery_store_requisition?->store_requisition_id }}
                                                    </td>
                                                    <td class="fw-bold" style="color: #007bff;">{{ $item->item_name }}</td>
                                                    <td class="fw-bold" style="color: #007bff;">
                                                        @php
                                                            $dest = $item->destinationLink?->destination;
                                                        @endphp
                                                        {{ $dest ? $dest->client . ' - ' . $dest->location : 'N/A' }}
                                                    </td>
                                                    <td class="fw-bold" style="color: #007bff;">{{ $item->quantity }}</td>
                                                    <td class="fw-bold" style="color: #007bff;">
                                                        {{ $item->store_return?->returned_on }} </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
</x-layout>