<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($purchases->isNotEmpty())
        <h2 class="mb-4 text-center">Purchase Requisitions</h2>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="get" action="{{ route('purchase.search') }}">
                <input type="text" class="form-control" name="q" placeholder="Purchase Requisition ID" aria-label="Search"
                    aria-describedby="search-button" required value={{ $query }}>
                <button class="btn btn-primary" type="submit" id="search-button">Search</button>
            </form>

        </div>

        <div class="d-flex mb-2">
            <a class="btn btn-success mx-auto" href="{{ route('purchase.index') }}">back</a>
        </div>
<!--
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success" id="export-pdf">Export to PDF</button>
        </div>
-->
        <div class="table-responsive shadow">
            <table class="table table-bordered table-hover text-center" id="purchase-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Requisition ID</th>
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
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td><a href="{{ route('purchase.show', $purchase->requisition_id) }}">{{ $number++ }}</a>
                            </td>
                            <td><a
                                    href="{{ route('purchase.show', $purchase->requisition_id) }}">{{ $purchase->requisition_id }}</a>
                            </td>
                            <td><a href="{{ route('purchase.show', $purchase->requisition_id) }}">{{ $purchase->creator->first_name }}
                                    {{ $purchase->creator->last_name }}</a>
                            </td>
                            <td><a
                                    href="{{ route('purchase.show', $purchase->requisition_id) }}">{{ $purchase->requested_on }}</a>
                            </td>
                            <td><a
                                    href="{{ route('purchase.show', $purchase->requisition_id) }}">{{ $purchase->approved_by }}</a>
                            </td>
                            <td class="text-center">


                                <form id="delete-form-{{ $purchase->requisition_id }}"
                                    action="{{ route('purchase.destroy', $purchase->requisition_id) }}" method="POST">
                                    @csrf

                                    <button type="button" class="btn btn-sm btn-light" title="delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal-{{ $purchase->requisition_id }}"
                                        style="cursor: pointer;">🗑️
                                    </button>
                                </form>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal-{{ $purchase->requisition_id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel-{{ $purchase->requisition_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $purchase->requisition_id }}">
                                                    Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete {{ $purchase->requisition_id }}?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">No</button>
                                                <button type="button" class="btn btn-danger confirm-delete"
                                                    data-requisition-id="{{ $purchase->requisition_id }}">Yes</button>
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

    @else
        <h2 class="mb-4 text-center">No Purchase Requisition records</h2>

    @endif
</x-layout>