<html lang="en" class="no-js">
<head>
    <meta charset="utf-8"/>
    <title>Sorry</title>
    <meta content="width=device-width,initial-scale=1" name="viewport"/>
    <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
    <script>
        var cl = document.querySelector('html').classList;
        cl.remove('no-js');
        cl.add('has-js');
        !function (a, b) {
            "function" == typeof define && define.amd ? define([], function () {
                return a.svg4everybody = b()
            }) : "object" == typeof module && module.exports ? module.exports = b() : a.svg4everybody = b()
        }(this, function () {
            function a(a, b, c) {
                if (c) {
                    var d = document.createDocumentFragment(),
                        e = !b.hasAttribute("viewBox") && c.getAttribute("viewBox");
                    e && b.setAttribute("viewBox", e);
                    for (var f = c.cloneNode(!0); f.childNodes.length;) d.appendChild(f.firstChild);
                    a.appendChild(d)
                }
            }

            function b(b) {
                b.onreadystatechange = function () {
                    if (4 === b.readyState) {
                        var c = b._cachedDocument;
                        c || (c = b._cachedDocument = document.implementation.createHTMLDocument(""), c.body.innerHTML = b.responseText, b._cachedTarget = {}), b._embeds.splice(0).map(function (d) {
                            var e = b._cachedTarget[d.id];
                            e || (e = b._cachedTarget[d.id] = c.getElementById(d.id)), a(d.parent, d.svg, e)
                        })
                    }
                }, b.onreadystatechange()
            }

            function c(c) {
                function e() {
                    for (var c = 0; c < o.length;) {
                        var h = o[c], i = h.parentNode, j = d(i),
                            k = h.getAttribute("xlink:href") || h.getAttribute("href");
                        if (!k && g.attributeName && (k = h.getAttribute(g.attributeName)), j && k) {
                            if (f) if (!g.validate || g.validate(k, j, h)) {
                                i.removeChild(h);
                                var l = k.split("#"), q = l.shift(), r = l.join("#");
                                if (q.length) {
                                    var s = m[q];
                                    s || (s = m[q] = new XMLHttpRequest, s.open("GET", q), s.send(), s._embeds = []), s._embeds.push({
                                        parent: i,
                                        svg: j,
                                        id: r
                                    }), b(s)
                                } else a(i, j, document.getElementById(r))
                            } else ++c, ++p
                        } else ++c
                    }
                    (!o.length || o.length - p > 0) && n(e, 67)
                }

                var f, g = Object(c), h = /\bTrident\/[567]\b|\bMSIE (?:9|10)\.0\b/, i = /\bAppleWebKit\/(\d+)\b/,
                    j = /\bEdge\/12\.(\d+)\b/, k = /\bEdge\/.(\d+)\b/, l = window.top !== window.self;
                f = "polyfill" in g ? g.polyfill : h.test(navigator.userAgent) || (navigator.userAgent.match(j) || [])[1] < 10547 || (navigator.userAgent.match(i) || [])[1] < 537 || k.test(navigator.userAgent) && l;
                var m = {}, n = window.requestAnimationFrame || setTimeout, o = document.getElementsByTagName("use"),
                    p = 0;
                f && e()
            }

            function d(a) {
                for (var b = a; "svg" !== b.nodeName.toLowerCase() && (b = b.parentNode);) ;
                return b
            }

            return c
        });
    </script>
    <link
        rel="stylesheet"
        href="{{ asset('static/ecl/styles/optional/ecl-ec-default.css') }}"
        crossorigin="anonymous"
        media="screen"
    >
    <link
        rel="stylesheet"
        href="{{ asset('static/ecl/styles/optional/ecl-reset.css') }}"
        crossorigin="anonymous"
        media="screen"
    >
    <link
        rel="stylesheet"
        href="{{ asset('static/ecl/styles/ecl-ec.css') }}"
        crossorigin="anonymous"
        media="screen"
    >
    <style>

        #root {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-wrap {
            flex-grow: 1;
        }

        .ecl-site-footer {
            flex-shrink: 0;
        }
    </style>


</head>
<body>
<header
    data-ecl-auto-init="SiteHeaderHarmonised"
    class="ecl-site-header-harmonised"
>
    <div class="ecl-site-header-harmonised__container ecl-container">
        <div class="ecl-site-header-harmonised__top">
            <img alt="European Commission logo"
                 title="European Commission"
                 class="ecl-site-header__logo-image ecl-site-header__logo-image-desktop"
                 src="{{asset('static/ecl/images/logo/positive/logo-ec--en.svg')}}">
            <div class="ecl-site-header-harmonised__action"></div>
        </div>
    </div>
    <div class="ecl-site-header-harmonised__banner">
        <div class="ecl-container">Server inaccessibility</div>
    </div>
