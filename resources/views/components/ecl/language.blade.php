@props(['lang' => 'en'])
<span class="ecl-u-d-flex" style="position: relative">
              <svg
                  focusable="false"
                  aria-hidden="true"
                  class="ecl-icon ecl-icon--m ecl-u-type-color-blue-100"
              >
                  <x-ecl.icon icon="general--language"/>
              </svg>
              <span
                  style="
                  left: 50%;
                  position: absolute;
                  top: 0;
                  transform: translate(-50%, 3px);
                "
                  class="ecl-u-type-xs ecl-u-type-color-white-100 ecl-u-type-uppercase"
              >{{$lang}}</span
              >
</span>

