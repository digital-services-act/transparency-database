## FAQ Général

<x-ecl.accordion label="Qu'est-ce que la base de données de transparence DSA ?">
L'article 17 de la loi sur les services numériques (DSA) exige que tous les fournisseurs de services d'hébergement fournissent des informations claires et spécifiques, appelées motifs, aux utilisateurs chaque fois qu'ils suppriment ou restreignent autrement l'accès à leur contenu.
De plus, l'article 24 (5) de la DSA exige des fournisseurs de plateformes en ligne, qui sont un type de service d'hébergement, qu'ils envoient tous leurs motifs à la
<a href="https://transparency.dsa.ec.europa.eu/">base de données de transparence DSA</a> de la Commission pour collecte. La base de données est publiquement accessible et lisible par machine.
</x-ecl.accordion>

<x-ecl.accordion label="Qu'est-ce qu'un service d'hébergement ? Qu'est-ce qu'une plateforme en ligne ?">
Les services d'hébergement comprennent une large gamme d'intermédiaires en ligne, par exemple les services d'hébergement cloud et web. Ces services stockent des informations fournies par les utilisateurs et à la demande de ceux-ci.
La base de données de transparence DSA ne collecte que des motifs de plateformes en ligne, un sous-ensemble des services d'hébergement. Les plateformes en ligne, telles que les places de marché en ligne, les magasins d'applications ou les réseaux sociaux, stockent non seulement des informations fournies par les utilisateurs, mais les diffusent également publiquement. Autrement dit, elles les rendent disponibles potentiellement à tous les utilisateurs d'une plateforme en ligne.
</x-ecl.accordion>

<x-ecl.accordion label="Qu'est-ce qu'un motif ?">
Un motif est un outil important pour permettre aux utilisateurs de comprendre et éventuellement contester les décisions de modération de contenu prises par les fournisseurs de services d'hébergement.
Comme spécifié à l'article 17 de la DSA, un motif est l'information que les fournisseurs de services d'hébergement, y compris les plateformes en ligne, sont tenus de partager avec un utilisateur chaque fois qu'ils suppriment ou restreignent autrement l'accès à leur contenu. Les restrictions peuvent être imposées au motif que le contenu est illégal ou incompatible avec les conditions générales du fournisseur.
Les informations contenues dans un motif comprennent, entre autres choses, le type de restriction mise en place, les motifs invoqués et les faits et circonstances autour desquels la décision de modération de contenu a été prise.
Les motifs que les fournisseurs de plateformes en ligne sont tenus de soumettre à la base de données de transparence DSA doivent contenir cette information.
</x-ecl.accordion>

<x-ecl.accordion label="Y a-t-il une partie d'un motif qui n'est pas publiée dans la base de données de transparence DSA ?">
Les fournisseurs de plateformes en ligne sont tenus de supprimer toutes les données personnelles des informations qu'ils publient dans la base de données de transparence DSA, conformément à l'article 24 (5) de la DSA. En cas de données personnelles incluses dans l'un des motifs, la Commission peut être informée en utilisant le bouton « Signaler un problème ».
Les options de recours ne sont pas non plus incluses dans la base de données de transparence DSA car elles ne sont pertinentes que pour le destinataire du motif.
</x-ecl.accordion>

<x-ecl.accordion label="Pourquoi la base de données de transparence DSA a-t-elle été créée ?">
La base de données de transparence DSA a été créée conformément à l'article 24 (5) de la DSA pour permettre une plus grande transparence et un meilleur contrôle sur les décisions de modération de contenu prises par les fournisseurs de plateformes en ligne, et pour mieux surveiller la propagation de contenu illégal et nuisible en ligne.
</x-ecl.accordion>

<x-ecl.accordion label="Qui peut utiliser la base de données de transparence DSA ?">
La base de données de transparence DSA est publiquement accessible. Elle permet aux personnes de
<a href="https://transparency.dsa.ec.europa.eu/statement-search">rechercher</a>, lire et télécharger des motifs. Pour obtenir un aperçu interactif des motifs contenus dans la base de données de transparence DSA, visitez l'Analytics de la base de données de transparence DSA.
</x-ecl.accordion>

