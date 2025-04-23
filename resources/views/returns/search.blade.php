<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-back-link href="{{ route('returns.index') }}">back</x-back-link>

    @if ($stores->isNotEmpty())
        <h2 class="mb-4 text-center">Store Returns</h2>
        <h4 class="mb-4 text-center">Search results for "{{ $query }}"</h4>

        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('returns.search') }}">
                @csrf
                <input type="text" class="form-control" name="q" placeholder="Store Requisition ID or Old Customer"
                    aria-label="Search" aria-describedby="search-button" required value="{{ $query }}">
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>

        </div>
      
        <!-- <div class="d-flex justify-content-end mb-1">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="table-responsive shadow">
            <table class="table table-bordered table-hover text-center" id="returns-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Store Requisition ID</th>
                        <th>Old Customer name</th>
                        <th>Location</th>
                        <th>Was created By</th>
                        <th>created By</th>
                        <th>Returned On</th>
                        <th>Was approved By</th>
                        <th>Approved By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1;
                    @endphp
                    @foreach ($stores as $store)
                        <tr>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $number++ }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->store_requisition_id }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->old_client }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->location }}</a>
                            </td>

                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->old_creator->first_name }} {{ $store->old_creator->last_name }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->creator->first_name }} {{ $store->creator->last_name }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ \Carbon\Carbon::parse($store->returned_on)->format('d M Y') }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->was_approved_by }}</a>
                            </td>
                            <td><a
                                    href="{{ route('recovered-items.index', ['store_return_id' => $store->id, 'requisition_id' => $store->store_requisition_id]) }}">{{ $store->approved_by }}</a>
                            </td>

                            <td class="text-center">
                                <form id="delete-form-{{ $store->store_requisition_id }}"
                                    action="{{ route('returns.destroy', $store->store_requisition_id) }}" method="POST">
                                    @csrf

                                    <button type="button" class="btn btn-sm btn-light" title="delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal-{{ $store->store_requisition_id }}"
                                        style="cursor: pointer;">🗑️
                                    </button>
                                </form>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal-{{ $store->store_requisition_id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel-{{ $store->store_requisition_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="deleteModalLabel-{{ $store->store_requisition_id }}">
                                                    Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete {{ $store->store_requisition_id }}?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">No</button>
                                                <button type="button" class="btn btn-danger confirm-delete"
                                                    data-requisition-id="{{ $store->store_requisition_id }}">Yes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                    const actionColumn = document.querySelectorAll('#returns-table th:nth-child(10), #returns-table td:nth-child(10)');
                    actionColumn.forEach(cell => cell.style.display = 'none');

                    doc.autoTable({
                        html: '#returns-table'
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
