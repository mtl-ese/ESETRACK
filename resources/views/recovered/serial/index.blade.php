<x-layout>
    <x-success></x-success>
    <x-back-link href="{{ route('recovered.index') }}" class="mb-1">Back</x-back-link>

    <!-- <h5 class="mb-4 text-center">
        Item name: {{ $item_name }}
    </h5> -->

    <!-- check if serial_numbers are present -->
    @if($serials->isEmpty())

        <p class="text-center fw-bold">{{ $item_name }} has no serial numbers</p>

    @else

        <!-- Show the serial numbers in a table -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>{{ $item_name }} Serial Numbers</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="{{ $item_name }} Serial Numbers">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Serial Numbers(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1;
                                    @endphp
                                    @foreach ($serials as $serial)
                                        <tr>
                                            <td class="fw-bold">

                                                {{ $number++ }}

                                            </td>
                                            <td class="fw-bold">
                                                {{ $serial->serial_numbers }}
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