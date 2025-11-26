<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($purchases->isNotEmpty())
        <!-- <h2 class="mb-4 text-center">Purchase Requisitions</h2>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="get" action="{{ route('purchase.search') }}">
                <input type="text" class="form-control" name="q" placeholder="Purchase Requisition ID" aria-label="Search"
                    aria-describedby="search-button" required>
                <button class="btn bg-warning" type="submit" id="search-button">Search</button>
            </form>

        </div> -->

       <!-- <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div>
-->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Purchase Requisitions</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class=" datatable table table-bordered table-striped table-hover"
                                data-title="Purchase Requisitions">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f ;">No.</th>
                                        <th style="color: white; background-color: #001f3f ;">Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f ;">Created By</th>
                                        <th style="color: white; background-color: #001f3f ;">Requested On</th>
                                        <th style="color: white; background-color: #001f3f ;">Approved By</th>
                                        @if(Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                            <th style="color: white; background-color: #001f3f ;" class="no-export">Action</th>

                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td><a
                                                    href="{{ route('purchase.show', $purchase->requisition_id) }}" class="text-decoration-none fw-bold">{{ $number++ }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('purchase.show', $purchase->requisition_id) }}" class="text-decoration-none fw-bold">{{ $purchase->requisition_id }}</a>
                                            </td>
                                            <td><a href="{{ route('purchase.show', $purchase->requisition_id) }}" class="text-decoration-none fw-bold">{{ $purchase->creator->first_name }}
                                                    {{ $purchase->creator->last_name }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('purchase.show', $purchase->requisition_id) }}" class="text-decoration-none fw-bold">{{ \Carbon\Carbon::parse($purchase->requested_on)->format('d M Y') }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('purchase.show', $purchase->requisition_id) }}" class="text-decoration-none fw-bold">{{ $purchase->approved_by }}</a>
                                            </td>
                                            @if (Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                                <td class="text-center">
                                                    <!-- View Button -->
                                                    <button type="button" class="btn btn-sm btn-primary view-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewModal" 
                                                        data-requisition-id="{{ $purchase->requisition_id }}" 
                                                        data-creator="{{ $purchase->creator->first_name }} {{ $purchase->creator->last_name }}" 
                                                        data-requested-on="{{ \Carbon\Carbon::parse($purchase->requested_on)->format('d M Y') }}" 
                                                        data-approved-by="{{ $purchase->approved_by }}" 
                                                        data-items="{{ json_encode($purchase->items) }}">
                                                        View
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal-{{ $purchase->requisition_id }}">
                                                        Delete
                                                    </button>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal-{{ $purchase->requisition_id }}" tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-{{ $purchase->requisition_id }}" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel-{{ $purchase->requisition_id }}">Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete {{ $purchase->requisition_id }}?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                    <form id="delete-form-{{ $purchase->requisition_id }}" 
                                                                        action="{{ route('purchase.destroy', $purchase->requisition_id) }}" method="POST">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-danger">Yes</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewModalLabel">Purchase Requisition Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Requisition ID:</strong> <span id="modal-requisition-id"></span></p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-items-table">
                                    <!-- Items will be dynamically populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript to handle delete confirmation and export to PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".confirm-delete").forEach(button => {
                    button.addEventListener("click", function () {
                        let requisitionId = this.getAttribute("data-requisition-id");
                        let form = document.getElementById(`delete-form-${requisitionId}`);
                        form.submit();
                    });
                });

                document.getElementById("export-pdf").addEventListener("click", function () {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();

                    // Hide the action column
                    const actionColumn = document.querySelectorAll('#purchase-table th:nth-child(6), #purchase-table td:nth-child(6)');
                    actionColumn.forEach(cell => cell.style.display = 'none');

                    doc.autoTable({
                        html: '#purchase-table'
                    });

                    // Show the action column again
                    actionColumn.forEach(cell => cell.style.display = '');

                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Handle View Button Click
                document.querySelectorAll(".view-btn").forEach(button => {
                    button.addEventListener("click", function () {
                        // Get data attributes from the clicked button
                        const requisitionId = this.getAttribute("data-requisition-id");
                        const items = JSON.parse(this.getAttribute("data-items"));

                        // Populate the modal with the data
                        document.getElementById("modal-requisition-id").textContent = requisitionId;

                        // Populate the items table
                        const itemsTable = document.getElementById("modal-items-table");
                        itemsTable.innerHTML = ""; // Clear previous items
                        items.forEach((item, index) => {
                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.item_description}</td>
                                    <td>${item.quantity}</td>
                                </tr>
                            `;
                            itemsTable.innerHTML += row;
                        });
                    });
                });
            });
        </script>

    @else
        <h2 class="mb-4 text-center">No Purchase Requisition records</h2>

    @endif
</x-layout>