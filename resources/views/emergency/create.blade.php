<x-layout>
  <x-error></x-error>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Emergency Requisition</h3>
    <form method="post" action="{{ route('emergencyStore') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID" value="{{ old('requisition_id') }}">
      </div>

      <div class="mb-3">
        <label for="initiator" class="form-label"><strong>Initiator</strong></label>
        <input type="text" class="form-control" id="initiator" name="initiator" placeholder="Enter Initiator's Name"
          value="{{ old('initiator') }}">
      </div>

      <div class="mb-3">
        <label for="department" class="form-label"><strong>Department</strong></label>
        <input type="text" class="form-control" id="department" name="department" placeholder="Enter Department's Name"
          value="{{ old('department') }}">
      </div>

      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by" placeholder="Enter Approver's Name"
          value="{{ old('approved_by') }}">
      </div>
      <x-form-button>Create</x-form-button>
    </form>
  </div>

</x-layout>