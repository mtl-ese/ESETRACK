<x-layout>
    <x-success></x-success>
    <x-error></x-error>
    <div class="d-flex justify-content-between mb-1">
        <x-back-link href="{{ route('acquired.index') }}">Back</x-back-link>
        <x-add-items href="{{ route('item.create', $id) }}">Add Items</x-add-items>

    </div>

    @if ($items->isNotEmpty())
        <!-- <h4 class="mb-4 text-center">Acquired Purchase Requisition: {{ $items[0]->acquired->purchase_requisition_id }}</h4> -->

        <div class="row">
            <div class="col-12">
                <div class="card bg-light bg-opacity-50">
                    <div class="card-header border-bottom-1">
                        <h3 class="card-title mt-2 text-lg"><strong>Acquired Purchase Requisition Items:
                                {{ $items[0]->acquired->purchase_requisition_id }}</strong></h3>
                    </div>
                    <div class="card-body bg-light bg-opacity-50">
                        <div class="table-responsive">
                            <table id="example1" class="table table-bordered table-striped table-hover"
                                data-title="Acquired Purchase Requisition Items-{{ $items[0]->acquired->purchase_requisition_id }}">
                                <thead>
                                    <tr>
                                        <th style="color: white; background-color: #001f3f;">No.</th>
                                        <th style="color: white; background-color: #001f3f;">Item Description</th>
                                        <th style="color: white; background-color: #001f3f;">Quantity</th>
                                        <th class="text-dark" style="background-color: rgb(255, 174, 0);">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $number = 1
                                    @endphp
                                    @foreach ($items as $item)
                                        <tr>
                                            <!-- Display item Description, Quantity and balance -->
                                            <td class="fw-bold">
                                                {{ $number++ }}
                                            </td>
                                            <td class="fw-bold">
                                                {{ $item->item_description }}
                                            </td>
                                            <td class="fw-bold">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="text-dark fw-bold" style="background-color: rgba(255, 174, 0,0.75);">
                                                {{ $item->balance }}
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

    @else

        <h4 class="mb-4 text-center">Acquired Purchase Requisition</h4>
        <h5 class="text-center">No items found, click the + icon to add items</h5>

    @endif
</x-layout>