<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($stores->isNotEmpty())
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
                        <h3 class="card-title mt-2 text-lg"><strong>Store Returns</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Store Returns">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Store Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f;">Old Customer name</th>
                                        <th style="color: white; background-color: #001f3f;">Location</th>
                                        <th style="color: white; background-color: #001f3f;">Was created By</th>
                                        <th style="color: white; background-color: #001f3f;">created By</th>
                                        <th style="color: white; background-color: #001f3f;">Returned On</th>
                                        <th style="color: white; background-color: #001f3f;">Was approved By</th>
                                        <th style="color: white; background-color: #001f3f;">Approved By</th>
                                        @if(Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                            <th style="color: white; background-color: #001f3f;">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($stores as $store)
                                        <tr>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $number++ }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->store_requisition_id }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->old_client }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->location }}</a>
                                            </td>

                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->old_creator->first_name }}
                                                    {{ $store->old_creator->last_name }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->creator->first_name }}
                                                    {{ $store->creator->last_name }}</a></td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ \Carbon\Carbon::parse($store->returned_on)->format('d M Y') }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->was_approved_by }}</a>
                                            </td>
                                            <td><a href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}"
                                                    class="text-decoration-none fw-bold">{{ $store->approved_by }}</a>
                                            </td>
                                            @if(Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                                <td class="text-center">
                                                    <!-- View Button -->
                                                    <button type="button" class="btn btn-sm btn-primary view-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewModal" 
                                                        data-requisition-id="{{ $store->store_requisition_id }}" 
                                                        data-items="{{ json_encode($store->items) }}"
                                                        data-balance="{{ $store->balance }}">
                                                        View
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <button type="button" class="btn btn-sm btn-light" title="delete"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal-{{ $store->store_requisition_id }}"
                                                        style="cursor: pointer;">🗑️
                                                    </button>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal-{{ $store->store_requisition_id }}" tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-{{ $store->store_requisition_id }}" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel-{{ $store->store_requisition_id }}">Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete {{ $store->store_requisition_id }}?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                                    <button type="button" class="btn btn-danger confirm-delete"
                                                                        data-requisition-id="{{ $store->store_requisition_id }}">Yes</button>
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
                        <h5 class="modal-title" id="viewModalLabel">Store Return Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Requisition ID:</strong> <span id="modal-requisition-id"></span></p>

                        <h5 class="mt-4">Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Balance</th> <!-- New Balance Column -->
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

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Handle View Button Click
                document.querySelectorAll(".view-btn").forEach(button => {
                    button.addEventListener("click", function () {
                        // Get data attributes from the clicked button
                        const requisitionId = this.getAttribute("data-requisition-id");
                        const balance = this.getAttribute("data-balance");
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
                                    <td>${item.item_name || 'N/A'}</td>
                                    <td>${item.quantity || 'N/A'}</td>
                                    <td>${item.balance || balance || 'N/A'}</td> <!-- Populate Balance -->
                                </tr>
                            `;
                            itemsTable.innerHTML += row;
                        });
                    });
                });
            });
        </script>

    @else
        <h2 class="mb-4 text-center">No Store Returns records </h2>

    @endif
</x-layout>