<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    <div class="d-flex justify-content-between mb-5">
        <x-back-link href="{{ route('recovery.index') }}">Back</x-back-link>
    </div>

    @if ($items->isNotEmpty())
        <!-- <h2 class="mb-4 text-center">Recovery Store Requisition ID: {{ $requisition_id }} </h2> -->



        <!-- <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Recovery Requisition Items </strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Recovery Store Requisition Items-{{ $requisition_id }} ">
                                <thead>
                                                                <tr>
                                                                                <th style="color: white; background-color: #001f3f;">No.</th>
                                                                                <th style="color: white; background-color: #001f3f;">Item Description</th>
                                                                                <th style="color: white; background-color: #001f3f;">Quantity</th>
                                                                                    <th style="color: white; background-color: #001f3f;">Customer Name</th>
                                                                                <th style="color: white; background-color: #001f3f;">Serial Number</th>
                                                                                <th style="color: white; background-color: #001f3f;">Returned Quantity</th>
                                                                                <th class="text-dark" style="background-color: rgb(255, 174, 0)">Balance</th>
                                                                        </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($items as $item)
                                        <tr>
                                            <td class="text-decoration-none fw-bold"> {{ $number++ }}</td>
                                            <td class="text-decoration-none fw-bold"> {{ $item->item_name }}</td>
                                            <td class="text-decoration-none fw-bold"> {{ $item->quantity }}</td>
                                            <td class="fw-bold">
                                               @php
                                                    $dest = optional(optional($item->destinationLink)->destination);
                                                @endphp

                                                @if ($dest)
                                                    {{ $dest->client ?? 'N/A' }} - {{ $dest->location ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="fw-bold">
                                                @php
                                                    $serialNumbers = [];
                                                    foreach ($item->serial_numbers as $serialRecord) {
                                                        $value = $serialRecord->serial_number;
                                                        if (is_string($value)) {
                                                            $decoded = json_decode($value, true);
                                                            $jsonError = json_last_error();
                                                            if ($jsonError === JSON_ERROR_NONE) {
                                                                if (is_array($decoded)) {
                                                                    foreach ($decoded as $serial) {
                                                                        if ($serial !== null && $serial !== '') {
                                                                            $serialNumbers[] = (string) $serial;
                                                                        }
                                                                    }
                                                                } elseif ($decoded !== null && $decoded !== '') {
                                                                    $serialNumbers[] = (string) $decoded;
                                                                }
                                                            } else {
                                                                $trimmed = trim($value);
                                                                if ($trimmed !== '') {
                                                                    $serialNumbers[] = $trimmed;
                                                                }
                                                            }
                                                        } elseif (is_array($value)) {
                                                            foreach ($value as $serial) {
                                                                if ($serial !== null && $serial !== '') {
                                                                    $serialNumbers[] = (string) $serial;
                                                                }
                                                            }
                                                        } elseif ($value !== null && $value !== '') {
                                                            $serialNumbers[] = (string) $value;
                                                        }
                                                    }
                                                    $serialNumbers = array_values(array_unique(array_filter(array_map(function ($serial) {
                                                        return is_string($serial) ? trim($serial) : (string) $serial;
                                                    }, $serialNumbers), function ($serial) {
                                                        return $serial !== '';
                                                    })));
                                                @endphp
                                                {{ !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A' }}
                                            </td>
                                            <td class="fw-bold">{{ $item->returned_quantity ?? 0 }}</td>
                                            <td class="fw-bold" style="background-color: rgb(255, 174, 0)">{{ $item->balance ?? ($item->quantity - ($item->returned_quantity ?? 0)) }}</td>
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
        <h2 class="mb-4 text-center">Recovery Store Requisition ID: {{ $requisition_id }} </h2>
        <h5 class="text-center">No items found, click the + icon to add items</h5>
    @endif
</x-layout>