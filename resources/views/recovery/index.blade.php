<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($recoveries->isNotEmpty())
        <!-- <h2 class="mb-4 text-center">Store Returns</h2>
                <div class="d-flex justify-content-center">

                    <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('returns.search') }}">
                        @csrf
                        <input type="text" class="form-control" name="q" placeholder="Store Requisition ID or Old Customer"
                            aria-label="Search" aria-describedby="search-button" required>
                        <button class="btn btn-warning" type="submit" id="search-button">Search</button>
                    </form>
                </div> -->
<!-- 
        <div class="d-flex justify-content-end mb-1">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Recovery Store Requisitions</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Recovery Store Requisitions">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Store Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f;">Customer Name</th>
                                        <th style="color: white; background-color: #001f3f;">Created By</th>
                                        <th style="color: white; background-color: #001f3f;">Recovery Date</th>
                                        <th style="color: white; background-color: #001f3f;">Approved By</th>
                                        
                                            <th style="color: white; background-color: #001f3f;">Action</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($recoveries as $recovery)
                                        <tr>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $number++ }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $recovery->store_requisition_id }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">
                                            @php
                                                $requisition = $recovery->store_requisition;
                                                $clients = $requisition->items
                                                    ->flatMap(fn($item) => $item->destinationItems->map(fn($di) => $di->link?->destination?->client))
                                                    ->unique()
                                                    ->values()
                                                    ->implode(', ');
                                            @endphp
                                                {{ $clients ? $clients : 'N/A' }}
                                            </td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $recovery->creator->first_name }} {{ $recovery->creator->last_name }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $recovery->recovered_on }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $recovery->approved_by }}</td>
                                            <td class="text-center">
                                                <!-- View Button -->
                                                <a href="{{ route('recovery-items.index', ['requisition_id' => $recovery->recovery_requisition_id]) }}" style="text-decoration:none">
                                                    <i class="fas fa-eye" style="color:#001f3f"></i>
                                                </a>
                                                @if (Auth::user()->isAdmin === true || Auth::user()->isSuperAdmin === 1)
                                                <a style="margin-left: 19px; margin-right: 10px; text-decoration:none;" href="{{ route("recovery.edit-form", $recovery->recovery_requisition_id) }}">
                                                    <i class="fas fa-edit" style="color:#343a40"></i>
                                                </a>

                                                <!-- Delete Button -->
                                                <form id="delete-form-{{ $recovery->recovery_requisition_id }}" 
                                                    action="{{ route('recovery.destroy', $recovery->recovery_requisition_id) }}" 
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-link p-0 delete-btn" 
                                                            data-requisition-id="{{ $recovery->recovery_requisition_id }}" 
                                                            title="delete">
                                                        <i class="fas fa-trash" style="color: red;"></i>
                                                    </button>
                                                </form>
                                             @endif
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

        <!-- JavaScript to handle delete confirmation and export to PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".delete-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        let requisitionId = this.getAttribute("data-requisition-id");
                        let formId = this.closest('form').id; // Get the actual form ID
                        if (confirm(`Are you sure you want to delete ${requisitionId}?`)) {
                            let form = document.getElementById(formId);
                            form.submit();
                        }
                    });
                });

                document.getElementById("export-pdf").addEventListener("click", function () {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();

                    // Add heading to the PDF
                    doc.text("Store Returns", 14, 16);

                    // Hide the action column
                    const actionColumn = document.querySelectorAll('#returns-table th:nth-child(10), #returns-table td:nth-child(10)');
                    actionColumn.forEach(cell => cell.style.display = 'none');

                    doc.autoTable({
                        html: '#returns-table',
                        startY: 40, // Adjust startY to position the table below the heading
                        didDrawPage: function (data) {
                            // Add heading to each page
                            doc.text("Store Returns", 14, 16);
                        }
                    });

                    // Show the action column again
                    actionColumn.forEach(cell => cell.style.display = '');

                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>
    @else
        <h2 class="mb-4 text-center">No Store Returns records </h2>

    @endif
</x-layout>
