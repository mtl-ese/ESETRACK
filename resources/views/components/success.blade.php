@if(session('success'))
    <div id="successAlert" class="alert alert-success position-fixed top-0 mt-3 start-50 translate-middle-x p-3" style="z-index:1050;">
        {{ session('success') }}
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                $('#successAlert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });
    </script>
@endif
