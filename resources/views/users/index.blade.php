<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    <div class="d-flex justify-content-between mb-3">
        <x-add-items href="{{ route('usersCreate') }}">Add User</x-add-items>
        <!-- <x-form-button id="export-pdf">Export to PDF</x-form-button> -->
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card bg-light bg-opacity-50">
                <div class="card-header border-bottom-1">
                    <h3 class="card-title mt-2 text-lg"><strong>All Users List</strong></h3>
                </div>
                <div class="card-body bg-light bg-opacity-50">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover" data-title="All Users">
                            <thead>
                                <tr>
                                    <th style="color: white; background-color: #001f3f;">No.</th>
                                    <th style="color: white; background-color: #001f3f;">Full Name</th>
                                    <th style="color: white; background-color: #001f3f;">Email</th>
                                    <th style="color: white; background-color: #001f3f;">Created On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $number = 1; @endphp
                                @foreach ($users as $user)
                                    @if (Auth::user()->id == $user->id)
                                        <tr>
                                            <td class="fw-bold">{{ $number++ }}</td>
                                            <td class="fw-bold">{{ $user->first_name }} {{ $user->last_name }}</td>
                                            <td class="fw-bold">{{ $user->email }}</td>
                                            <td class="fw-bold">{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>
                                                <a href="{{ route('usersShow', $user->id) }}" class="text-decoration-none fw-bold">
                                                    {{ $number++ }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('usersShow', $user->id) }}" class="text-decoration-none fw-bold">
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('usersShow', $user->id) }}" class="text-decoration-none fw-bold">
                                                    {{ $user->email }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('usersShow', $user->id) }}" class="text-decoration-none fw-bold">
                                                    {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
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
            document.getElementById("export-pdf").addEventListener("click", function () {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Add heading to the PDF
                doc.text("Users", 14, 16);

                // Hide the action column
                const actionColumn = document.querySelectorAll('#example1 th:nth-child(10), #example1 td:nth-child(10)');
                actionColumn.forEach(cell => cell.style.display = 'none');

                doc.autoTable({
                    html: '#example1',
                    startY: 40, // Adjust startY to position the table below the heading
                    didDrawPage: function (data) {
                        // Add heading to each page
                        doc.text("Users", 14, 16);
                    }
                });

                // Show the action column again
                actionColumn.forEach(cell => cell.style.display = '');

                window.open(doc.output('bloburl'), '_blank');
            });
        });
    </script>
</x-layout>