@if($errors->any())
    <div id="errorAlert" class="alert alert-danger position-fixed top-0 mt-3 start-50 translate-middle-x p-3"
        style="z-index:1050;">
        <div id="errorToast" class="toast align-items-center text-bg-danger border-0 show" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body text-center">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                $('#errorAlert').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 5000);
        });
    </script>
@endif
