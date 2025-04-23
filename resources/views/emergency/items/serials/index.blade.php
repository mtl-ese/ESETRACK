<x-layout>
    <x-success></x-success>
    <x-back-link class="mb-1" href="{{ route('emergencyItemsIndex', $requisition_id) }}">back</x-back-link>

    <div class="d-flex">
    </div>

    <!-- check if serial_numbers are present -->
    @if($serial_numbers->isEmpty())
    <p class="text-center fw-bold">{{ $item_name }} has no serial numbers</p>

    @else

    <!-- Show the serial numbers in a table -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg">{{ $item_name }} serial numbers</strong></h3>
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover"
                            data-title="{{ $item_name }} serial numbers">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f;">No.</th>
                                    <th style="color: white; background-color: #001f3f;">Serial Numbers(s)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1;
                                @endphp
                                @foreach ($serial_numbers as $serial_number)
                                <tr>
                                    <td class="fw-bold">

                                        {{ $number++ }}

                                    </td>
                                    <td class="fw-bold">
                                        {{ $serial_number->serial_number }}
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

    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".confirm-delete").forEach(button => {
            button.addEventListener("click", function() {
                let requisitionId = this.getAttribute("data-requisition-id");
                let form = document.getElementById(`delete-form-${requisitionId}`);
                form.submit();
            });
        });

        document.getElementById("export-pdf").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            // Hide the action column
            const actionColumn = document.querySelectorAll(
                '#example1 th:nth-child(9), #example1 td:nth-child(9)');
            actionColumn.forEach(cell => cell.style.display = 'none');

            doc.autoTable({
                html: '#example1'
            });

            // Show the action column again
            actionColumn.forEach(cell => cell.style.display = '');

            window.open(doc.output('bloburl'), '_blank');
        });
    });
    </script>
</x-layout>