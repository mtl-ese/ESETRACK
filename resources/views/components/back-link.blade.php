<a {{ $attributes->merge(['class'=> "btn mx-auto shadow ms-0", 'style'=> "background-color: rgb(255, 174, 0);"]) }}>
    <i class="fas fa-chevron-left"></i>
    {{ $slot }}
</a>

