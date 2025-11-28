<x-layout>
  <x-error></x-error>
  <x-error-any></x-error-any>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Purchase Requisition</h3>
    <form action="{{ route('store.purchase') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID e.g. MTL 0023" value="{{ old('requisition_id') }}" required>
      </div>
  <div class="mb-3">
        <label for="project" class="form-label"><strong>Project Description</strong></label>
        <input type="text" class="form-control" id="project" name="project_description" placeholder="Enter project description"
          required value="{{ old('project_description') }}">
      </div>
      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by" placeholder="Enter Approver's Name"
          value="{{ old('approved_by') }}" required>
      </div>
       <div class="mb-3">
        <label for="requisitionDate" class="form-label"><strong>Requisition date</strong></label>
        <input type="date" class="form-control" id="requisitionDate" name="requisition_date"
          placeholder="Enter Requisition Date" required max="{{ date('Y-m-d') }}" value="{{ old('requisition_date') }}">
      </div>
      <x-form-button>Add Materials</x-form-button>
    </form>
  </div>

</x-layout>
