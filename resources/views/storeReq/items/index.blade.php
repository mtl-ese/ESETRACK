@php
use Illuminate\Support\Arr;
@endphp
<x-layout>
    <x-success></x-success>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('store.index') }}">back</x-back-link>
    </div>

    <!-- show this when no items are found -->
    <h5 class="mb-4 text-center">
        @if ($items->isNotEmpty())
        @else
        No items are available yet.
        @endif
    </h5>


    <!-- Show this when items are found -->
    @if ($items->isNotEmpty())

    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Stores Requisition ID:
                            {{ $items[0]->store_requisition_id }}</strong></h3>
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="datatable table table-bordered table-striped table-hover"
                            data-title="Store Requisition items-{{ $items[0]->store_requisition_id }}">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f ;">No.</th>
                                    <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                    <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                    <th style="color: white; background-color: #001f3f ;">Customer</th>
                                    <th style="color: white; background-color: #001f3f ;">Serial Number</th>
                                </tr>
                            </thead>
                           <tbody>
@php $number = 1; @endphp
@foreach ($items as $item)
<tr>
    <td class="text-decoration-none fw-bold">{{ $number++ }}</td>
    <td class="fw-bold">{{ $item->item_name }}</td>
    <td class="fw-bold">{{ $item->quantity }}</td>
    <td class="fw-bold">{{ $item->destination_info ?? 'N/A' }}</td>
    <td class="fw-bold">
        @php
            $serialNumbers = [];
            // Collect serials from all destinationItems for this item
            foreach ($item->destinationItems as $di) {
                if (!empty($di->serials)) {
                    $serialNumbers = array_merge($serialNumbers, $di->serials);
                }
            }
            // Limit to the item's quantity
            $serialNumbers = array_slice($serialNumbers, 0, $item->quantity);
        @endphp
        {{ !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A' }}
    </td>
</tr>
@endforeach
</tbody>
  </table>
                    </div>
                @if($items[0]->store_requisition->item_diversion_note)
                    <div class="card-header border-bottom-1 bg-warning bg-opacity-50 mt-3">
                        <h2 class="card-title mt-2 text-center"><strong>Item Diversion Note</strong></h2>
                        <h5 class="card-title mt-2 text-center"><strong>
                                {{ $items[0]->store_requisition->item_diversion_note}}</strong></h5>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript to handle export to PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("export-pdf").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            doc.autoTable({
                html: '#items-table'
            });
            window.open(doc.output('bloburl'), '_blank');
        });
    });
    </script>
    @endif
</x-layout>
