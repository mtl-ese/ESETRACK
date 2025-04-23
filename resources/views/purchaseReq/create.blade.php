<x-layout>
  <x-error></x-error>
  <x-error-any></x-error-any>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Purchase Requisition</h3>
    <form method="post" action="{{ route('store.purchase') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID" value="{{ old('requisition_id') }}" required>
      </div>

      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by" placeholder="Enter Approver's Name"
          value="{{ old('approved_by') }}" required>
      </div>
      <x-form-button>Create</x-form-button>
    </form>
  </div>

</x-layout>