</header>

<main class="ecl-container ecl-u-mv-xl ecl-u-mv-md-3xl">
    <div class="ecl-row">
        <div class="ecl-col-md-6">
            <div class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-width-100">
                <x-ecl.language lang="bg" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Сървърът е временно недостъпен
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Извиняваме се за неудобството. Моля, опитайте пак по-късно.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="cs" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Server je dočasně nedostupný
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Omlouváme se za způsobené nepříjemnosti. Zkuste to prosím znovu později.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="da" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Serveren er midlertidigt utilgængelig
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Vi undskylder ulejligheden. Prøv igen senere.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="de" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Der Server ist vorübergehend nicht verfügbar
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Wir entschuldigen uns für etwaige Unannehmlichkeiten. Bitte
                        versuchen Sie es später noch einmal.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="el" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Ο διακομιστής δεν είναι διαθέσιμος προσωρινά
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Ζητούμε συγγνώμη για τυχόν προβλήματα. Δοκιμάστε ξανά αργότερα.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="en" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        The server is temporarily unavailable
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        We apologise for any inconvenience. Please try again later.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="es" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        El servidor no está disponible temporalmente
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Le rogamos disculpe las molestias. Vuelva a intentarlo más
                        tarde.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="et" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Server ei ole ajutiselt kättesaadav
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Palume vabandust tekkinud ebamugavuste pärast. Palun proovige
                        hiljem uuesti.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="fi" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Palvelin on väliaikaisesti poissa käytöstä
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Pahoittelemme mahdollista haittaa. Yritä myöhemmin uudelleen.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="fr" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Le serveur est temporairement indisponible
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Veuillez nous excuser pour ce désagrément. Merci de réessayer
                        plus tard.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="ga" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Níl fáil ar an bhfreastalaí i láthair na huaire
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Is oth linn an chiotaí. Bain triail as arís ar ball.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="hr" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Poslužitelj je privremeno nedostupan
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Ispričavamo se zbog neugodnosti. Pokušajte ponovno kasnije.
                    </p>
                </div>
            </div>
        </div>

        <div class="ecl-col-md-6">
            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-mt-md-none ecl-u-width-100"
            >
                <x-ecl.language lang="hu" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        A szerver átmenetileg nem érhető el, kérjük, próbálkozzon újra
                        később
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Az esetleges kellemetlenségekért elnézését kérjük.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="it" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Il server è temporaneamente non disponibile
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Ci scusiamo per il disagio arrecato. Riprovare più tardi.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="lt" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Serveris laikinai neveikia
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Atsiprašome už nepatogumus. Bandykite dar kartą vėliau.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="lv" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Serveris pagaidām nav pieejams
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Atvainojamies par neērtībām. Mēģiniet vēlreiz vēlāk.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="mt" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Is-server mhux disponibbli temporanjament
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Niskużaw ruħna għal kwalunkwe inkonvenjenza. Jekk jogħġbok erġa’
                        pprova aktar tard.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="nl" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        De server is tijdelijk niet beschikbaar
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Onze excuses voor het ongemak. Probeer het later nog een keer.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="pl" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Serwer jest tymczasowo niedostępny
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Przepraszamy za wszelkie niedogodności. Proszę spróbować
                        później.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="pt" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        O servidor está temporariamente indisponível
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Pedimos desculpa pelo incómodo. Volte a tentar mais tarde.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="ro" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Serverul este temporar indisponibil
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Ne cerem scuze pentru eventualele inconveniente. Vă rugăm să
                        reîncercați mai târziu.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="sk" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Server je dočasne nedostupný
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Ospravedlňujeme sa za prípadné problémy, ktoré môžu v dôsledku
                        toho vzniknúť.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="sl" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Strežnik je začasno nedosegljiv
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Opravičujemo se za morebitne nevšečnosti. Poskusite ponovno
                        pozneje.
                    </p>
                </div>
            </div>

            <div
                class="ecl-u-d-inline-flex ecl-u-pt-m ecl-u-mt-l ecl-u-width-100"
            >
                <x-ecl.language lang="sv" />
                <div class="ecl-u-ml-m ecl-u-width-100">
                    <div
                        class="ecl-u-type-prolonged-m ecl-u-type-color-blue-100 ecl-u-type-bold"
                    >
                        Servern är inte tillgänglig för tillfället
                    </div>
                    <p
                        class="ecl-u-type-paragraph ecl-u-border-bottom ecl-u-border-color-grey-15 ecl-u-mt-s ecl-u-mb-none ecl-u-pb-m"
                    >
                        Vi ber om ursäkt för besväret. Försök igen senare.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="ecl-u-bg-blue-100 ecl-u-pa-l"></footer>

<script>
    svg4everybody({polyfill: true});
</script>
</body>
</html>







