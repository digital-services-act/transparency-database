## Explication des informations contenues dans la base de données de transparence DSA

La base de données de transparence DSA est structurée de manière à permettre la transparence et l'examen des décisions de modération de contenu des plateformes en ligne. Cette page explique quel type d'informations est collecté et comment les différents champs de données se rapportent à l'article 17 du DSA, qui établit les exigences d'envoi de déclarations de motifs aux destinataires du service.

## Soumission de déclarations claires et spécifiques

Cette sous-catégorie comprend les éléments suivants :

[1. Identifiant Unique de Plateforme (PUID)](#1-identifiant-unique-de-plateforme-puid)

[2. Spécifications sur le contenu affecté par la décision](#2-spécifications-du-contenu-affecté-par-la-décision)

_Pourquoi ces informations sont-elles incluses ?_

_Les articles 17(1) et 17(4) du DSA exigent que les déclarations de motifs soumises à la base de données soient claires et spécifiques. De plus, les caractéristiques du contenu affecté par la décision de modération de contenu font partie des faits et circonstances visés à l'article 17(3)(b) du DSA. Les exigences de cette première sous-catégorie renforcent également la fonction de la base de données pour garantir la transparence et permettre l'examen des décisions de modération de contenu conformément au considérant 66 du DSA._

### 1. Identifiant Unique de Plateforme (PUID)

Il s'agit d'une chaîne qui identifie de manière unique une déclaration de motifs dans les systèmes de la plateforme en ligne.

Le PUID est attribué par la plateforme en ligne concernée, et il est unique pour chaque déclaration de motifs. Cela permet aux plateformes en ligne d'identifier les informations exactes qui ont été affectées par la décision de modération de contenu et offre la possibilité de relier les données de cette base de données à des informations supplémentaires, telles que l'URL spécifique, par exemple.

### 2. Spécifications du contenu affecté par la décision

Les spécifications du contenu affecté par la décision couvrent les champs requis concernant le type de contenu affecté et la date de création du contenu, ainsi que le champ facultatif concernant la langue de l'information affectée.

#### 2.1. Type de contenu affecté

Ce champ de données décrit le type de contenu restreint par la décision à laquelle la déclaration de motifs se rapporte. Le contenu peut appartenir à plusieurs des catégories suivantes : application, audio, image, produit, média synthétique, texte et vidéo. Par exemple, une publication sur une plateforme de médias sociaux pourrait comprendre du contenu textuel et visuel. De même, une vidéo modifiée par des outils d'IA (générative) pourrait être décrite à la fois comme une vidéo et un média synthétique. Si les options spécifiques ne décrivent pas adéquatement le type de contenu restreint, "Autre" peut également être sélectionné et spécifié ultérieurement.

#### 2.2. Spécification du type de contenu "Autre"

Si l'option sélectionnée pour l'attribut content_type était content_type_other, une spécification supplémentaire est requise. Seuls les types de contenu qui ne sont pas inclus dans la liste prédéfinie des types de contenu doivent être indiqués ici.

#### 2.3. Date de création du contenu sur la plateforme en ligne

Cet attribut indique la date de création du contenu, c'est-à-dire quand la plateforme en ligne a commencé à héberger l'information affectée par la décision. Par exemple, cela peut être la date à laquelle un contenu spécifique a été publié ou la date à laquelle un compte utilisateur a été enregistré.

#### 2.4. Langue du contenu

Cet attribut concerne la langue de l'information affectée par la décision.

---

## Informations sur le type de restriction(s) imposée(s), la portée territoriale et la durée de la restriction

Cette sous-catégorie comprend les éléments suivants :

[3. Le type de restrictions imposées](#3-le-type-de-restrictions-imposées)

[4. La durée de la restriction](#4-la-durée-de-la-décision)

[5. La portée territoriale de la décision](#5-la-portée-territoriale-de-la-décision)

_Pourquoi ces informations sont-elles incluses ?_

_L'article 17(3) du DSA établit des exigences minimales pour les informations à inclure dans une déclaration de motifs. Cela comprend des informations sur le type de restriction(s) imposée(s), ainsi que leur portée territoriale et leur durée, comme spécifié à l'article 17(3)(a) du DSA. Conformément à l'article 17(1) (a)-(d) du DSA, les types de restrictions inclus sont les restrictions de visibilité, les restrictions de paiement monétaire, les restrictions de service et les restrictions de compte._

### 3. LE TYPE DE RESTRICTION(S) IMPOSÉE(S)

Chaque déclaration de motifs doit inclure au moins une décision concernant une restriction imposée par la plateforme en ligne. L'un des quatre types de restrictions (visibilité, monétaire, fourniture du service, compte du service) mentionnés à l'article 17(1)(a)-(d) sera donc toujours indiqué.

Cependant, une décision et une déclaration de motifs peuvent contenir plusieurs restrictions. Par exemple, une publication sur une plateforme de médias sociaux jugée présumément illégale en vertu d'une loi nationale particulière pourrait être soumise à une restriction de visibilité et entraîner une suspension temporaire du compte du destinataire du service. De même, un produit contrefait proposé sur une place de marché pourrait être soumis à une restriction de visibilité ainsi qu'à une restriction des paiements monétaires. Il est donc possible que chaque déclaration de motifs inclue plusieurs types de décisions de restriction.

#### 3.1. Décision de restreindre la visibilité

Cet attribut décrit la ou les restrictions de visibilité imposées sur des éléments spécifiques d'information fournis par le destinataire du service. La restriction peut appartenir à une ou plusieurs des catégories suivantes : suppression de contenu, désactivation de l'accès au contenu, rétrogradation du contenu, restriction d'âge du contenu, restriction d'interaction avec le contenu et étiquetage du contenu. Si aucune de ces options ne décrit adéquatement la ou les restrictions de visibilité imposées par la déclaration de motifs, la plateforme en ligne doit sélectionner "Autre".

#### 3.2. Spécification d'autres restrictions de visibilité

Lorsque la décision contient une restriction de visibilité qui est différente de l'une des restrictions spécifiques proposées par le formulaire de soumission, ce champ indique la nature de la restriction.

#### 3.3. Décision de restreindre les paiements monétaires

Cet attribut décrit la restriction des paiements monétaires imposée sur des éléments spécifiques d'information fournis par le destinataire du service. La restriction peut appartenir à l'une des catégories suivantes : suspension des paiements monétaires ou résiliation des paiements monétaires. Si aucune de ces options ne décrit adéquatement la ou les restrictions monétaires imposées par la plateforme en ligne, "Autre" sera sélectionné.

#### 3.4. Spécification d'autres restrictions monétaires

Lorsque la décision contient une restriction monétaire qui est différente d'une suspension monétaire ou d'une restriction de résiliation monétaire, ce champ est requis pour indiquer la nature de la restriction.

#### 3.5. Décision de restreindre la fourniture du service

Cet attribut décrit la restriction imposée sur la fourniture du service à un destinataire du service. La restriction peut appartenir à l'une des catégories suivantes : suspension partielle de la fourniture du service, suspension totale de la fourniture du service, résiliation partielle de la fourniture du service ou résiliation totale de la fourniture du service.

#### 3.6. Décision de restreindre l'accès au compte

Cet attribut décrit la restriction imposée sur le compte du destinataire du service. La restriction peut appartenir à l'une des catégories suivantes : suspension du compte ou résiliation du compte.

---

### 4. LA DURÉE DE LA RESTRICTION

Chaque décision de restriction a une portée temporelle déterminée par sa date de début et de fin, ou l'absence de date de fin. Si une déclaration de motifs comprend plusieurs restrictions, la durée peut être différente pour chaque type de restriction imposée.

Par exemple, une restriction de visibilité pourrait être imposée indéfiniment sur un contenu jugé présumément illégal. Simultanément, le compte du destinataire du service pourrait être suspendu pendant trois mois pour la même infraction. Dans ce cas, la durée des deux restrictions imposées serait différente, car elles auraient des dates de fin différentes.

En fonction des restrictions incluses dans la déclaration de motifs, les valeurs attributaires de la date de fin pertinentes seront fournies. Lorsqu'aucune date de fin n'est indiquée, cela signifie que la restriction pertinente a été imposée indéfiniment.

En reprenant l'exemple ci-dessus, l'attribut end_date_visibility_restriction serait vide, tandis que l'attribut end_date_account_restriction serait défini sur une date trois mois après l'attribut application_date.

#### 4.1. Date d'application

Il s'agit de la date à partir de laquelle la ou les restrictions s'appliquent.

#### 4.2. Date de fin des restrictions de visibilité

Il s'agit de la date à laquelle la restriction de visibilité imposée prend fin.

#### 4.3. Date de fin des restrictions monétaires

Il s'agit de la date à laquelle la restriction monétaire imposée prend fin.

#### 4.4. Date de fin des restrictions de service

Il s'agit de la date à laquelle la restriction de service prend fin.

#### 4.5. Date de fin des restrictions de compte

Il s'agit de la date à laquelle la restriction de compte prend fin.

---

### 5. LA PORTÉE TERRITORIALE DE LA DÉCISION

**Portée territoriale**
Il s'agit de la portée territoriale des restrictions imposées. Plusieurs pays de l'UE ou de l'EEE peuvent être indiqués.

---

## Informations sur les faits et circonstances sur lesquels repose la décision

Cette sous-catégorie comprend les éléments suivants :

[6. Une description des faits et circonstances](#6-description-des-faits-et-circonstances)

[7. Informations sur la source de l'enquête](#7-informations-sur-la-source-de-l'enquête)

[8. Informations sur le compte affecté par la décision](#8-informations-sur-le-compte-affecté-par-la-décision)

_Pourquoi ces informations sont-elles incluses ?_

_L'article 17(3) du DSA établit des exigences minimales pour les informations à inclure dans une déclaration de motifs. Cela comprend les faits et circonstances sur lesquels repose la décision, comme spécifié à l'article 17(3)(b) du DSA._

### 6. DESCRIPTION DES FAITS ET CIRCONSTANCES

Les faits et circonstances de chaque modération de contenu peuvent être différents. Outre le type, la date et la langue du contenu, les plateformes en ligne doivent fournir des informations adéquates sur lesquelles elles se sont appuyées pour prendre la décision. Il est important que, conformément à l'article 24(5) du DSA, les plateformes en ligne ne doivent pas inclure de données personnelles dans leurs soumissions.

**Faits et circonstances sur lesquels repose la décision**

Il s'agit d'un champ de texte libre pour décrire les faits et circonstances sur lesquels repose la décision.

### 7. INFORMATIONS SUR LA SOURCE DE L'ENQUÊTE

#### 7.1. Source de l'information

Cet attribut décrit ce qui a conduit à l'enquête sur le contenu, qui fait partie des faits et circonstances sur lesquels repose la décision. La source de l'enquête peut appartenir à l'une des catégories suivantes : Une enquête peut être basée sur un avis soumis conformément à l'article 16 du DSA ; un avis soumis par un signaleur de confiance dans le cadre du DSA ; toute autre notification externe ; ou une initiative volontaire de la plateforme en ligne elle-même.

#### 7.2. L'identité du notificateur

Conformément à l'article 17(3)(b) du DSA, l'identité du notificateur doit être incluse dans la déclaration de motifs, mais seulement si cela est strictement nécessaire pour identifier l'illégalité du contenu. Cela peut être le cas, par exemple, pour les infractions aux droits de propriété intellectuelle.

Même dans de tels cas, les fournisseurs de plateformes en ligne doivent veiller à ce que les informations soumises ne contiennent pas de données personnelles, conformément à l'article 24(5) du DSA.

### 8. INFORMATIONS SUR LE COMPTE AFFECTÉ PAR LA DÉCISION

**Type de compte**

Cet attribut concerne la nature du compte lié aux informations traitées par la décision. Il indique si le titulaire du compte est un utilisateur professionnel au sens du règlement (UE) 2019/1150 ou si le compte était un compte privé, c'est-à-dire un compte à usage personnel uniquement.

---

## Informations sur l'utilisation des moyens automatisés

Cette sous-catégorie comprend les éléments suivants :

[9. Informations sur l'utilisation des moyens automatisés pour la détection des infractions](#9-détection-automatisée)

[10. Informations sur l'utilisation des moyens automatisés pour prendre des décisions sur les infractions](#10-décision-automatisée)

_Pourquoi ces informations sont-elles incluses ?_

_L'article 17(3)(c) du Règlement 2022/2065 (DSA) exige que les parties qui soumettent des déclarations de motifs fournissent des informations sur l'utilisation des moyens automatisés pour prendre la décision, y compris des informations sur le fait que la décision a été prise concernant du contenu détecté ou identifié à l'aide de moyens automatisés._

### 9. DÉTECTION AUTOMATISÉE

Cet attribut indique si et dans quelle mesure des moyens automatisés ont été utilisés pour identifier les informations spécifiques traitées par la décision. "Oui" signifie que des moyens automatisés ont été utilisés pour identifier les informations spécifiques traitées par la décision.

### 10. DÉCISION AUTOMATISÉE

Cet attribut indique si et dans quelle mesure des moyens automatisés ont été utilisés pour décider de la nature des informations spécifiques traitées par la décision. "Entièrement automatisé" signifie que l'ensemble du processus décisionnel a été effectué sans intervention humaine. Cet attribut ne fait pas référence à l'identification de ce contenu, mais uniquement à la décision prise après l'identification. "Non automatisé" signifie que l'ensemble du processus décisionnel a été effectué sans l'utilisation de moyens automatisés. "Partiellement automatisé" signifie que des moyens automatisés et une interaction humaine ont été appliqués dans le processus de prise de décision concernant la nature des informations litigieuses.

---

## Les motifs juridiques ou contractuels sur lesquels repose la décision

Cette sous-catégorie comprend les éléments suivants :

[11. Les motifs de la décision](#11-motifs-de-la-décision)

[12. Pour les informations présumées illégales : le fondement juridique sur lequel repose la décision](#12-pour-les-informations-présumées-illégales-le-fondement-juridique-sur-lequel-repose-la-décision)

[13. Pour les informations présumées en infraction : le fondement contractuel sur lequel repose la décision](#13-pour-les-informations-présumées-en-infraction-le-fondement-contractuel-sur-lequel-repose-la-décision)

[14. Chevauchement entre incompatibilité des conditions générales et illégalité](#14-chevauchement-entre-l'incompatibilité-des-conditions-générales-et-l'illégalité-contenu-incompatible-illégal)

[15. Référence (URL) au fondement juridique ou contractuel](#15-référence-url-au-fondement-juridique-ou-contractuel-motifs-de-la-décision-référence-url)

[16. Catégorie & Spécification de la catégorie](#16-catégorie-spécification-catégorie-catégorie-addition-spécification-de-la-catégorie)

_Pourquoi ces informations sont-elles incluses ?_

_L'article 17(3)(d) et (e) du DSA exigent que les parties qui soumettent des déclarations de motifs incluent une référence au fondement juridique ou contractuel sur lequel repose la décision. Cette exigence inclut également une explication sur la raison pour laquelle l'information est considérée comme illégale ou incompatible avec le fondement référencé. Une catégorie indiquant le type d'illégalité ou le type d'incompatibilité avec les conditions générales du service, sur lesquels le contenu a été modéré, doit être sélectionnée. Les catégories permettent d'interroger les informations nécessaires pour permettre un examen des décisions de modération de contenu._

### 11. MOTIFS DE LA DÉCISION

Cet attribut indique si la décision a été prise conformément à l'article 17(3)(d) du DSA, ce qui signifie que l'information était présumée illégale, ou conformément à l'article 17(3)(e) du DSA, ce qui signifie que l'information était présumée incompatible avec les conditions générales du service.

### 12. POUR LES INFORMATIONS PRÉSUMÉES ILLÉGALES : LE FONDEMENT JURIDIQUE SUR LEQUEL REPOSE LA DÉCISION

##### 12.1. Référence au fondement juridique

Il s'agit d'un champ où le fondement juridique exact (c'est-à-dire la ou les lois applicables) sur lequel la décision a été prise doit être indiqué.

##### 12.2. Explication de l'applicabilité du fondement juridique

Il s'agit d'un champ pour expliquer pourquoi l'information est considérée comme illégale sur la base du fondement juridique indiqué. L'explication n'a pas besoin de répéter les faits et circonstances, mais peut s'y référer.

### 13. POUR LES INFORMATIONS PRÉSUMÉES EN INFRACTION : LE FONDEMENT CONTRACTUEL SUR LEQUEL REPOSE LA DÉCISION

#### 13.1. Motifs de contenu incompatible

Il s'agit d'un champ où le fondement contractuel exact (c'est-à-dire la section pertinente des conditions générales applicables) sur lequel la décision a été prise doit être indiqué.

#### 13.2. Explication du contenu incompatible

Il s'agit d'un champ pour expliquer pourquoi l'information est considérée comme incompatible avec les conditions générales du service sur la base du fondement contractuel indiqué. L'explication n'a pas besoin de répéter les faits et circonstances, mais peut s'y référer.

### 14. CHEVAUCHEMENT ENTRE INCOMPATIBILITÉ DES CONDITIONS GÉNÉRALES ET ILLÉGALITÉ (CONTENU INCOMPATIBLE ILLÉGAL)

Cet attribut indique s'il y a eu chevauchement entre les conditions générales du service et la présumée illégalité du contenu. Si oui, la déclaration de motifs doit être traitée comme une déclaration de motifs concernant une information illégale.

### 15. RÉFÉRENCE (URL) AU FONDEMENT JURIDIQUE OU CONTRACTUEL (MOTIFS DE LA DÉCISION - RÉFÉRENCE URL)

Si le fondement juridique ou contractuel est disponible en ligne, cet attribut indique où il peut être consulté. Il s'agit d'un champ facultatif.

Des exemples comprennent un lien URL direct vers la version applicable des termes et conditions sur lesquels la décision a été prise, ou un lien URL direct vers la loi applicable sur laquelle la décision a été prise concernant l'illégalité présumée de l'information. Un exemple de référence directe au DSA est : [https://eur-lex.europa.eu/eli/reg/2022/2065](https://eur-lex.europa.eu/eli/reg/2022/2065).

### 16. CATÉGORIE & SPÉCIFICATION

Une liste de catégories et de spécifications est incluse pour codifier le type d'illégalité et/ou le type d'incompatibilité avec les termes et conditions ayant conduit à la restriction de l'information. La section catégorie et spécifications se compose de trois attributs :

Tout d'abord, une classification de catégorie de haut niveau doit être indiquée. La classification de haut niveau indique la catégorie principale sous laquelle les motifs sur lesquels la décision a été prise tombent. La liste des catégories de haut niveau est exhaustive, et une seule sélection est requise. L'option qui couvre le mieux les motifs sur lesquels la décision a été prise devrait être sélectionnée par la plateforme en ligne.

Dans les cas où plus de granularité est nécessaire, deux attributs supplémentaires - optionnels - peuvent être utilisés pour clarifier davantage la catégorie en question. L'attribut de catégorie supplémentaire fournit une liste d'options de catégorie identiques à la classification de catégorie de haut niveau. Cette liste est également exhaustive, mais elle permet une sélection multiple. Enfin, l'attribut de spécification de catégorie permet l'ajout de mots-clés qui spécifient davantage les catégories de haut niveau. Les plateformes sont encouragées à utiliser autant d'options de catégorie et de spécification supplémentaires que nécessaire pour décrire les motifs sur lesquels la décision a été prise de manière aussi précise et spécifique que raisonnablement possible. Le champ de spécification de catégorie contient également une option "Autre", qui peut être précisée davantage. Les listes ont été conçues de manière à ce que les spécifications relèvent des catégories de haut niveau comme suit (listées par ordre alphabétique) :

- Bien-être animal
    - Dommages aux animaux
    - Vente illégale d'animaux

- Violations de la protection des données et de la vie privée
    - Violation des données biométriques
    - Motif de traitement manquant pour les données
    - Droit à l'oubli
    - Falsification de données

- Discours illégaux ou nuisibles
    - Diffamation
    - Discrimination
    - Discours de haine

- Violations de la propriété intellectuelle
    - Violation du droit d'auteur
    - Violation de dessin
    - Violation d'indications géographiques
    - Violation de brevet
    - Violation de secret commercial
    - Violation de marque de commerce

- Effets négatifs sur le débat civique ou les élections
    - Désinformation
    - Manipulation et interférence des informations étrangères
    - Désinformation

- Comportement non consenti
    - Partage d'images non consenti
    - Articles non consentis contenant des deepfakes ou une technologie similaire utilisant les caractéristiques d'un tiers

- Intimidation en ligne
    - Harcèlement

- Contenu pornographique ou sexualisé
    - Matériel sexuel pour adultes
    - Abus sexuel basé sur l'image (à l'exclusion du contenu représentant des mineurs)

- Protection des mineurs
    - Restrictions spécifiques à l'âge concernant les mineurs
    - Matériel de pornographie juvénile
    - Système de prédateurs/attirance sexuelle de mineurs
    - Défis dangereux

- Risque pour la sécurité publique
    - Organisations illégales
    - Risque de dommages environnementaux
    - Risque pour la santé publique
    - Contenu terroriste

- Escroqueries et/ou fraudes
    - Comptes inauthentiques
    - Annonces inauthentiques
    - Avis d'utilisateurs inauthentiques
    - Usurpation d'identité ou détournement de compte
    - Phishing
    - Systèmes pyramidaux

- Auto-mutilation
    - Contenu promouvant les troubles alimentaires
    - Auto-mutilation
    - Suicide

- Portée du service de la plateforme
    - Restrictions spécifiques à l'âge
    - Exigences géographiques
    - Biens/services non autorisés à être offerts sur la plateforme
    - Exigences linguistiques
    - Nudité

- Produits dangereux et/ou illégaux
    - Informations insuffisantes sur les commerçants
    - Biens et services réglementés
    - Jouets dangereux

- Violence
    - Dommages coordonnés
    - Violence basée sur le genre
    - Exploitation humaine
    - Traite des êtres humains
    - Incitation à la violence et/ou à la haine

Non couvert par aucune catégorie de haut niveau

- Autre

Par exemple, si un produit dangereux a été retiré d'une place de marché en ligne qui était également un produit contrefait, le relevé de motifs pertinent pourrait indiquer soit "Produits dangereux et/ou illégaux" soit "Violations de la propriété intellectuelle" comme catégorie principale, selon celle qui était considérée comme plus pertinente pour la décision par la plateforme. L'option respective "Autre" pourrait être choisie dans l'attribut d'ajout, et une ou plusieurs des options de spécification de catégorie pertinentes (par exemple "Violation de marque de commerce" et/ou "Jouets dangereux" ou d'autres options de spécification de catégorie considérées comme pertinentes par la plateforme) pourraient être ajoutées pour fournir des informations supplémentaires. Les plateformes sont encouragées à utiliser autant d'options qu'elles le jugent approprié pour décrire au mieux leurs relevés de motifs avec ces données.

#### 16.4. Champ pour ajouter une spécification ouverte

Pour les relevés de motifs où les plateformes estiment qu'aucune spécification existante ne reflète correctement les motifs qu'elles ont utilisés, cette section permet une soumission de texte libre pour ajouter des spécifications. La Commission surveillera les soumissions dans ce champ de texte libre et mettra à jour les options de spécification de catégorie en conséquence, si des soumissions récurrentes prévalent.
