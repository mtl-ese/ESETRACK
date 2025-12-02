<x-layout>
  <x-error></x-error>
  <x-success></x-success>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Emergency Return</h3>
    <form method="post" action="{{ route('emergencyReturnStore') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID" value="{{ old('requisition_id') }}" required>
      </div>
      <div class="mb-3">
                  <label for="returnDate" class="form-label"><strong>Return Date</strong></label>
                  <input type="date" class="form-control" id="returnDate" name="return_date"
                    placeholder="Enter Return Date" required max="{{ date('Y-m-d') }}" value="{{ old('return_date') }}">
                </div>
      <x-form-button>Create</x-form-button>
    </form>
  </div>

</x-layout>