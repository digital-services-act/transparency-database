@props(['image' => '', 'highlight' => '', 'status' => '', 'tags' => false, 'title' => 'card_title', 'title_link' => '', 'links' => false, 'description' => ''])

<article class="ecl-card">
    @if($image)
        <picture class="ecl-picture ecl-card__picture"
                 aria-label="{{ $title }}"
                 @if($title_link) data-ecl-picture-link="{{ $title_link }}" @endif >
            <img
                    class="ecl-card__image"
                    src="{{ $image }}"
                    alt="{{ $title }}" />
        </picture>
    @endif

    <div class="ecl-card__body">
        <div class="ecl-content-block ecl-card__content-block"
             data-ecl-auto-init="ContentBlock"
             data-ecl-content-block="">
            @if($highlight || $status)
                <ul class="ecl-content-block__label-container">
                    @if($highlight)
                        <li class="ecl-content-block__label-item">
                            <span class="ecl-label ecl-label--highlight">{{ $highlight }}</span>
                        </li>
                    @endif
                    @if($status)
                        <li class="ecl-content-block__label-item">
                            <span class="ecl-label ecl-label--medium">{{ $status }}</span>
                        </li>
                    @endif
                </ul>
            @endif
            @if($tags)
                <ul class="ecl-content-block__primary-meta-container">
                    @foreach($tags as $tag)
                        <li class="ecl-content-block__primary-meta-item">{{ $tag }}</li>
                    @endforeach
                </ul>
            @endif
            <h1 class="ecl-content-block__title" @if($title_link) data-ecl-title-link="{{ $title_link }} @endif">
                @if($title_link)
                    <a href="{{ $link }}" class="ecl-link ecl-link--standalone">{{ $title }}</a>
                @else
                    {{ $title }}
                @endif
            </h1>

            @if($description)
                <div class="ecl-content-block__description">
                    {{ $description }}
                </div>
            @endif

            <div class="ecl-content-block__list-container">

                @if($slot)
                    <dd class="ecl-description-list__definition">
                        {{ $slot }}
                    </dd>
                @endif

                @if($links)
                    <dd class="ecl-description-list__definition ecl-description-list__definition--inline">
                        @foreach($links as $link)
                            <a href="{{ $link['url'] }}"
                               class="ecl-link ecl-description-list__definition-item">{{ $link['label'] }}</a>
                        @endforeach
                    </dd>
                @endif
            </div>
        </div>
    </div>
</article>