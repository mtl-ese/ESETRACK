<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($emergencies->isNotEmpty())
        <h2 class="mb-4 text-center">Emergency Requisitions</h2>
        <h4 class="mb-4 text-center">Search results for "{{ $query }}"</h4>

        <div class="d-flex justify-content-center">
            <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('emergencySearch') }}">
                @csrf
                <input type="text" class="form-control" name="q" placeholder="Emergency Requisition ID"
                    aria-label="Search" aria-describedby="search-button" required value="{{$query }}">
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>
        </div>
        <div class="d-flex mb-2">
            <x-back-link href="{{ route('emergencyIndex') }}">back</x-back-link>
        </div>

        <!-- <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->
        <div class="table-responsive shadow">
            <table class="table table-bordered table-hover text-center" id="emergency-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Requisition ID</th>
                        <th>Initiator</th>
                        <th>Department</th>
                        <th>Created By</th>
                        <th>Requested On</th>
                        <th>Approved By</th>
                        <th>Returned On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1
                    @endphp
                    @foreach ($emergencies as $emergency)
                        <tr>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $number++ }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $emergency->requisition_id }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $emergency->initiator }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $emergency->department }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $emergency->creator->first_name }} {{ $emergency->creator->last_name }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ \Carbon\Carbon::parse($emergency->created_at)->format('d M Y') }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">{{ $emergency->approved_by }}</a></td>
                            <td><a href="{{ route('emergencyItemsIndex', $emergency->requisition_id) }}">
                                @if ($emergency->returned_on == null)
                                    Not yet returned
                                @else
                                    {{ \Carbon\Carbon::parse($emergency->returned_on)->format('d M Y') }}
                                @endif
                            </a></td>
                            <td class="text-center">
                                <form id="delete-form-{{ $emergency->requisition_id }}"
                                    action="{{ route('emergencyDestroy', $emergency->requisition_id) }}" method="POST">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-light" title="delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal-{{ $emergency->requisition_id }}" style="cursor: pointer;">🗑️
                                    </button>
                                </form>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal-{{ $emergency->requisition_id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel-{{ $emergency->requisition_id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel-{{ $emergency->requisition_id }}">
                                                    Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete {{ $emergency->requisition_id }}?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">No</button>
                                                <button type="button" class="btn btn-danger confirm-delete"
                                                    data-requisition-id="{{ $emergency->requisition_id }}">Yes</button>
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
                    const actionColumn = document.querySelectorAll('#emergency-table th:nth-child(9), #emergency-table td:nth-child(9)');
                    actionColumn.forEach(cell => cell.style.display = 'none');

                    doc.autoTable({
                        html: '#emergency-table'
                    });

                    // Show the action column again
                    actionColumn.forEach(cell => cell.style.display = '');

                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>

    @else
        <h2 class="mb-4 text-center">No Emergency Requisition records</h2>

    @endif
</x-layout>
