<a {{ $attributes->merge(['class' => 'btn mb-1', 'style' => "background-color: rgb(255, 174, 0);"]) }} title="add">
    <!-- <img style="max-height: 30px;" src="{{ asset('images/add.png') }}"> -->
     <i class="fas fa-plus-circle" style="max-height: 30px;"></i><br>{{ $slot }}
</a>