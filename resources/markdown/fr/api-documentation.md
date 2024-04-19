## Comment utiliser l'API

Des utilisateurs spécifiques de cette base de données ont la possibilité de créer
des déclarations de raisons en utilisant un point de terminaison d'API. Cela augmente considérablement
l'efficacité et permet l'automatisation.

## Demande d'accès à l'API

Pour configurer votre processus de soumission de déclaration de raisons, veuillez vous inscrire [ici](https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons) concernant vos obligations en vertu de l'article 24(5) du DSA.
Après avoir reçu votre formulaire d'inscription, le coordinateur des services numériques de votre État membre vous contactera pour vous fournir les détails sur la manière de finaliser l'intégration de votre plateforme en ligne.

Une fois que vous êtes intégré via votre coordinateur de services numériques, vous aurez accès à un environnement de bac à sable pour tester vos soumissions à la Base de données de transparence de la DSA, que vous pouvez effectuer soit via une Interface de programmation d'application (API) soit un formulaire web, en fonction du volume de vos données et de vos besoins techniques.

Une fois la phase de test terminée, vous pourrez passer à l'environnement de production de la Base de données de transparence de la DSA, où vous pourrez commencer à soumettre votre déclaration de raisons via une API ou un formulaire web.

## Votre jeton d'API

Lorsque votre compte a la possibilité d'utiliser l'API, vous pouvez générer un jeton sécurisé privé qui vous permettra d'utiliser l'API.

Ce jeton ressemble à ceci :

<pre>
    X|ybqkCFX7ZkIFoLxtI0VAk1JBzMR9jVk4c4EU
</pre>

Si vous ne connaissez pas votre jeton ou si vous devez en générer un nouveau, vous pouvez le faire
dans le [profil utilisateur](/profile/start) de cette application. Cliquez simplement sur le bouton "Générer un nouveau jeton".

Ce jeton ne sera affiché qu'une seule fois, il devra donc être copié et stocké en toute sécurité.

__Chaque fois que vous générez un nouveau jeton, l'ancien jeton devient invalide !__

<x-ecl.message type="warning" icon="warning" title="Avertissement de sécurité" message="Ce jeton identifie
les appels à l'API en tant que vous ! Ne partagez pas ce jeton avec d'autres entités.
Ils pourront vous usurper et agir en votre nom ! Si vous pensez que quelqu'un utilise votre jeton,
veuillez générer immédiatement un nouveau jeton pour invalider l'ancien." close="" />


## Création d'une déclaration

Pour créer une déclaration de raison en utilisant l'API, vous devrez effectuer une requête ```POST``` vers cet endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statement.store')}}
</pre>

Pour cette requête, vous devrez fournir une autorisation, une acceptation et un type de contenu en-têtes de la requête :

<pre>
    Authorization: Bearer VOTRE_JETON
    Accept: application/json
    Content-Type: application/json
</pre>

Le corps de votre requête doit être un payload encodé en json avec les informations de la déclaration.

Exemple de corps de payload JSON :


```json
{
    "decision_visibility": ["DECISION_VISIBILITY_CONTENT_DISABLED"],
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "end_date_monetary_restriction": "2023-08-08",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https://www.anurl.com",
    "content_type": ["CONTENT_TYPE_VIDEO","CONTENT_TYPE_AUDIO","CONTENT_TYPE_SYNTHETIC_MEDIA"],
    "category": "STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT",
    "illegal_content_legal_ground": "illegal content legal grounds",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "territorial_scope": [
        "PT",
        "ES",
        "DE"
    ],
    "content_language": "EN",
    "content_date": "2023-08-08",
    "application_date": "2023-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
    "puid": "TK421"
}
```

### La réponse

Lorsque la requête a été envoyée et qu'elle est correcte, une réponse de ```201``` ```Créé``` sera renvoyée.

Vous recevrez également un payload avec la déclaration telle qu'elle a été créée dans la base de données :


