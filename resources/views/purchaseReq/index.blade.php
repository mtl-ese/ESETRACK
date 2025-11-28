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
                                    <th style="color: white; background-color: #001f3f ;">Project Description</th>
                                    <th style="color: white; background-color: #001f3f ;">Created By</th>
                                    <th style="color: white; background-color: #001f3f ;">Requested On</th>
                                    <th style="color: white; background-color: #001f3f ;">Approved By</th>
                                    <th style="color: white; background-color: #001f3f ;" class="no-export">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $number = 1
                                @endphp
                                @foreach ($purchases as $purchase)
                                <tr>
                                    <td class="fw-bold" style="color: #007bff;">{{ $number++ }}
                                    </td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $purchase->requisition_id }}
                                    </td>
                                      <td class="fw-bold" style="color: #007bff;">{{ $purchase->project_description }}
                                    </td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $purchase->creator->first_name }}
                                            {{ $purchase->creator->last_name }}
                                    </td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $purchase->requested_on }}
                                    </td>
                                    <td class="fw-bold" style="color: #007bff;">{{ $purchase->approved_by }}
                                    </td>
                                    <td class="text-center">
                                        <!-- View Button -->
                                        <a href="{{ route('purchase.show', $purchase->requisition_id) }}" style="text-decoration:none">
                                            <i class="fas fa-eye" style="color:#001f3f"></i>
                                        </a>

                                        @if (Auth::user()->isAdmin === true || Auth::user()->isSuperAdmin === 1)
                                        <a style="margin-left: 19px; margin-right: 10px; text-decoration:none;" href="{{ route('purchase.edit-form', $purchase->requisition_id) }}">
                                            <i class="fas fa-edit" style="color:#343a40"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form id="delete-form-{{ $purchase->requisition_id }}" 
                                              action="{{ route('purchase.destroy', $purchase->requisition_id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            <button type="button" class="btn btn-link p-0 delete-btn" 
                                                    data-requisition-id="{{ $purchase->requisition_id }}" title="delete">
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
                if (confirm(`Are you sure you want to delete ${requisitionId}?`)) {
                    let form = document.getElementById(`delete-form-${requisitionId}`);
                    form.submit();
                }
            });
        });

        document.getElementById("export-pdf").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            // Hide the action column
            const actionColumn = document.querySelectorAll(
                '#purchase-table th:nth-child(6), #purchase-table td:nth-child(6)');
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
