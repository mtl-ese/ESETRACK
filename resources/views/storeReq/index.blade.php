<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($stores->isNotEmpty())
        <!-- <h2 class="mb-4 text-center">Store Requisitions</h2>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('store.search') }}">
                @csrf
                <input type="text" class="form-control" name="q" placeholder="Requisition ID or Customer name"
                    aria-label="Search" aria-describedby="search-button" required>
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>
        </div> -->

        <!-- <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->


        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Store Requisitions</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Store Requisitions">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f ;">No.</th>
                                        <th style="color: white; background-color: #001f3f ;">Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f ;"> Customer name</th>
                                        <th style="color: white; background-color: #001f3f ;">Location</th>
                                        <th style="color: white; background-color: #001f3f ;">Created By</th>
                                        <th style="color: white; background-color: #001f3f ;">Requested On</th>
                                        <th style="color: white; background-color: #001f3f ;">Approved By</th>
                                        @if(Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                            <th class="no-export" style="color: white; background-color: #001f3f ;">Action
                                            </th>
                                        @endif

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($stores as $store)
                                        <tr>
                                            <td><a href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $number++ }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $store->requisition_id }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $store->client_name }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $store->location }}</a>
                                            </td>

                                            <td><a href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $store->creator->first_name }}
                                                    {{ $store->creator->last_name }}</a></td>
                                            <td><a
                                                    href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ \Carbon\Carbon::parse($store->requested_on)->format('d M Y') }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ route('store.show', $store->requisition_id) }}" class="text-decoration-none fw-bold">{{ $store->approved_by }}</a>
                                            </td>
                                            @if (Auth::user()->isAdmin === 1 || Auth::user()->isSuperAdmin === 1)
                                                <td class="text-center">
                                                    <form id="delete-form-{{ $store->requisition_id }}"
                                                        action="{{ route('store.destroy', $store->requisition_id) }}" method="POST">
                                                        @csrf

                                                        <button type="button" class="btn btn-sm btn-light" title="delete"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal-{{ $store->requisition_id }}"
                                                            style="cursor: pointer;">🗑️
                                                        </button>
                                                    </form>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal-{{ $store->requisition_id }}"
                                                        tabindex="-1"
                                                        aria-labelledby="deleteModalLabel-{{ $store->requisition_id }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="deleteModalLabel-{{ $store->requisition_id }}">
                                                                        Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete
                                                                    {{ $store->requisition_id }}?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">No</button>
                                                                    <button type="button" class="btn btn-danger confirm-delete"
                                                                        data-requisition-id="{{ $store->requisition_id }}">Yes</button>
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
                    const actionColumn = document.querySelectorAll('#example1 th:nth-child(8), #example1 td:nth-child(8)');
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

    @else
        <h2 class="mb-4 text-center">No Store Requisition records</h2>

    @endif
</x-layout>