<x-layout>
  <x-error></x-error>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Store Requisition</h3>

    <form method="post" action="{{ route('create.store') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID" required value="{{ old('requisition_id') }}">
      </div>

      <div class="mb-3">
        <label for="client" class="form-label"><strong>Client name</strong></label>
        <input type="text" class="form-control" id="client" name="client_name" placeholder="Enter client name" required
          value="{{ old('client_name') }}">
      </div>

      <div class="mb-3">
        <label for="location" class="form-label"><strong>Client Location</strong></label>
        <input type="text" class="form-control" id="location" name="location" placeholder="Enter client location"
          required value="{{ old('location') }}">
      </div>

      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by" placeholder="Enter Approver's Name"
          required value="{{ old('approved_by') }}">
      </div>
      <x-form-button>Create</x-form-button>
    </form>

  </div>
</x-layout>