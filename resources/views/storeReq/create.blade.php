<x-layout>
  <x-error></x-error>

  <div class="card p-5 bg-light bg-opacity-50">
    <h3 class="mb-4">Create Store Requisition</h3>

    <form method="post" action="{{ route('create.store') }}">
      @csrf
      <div class="mb-3">
        <label for="requisitionId" class="form-label"><strong>Requisition ID</strong></label>
        <input type="text" class="form-control @error('requisition_id') is-invalid @enderror" id="requisitionId" name="requisition_id"
          placeholder="Enter Requisition ID e.g. MTL 13376" required value="{{ old('requisition_id') }}">
        @error('requisition_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
      </div>

      <!-- Multiple Destinations Section -->
      <div class="mb-4 p-3 border rounded bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Customer(s)</h5>
          <button type="button" class="btn btn-sm btn-primary" id="addDestination">
            <i class="bi bi-plus-circle"></i> Add Customer
          </button>
        </div>

        <div id="destinationsContainer">
          <!-- Destinations will be added here dynamically -->
        </div>
      </div>

      <div class="mb-3">
        <label for="approvedBy" class="form-label"><strong>Approved By</strong></label>
        <input type="text" class="form-control" id="approvedBy" name="approved_by"
          placeholder="Enter Approver's Name" required value="{{ old('approved_by') }}">
      </div>
       <div class="mb-3">
        <label for="requisitionDate" class="form-label"><strong>Requisition date</strong></label>
        <input type="date" class="form-control" id="requisitionDate" name="requisition_date"
          placeholder="Enter Requisition Date" required max="{{ date('Y-m-d') }}" value="{{ old('requisition_date') }}">
      </div>
      <x-form-button>Add Materials</x-form-button>
    </form>

  </div>

  <script>
    let destinationCount = 0;

    document.getElementById('addDestination').addEventListener('click', function() {
      const container = document.getElementById('destinationsContainer');
      const destinationDiv = document.createElement('div');
      destinationDiv.className = 'destination-item mb-3 p-3 border rounded';
      destinationDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Destination ${destinationCount + 1}</h6>
          <button type="button" class="btn btn-sm btn-danger remove-destination">
            <i class="bi bi-trash"></i> Remove
          </button>
        </div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label"><strong>Client</strong></label>
            <input type="text" class="form-control" name="destinations[${destinationCount}][client]"
              placeholder="Enter client name" required>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label"><strong>Location</strong></label>
            <input type="text" class="form-control" name="destinations[${destinationCount}][location]"
              placeholder="Enter location" required>
          </div>
        </div>
      `;

      container.appendChild(destinationDiv);
      destinationCount++;

      // Add remove functionality
      destinationDiv.querySelector('.remove-destination').addEventListener('click', function() {
        destinationDiv.remove();
      });
    });
  </script>
</x-layout>