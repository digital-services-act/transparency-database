@props(['tags' => [] ])
<ul class="ecl-tag-set">
    @foreach($tags as $tag)

        @if($tag['removable'] ?? false)

            <li class="ecl-tag-set__item">
                <button type="button" class="ecl-tag ecl-tag--removable">
                    {{ $tag['label'] }}
                    <span class="ecl-tag__icon">
                        <svg class="ecl-icon ecl-icon--xs ecl-tag__icon-close" focusable="false" aria-hidden="false" role="img">
                            <title>Dismiss</title>
                            <x-ecl.icon icon="close-outline" />
                        </svg>
                    </span>
                </button>
            </li>

        @else

            <li class="ecl-tag-set__item">
                <a href="{{ $tag['url'] }}" class="ecl-tag">{{ $tag['label'] }}</a>
            </li>

        @endif
    @endforeach
</ul>