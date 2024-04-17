## Soumission de déclarations claires et spécifiques

Cette sous-catégorie se compose des points de données suivants:

[1. Identifiant Unique de Plateforme (PUID)](#1-identifiant-unique-de-plateforme-puid)

[2. Spécifications sur le contenu affecté par la déclaration](#2-spécifications-du-contenu-affecté-par-la-décision)

_Pourquoi cette information est-elle incluse?_

_L'article 17(1) et 17(4) du Règlement 2022/2065 (DSA) exigent que les déclarations de motifs soumises à la base de données soient claires et spécifiques. Les exigences de cette première sous-catégorie augmentent également la fonction de la base de données pour assurer la transparence et permettre un examen des décisions de modération de contenu conformément au considérant 66 du DSA._

### 1. Identifiant Unique de Plateforme (PUID)

C'est une chaîne qui identifie de manière unique cette déclaration dans les systèmes de la plateforme.

Le PUID permet la localisation des informations exactes affectées par la décision et la déclaration de motifs au sein des propres systèmes d'une plateforme. Il offre la possibilité de connecter les données de cette base de données avec les informations qui ont été affectées par la décision, y compris l'URL où les informations sont/étaient hébergées par la plateforme. Les caractéristiques du PUID doivent être décidées par la plateforme. Il est obligatoire que chaque PUID soit unique et ne puisse pas être réutilisé à l'avenir.

### 2. Spécifications du contenu affecté par la décision

Les spécifications du contenu affecté par la décision couvrent les champs requis concernant le type de contenu affecté et la date de création du contenu, ainsi que des champs facultatifs concernant la langue de l'information affectée.

#### 2.1. Type de contenu affecté (content_type)

Cet attribut décrit le type de contenu qui est soumis aux restrictions imposées par la déclaration de motifs. Le contenu peut tomber dans plusieurs des catégories suivantes: Application, audio, image, produit, média synthétique, texte et vidéo. Par exemple, une publication sur une plateforme de médias sociaux pourrait être constituée de contenu texte et image. De même, une vidéo modifiée par des outils d'IA (générative) pourrait être décrite à la fois comme une vidéo et un média synthétique. Si aucune des options mentionnées précédemment ne décrit adéquatement le type de contenu soumis aux restrictions imposées par la déclaration de motifs, "Autre" peut également être sélectionné.

#### 2.2. Spécification du type de contenu Autre (content_type_other)

Si l'option sélectionnée pour l'attribut content_type était content_type_other, une spécification supplémentaire est requise. Veuillez indiquer uniquement la nature d'un type de contenu qui n'est inclus dans aucune des autres catégories de l'attribut content_type.

#### 2.3. Date de création du contenu sur la plateforme (content_date)

Cet attribut indique la date de création du contenu, c'est-à-dire lorsque la plateforme a commencé à héberger les informations affectées par la décision. Par exemple, il peut s'agir de la date à laquelle un compte utilisateur a été enregistré, ou de la date à laquelle un contenu spécifique a été publié.

#### 2.4. Langue du contenu (content_language)

Cet attribut concerne la langue des informations affectées par la décision. Par exemple, la langue sélectionnée comme paramètres du compte utilisateur, ou la langue dans laquelle le contenu a été publié.

#### 2.5. Langue non incluse ci-dessus (content_language_other)

Si "Autre" a été choisi comme contenu_language, ce champ permet de spécifier la langue exacte. Veuillez indiquer uniquement une langue qui n'est pas incluse dans la liste fournie sous content_language.

---

## Informations sur le type de restriction(s) imposée(s), la portée territoriale et la durée de la restriction

Cette sous-catégorie se compose des points de données suivants:

[3. Le type de restriction(s) imposée(s)](#3-le-type-de-restrictions-imposées)

[4. La durée de la restriction](#4-la-durée-de-la-décision)

[5. La portée territoriale de la décision](#5-la-portée-territoriale-de-la-décision)

_Pourquoi cette information est-elle incluse?_

_L'article 17(3) du Règlement 2022/2065 (DSA) établit des exigences minimales pour les informations qui doivent être incluses dans une déclaration de motifs. Cela inclut des informations sur le type de restriction(s) imposée(s) ainsi que leur portée territoriale et leur durée, comme spécifié à l'article 17(3)(a). Conformément à l'article 17(1) (a)-(d), les types de restrictions inclus sont les restrictions de visibilité, les restrictions monétaires, les restrictions de prestation de service et les restrictions de compte._

### 3. Le type de restriction(s) imposée(s)

Chaque déclaration de motifs doit inclure au moins une décision concernant une restriction imposée par la plateforme. L'un des quatre types de restrictions (visibilité, monétaire, prestation du service, compte du service) mentionnés à l'article 17(1)(a)-(d) doit donc être sélectionné.

Cependant, une décision et une déclaration de motifs peuvent contenir plusieurs restrictions. Par exemple, une publication sur une plateforme de médias sociaux qui a été jugée présumément illégale en vertu d'une loi nationale particulière pourrait faire l'objet d'une restriction de visibilité et entraîner une suspension temporaire du compte du destinataire du service. De même, un produit contrefait proposé sur une place de marché pourrait faire l'objet d'une restriction de visibilité ainsi que d'une restriction de paiements monétaires. Il est donc possible de sélectionner plusieurs types de décisions de restriction pour chaque déclaration.

Ainsi, au moins l'un des _decision_visibility, decision_mandatory, decision_provision, decision_account_ est obligatoire. De plus, en fonction des circonstances individuelles de chaque déclaration de motifs, les plateformes doivent sélectionner chaque type de décision applicable supplémentaire.

#### 3.1. Décision de restreindre la visibilité (decision_visibility)

Cet attribut décrit la ou les restriction(s) de visibilité imposée(s) sur des éléments d'information spécifiques fournis par le destinataire du service. La restriction peut entrer dans une ou plusieurs des catégories suivantes: Suppression de contenu, désactivation d'accès au contenu, déclassement de contenu, restriction d'âge du contenu, restriction d'interaction avec le contenu et étiquetage de contenu. Si aucune de ces options ne décrit adéquatement la ou les restriction(s) de visibilité imposée(s) par la déclaration de motifs, "Autre" peut également être sélectionné.

#### 3.2. Spécification d'une autre restriction de visibilité (decision_visibility_other)

Lorsque la décision contient une restriction de visibilité différente de l'une des restrictions spécifiques incluses dans decision_visibility, ce champ est requis pour indiquer la nature de la restriction.

#### 3.3. Décision de restreindre les paiements monétaires (decision_monetary)

Cet attribut décrit la restriction de paiement monétaire imposée sur des éléments d'information spécifiques fournis par le destinataire du service. La restriction peut entrer dans l'une des catégories suivantes: Suspension de paiements monétaires ou résiliation de paiements monétaires. Si aucune de ces options ne décrit adéquatement la ou les restriction(s) monétaire(s) imposée(s) par la déclaration de motifs, "Autre" peut également être sélectionné.

#### 3.4. Spécification d'une autre restriction monétaire (decision_monetary_other)

Lorsque la décision contient une restriction monétaire différente d'une restriction de suspension ou de résiliation monétaire, ce champ est requis pour indiquer la nature de la restriction.

#### 3.5. Décision de restreindre la prestation du service (decision_provision)

Cet attribut décrit la restriction imposée sur la prestation du service à un destinataire du service. La restriction peut entrer dans l'une des catégories suivantes: Suspension partielle de la prestation du service, suspension totale de la prestation du service, résiliation partielle de la prestation du service ou résiliation totale de la prestation du service.

#### 3.6. Décision de restreindre l'accès au compte (decision_account)

Cet attribut décrit la restriction imposée sur le compte du destinataire du service. La restriction peut entrer dans l'une des catégories suivantes: Suspension du compte ou résiliation du compte.
___

### 4. La durée de la décision

Chaque décision de restriction a une portée temporelle déterminée par sa date de début et de fin. Si une déclaration de motifs comprend plusieurs restrictions, la durée peut être différente pour chaque type de restriction imposée.

Par exemple, une restriction de visibilité pourrait être imposée indéfiniment sur un contenu présumé illégal. Simultanément, le compte du destinataire du service pourrait être suspendu pendant trois mois pour la même infraction. Dans ce cas, la durée des deux restrictions imposées serait différente, car elles auraient des dates de fin différentes.

En fonction des restrictions soumises sous _decision_visibility, decision_mandatory, decision_provision et decision_account_, les valeurs d'attribut de date de fin pertinentes doivent être fournies. Laisser les attributs de date de fin vides indique que la restriction pertinente a été imposée indéfiniment.

Prenant l'exemple ci-dessus, l'attribut end_date_visibility_restriction serait vide, tandis que l'attribut end_date_account_restriction serait défini sur une date trois mois après la date d'application.

#### 4.1. Date de demande (application_date)

Il s'agit de la date à partir de laquelle la ou les restriction(s) s'applique(nt).

#### 4.2. Date de fin pour les restrictions de visibilité (end_date_visibility_restriction)

Il s'agit de la date à laquelle la (la plus longue - si plusieurs ont été fournies) restriction de visibilité imposée prend fin.

#### 4.3. Date de fin pour les restrictions monétaires (end_date_monetary_restriction)

Il s'agit de la date à laquelle la restriction monétaire imposée prend fin.

#### 4.4. Date de fin pour les restrictions de service (end_date_service_restriction)

Il s'agit de la date à laquelle la restriction de service prend fin.

#### 4.5. Date de fin pour les restrictions de compte (end_date_account_restriction)

Il s'agit de la date à laquelle la restriction de compte prend fin.
___

### 5. La portée territoriale de la décision

**Portée territoriale  (territorial_scope)**
Il s'agit de la portée territoriale des restrictions imposées. Chaque valeur sélectionnée doit être le code ISO à 2 lettres du pays respectif et les pays doivent être des pays de l'UE ou de l'EEE. Plusieurs pays peuvent être sélectionnés.

Si la portée de la décision est l'ensemble de l'Union européenne (UE) ou de l'Espace économique européen (EEE), tous les pays de l'UE ou de l'EEE peuvent être sélectionnés simultanément dans le formulaire web.
___

## Informations sur les faits et circonstances sur lesquels repose la décision

Cette sous-catégorie se compose des points de données suivants:

[6. Une description des faits et circonstances](#6-description-des-faits-et-circonstances)

[7. Informations sur la source de l'enquête](#7-informations-sur-la-source-de-l'enquête)

[8. Informations sur le compte affecté par la décision](#8-informations-sur-le-compte-affecté-par-la-décision)

_Pourquoi cette information est-elle incluse?_

_L'article 17(3) du Règlement 2022/2065 (DSA) établit des exigences minimales pour les informations qui doivent être incluses dans une déclaration de motifs. Cela inclut les faits et circonstances sur lesquels repose la décision, comme spécifié à l'article 17(3)(b)._

### 6. Description des faits et circonstances

Les faits et circonstances de chaque modération de contenu peuvent être différents. Il est important que, conformément à l'article 24(5) du Règlement, les plateformes en ligne ne doivent pas inclure de données personnelles dans leurs soumissions. Les plateformes en ligne doivent être particulièrement prudentes avec les champs de texte libre à cet égard.

**Faits et circonstances sur lesquels repose la décision (decision_facts)**

Il s'agit d'un champ pour décrire les faits et circonstances sur lesquels repose la décision. Les plateformes sont encouragées à utiliser un langage standard pour des cas similaires.

### 7. Informations sur la source de l'enquête

#### 7.1. Source d'information (source_type)

Cet attribut décrit ce qui a conduit à l'enquête sur le contenu, qui fait partie des faits et circonstances sur lesquels repose la décision. La source de l'enquête peut entrer dans l'une des catégories suivantes: Une enquête peut être basée sur un avis soumis conformément à l'article 16 du DSA, un avis soumis par un signaleur de confiance ou toute autre notification qui n'est pas considérée comme un avis au titre de l'article 16. En cas de notifications d'incompatibilité avec les conditions générales qui ne sont pas considérées comme des avis au titre de l'article 16, l'option "autre notification" doit être sélectionnée. Bien que l'article 17 du DSA ne couvre pas les ordres au titre de l'article 9, les ordres des autorités qui ne relèvent pas de l'article 9 peuvent être indiqués comme motif d'enquête dans l'option "autres notifications". Les avis soumis par des signaleurs de confiance sont également des avis au titre de l'article 16, mais s'ils sont soumis par un signaleur de confiance, cette option doit être sélectionnée. Alternativement, une enquête peut être basée sur une initiative volontaire de la plateforme elle-même.

#### 7.2. Source/Notificateur (source_identity)

Conformément à l'article 17(3)(b) du Règlement, l'identité du notificateur doit être incluse dans la déclaration de motifs, mais uniquement si cela est strictement nécessaire pour identifier le caractère illégal du contenu. Cela peut être le cas, par exemple, pour les violations des droits de propriété intellectuelle.

Même dans de tels cas, les fournisseurs de plateformes en ligne doivent veiller à ce que les informations soumises ne contiennent pas de données personnelles, conformément à l'article 24(5) du DSA.

### 8. Informations sur le compte affecté par la décision

**Type de compte (account_type)**

Cet attribut concerne la nature du compte lié aux informations abordées par la décision. Il indique si le titulaire du compte est un utilisateur professionnel au sens du règlement (UE) 2019/1150 ou si le compte était un compte privé, c'est-à-dire un compte à usage personnel uniquement.
___


## Informations sur l'utilisation des moyens automatisés

Cette sous-catégorie se compose des points de données suivants:

[9. Informations sur l'utilisation des moyens automatisés pour la détection des infractions](#9-détection-automatique)

[10. Informations sur l'utilisation des moyens automatisés pour prendre des décisions sur les infractions](#10-décision-automatique)

_Pourquoi cette information est-elle incluse?_

_L'article 17(3)(c) du Règlement 2022/2065 (DSA) exige que les parties qui soumettent des déclarations de motifs fournissent des informations sur l'utilisation des moyens automatisés pour prendre la décision, y compris des informations sur le fait que la décision a été prise à l'égard du contenu détecté ou identifié à l'aide de moyens automatisés._

### 9. Détection Automatique

Cet attribut detection_automatique indique si et dans quelle mesure des moyens automatisés ont été utilisés pour identifier les informations spécifiques abordées par la décision. "Oui" signifie que des moyens automatisés ont été utilisés pour identifier les informations spécifiques abordées par la décision.

### 10. Décision Automatique

Cet attribut décision_automatique indique si et dans quelle mesure des moyens automatisés ont été utilisés pour décider du caractère infractionnel des informations spécifiques abordées par la décision. "Entièrement automatisé" signifie que l'ensemble du processus de décision a été effectué sans intervention humaine. Cet attribut ne fait pas référence à l'identification de tel contenu, mais uniquement à la décision prise après l'identification. "Non automatisé" signifie que l'ensemble du processus de décision a été effectué sans l'utilisation de moyens automatisés. "Partiellement automatisé" signifie que des moyens automatisés et une interaction humaine ont été appliqués dans le processus de prise de décision concernant le caractère infractionnel de l'information.
___

## Les motifs juridiques ou contractuels sur lesquels repose la décision

Cette sous-catégorie se compose des points de données suivants:

[11. Les motifs de la décision](#11-motifs-de-la-décision-decision-ground)

[12. Pour les informations présumées illégales: le fondement juridique invoqué](#12-pour-les-informations-présumées-illégales-le-fondement-juridique-invoqué)

[13. Pour les informations présumées en violation: le fondement contractuel invoqué](#13-pour-les-informations-présumées-en-violation-le-fondement-contractuel-invoqué)

[14. Chevauchement entre l'incompatibilité et l'illégalité (incompatible_content_illegal)](#14-chevauchement-entre-l'incompatibilité-et-l'illégalité-incompatible-content-illegal)

[15. Référence (URL) au fondement juridique ou contractuel](#15-référence-url-au-fondement-juridique-ou-contractuel-de-la-décision-ground-reference-url)

[16. Catégorie & Spécification (category, category_addition, category_specification)](#16-catégorie-specification-category-category-addition-category-specification)

_Pourquoi cette information est-elle incluse?_

_L'article 17(3)(d) et (e) du Règlement 2022/2065 (DSA) exigent que les parties qui soumettent des déclarations de motifs incluent une référence au fondement juridique ou contractuel sur lequel la décision a été prise. Cette exigence inclut également une explication de la raison pour laquelle l'information est considérée comme illégale ou incompatible avec le fondement référencé. Une catégorie indiquant le type d'illégalité ou le type d'incompatibilité avec les conditions d'utilisation, ainsi qu'une spécification de la catégorie, doivent également être incluses._

### 11. Motifs de la décision (decision_ground)

Cet attribut fournit une indication du fondement sur lequel la décision a été prise. La décision peut être fondée sur des motifs d'illégalité ou de non-conformité aux conditions d'utilisation de la plateforme.

#### 11.1. Si la décision est fondée sur l'illégalité (illegal_ground)

Si la décision est fondée sur l'illégalité, cet attribut fournit des informations supplémentaires sur le type d'illégalité invoquée. Les options peuvent inclure la diffamation, l'incitation à la haine, la violence, etc.

#### 11.2. Si la décision est fondée sur la non-conformité (non_compliance_ground)

Si la décision est fondée sur la non-conformité, cet attribut fournit des informations supplémentaires sur la clause ou les conditions des conditions d'utilisation de la plateforme qui n'ont pas été respectées. Les options peuvent inclure les droits de propriété intellectuelle, la violence, le harcèlement, etc.

### 12. Pour les informations présumées illégales: le fondement juridique invoqué (legal_basis)

Si la décision est fondée sur l'illégalité, cet attribut fournit des informations supplémentaires sur le fondement juridique invoqué. Par exemple, cela pourrait inclure une référence spécifique à la loi nationale ou de l'UE.

### 13. Pour les informations présumées en violation: le fondement contractuel invoqué (contract_basis)

Si la décision est fondée sur la non-conformité, cet attribut fournit des informations supplémentaires sur le fondement contractuel invoqué. Par exemple, cela pourrait inclure une référence spécifique à la clause des conditions d'utilisation de la plateforme.

### 14. Chevauchement entre l'incompatibilité et l'illégalité (incompatible_content_illegal)

Cet attribut indique s'il existe un chevauchement entre les motifs de non-conformité et les motifs d'illégalité pour la même information. "Oui" indique qu'il y a un chevauchement, "Non" indique qu'il n'y a pas de chevauchement.

### 15. Référence (URL) au fondement juridique ou contractuel de la décision (ground_reference_url)

Cet attribut fournit une référence (URL) directe au fondement juridique ou contractuel sur lequel repose la décision. Cela peut être une référence à une loi spécifique ou à une clause spécifique des conditions d'utilisation de la plateforme.

### 16. Catégorie & Spécification (category, category_addition, category_specification)

Cet attribut fournit une catégorie et une spécification de la décision. Par exemple, la catégorie pourrait être "Contenu interdit" et la spécification pourrait être "Propos haineux". Cela fournit une indication plus précise de la nature de la décision.
___
