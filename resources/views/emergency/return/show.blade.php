<x-layout>
    <div class="card p-5 bg-light bg-opacity-50">
        <h3 class="mb-4">Please confirm the items</h3>
        <form method="post" action="{{ route('emergencyReturnConfirm') }}">
            @csrf
            <input type="text" class="form-control" id="requisitionId" name="requisition_id"
                placeholder="Enter Requisition ID" value="{{ $identity->requisition_id }}" required readonly hidden>
            @php
            $number = 1;
            @endphp
            @foreach($items as $item)
            <div class="mb-3 bg-light p-2 rounded-3 border border-2">
                <h4>{{$number++}}. {{ $item->item_name }}<sup>
                        @if($item->same_to_return === 1)
                        <span class="text-danger">same</span>
                        @else
                        <span class="text-info">new</span>
                        @endif
                    </sup>({{$item->quantity}})</h4>
                @if($item->serial_numbers !== null && $item->same_to_return === 1)
                @foreach($item->serial_numbers as $serial)
                <div class="ms-5">
                    <strong>Serial Number: {{$serial->serial_number}}</strong>
                </div>
                @endforeach
                @endif
            </div>
            @endforeach
            <x-form-button>Confirm</x-form-button>
        </form>
    </div>
</x-layout>