<x-ecl.accordion label="Quelles sont les principales fonctionnalités offertes par la base de données pour résumer rapidement les informations ?">
Le tableau de bord de la base de données de transparence DSA offre une manière conviviale d'accéder rapidement à des informations résumées sur les motifs soumis par les fournisseurs de plateformes en ligne. Vous pouvez naviguer à travers différentes sections du tableau de bord avec différentes visualisations pour obtenir un aperçu complet des données.
Chaque visualisation peut être personnalisée par plusieurs filtres pour trouver les informations que vous recherchez. Par exemple, vous pouvez regarder des plateformes en ligne spécifiques, rechercher une plage de temps spécifique ou trouver un type de restriction spécifique. Le tableau de bord est conçu pour rationaliser le processus d'extraction d'informations significatives de la base de données de transparence DSA.
</x-ecl.accordion>

<x-ecl.accordion label="Où puis-je trouver des informations sur la navigation dans le tableau de bord ? Quelles données sont affichées dans le tableau de bord ? ">
Pour en savoir plus sur les options de navigation et de filtrage du tableau de bord, vous pouvez visiter la page « Instructions » du
<a href="{{route('dashboard')}}">tableau de bord</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Les filtres du tableau de bord seront-ils mis à jour et élargis avec des options de filtrage supplémentaires, telles qu'une répartition par État membre ?">
Nous cherchons toujours à améliorer le tableau de bord et ses fonctionnalités, y compris les filtres disponibles. En proposant des options de filtrage, le tableau de bord cherche à trouver un équilibre entre la personnalisabilité et la complexité, dans les contraintes techniques de la base de données. Pour cette raison, les options de filtrage ou les répartitions par États membres ou langues ne sont actuellement pas mises en œuvre. Cependant, nous explorons activement des solutions pour incorporer de telles fonctionnalités à l'avenir pour améliorer les capacités du tableau de bord. Pour accéder à l'ensemble complet des catégories, vous pouvez utiliser la page de <a
href={{route('dayarchive.index')}}>« Téléchargement des données »</a>, qui fournit un accès au schéma complet de la base de données via des fichiers quotidiens.
</x-ecl.accordion>

<x-ecl.accordion label="Où puis-je trouver des informations sur les points de données inclus dans la base de données de transparence DSA ?">
Pour plus d'informations sur les données incluses dans la base de données de transparence DSA, veuillez visiter la
<a href="https://transparency.dsa.ec.europa.eu/page/documentation">page de documentation</a>.
Elle explique quel type d'informations est collecté, et comment les différents champs de données se rapportent à l'article 17 de la DSA,
qui établit les informations requises dans un motif. Plusieurs attributs inclus dans la base de données de transparence DSA peuvent être utilisés pour filtrer les visualisations agrégées du tableau de bord de la base de données de transparence DSA. Pour plus
d'informations sur la navigation dans le tableau de bord et les informations qu'il contient, veuillez visiter la page « Instructions » du <a href="{{route('dashboard')}}">tableau de bord</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Comment puis-je rechercher des motifs spécifiques ? ">
La <a href="{{route('statement.index')}}">page « Rechercher des motifs »</a> vous offre la possibilité de rechercher
les champs de texte libre des motifs dans la base de données pour des mots-clés de votre choix.

Lorsque vous cliquez sur le bouton « Recherche avancée », vous êtes redirigé vers la page <a href="{{route('statement.search')}}">
« Recherche avancée »</a> où vous pouvez rechercher
des motifs spécifiques à partir de plates-formes ou de plages de temps spécifiques. Vous pouvez également filtrer les motifs en fonction de tout autre champ de données (par exemple, un type spécifique de restriction ou un mot-clé) qui vous intéresse.
Veuillez noter qu'une <a href="{{  route('page.show', ['data-retention-policy']) }}">politique de rétention des données</a> s'applique.
</x-ecl.accordion>

