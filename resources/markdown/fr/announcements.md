#### [Annonce - API] Restauration de l'erreur 422 pour l'UID de plateforme dupliqué (puid)
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Publié le 08/04/2024</p>
Le 8 avril 2024, la Commission européenne a restauré l'erreur 422 - Contenu non traitable. Le point de terminaison de l'API renverra l'erreur chaque fois qu'une plateforme soumet une Déclaration de motifs (SoR) contenant un identifiant unique de plateforme (puid) déjà trouvé dans la SoR précédemment soumise à la base de données par la même plateforme.

Veuillez consulter la [section Erreurs](/page/api-documentation#errors) de la [documentation de l'API](/page/api-documentation) pour plus de détails.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Modification - API] Application du format de l'UID de plateforme (puid)
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Publié le 08/04/2024</p>

À partir du 18 avril 2024, l'équipe de la Base de données de transparence imposera le format de l'Identifiant unique de plateforme (puid) à une chaîne de caractères maximale de 500 caractères contenant uniquement des caractères alphanumériques (a-z, A-Z, 0-9), des tirets "-" et des traits de soulignement "_" uniquement. Aucun espace, saut de ligne ou aucun autre caractère spécial ne sera accepté.

Par exemple, le puid "344ndbd_3338383-11aST" sera valide, tandis que le puid "123.STATE sor/category" ne le sera pas.

Nous vous renvoyons à la [documentation de l'API](/page/api-documentation#additional-explanation-for-statement-attributes) pour plus de détails.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Modification - Accès aux données] Amélioration du format des fichiers de vidage quotidien
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Publié le 08/04/2024</p>

Le 8 avril 2024, l'équipe de la Base de données de transparence (TDB) met à jour la structure des fichiers CSV de vidage quotidien disponibles dans la section [Téléchargement de données](/data-download). Ce changement vise à améliorer la création du CSV de vidage quotidien pour accélérer le processus et le rendre plus efficace en termes de calcul. Cela permettra de publier les fichiers CSV de manière rapide et opportune même avec le volume quotidien élevé actuel de Déclarations de motifs (SoRs) soumises à la TDB, qui devrait encore augmenter dans les mois à venir lorsque de petites plateformes rejoindront.

La nouvelle structure consistera en un fichier zip, avec plusieurs fichiers zip à l'intérieur.  
Chaque fichier zip interne contiendra au maximum 1 million d'enregistrements répartis en parties CSV de 100 000.

Par exemple, la version légère du vidage global pour le 25 septembre 2024 -nommée sor-global-2023-09-25-light.zip-, contiendra plusieurs fichiers zip nommés comme sor-global-2023-09-25-light-00000.csv.zip. Chacun de ces derniers contiendra plusieurs tranches CSV, avec environ 100 000 SoR dans chacun, nommés sor-global-2023-09-25-light-00000-00000.csv.

Les fichiers au format ancien seront progressivement remplacés par le nouveau format dans les jours suivants.

Bien que l'implémentation actuelle puisse facilement gérer le volume actuel des soumissions quotidiennes, l'équipe de la TDB se réserve le droit d'apporter d'autres modifications à la structure des fichiers ou au pipeline de création, si des améliorations supplémentaires sont nécessaires pour gérer le taux de soumission quotidien croissant.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Annonce - Accès aux données] Mise en œuvre d'une nouvelle politique de conservation des données
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Publié le 08/04/2024</p>

À partir du 15 avril 2024, la Base de données de transparence (TDB) suivra la [politique de conservation des données](/page/data-retention-policy) mise en place par la Commission européenne. En particulier, chaque Déclaration de motifs (SoR) sera recherchable à partir de la [Recherche de déclarations de motifs](/statement) dans les six mois (180 jours) suivant son insertion dans la base de données. Après cette période, la SoR sera supprimée de l'index de recherche et sera disponible dans les fichiers CSV des [fichiers de vidage quotidien](/data-download) et continuera de contribuer au [Tableau de bord](/dashboard).

Les [fichiers de vidage quotidien](/data-download) seront disponibles pendant 18 mois (540 jours) après leur création. Après cette période, ils seront archivés dans un stockage à froid.

Enfin, le [Tableau de bord](/dashboard) contiendra les statistiques agrégées pour les 5 dernières années de données.

<p class="ecl-u-type-paragraph" style="font-style: italic">
<img width="100%" src="{{asset('/static/images/dsa-retention-policy.png')}}">
</p>
<p class="ecl-u-type-paragraph" style="width:100%; text-align:center; font-style: italic !important; margin-top:-20px"><span style="font-size: smaller">La politique de conservation des données de la Base de données de transparence du DSA.</span></p>

<p class="ecl-u-type-paragraph" style="margin-bottom:100px"></p>
