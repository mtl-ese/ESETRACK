<x-layout>
    <x-success></x-success>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('recovery.index') }}">back</x-back-link>
    </div>

    <!-- show this when no items are found -->
    <h5 class="mb-4 text-center">
    @if ($recoverystorerequisitionItems->isNotEmpty())
        {{-- Show nothing when items exist --}}
    @else
        No items are available yet.
    @endif
    </h5>

    <!-- Show this when items are found -->
    @if ($recoverystorerequisitionItems->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Recovered Materials</strong></h3>         
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="datatable table table-bordered table-striped table-hover"
                            data-title="Recovery Requisition items">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f ;">No.</th>
                                    <th style="color: white; background-color: #001f3f ;">Store Requisition ID</th>
                                    <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                    <th style="color: white; background-color: #001f3f ;">Project Description</th>
                                    <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                    <th style="color: white; background-color: #001f3f ;">Recovery Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($recoverystorerequisitionItems as $item)
                                <tr>
                                    <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $number++ }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->recovery_store_requisition->store_requisition_id }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->item_name }}</td>
                                    <td class="fw-bold" style="color: #007bff;"> 
                                     {{ $item->destinationLink && $item->destinationLink->destination 
                                        ? $item->destinationLink->destination->client . ' - ' . $item->destinationLink->destination->location 
                                        : 'N/A' }}
                                    </td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->quantity }}</td>
                                    <td class="fw-bold" style="color: #007bff;"> {{ $item->recovery_store_requisition->recovered_on }} </td>
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
