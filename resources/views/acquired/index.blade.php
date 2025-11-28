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
                                        <th style="color: white; background-color: #001f3f;">Project Description</th>
                                        <th style="color: white; background-color: #001f3f;">Date</th>
                                    
                                            <th style="color: white; background-color: #001f3f;" class="no-export">Action</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($purchases as $purchase)

                                        <tr>
                                            <!-- Display purchase requisition ID and date -->
                                            <td class="fw-bold" style="color: #007bff;">
                                                    {{ $number++}}
                                            </td>
                                            <td class="fw-bold" style="color: #007bff;">
                                                    {{ $purchase->purchase_requisition_id }}
                                            </td>
                                            <td class="fw-bold" style="color: #007bff;">
                                                    {{ $purchase->requisition->project_description }}
                                            </td>
                                            <td class="fw-bold" style="color: #007bff;">
                                                    {{ $purchase->requisition->requested_on}}
                                            </td>
                                            </td>
                                             <td class="text-center">
                                                <!-- View Button -->
                                                <a href="{{ route('item.index', $purchase->id) }}" style="text-decoration:none">
                                                    <i class="fas fa-eye" style="color:#001f3f"></i>
                                                </a>

                                                @if (Auth::user()->isAdmin === true || Auth::user()->isSuperAdmin === 1)
                                                <a style="margin-left: 19px; margin-right: 10px; text-decoration:none;" href="{{ route('acquired.edit-form', $purchase->id) }}">
                                                    <i class="fas fa-edit" style="color:#343a40"></i>
                                                </a>

                                                <!-- Delete Button -->
                                                <form id="delete-form-{{ $purchase->id }}" 
                                                    action="{{ route('acquired.destroy', $purchase->id) }}" 
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-link p-0 delete-btn" 
                                                            data-requisition-id="{{ $purchase->purchase_requisition_id }}" title="delete">
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

        <!-- JavaScript to handle export to PDF -->
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
            });
        </script>
    @else
        <h4 class="mb-4 text-center">Acquired Purchase Requsitions</h4>
        <h5 class="text-center">No records found, click the + icon to add acquired purchase requisitions</h5>
        <x-add-items href="{{ route('acquired.create') }}">Add Acquired</x-add-items>
    @endif
</x-layout>
