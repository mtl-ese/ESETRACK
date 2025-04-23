<x-layout>
    <x-success></x-success>
    <x-back-link class="mb-1" href="{{ route('recovered-items.index', [$store_return_id, $requisition_id]) }}">back</x-back-link>

    <!-- check if serial_numbers are present -->
    @if($serial_numbers->isEmpty())

        <p class="text-center fw-bold">{{ $serial_numbers[0]->item->item_name }} has no serial numbers</p>

    @else

        <!-- Show the serial numbers in a table -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>{{ ucwords($serial_numbers[0]->item->item_name ) }} Serial Numbers</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="{{ ucwords($serial_numbers[0]->item->item_name ) }} Serial Numbers-{{ $requisition_id }}">
                                <thead>

                                    <tr>
                                        <th style="color: white; background-color: #001f3f ;">No.</th>
                                        <th style="color: white; background-color: #001f3f ;">Serial Numbers(s)</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($serial_numbers as $serial_number)
                                        <tr>
                                            <td class="fw-bold">

                                                {{ $number++ }}

                                            </td>
                                            <td class="fw-bold">
                                                {{ $serial_number->serial_numbers }}
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
    @endif
</x-layout>