<x-layout>
  <x-error></x-error>
  <x-error-any></x-error-any>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Recovery Store Requisition</h3>

    <form method="post" action="{{ route('recovery.store') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisition_id" name="requisition_id"
          placeholder="Enter Requisition ID" required>
      </div>

      <div class="mb-3">
        <label for="client" class="form-label"><strong>Client name</strong></label>
        <input type="text" class="form-control" id="client" name="client" placeholder="Enter client name"
          required value="{{ old('client') }}">
      </div>

      <div class="mb-3">
        <label for="location" class="form-label"><strong>Client Location (optional)</strong></label>
        <input type="text" class="form-control" id="location" name="location"
          placeholder="Enter client location" value="{{ old('location') }}">
      </div>

      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by"
          placeholder="Enter Approver's Name" required value="{{ old('approved_by') }}">
      </div>
      <x-form-button>Create</x-form-button>
    </form>

  </div>
</x-layout>