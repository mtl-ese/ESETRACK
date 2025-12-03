<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($returns->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Emergency Returns</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Emergency Returns">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Requisition ID</th>
                                        <th style="color: white; background-color: #001f3f;">Created By</th>
                                        <th style="color: white; background-color: #001f3f;">Returned On</th>
                                        <th style="color: white; background-color: #001f3f;">Approved By</th>
                                        <th style="color: white; background-color: #001f3f;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($returns as $return)
                                        <tr>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $number++ }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $return->requisition?->requisition_id }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $return->creator->first_name }} {{ $return->creator->last_name }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $return->returned_on }}</td>
                                            <td class="text-decoration-none fw-bold" style="color: #007bff;">{{ $return->approved_by }}</td>

                                            <td class="text-center">
                                                <!-- View Button -->
                                                <a href="{{ route('emergency.return.items.index', [$return->id, $return->requisition?->requisition_id]) }}" style="text-decoration:none">
                                                    <i class="fas fa-eye" style="color:#001f3f"></i>
                                                </a>
                                                 @if (Auth::user()->isAdmin === true || Auth::user()->isSuperAdmin === 1)
                                                <a style="margin-left: 19px; margin-right: 10px; text-decoration:none;" href="{{ route('emergency.return.edit-form', $return->requisition?->requisition_id) }}">
                                                    <i class="fas fa-edit" style="color:#343a40"></i>
                                                </a>

                                                <!-- Delete Button -->
                                                <form id="delete-form-{{ $return->id }}" 
                                                    action="{{ route('emergency.return.destroy', $return->emergency_requisition_id) }}" 
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-link p-0 delete-btn" 
                                                            data-return-id="{{ $return->id }}" 
                                                            data-reference="{{ $return->requisition?->requisition_id }}" 
                                                            title="delete">
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

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".delete-btn").forEach(button => {
                    button.addEventListener("click", function() {
                        const returnId = this.getAttribute("data-return-id");
                        const reference = this.getAttribute("data-reference") || returnId;
                        if (confirm(`Are you sure you want to delete ${reference}?`)) {
                            const form = document.getElementById(`delete-form-${returnId}`);
                            if (form) {
                                form.submit();
                            }
                        }
                    });
                });
            });
        </script>
    @else
        <h2 class="mb-4 text-center">No Emergency Return records </h2>

    @endif
</x-layout>
