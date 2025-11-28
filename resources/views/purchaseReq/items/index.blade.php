<x-layout>

    <x-success></x-success>

    <div class="d-flex">
        <x-back-link style="max-height: fit-content;" href="{{ route('purchase.index') }}">Back</x-back-link>
    </div>

    <h5 class="mb-4 text-center">
        @if ($items->isNotEmpty())
        <!-- Purchase Requisition ID: {{ $items[0]->purchase_requisition_id }} -->
        @else

        No data on {{ session('requisition_id') }}, click the + icon to add items
        @endif

    </h5>


    @if ($items->isNotEmpty())
    <!--
        <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div>
-->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>Purchase Requisition Items:
                            {{ session('requisition_id') }}</strong></h3>
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover"
                            data-title="Purchase Requisition Items - {{ session('requisition_id') }}">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f ;">No.</th>
                                    <th style="color: white; background-color: #001f3f ;">Item name</th>
                                    <th style="color: white; background-color: #001f3f ;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($items as $item)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $number++}}
                                    </td>
                                    <td class="fw-bold">
                                        {{ $item->item_description}}
                                    </td>
                                    <td class="fw-bold">
                                        {{ $item->quantity}}
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