<x-ecl.accordion label="Y a-t-il une politique de rétention des données en place ? Les données seront-elles disponibles indéfiniment ?">
La base de données de transparence DSA est soumise à une <a href="{{  route('page.show', ['data-retention-policy']) }}">politique de rétention des données</a> pour réduire son
empreinte computationnelle et garantir une expérience utilisateur sans faille. La politique de rétention des données peut être résumée comme suit :
après qu'un Motif (SoR) a été inséré avec succès dans la base de données, il est disponible dès le lendemain dans la page <a href="{{route('statement.index')}}">« Recherche de motifs »</a>, sur le <a href="{{route('dashboard')}}">tableau de bord</a>, et dans les téléchargements quotidiens postés dans la page <a href="{{route('dayarchive.index')}}">« Téléchargement de données »</a>. Après six mois (180 jours), le SoR sera
supprimé de la fonction de recherche, mais il sera présent dans les téléchargements quotidiens, et il contribuera toujours au
tableau de bord. Après 18 mois (540 jours), les téléchargements quotidiens sont supprimés de la section de téléchargement de données et archivés dans un stockage à froid. Le tableau de bord fournira un accès à long terme aux statistiques agrégées pour les 5 dernières années.
</x-ecl.accordion>

<x-ecl.accordion label="Y a-t-il une période de conservation pour le tableau de bord des statistiques quotidiennes sur le site de la base de données de transparence ?">
Le tableau de bord contiendra les statistiques agrégées des 5 dernières années de données. Veuillez noter que cette politique de conservation est sujette à modification et peut être mise à jour si nécessaire.
</x-ecl.accordion>

<x-ecl.accordion label="Combien de temps les données de recherche (recherche en texte intégral, tous les filtres, résultats limités à 10 000 motifs par demande de recherche) seront-elles conservées ?">
Les données de recherche seront conservées pendant six mois (180 jours). Veuillez noter que cette politique de conservation est sujette à modification et peut être mise à jour si nécessaire.
</x-ecl.accordion>

<x-ecl.accordion label="Quelle est la période de rétention pour le téléchargement de données sous forme de téléchargements quotidiens (fichiers .csv) sur le site de la base de données ?">
Les téléchargements quotidiens (fichiers .csv) seront conservés pendant 18 mois (540 jours). Après cette date, les téléchargements quotidiens archivés seront stockés dans un stockage à froid interne à la Commission. Veuillez noter que cette politique de conservation est sujette à modification et peut être mise à jour si nécessaire.
</x-ecl.accordion>

<x-ecl.accordion label="Pourquoi ai-je remarqué un changement/une diminution/une augmentation dans les statistiques des SoR sur la page d'accueil/le tableau de bord ? ">
Nous surveillons constamment la qualité des données de la base de données et effectuons des vérifications de cohérence. Certaines de ces routines peuvent affecter le contenu de la base de données. Nous vous encourageons à consulter les <a href="{{  route('page.show', ['announcements']) }}">Annonces</a> pour connaître toutes les mesures passées et à venir que nous prenons à cet égard.
</x-ecl.accordion>

<x-ecl.accordion label="Qu'est-ce qu'un champ de texte libre ? Quels sont les champs de texte libre dans chaque motif ? ">
Dans un champ de texte libre, les fournisseurs de plateformes en ligne peuvent fournir des informations dans leurs propres mots, par exemple pour expliquer pourquoi le contenu modéré est illégal ou pour expliquer les faits et circonstances qui ont conduit à la (aux) décision(s) de modération.
Pour un aperçu complet des champs de texte libre contenus dans le schéma de la base de données de transparence DSA, veuillez consulter la <a href="{{  route('profile.page.show', ['api-documentation']) }}">documentation de l'API</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Où puis-je trouver plus d'informations sur la DSA ?">
La DSA est un ensemble complet de nouvelles règles qui réglementent les responsabilités des services numériques.
<a href="https://commission.europa.eu/strategy-and-policy/priorities-2019-2024/europe-fit-digital-age/digital-services-act_en">Découvrez-en plus sur la DSA</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Je voudrais donner des commentaires - comment puis-je faire ?">
Veuillez utiliser le formulaire de commentaires. Pour utiliser le <a href="https://transparency.dsa.ec.europa.eu/feedback">formulaire de commentaires</a>, vous devez créer un compte EU Login.
</x-ecl.accordion>

<h2>FAQ technique</h2>

