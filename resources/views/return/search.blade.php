<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <x-back-link href="{{ route('return.index') }}">Back</x-back-link>

    @if ($items->isNotEmpty())
        <h2 class="mb-4 text-center">Returns Storage</h2>
        <h4 class="mb-4 text-center">Search results for "{{ $query }}"</h4>

        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="post" action="{{ route('return.search') }}">
                @csrf
                <input type="text" class="form-control" name="q" placeholder="Item name" aria-label="Search"
                    aria-describedby="search-button" required>
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>

        </div>

<!-- 
        <div class="d-flex justify-content-end mb-3">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="table-responsive shadow">
            <table class="table table-bordered table-hover text-center" id="return-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Item name</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1;
                    @endphp
                    @foreach ($items as $item)
                        <tr>
                            <td><a href="{{ route('serial.index', $item->id) }}">{{ $number++ }}</a></td>
                            <td><a href="{{ route('serial.index', $item->id) }}">{{ $item->item_name }}</a></td>
                            <td><a href="{{ route('serial.index', $item->id) }}">{{ $item->quantity }}</a></td>
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
                        html: '#return-table'
                    });
                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>

    @else
        <h2 class="mb-4 text-center">No Return Stores records</h2>

    @endif
</x-layout>
