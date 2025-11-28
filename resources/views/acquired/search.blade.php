<x-layout>
    <x-success></x-success>
    <x-error></x-error>

    @if ($items->isNotEmpty())
        <h2 class="mb-4 text-center">Acquired Purchase requisitions</h2>
        <p class="text-center fw-bold">Search results</p>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('acquired.search') }}">
                @csrf
                <input type="text" class="form-control" name="q" placeholder="Purchase Requisition ID" aria-label="Search"
                    aria-describedby="search-button" value="{{ $query }}" required>
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>

        </div>
        <div class="d-flex mb-2">
            <a class="btn btn-success mx-auto" href="{{ route('acquired.index') }}">back</a>
        </div>

        <a href="{{ route('acquired.create') }}" class="btn btn-success mb-3" title="add items">
            <img class="navbar navbar-brand rounded-5 mx-auto bg-transparent" style="max-height: 40px;"
                src="{{ asset('images/add.png') }}">
        </a>

        <!-- <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success" id="export-pdf">Export to PDF</button>
        </div> -->

        <div class="table-responsive shadow">
            <table class="table table-bordered table-hover text-center" id="acquired-table">
                <thead class="table-dark">
                    <tr>
                        <th>Purchase Requisition ID</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <!-- Display purchase requisition ID and date -->
                            <td>
                                <a href="{{ route('item.create', $item->purchase_requisition_id)}}">
                                    {{ $item->purchase_requisition_id }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('item.create',$item->purchase_requisition_id)}}">
                                    {{ $item->created_at }}
                                </a>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- JavaScript to handle export to PDF -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
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
        <h4 class="mb-4 text-center">Acquired Items</h4>
        <h5 class="text-center">No records found, click the + icon to add acquired items</h5>
        <a href="{{ route('acquired.add-items-form') }}" class="btn btn-success mb-3" title="add items">
            <img class="navbar navbar-brand rounded-5 mx-auto bg-transparent" style="max-height: 40px;"
                src="{{ asset('images/add.png') }}">
        </a>
    @endif
</x-layout>
