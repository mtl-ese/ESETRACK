<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    
    <div class="d-flex justify-content-between">
    <x-back-link href="{{ route('returns.index') }}">Back</x-back-link>
    <x-add-items
            href="{{ route('recovered-items.create', ['store_return_id' => $store_return_id, 'requisition_id' => $requisition_id]) }}">Add
            Items</x-add-items>
    </div>

    @if ($items->isNotEmpty())
        <!-- <h4 class="mb-4 text-center">Store Return items</h4>
        <h5 class="mb-4 text-center">Store requisition ID: {{ $requisition_id }}</h5> -->

        

        <!-- <div class="d-flex justify-content-end mb-1">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Store Return items: {{ $requisition_id }}</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Store Return items: {{ $requisition_id }}">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Item Description</th>
                                        <th style="color: white; background-color: #001f3f;">Status</th>
                                        <th style="color: white; background-color: #001f3f;">Quantity</th>
                                        <th class="text-dark" style="background-color: rgb(255, 174, 0)">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($items as $item)
                                        <tr>
                                            <td ><a href="{{ route('recoveredItemSerialIndex',[$store_return_id, $requisition_id]) }}" class="fw-bold text-decoration-none">{{ $number++ }}</a></td>
                                            <td><a href="{{ route('recoveredItemSerialIndex',[$store_return_id, $requisition_id]) }}" class="fw-bold text-decoration-none">{{ $item->item_name }}</a></td>
                                            <td><a href="{{ route('recoveredItemSerialIndex',[$store_return_id, $requisition_id]) }}" class="fw-bold text-decoration-none">{{ $item->status }}</a></td>
                                            <td><a href="{{ route('recoveredItemSerialIndex',[$store_return_id, $requisition_id]) }}" class="fw-bold text-decoration-none">{{ $item->quantity }}</a></td>
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

        <!-- JavaScript to handle export to PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("export-pdf").addEventListener("click", function () {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    doc.autoTable({
                        html: '#items-table'
                    });
                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>

    @else
        <h4 class="mb-4 text-center">Store Return items</h4>
        <h5 class="text-center">No items found on this store return, click the + icon to add items</h5>

    @endif
</x-layout>