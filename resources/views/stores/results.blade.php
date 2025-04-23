<x-layout>
<x-back-link href="{{ route('stores.index') }}">Back</x-back-link>

    @if ($items->isNotEmpty())

        <h4 class="mb-4 text-center">Stores</h4>
        <div class="d-flex justify-content-center">

            <form class="input-group mb-3" style="max-width: 400px;" method="get" action="{{ route('stores.search') }}">
                <input type="text" class="form-control" name="q" placeholder="Item name" aria-label="Search"
                    aria-describedby="search-button" value="{{ $query }}" required>
                <button class="btn btn-warning" type="submit" id="search-button">Search</button>
            </form>
        </div>

        <!-- <div class="d-flex justify-content-end mb-1">
            <x-form-button id="export-pdf">Export to PDF</x-form-button>
        </div> -->

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center" id="stores-table">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Item</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1
                    @endphp
                    @foreach ($items as $item)
                        <tr>
                            <td>
                                <a>{{ $number++ }}</a>
                            </td>
                            <td>
                                {{ $item->item_name }}
                            </td>
                            <td>
                                {{ $item->quantity }}
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
                        html: '#stores-table'
                    });

                    window.open(doc.output('bloburl'), '_blank');
                });
            });
        </script>
    @else
        <h4 class="mb-4 text-center">Stores</h4>
        <h5 class="text-center">No items found</h5>
    @endif
</x-layout>