<x-ecl.accordion label="Je voudrais extraire un grand nombre de motifs de la base de données de transparence DSA. Comment faire ?">
La page de <a
href={{route('dayarchive.index')}}>« Téléchargement de données »</a> de la base de données de transparence DSA contient tous les motifs soumis
organisés en fichiers zip quotidiens. Il est possible de télécharger
des fichiers zip contenant les soumissions de toutes les plateformes en ligne, ou de sélectionner les fichiers zip contenant les motifs de
chacune des plateformes en ligne individuelles. Les fichiers peuvent être filtrés via un menu déroulant en haut à droite.
Les fichiers sont fournis dans des versions complètes et légères. La version complète contient tous les champs de données de chaque motif (<a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">voir le schéma complet de la base de données</a>),
alors que la version légère ne contient pas les attributs de texte libre avec un
limite de caractères supérieure à 2000 caractères (c'est-à-dire <i>explication_contenu_illégal</i>, <i>explication_contenu_incompatible</i> ou
<i>décision_faits</i>). Les fichiers d'archive légers ne contiennent pas non plus l'attribut <i>portée_territoire</i>.
Notez qu'une <a href="{{  route('page.show', ['data-retention-policy']) }}">politique de rétention des données</a>
s'applique au fichier de téléchargements quotidiens.  
</x-ecl.accordion>

<x-ecl.accordion label="Comment les fichiers de téléchargement quotidiens sont-ils organisés ? Quel est leur format ?">
Les fichiers de téléchargement quotidiens contiennent tous les motifs soumis par les plateformes au cours d'une journée donnée. Les fichiers sont fournis dans une archive zip imbriquée contenant les morceaux. Plus précisément, chaque fichier .zip contient plusieurs fichiers zip. Chacun de ces derniers contient plusieurs fichiers CSV stockant tous les motifs reçus un jour donné à partir des plateformes sélectionnées.
<br>
<br>
Par exemple, la version légère du téléchargement global pour le 25 septembre 2024 -nommée sor-global-2023-09-25-light.zip-, contiendra plusieurs fichiers zip nommés comme sor-global-2023-09-25-light-00000.csv.zip. Chacun de ces derniers contiendra plusieurs morceaux de CSV, avec environ 100 000 SoR chacun, nommés sor-global-2023-09-25-light-00000-00000.csv.
</x-ecl.accordion>

<x-ecl.accordion label="Je voudrais échantillonner des données de la base de données de transparence DSA. Comment faire ?">
Pour obtenir un échantillon des soumissions à la base de données de transparence DSA, vous pouvez utiliser le lien de téléchargement du fichier .csv disponible
au-dessus du tableau affichant les résultats d'une recherche de motifs.<br>
<br>
Par défaut, les 1000 derniers résultats seront disponibles en téléchargement. Pour adapter le contenu de l'échantillon, vous pouvez spécifier
des paramètres de recherche dans la page de recherche avancée. Les 1000 premiers résultats de votre recherche avancée seront ensuite disponibles
en téléchargement de fichier .csv.
</x-ecl.accordion>

<x-ecl.accordion label="Quels sont les fichiers SHA1 fournis avec les téléchargements quotidiens et comment les utiliser?">
Les fichiers sont fournis au format de valeurs séparées par des virgules (CSV) zippé. Chaque fichier est accompagné d'un fichier de somme de contrôle SHA1 contenant le nom de fichier d'origine et son hachage. Le fichier SHA1 permet de vérifier le fichier .zip csv téléchargé à l'aide d'outils standard. Par exemple, dans un terminal bash, en ayant à la fois les fichiers csv.zip et csv.zip.sha1 dans le dossier de travail, faire sha1sum -c sor-global-2023-09-25-full.csv.zip.sha1 devrait afficher OK si le fichier sor-global-2023-09-25-full.csv.zip a été correctement téléchargé.
</x-ecl.accordion>

<x-ecl.accordion label="Je voudrais extraire des graphiques directement depuis le tableau de bord. Comment faire?">
Pour extraire des visualisations directement du <a href="{{route('dashboard')}}">tableau de bord</a> au format .jpg, cliquez sur le menu « Plus d'options » en haut à droite de la visualisation du tableau de bord que vous souhaitez extraire. Ensuite, cliquez sur « Copier la visualisation en tant qu'image ». Lorsque votre visualisation est prête, vous pouvez coller votre image à votre destination souhaitée, en utilisant « Ctrl + V » ou clic droit et sélectionner Coller.
</x-ecl.accordion>

<x-ecl.accordion label="Lorsque j'exporte des données à partir des visualisations du tableau de bord, quels sont les chiffres que je regarde?">
Dans le tableau de bord, vous pouvez exporter les données sous-jacentes de chaque visualisation dans un fichier .csv ou excel. Les données dans ces fichiers sont les statistiques agrégées sur lesquelles la visualisation spécifique est construite.
</x-ecl.accordion>

<x-ecl.accordion label="Je voudrais accéder au contenu pour lequel un motif a été créé. Comment faire?">
La base de données de transparence DSA enregistre uniquement les motifs. Ils contiennent des informations sur la décision de modération du contenu elle-même ainsi que les informations accompagnant de telles décisions, à l'exception des données personnelles, que les fournisseurs de plateformes en ligne sont tenus de supprimer de leurs motifs avant de les soumettre à la base de données. La base de données de transparence DSA ne contient pas le contenu qui a fait l'objet de la modération. <br> <br> Pour les chercheurs intéressés par l'accès au contenu sous-jacent à certaines déclarations de motifs, le mécanisme d'accès aux données spécifié à l'article 40 de la DSA peut fournir un moyen d'obtenir un tel accès à l'avenir. <br> <br> Une fois que les coordinateurs des services numériques sont établis d'ici le 17 février 2024, les demandes d'accès aux données peuvent être soumises soit au coordinateur des services numériques de l'État membre d'un chercheur, soit au(x) coordinateur(s) des services numériques où le fournisseur de la (des) plateforme(s) en ligne concernée(s) est établi. La Commission rédige actuellement un acte délégué qui fixera les exigences techniques et procédurales du mécanisme d'accès aux données prévu à l'article 40.
</x-ecl.accordion>

<h2>FAQ de la plateforme</h2>

<x-ecl.accordion label="Je suis responsable de la mise en œuvre de l'article 24(5) de la DSA en tant que fournisseur d'une plateforme en ligne. Quelles étapes dois-je suivre?">
Pour mettre en place votre processus de soumission de déclarations de motifs, veuillez vous inscrire <a href='https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons'>ici</a> concernant vos obligations en vertu de l'article 24(5) de la DSA. À un stade ultérieur, le coordinateur des services numériques de votre État membre vous contactera avec des détails sur la façon d'intégrer votre plateforme en ligne. <br> <br> Une fois intégré par le biais de votre coordinateur des services numériques, vous aurez accès à un environnement de test pour tester vos soumissions à la base de données de transparence DSA, que vous pouvez effectuer soit via une interface de programmation d'applications (API) soit via un formulaire web, en fonction du volume de vos données et de vos besoins techniques. <br> <br> Une fois la phase de test terminée, vous pourrez passer à l'environnement de production de la base de données de transparence DSA, où vous pourrez commencer à soumettre vos déclarations de motifs via une API ou un formulaire web.
</x-ecl.accordion>

<x-ecl.accordion label="Quelles sont les options techniques pour envoyer des déclarations de motifs à la base de données de transparence DSA?">
Les déclarations de motifs peuvent être soumises soit via une API, soit via un formulaire web. Pour plus d'informations sur l'API, veuillez consulter la <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">documentation de l'API</a>. Le schéma de données du formulaire web est le même que celui de l'API.
</x-ecl.accordion>

<x-ecl.accordion label="Où puis-je trouver le schéma de données avec tous les attributs de déclaration de motifs utilisés dans la base de données de transparence DSA?">
Tous les attributs, qui font partie d'une soumission de déclaration de motifs à la base de données de transparence DSA, sont détaillés dans la <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">documentation de l'API</a>. Le schéma de données du formulaire web est le même que celui de l'API.
</x-ecl.accordion>

<x-ecl.accordion label="Quelles sont les options de point de terminaison API pour la base de données de transparence DSA et laquelle recommanderiez-vous pour envoyer des déclarations de motifs à un volume très élevé?">
La base de données de transparence DSA dispose de deux points de terminaison API, l'un qui permet de soumettre une déclaration de motifs par appel et l'autre qui permet de soumettre de 1 à 100 déclarations de motifs par appel. Pour plus d'informations sur les points de terminaison de l'API, veuillez lire la <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">documentation de l'API</a>. <br> <br> Pour des soumissions à haut volume de plusieurs déclarations de motifs par minute, nous recommandons d'utiliser le point de terminaison API par lots.
</x-ecl.accordion>

<x-ecl.accordion label="Où puis-je trouver des informations sur les codes d'erreur?">
Pour des informations détaillées sur les codes d'erreur possibles, veuillez lire la section pertinente dans la <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">documentation de l'API</a>.
</x-ecl.accordion>
