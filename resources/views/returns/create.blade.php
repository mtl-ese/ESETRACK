<x-layout>
  <x-error></x-error>
  <x-error-any></x-error-any>
  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Store Return</h3>

    <form method="post" action="{{ route('returns.store') }}">
      @csrf
      <div class="mb-3">
        <label for="requisition_id" class="form-label"><strong>Store Requisition ID</strong></label>
        <input type="text" class="form-control" id="requisition_id" name="requisition_id"
          placeholder="Enter Store Requisition ID" required>
      </div>

      <div class="mb-3">
        <label for="approved_by" class="form-label"><strong>Approved by</strong></label>
        <input type="text" class="form-control" id="approved_by" name="approved_by" placeholder="Enter current approver"
          required>
      </div>
      <x-form-button>Create</x-form-button>
    </form>

  </div>
</x-layout>