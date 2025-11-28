<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('emergencyIndex') }}">Back</x-back-link>
        <x-add-items href="{{ route('emergencyItemsCreate', $requisition_id) }}">Add Items</x-add-items>
    </div>

    @if ($items->isNotEmpty())
    <!-- <h2 class="mb-4 text-center">Emergency Purchase Requisition ID: {{ $requisition_id }} </h2> -->



    <!-- <div class="d-flex justify-content-end mb-1">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Emergency Requisition Items:
                            {{$requisition_id }}</strong></h3>
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover"
                            data-title="Emergency Requisition Items-{{$requisition_id }}">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f;">No.</th>
                                    <th style="color: white; background-color: #001f3f;">Item Description</th>
                                    <th style="color: white; background-color: #001f3f;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $number++ }}</td>
                                    <td class="fw-bold">
                                        @if ($item->serial_numbers->isNotEmpty())
                                        <a href="{{ route('emergencyItemSerialsIndex', $item->id) }}"
                                            class="text-decoration-none">{{ $item->item_name }}</a>
                                        @else
                                        {{ $item->item_name }}
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ $item->quantity }}</td>
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

    @else
    <h2 class="mb-4 text-center">Emergency Requisition ID: {{ $requisition_id }} </h2>
    <h5 class="text-center">No items found, click the + icon to add items</h5>
    @endif
</x-layout>