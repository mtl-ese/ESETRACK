<x-layout>
    @if ($stores->isNotEmpty())
        <h2 class="mb-4 text-center">Store Requisitions</h2>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="get" action="{{ route('store.search') }}">
                <input type="text" class="form-control" name="q" placeholder="Store Requisition ID" aria-label="Search"
                    aria-describedby="search-button" value={{ $query }} required>
                <button class="btn btn-primary" type="submit" id="search-button">Search</button>
            </form>
        </div>

        <div class="d-flex mb-2">
            <a class="btn btn-success mx-auto" href="{{ route('store.index') }}">back</a>
        </div>

        <!-- <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success" id="export-pdf">Export to PDF</button>
        </div> -->

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center" id="store-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Requisition ID</th>
                        <th>Customer name</th>
                        <th>Location</th>
                        <th>Created By</th>
                        <th>Requested On</th>
                        <th>Approved By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1
                    @endphp
                    @foreach ($stores as $store)
                        <tr>
                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $number++ }}</a></td>

                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $store->requisition_id }}</a>
                            </td>
                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $store->client_name }}</a>
                            </td>
                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $store->location }}</a></td>

                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $store->creator->name }}</a>
                            </td>
                            <td><a
                                    href="{{ route('store.show', $store->requisition_id) }}">{{ $store->requested_on }}</a>
                            </td>
                            <td><a href="{{ route('store.show', $store->requisition_id) }}">{{ $store->approved_by }}</a>
                            </td>
                            <td class="text-center">


                                <form id="delete-form" action="{{ route('store.destroy', $store->requisition_id) }}"
                                    method="POST">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-light" title="delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal" style="cursor: pointer;">🗑️</button>
                                    <!-- Logout Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete {{ $store->requisition_id }}?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">No</button>
                                                    <button type="button" class="btn btn-danger" id="confirmdelete">Yes</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <!-- JavaScript to handle delete confirmation and export to PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("export-pdf").addEventListener("click", function () {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();

                    // Hide the action column
                    const actionColumn = document.querySelectorAll('#store-table th:nth-child(8), #store-table td:nth-child(8)');
                    actionColumn.forEach(cell => cell.style.display = 'none');

                    doc.autoTable({
                        html: '#store-table'
                    });

                    // Show the action column again
                    actionColumn.forEach(cell => cell.style.display = '');

                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>
    @else
        <div class="d-flex justify-content-center">
            <form class="input-group mb-3" style="max-width: 400px;" method="get" action="{{ route('purchase.search') }}">
                <input type="text" class="form-control" name="q" placeholder="Store Requisition ID" aria-label="Search"
                    aria-describedby="search-button">
                <button class="btn btn-primary" type="submit" id="search-button">Search</button>
            </form>

        </div>
        <h2 class="mb-4 text-center">No records found</h2>
    @endif
</x-layout>