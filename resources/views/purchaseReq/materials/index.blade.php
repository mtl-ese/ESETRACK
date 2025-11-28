<x-layout>
    <x-success></x-success>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('purchase.index') }}">back</x-back-link>
    </div>

    <!-- show this when no items are found -->
    <h5 class="mb-4 text-center">
    @if ($purchaseItems->isNotEmpty())
        {{-- Show nothing when items exist --}}
    @else
        No items are available yet.
    @endif
    </h5>

    <!-- Show this when items are found -->
    @if ($purchaseItems->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Materials for Purchase</strong></h3>         
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="datatable table table-bordered table-striped table-hover"
                            data-title="Purchase Requisition items">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f ;">No.</th>
                                    <th style="color: white; background-color: #001f3f ;">Requisition ID</th>
                                    <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                    <th style="color: white; background-color: #001f3f ;">Project Description</th>
                                    <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                    <th style="color: white; background-color: #001f3f ;">Requisition Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($purchaseItems as $item)
                                <tr>
                                    <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $number++ }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->purchase_requisition_id }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->item_description }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->requisition->project_description }}</td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $item->quantity }}</td>
                                    <td class="fw-bold" style="color: #007bff;"> {{ $item->requisition->requested_on }} </td>
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
