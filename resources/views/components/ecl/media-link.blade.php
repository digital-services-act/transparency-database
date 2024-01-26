@props(['url' => null, 'label' => '', 'image' => 'https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-text-logo.jpg'])
<div class="ecl-media-container">
    <figure class="ecl-media-container__figure">
        <div class="ecl-media-container__caption">
            <a href="{{ $url }}">
                <picture class="ecl-picture ecl-media-container__picture">
                    <img class="ecl-media-container__media"
                         src="{{ $image }}"
                         @if($label) alt="{{ $label }}" @endif />
                </picture>
            </a>
            @if($label)
                <x-ecl.cta-button label="{{ $label }}" url="{{ $url }}" priority="ghost" :icon="false"/>
            @endif
        </div>
    </figure>
</div>