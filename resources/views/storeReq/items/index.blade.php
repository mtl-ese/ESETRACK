<x-layout>
    <x-success></x-success>
    <div class="d-flex">
    <x-back-link href="{{ route('store.index') }}">back</x-back-link>
    <x-add-items href="{{ route('store.add-items-form', session('requisition_id')) }}">Add Items</x-add-items>
    </div>

    <!-- show this when no items are found -->
    <h5 class="mb-4 text-center">
        @if ($items->isNotEmpty())
        @else
            No data on {{ session('requisition_id') }}, click the + icon to add items
        @endif
    </h5>


    <!-- Show this when items are found -->
    @if ($items->isNotEmpty())

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Store Requisition items:
                                {{ $items[0]->store_requisition_id }}</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Store Requisition items-{{ $items[0]->store_requisition_id }}">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f ;">No.</th>
                                        <th style="color: white; background-color: #001f3f ;">Item Name</th>
                                        <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($items as $item)
                                        <tr>
                                            <td><a class="text-decoration-none fw-bold"
                                                    href="{{ route('item.show', [$item->store_requisition_id, $item->id]) }}">{{ $number++ }}</a>
                                            </td>
                                            <td><a class="text-decoration-none fw-bold"
                                                    href="{{ route('item.show', [$item->store_requisition_id, $item->id]) }}">{{ $item->item_name }}</a>
                                            </td>
                                            <td><a class="text-decoration-none fw-bold"
                                                    href="{{ route('item.show', [$item->store_requisition_id, $item->id]) }}">{{ $item->quantity }}</a>
                                            </td>
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
    @endif
</x-layout>