```json
{
    "decision_visibility": [
        "DECISION_VISIBILITY_CONTENT_DISABLED"
    ],
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https:\/\/www.anurl.com",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "content_type": [
        "CONTENT_TYPE_AUDIO",
        "CONTENT_TYPE_SYNTHETIC_MEDIA",
        "CONTENT_TYPE_VIDEO"
    ],
    "category": "STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT",
    "territorial_scope": [
        "DE",
        "ES",
        "PT"
    ],
    "content_language": "EN",
    "content_date": "2023-08-08",
    "application_date": "2023-08-08",
    "end_date_monetary_restriction": "2023-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
    "end_date_visibility_restriction": null,
    "end_date_account_restriction": null,
    "end_date_service_restriction": null,
    "puid": "TK421",
    "uuid": "7d0d0f7c-3ba9-45ba-966a-ec621eb17225",
    "platform_name": "...",
    "created_at": "2023-08-08 08:08:08",
    "permalink": ".... statement/7d0d0f7c-3ba9-45ba-966a-ec621eb17225",
    "self": ".... api/v1/statement/7d0d0f7c-3ba9-45ba-966a-ec621eb17225"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Chaque fois que vous effectuez
un appel à une API, vous devez toujours valider que vous avez reçu le bon statut, '201 Créé'.
Si vous n'avez pas reçu de 201 Créé, alors la déclaration n'a pas été faite, elle n'est pas dans la base de données
et vous devrez réessayer ultérieurement." close="" />

## UUID

Chaque déclaration créée dans la base de données reçoit un UUID qui identifie de manière unique la déclaration.

Cet UUID est ensuite utilisé dans les URL pour récupérer et visualiser la déclaration en ligne.

Ces URL sont présentes dans la réponse après la création en tant qu'attributs "uuid", "permalink" et "self".

## Création de plusieurs déclarations

Nous encourageons vivement toutes les plateformes à regrouper et à créer plusieurs déclarations de raisons en une seule appel API
en utilisant l'endpoint multiple.

Veuillez effectuer une requête ```POST``` vers cet endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statements.store')}}
</pre>

Le payload de cette requête doit contenir un champ appelé "statements" et ce champ
doit être un tableau de déclarations de raisons.

Voici un exemple :


```javascript
{
    "statements": [
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        },
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        },
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        }
        ...
    ]
}
```

L'endpoint multiple est capable de faire 100 déclarations par appel.

Lorsque la requête a été envoyée et qu'elle est correcte, une réponse de ```201``` ```Créé``` sera renvoyée.

Le payload de la réponse lors de l'appel à l'endpoint multiple sera un tableau des déclarations de raison lorsqu'il est réussi. Chaque déclaration de raison aura ensuite un
uuid, created_at, self et permalink attribut pour refléter qu'elle a été créée.


```javascript
{
    "statements": [
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DEMOTED"
            ],
            "decision_monetary": "DECISION_MONETARY_OTHER",
            ...
            ...
            "uuid": "bf92a941-c77a-4b9d-a236-38956ae79cc5",
            "created_at": "2023-11-07 07:53:43",
            "platform_name": "DSA Team",
            "permalink": "https://.../statement/bf92a941-c77a-4b9d-a236-38956ae79cc5",
            "self": "https://.../api/v1/statement/bf92a941-c77a-4b9d-a236-38956ae79cc5",
            "puid": "b5ec958d-892a-4c11-a3f2-6a3ad597eeb1"
        },
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DEMOTED"
            ],
            ...
            ...
            ...
            "uuid": "174a1921-0d9e-4864-b095-6774fb0237da",
            "created_at": "2023-11-07 07:53:44",
            "platform_name": "DSA Team",
            "permalink": "https://.../statement/174a1921-0d9e-4864-b095-6774fb0237da",
            "self": "https://.../api/v1/statement/174a1921-0d9e-4864-b095-6774fb0237da",
            "puid": "a12b436a-33b1-4403-99b2-8c16e3c5502f"
        },
        {
            "decision_account": "DECISION_ACCOUNT_SUSPENDED",
            "account_type": "ACCOUNT_TYPE_PRIVATE",
            "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
            ...
            ...
            ...
            "uuid": "b8f03bf5-b8fd-4987-ac56-6fe6ab155e9e",
            "created_at": "2023-11-07 07:53:45",
            "platform_name": "DSA Team",
            "permalink": "https://.../statement/b8f03bf5-b8fd-4987-ac56-6fe6ab155e9e",
            "self": "https://.../api/v1/statement/b8f03bf5-b8fd-4987-ac56-6fe6ab155e9e",
            "puid": "649c58f6-8412-4100-b10c-010b76f5a41a"
        },
        ...
    ]
}
```

## Attributs de la déclaration

Les attributs de la déclaration prennent deux formes principales.

* texte libre (des limites de caractères maximales s'appliquent, voir ci-dessous)
* limité, la valeur fournie doit être l'une des options autorisées

Lors de la soumission des déclarations, veuillez prendre soin de ne PAS soumettre DE DONNÉES personnelles. Nous effectuerons régulièrement des vérifications dans la base de données pour nous assurer qu'aucune donnée personnelle n'a été soumise. Cependant, conformément à l'article 24(5), il incombe aux fournisseurs de plates-formes en ligne de s'assurer que les informations soumises ne contiennent pas de données personnelles.

## Explication supplémentaire pour les attributs de la déclaration

Veuillez vous référer à notre page [Explication supplémentaire pour les attributs de la déclaration](/page/additional-explanation-for-statement-attributes) pour plus d'informations sur les attributs.

### Visibilité de la décision (decision_visibility)

Cet attribut nous indique la restriction de visibilité des éléments d'information spécifiques fournis par le destinataire du service.

Cet attribut est obligatoire uniquement si les champs suivants sont vides : decision_monetary, decision_provision et decision_account

La valeur fournie doit être un tableau avec au moins un des éléments suivants :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_VISIBILITIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Autre visibilité de la décision (decision_visibility_other)

Ceci est requis si DECISION_VISIBILITY_OTHER était la decision_visibility.

Limité à 500 caractères.

### Suspension des paiements monétaires, résiliation ou autre restriction (decision_monetary)

C'est un attribut qui donne des informations sur la suspension des paiements monétaires, la résiliation ou d'autres restrictions.

Cet attribut est obligatoire uniquement si les champs suivants sont vides : decision_visibility, decision_provision et decision_account

La valeur fournie doit être l'une des suivantes :


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_MONETARIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Autre décision monétaire (decision_monetary_other)

Ceci est requis si DECISION_MONETARY_OTHER était la decision_monetary.

Limité à 500 caractères.

### Décision concernant la fourniture du service (decision_provision)

C'est un attribut qui nous informe sur la suspension ou la résiliation de la fourniture du service.

Cet attribut est obligatoire uniquement si les champs suivants sont vides : decision_visibility, decision_monetary et decision_account

La valeur fournie doit être l'une des suivantes :


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_PROVISIONS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Décision concernant le statut du compte (decision_account)

C'est un attribut qui nous informe sur le statut du compte.

Cet attribut est obligatoire uniquement si les champs suivants sont vides : decision_visibility, decision_monetary et decision_provision

La valeur fournie doit être l'une des suivantes :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_ACCOUNTS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>


### Type de compte (account_type)

C'est un attribut qui nous informe sur le type de compte.

Cet attribut est facultatif.

La valeur fournie doit être l'une des suivantes :


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::ACCOUNT_TYPES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Faits et circonstances sur lesquels la décision repose (decision_facts)

Il s'agit d'un champ textuel obligatoire pour décrire les faits et circonstances sur lesquels la décision repose.

Limité à 5000 caractères.

### Fondements de la décision (decision_ground)

Il s'agit d'un champ obligatoire qui nous indique les bases sur lesquelles la décision a été prise.


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_GROUNDS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>


### URL de référence des fondements de la décision (decision_ground_reference_url)

Il s'agit d'une URL vers les CGV ou la loi sur lesquelles la décision repose.

C'est un attribut facultatif.

### Fondements juridiques du contenu illégal (illegal_content_legal_ground)

Ceci est requis si DECISION_GROUND_ILLEGAL_CONTENT était la decision_ground.
Il s'agit des fondements juridiques sur lesquels on s'appuie.

Limité à 500 caractères.

### Explication du contenu illégal (illegal_content_explanation)

Ceci est requis si DECISION_GROUND_ILLEGAL_CONTENT était la decision_ground.
Il s'agit d'un texte qui explique pourquoi le contenu était illégal.

Limité à 2000 caractères.

### Fondements du contenu incompatible (incompatible_content_ground)

Ceci est requis si DECISION_GROUND_INCOMPATIBLE_CONTENT était la decision_ground.
Il s'agit de la référence à des fondements contractuels.

Limité à 500 caractères.

### Explication du contenu incompatible (incompatible_content_explanation)

Ceci est requis si DECISION_GROUND_INCOMPATIBLE_CONTENT était la decision_ground.
Il s'agit d'un texte qui explique pourquoi le contenu est considéré comme incompatible sur ce fondement.

Limité à 2000 caractères.

### Contenu incompatible illégal (incompatible_content_illegal)

Ceci est un attribut facultatif et peut être sous la forme "Oui" ou "Non".
C'est une possibilité d'indiquer que le contenu a été considéré non seulement comme incompatible mais aussi illégal.

### Type de contenu (content_type)

C'est un attribut obligatoire, et il nous indique quel type de contenu est visé par la déclaration de raison.

La valeur fournie doit être un tableau avec au moins l'un des éléments suivants :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::CONTENT_TYPES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Autre type de contenu (content_type_other)

Ceci est requis si CONTENT_TYPE_OTHER était le content_type.
Il s'agit d'un type de contenu qui ne fait pas partie de la liste des types de contenu fournie.

Limité à 500 caractères.

### Catégorie (category)

C'est un attribut obligatoire, et il nous indique à quelle catégorie appartient la déclaration.

La valeur fournie doit être l'une des suivantes :


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::STATEMENT_CATEGORIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Catégories supplémentaires (category_addition)

C'est un attribut facultatif, et il nous indique à quelles catégories supplémentaires appartient la déclaration.

La valeur fournie doit être un tableau avec l'une des options suivantes :


<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::STATEMENT_CATEGORIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Spécification de catégorie (category_specification)

C'est un attribut facultatif, et il nous indique à quels mots-clés supplémentaires la déclaration appartient.

La valeur fournie doit être un tableau avec l'une des options suivantes :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::KEYWORDS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Autre mot-clé (category_specification_other)

Ce champ peut être fourni si KEYWORD_OTHER fait partie de la category_specification.

Limité à 500 caractères.

### Portée territoriale (territorial_scope)

C'est un attribut requis qui définit la portée territoriale de la restriction. Chaque valeur doit être le code ISO à 2 lettres
pour le pays et les pays doivent être des pays (UE/EEE).

La valeur fournie doit être un tableau.

Les valeurs autorisées sont :

@php echo implode(', ', \App\Services\EuropeanCountriesService::EUROPEAN_COUNTRY_CODES); @endphp

Pour l’utilisation de l’Union européenne (UE):

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_UNION_COUNTRY_CODES) . '"]'; @endphp

Pour l’Espace économique européen (EEE), utilisez:

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES) . '"]'; @endphp

### Langue du contenu (content_language)

Il s'agit de la langue dans laquelle le contenu était rédigé.

Cet attribut est facultatif.

La valeur doit cependant être l'un des codes ISO 639-1 à deux lettres en majuscules [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

Ex,

@php echo '"' . implode('", "', \App\Services\EuropeanLanguagesService::EUROPEAN_LANGUAGE_CODES) . '"'; @endphp

### Date du contenu (content_date)

Il s'agit d'un champ de date requis qui indique la date de téléchargement ou de publication du contenu. La date doit suivre ce format :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à 2000-01-01.

### Date d'application (application_date)

Il s'agit de la date à partir de laquelle cette décision prend effet. La date doit être sous la forme :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à 2020-01-01.

### Date de fin de la restriction du compte (end_date_account_restriction)

Il s'agit de la date à laquelle la décision concernant le compte prend fin. Laissez vide pour une durée indéterminée. La date doit être sous la forme :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à la date d'application.

### Date de fin de la restriction monétaire (end_date_monetary_restriction)

Il s'agit de la date à laquelle la décision monétaire prend fin. Laissez vide pour une durée indéterminée. La date doit être sous la forme :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à la date d'application.

### Date de fin de la restriction du service (end_date_service_restriction)

Il s'agit de la date à laquelle la décision concernant la fourniture du service prend fin. Laissez vide pour une durée indéterminée. La date doit être sous la forme :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à la date d'application.

### Date de fin de la restriction de la visibilité (end_date_visibility_restriction)

Il s'agit de la date à laquelle la décision concernant la visibilité prend fin. Laissez vide pour une durée indéterminée. La date doit être sous la forme :

```AAAA-MM-JJ```

Le jour et le mois ont des zéros en tête.

La date doit être postérieure ou égale à la date d'application.

### Source d'information (source_type)

Il s'agit d'un champ obligatoire qui nous indique les faits et circonstances sur lesquels la décision repose.

La valeur fournie doit être l'une des suivantes :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::SOURCE_TYPES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Identité de la source (source_identity)

Il s'agit d'un champ facultatif pour décrire la source ou le notifier si nécessaire. Ne sera pas pris en compte si le 'source_type' est défini sur 'SOURCE_VOLONTAIRE'

Limité à 500 caractères.

### Détection automatisée (automated_detection)

Il s'agit d'un attribut requis et il doit être sous la forme "Oui" ou "Non".
Cela nous indique que la décision a été prise en respect des moyens détectés automatiquement.

### Décision automatisée (automated_decision)

Il s'agit d'un attribut requis et il doit être l'une des options suivantes :

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::AUTOMATED_DECISIONS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>


### Identifiant unique de la plateforme (PUID)

Il s'agit d'une chaîne qui identifie de manière unique cette déclaration au sein de la plateforme. Cet attribut est requis et il doit être unique au sein de votre plateforme.

Limité à 500 caractères et doit contenir uniquement des caractères alphanumériques (a-z, A-Z, 0-9), des tirets "-" et des traits de soulignement "_" uniquement. Aucun espace, saut de ligne ou tout autre caractère spécial n'est accepté.


## PUID existant

Il existe un point de terminaison qui vous permettra de vérifier si une valeur PUID est déjà utilisée.

Pour vérifier si une valeur PUID existe déjà dans une déclaration de raison en utilisant l'API, vous devrez effectuer une requête ```GET``` vers cet endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statement.existing-puid', ['puid' => '&lt;PUID&gt;'])}}
</pre>

Où ```<PUID>``` est le PUID que vous souhaitez vérifier s'il existe déjà dans la base de données.

Pour cette requête, vous devrez fournir une autorisation, une acceptation et un type de contenu
en-têtes de la requête :

<pre>
    Authorization: Bearer VOTRE_JETON
    Accept: application/json
    Content-Type: application/json
</pre>

La réponse sera soit ```404 Not Found``` ou ```302 Found```.

Lorsqu'aucune déclaration n'est trouvée, le corps contiendra le message.

```javascript
{
    "message": "statement of reason not found"
}
```

Lorsqu'une instruction est trouvée, l'instruction existante sera renvoyée dans le corps.

```javascript
{
    "uuid": "4b449e79-a41f-4934-aa9a-9c442899951e",
    "decision_visibility": [
        "DECISION_VISIBILITY_CONTENT_REMOVED"
    ],
    "decision_visibility_other": null,
    ...
```

## Erreurs

Lorsqu'un appel à l'API a été effectué ET qu'il y a eu une erreur dans l'appel, vous pouvez vous attendre à ce qui suit :

- Vous ne recevrez PAS de code de statut HTTP ```201 Created```.
- La déclaration de raison n'a PAS été créée.
- Vous recevez en retour un payload qui contient plus d'informations.

Par exemple,

Vous avez effectué un appel API avec un payload JSON vide ou un payload JSON invalide.

```javascript
{}
```

Le code de statut HTTP renvoyé sera ```422 Unproccessable Content```.

Le corps du payload sera un objet JSON contenant plus d'informations et les erreurs de l'appel à l'API.


```javascript
{
    "message": "The decision visibility field is required when none of decision monetary / decision provision / decision account are present. (and 13 more errors)",
    "errors": {
        "decision_visibility": [
            "The decision visibility field is required when none of decision monetary / decision provision / decision account are present."
        ],
        "decision_monetary": [
            "The decision monetary field is required when none of decision visibility / decision provision / decision account are present."
        ],
        "decision_provision": [
            "The decision provision field is required when none of decision visibility / decision monetary / decision account are present."
        ],
        "decision_account": [
            "The decision account field is required when none of decision visibility / decision monetary / decision provision are present."
        ],
        "decision_ground": [
            "The decision ground field is required."
        ],
        "content_type": [
            "The content type field is required."
        ],
        "category": [
            "The category field is required."
        ],
        "application_date": [
            "The application date field is required."
        ],
        "decision_facts": [
            "The decision facts field is required."
        ],
        "source_type": [
            "The source type field is required."
        ],
        "automated_detection": [
            "The automated detection field is required."
        ],
        "automated_decision": [
            "The automated decision field is required."
        ],
        "puid": [
            "The puid field is required."
        ]
    }
}
```

Les messages d'erreur pour les champs individuels varient en fonction de ce qui a été envoyé.

Tels que les suivants:

Si vous avez envoyé
```
{
    ...
    "automated_decision":"maybe"
    ...
}
```

"maybe" n'est pas une valeur valide pour automated_decision. (seulement "Yes" ou "No")

```javascript
{
    "message": "The selected automated decision is invalid.",
    "errors": {
        "automated_decision": [
            "The selected automated decision is invalid."
        ]
    }
}
```

### Erreurs lors de la création de plusieurs déclarations de raison

Lorsque vous appelez l'endpoint multiple, vous rencontrerez les mêmes erreurs que pour l'endpoint unique.
Cependant, les erreurs seront indexées sur la déclaration de raison que vous essayez de créer.

ex,
```javascript
{
    "errors": {
        "statement_0": {
            "decision_monetary": [
                "The selected decision monetary is invalid."
            ],
                "decision_ground": [
                "The selected decision ground is invalid."
            ],
                "automated_detection": [
                "The automated detection field is required."
            ]
        },
        "statement_2": {
            "decision_provision": [
                "The selected decision provision is invalid."
            ]
        }
    }
}
```

Cela signifie que les champs décision monétaire, fondement de la décision et détection automatisée étaient invalides dans la déclaration de raison à la position 0 dans le tableau.
Cela signifie que la décision de fourniture est invalide dans la déclaration de raison à la position 2 dans le tableau.

Dans ce cas, **AUCUNE** des déclarations n'a été créée, la demande doit être corrigée et renvoyée.

### Erreur de jeton

Une autre erreur courante qui peut se produire lors de l'appel de l'API est que le jeton d'autorisation n'est pas valide.

Cela se traduira par un code de statut HTTP de ```401 Unauthorized```

Le jeton d'autorisation de l'API doit être vérifié doublement ou un nouveau jeton d'autorisation de l'API doit être
généré. Consultez à nouveau la section ci-dessus : [Votre jeton d'API](#votre-jeton-dapi)

En plus des erreurs ```422``` et ```401``` courantes, n'importe quel des statuts HTTP 4XX standards peut être
rencontré. Les statuts 4XX indiquent généralement qu'il y a un problème avec votre demande. Veuillez essayer de
dépanner et résoudre le problème.

Lorsqu'il y a une erreur de 5XX, nous sommes immédiatement informés et il n'est pas nécessaire
de signaler le problème.

### Erreur PUID

Lorsque vous essayez de créer une déclaration pour votre plateforme et qu'il existe une déclaration avec le même PUID, la
la réponse sera toujours ```422 Unprocessable Content``` et l'erreur retournée contiendra l'existant
la déclaration. Cela ressemblera à ce qui suit :


```javascript
{
  "message": "The identifier given is not unique within this platform.",
  "errors": {
    "puid": [
      "The identifier given is not unique within this platform."
    ]
  },
  "existing": {
    "uuid": "6bf8beb0-765c-4e79-8cb1-dc93fc7478bb",
    "decision_visibility": [
      ...
    ],
    ...
    "permalink": "... /statement/6bf8beb0-765c-4e79-8cb1-dc93fc7478bb",
    "self": "... /api/v1/statement/6bf8beb0-765c-4e79-8cb1-dc93fc7478bb"
  }
}
```

## Code source

Le code source de cette application peut être consulté ici :

[Code source de la base de données de transparence du DSA - GitHub](https://github.com/digital-services-act/transparency-database)

En utilisant le code du dépôt, vous pouvez même configurer et exécuter une zone de test de développement en local.

Dans l'environnement GitHub, vous êtes également invité à donner des commentaires, des demandes de tirage (pull requests) et des avis concernant le code source.
