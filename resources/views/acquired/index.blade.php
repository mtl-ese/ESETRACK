<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($purchases->isNotEmpty())
        <!-- <h2 class="mb-4 text-center">Acquired Purchase Requsitions</h2>
            <div class="d-flex justify-content-center">

                <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('acquired.search') }}">
                    @csrf
                    <input type="text" class="form-control" name="q" placeholder="Purchase Requisition ID" aria-label="Search"
                        aria-describedby="search-button" required>
                    <button class="btn btn-warning" type="submit" id="search-button">Search</button>
                </form>

            </div> -->

        <!-- link to add acquired purchase requisitions -->

        <div class="d-flex justify-content-between mb-1">
            <x-add-items href="{{ route('acquired.create') }}">Add Acquired</x-add-items>
            <!-- <x-form-button class="btn" id="export-pdf">Export to PDF</x-form-button> -->
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Acquired Purchase Requisitions</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Acquired Purchase Requisitions">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Purchase Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f;">Date</th>
                                        @if (Auth::user()->isAdmin === true)
                                            <th style="color: white; background-color: #001f3f;" class="no-export">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($purchases as $purchase)

                                        <tr>
                                            <!-- Display purchase requisition ID and date -->
                                            <td>
                                                <a href="{{ route('item.index', $purchase->id)}}" title="show items" class="text-decoration-none fw-bold">
                                                    {{ $number++}}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('item.index', $purchase->id)}}" title="show items" class="text-decoration-none fw-bold">
                                                    {{ $purchase->purchase_requisition_id }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('item.index', $purchase->id)}}" title="show items" class="text-decoration-none fw-bold">
                                                    {{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y') }}
                                                </a>
                                            </td>
                                            </td>
                                            @if (Auth::user()->isAdmin === true)
                                                <td class="text-center">

                                                    <form id="delete-form-{{ $purchase->purchase_requisition_id }}"
                                                        action="{{ route('acquired.destroy', $purchase->purchase_requisition_id) }}"
                                                        method="POST">
                                                        @csrf

                                                        <button type="button" class="btn btn-sm btn-light" title="delete"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal-{{ $purchase->purchase_requisition_id }}"
                                                            style="cursor: pointer;">🗑️
                                                        </button>
                                                    </form>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade"
                                                        id="deleteModal-{{ $purchase->purchase_requisition_id }}" tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-{{ $purchase->purchase_requisition_id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="deleteModalLabel-{{ $purchase->purchase_requisition_id }}">
                                                                        Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete
                                                                    {{ $purchase->purchase_requisition_id }}?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">No</button>
                                                                    <button type="button" class="btn btn-danger confirm-delete"
                                                                        data-requisition-id="{{ $purchase->purchase_requisition_id }}">Yes</button>
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


        <!-- JavaScript to handle export to PDF -->
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
                    doc.autoTable({
                        html: '#acquired-table'
                    });
                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>

    @else
        <h4 class="mb-4 text-center">Acquired Purchase Requsitions</h4>
        <h5 class="text-center">No records found, click the + icon to add acquired purchase requisitions</h5>
        <x-add-items href="{{ route('acquired.create') }}">Add Acquired</x-add-items>
    @endif
</x-layout>