@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "Oops...",
                text: "{{ session('error') }}",
                icon: "error",
                confirmButtonColor: "#d33",
                confirmButtonText: "OK"
            }).then(() => {
                // Add this to prevent background scroll when modal is open
                document.body.style.overflow = 'auto'; 
            });
        });
    </script>
@endif
