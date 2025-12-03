<x-layout>
    <x-success></x-success>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('emergency.return.index') }}">back</x-back-link>
    </div>

    <!-- show this when no items are found -->
    <h5 class="mb-4 text-center">
    @if ($returnItems->isNotEmpty())
        {{-- Show nothing when items exist --}}
    @else
        No returned items are available yet.
    @endif
    </h5>

    <!-- Show this when items are found -->
    @if ($returnItems->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Emergency Return Materials</strong></h3>         
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="datatable table table-bordered table-striped table-hover"
                            data-title="Emergency Return Materials">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f ;">No.</th>
                                    <th style="color: white; background-color: #001f3f ;">Requisition ID</th>
                                    <th style="color: white; background-color: #001f3f ;">Department</th>
                                    <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                    <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                    <th style="color: white; background-color: #001f3f ;">Return Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($returnItems as $item)
                                <tr>
                                    <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $number++ }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->returns->emergency_requisition_id ?? 'N/A' }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->returns->requisition->department ?? 'N/A' }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->item_name }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->quantity }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->returns->returned_on ?? 'N/A' }}</td>
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
