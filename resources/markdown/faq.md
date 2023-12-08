# DSA Transparency Database FAQ

_Frequently Asked Questions and Answers_

## General FAQ

<x-ecl.accordion label="What is the DSA Transparency Database?">
Article 17 of the Digital Services Act (DSA) requires all providers of hosting services to provide clear and
specific information, called statements of reasons, to users whenever they remove or otherwise restrict access to
their content. In line with Article 24(5) of the DSA, providers of online platforms, which are a type of hosting
service, are required to also send all their statements of reasons to the Commission’s DSA Transparency Database for
collection. The database is publicly accessible and machine-readable.
</x-ecl.accordion>

<x-ecl.accordion label="What is a hosting service? What is an online platform?">
Hosting services include a broad range of online intermediaries, for example cloud and webhosting services.
These services store information provided by, and at the request of, users. The DSA Transparency Database only
collects statements of reasons from online platforms, a subset of hosting services. Online platforms not only
store information provided by users but also disseminate it publicly, i.e. make it available to potentially all
users of an online platform.
</x-ecl.accordion>

<x-ecl.accordion label="What is a statement of reasons?">
A statement of reasons is the information, specified in Article 17 of the DSA, that providers of hosting services are
required to share with a user whenever they remove or otherwise restrict access to their content. Restrictions can be
imposed on the grounds that the content is illegal or incompatible with the terms and conditions of the provider.
Information contained in a statement of reasons includes amongst other things the type of restriction put in place, the
grounds relied upon and the facts and circumstances based on which the content moderation decision was taken. It is this
information that providers of online platforms are required to submit to the DSA Transparency Database. A statement of
reasons is an important tool to empower users to understand and potentially challenge content moderation decisions taken
by providers of hosting services.
</x-ecl.accordion>

<x-ecl.accordion label="Is there any part of a statement of reasons that is not published in the DSA Transparency
Database?">
Providers of online platforms are obliged to remove any personal data from the information they publish in the DSA
Transparency Database, in accordance with Article 24(5) of the DSA. In case personal data is included in any of the
statements of reasons, the Commission can be notified using the ‘Report an issue’ button.<br />
<br />
Redress options are also not included in the DSA Transparency Database as those are relevant only for the addressee of
the statement of reasons, and in any event would be identical in all cases: internal complaint mechanism under Article
20 of the DSA, out-of-court dispute settlement under Article 21 of the DSA, and judicial review under the relevant
national laws.
</x-ecl.accordion>

<x-ecl.accordion label="Why was the DSA Transparency Database created?">
The DSA Transparency Database was created, in line with Article 24(5) of the DSA, to enable more transparency and
scrutiny over the content moderation decisions taken by providers of online platforms, and to better monitor the spread
of illegal and harmful content online.
</x-ecl.accordion>

<x-ecl.accordion label="Who can use the DSA Transparency Database?">
The DSA Transparency Database is publicly accessible. Visitors of the <a href="{{route('home')}}">website</a>
can search, read, and download statements of reasons. You can find the “Search for statements of reasons”
page <a href="{{route('statement.index')}}">here</a>. 


</x-ecl.accordion>



<x-ecl.accordion label="What are other transparency initiatives under the DSA?">
The DSA Transparency Database is one of many tools that the DSA has put in place to enable more transparency online. You
can find out about other DSA transparency tools <a href="" target="_blank">here</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find more information about the DSA?">
The DSA is a comprehensive set of new rules that regulate the responsibilities of digital services. Find out more about
what this
means <a href="https://digital-strategy.ec.europa.eu/en/faqs/digital-services-act-questions-and-answers" target="_blank">
here</a>.
</x-ecl.accordion>

<x-ecl.accordion label="I would like to give feedback – how can I do that?">
Please use the <a href="{{ route('feedback.index') }}">feedback form</a>. To use the feedback form, you need to create
an EU Login account.
</x-ecl.accordion>

<h2 class="ecl-u-type-heading-2">Technical FAQ</h2>

<x-ecl.accordion label="I would like to extract a large number of statements of reasons from the DSA Transparency
Database. How do I do that?">
The <a href="{{ route('dayarchive.index') }}">Data Download</a> page of the DSA Transparency Database contains all of
its submissions organized into daily zip files
containing the submissions of either all online platforms or each individual online platform, to be selected from a
dropdown menu in the top right corner. The files are provided in two different versions: full and light. The former
contains all data fields of each statement of reasons (for the full database schema,
see <a href="{{ route('profile.page.show', ['api-documentation']) }}">here</a>), the latter does not
contain free text attributes with a character limit higher than 2000 characters (i.e. *illegal_content_explanation*,
*incompatible_content_explanation* or *decision_facts*). Light archive files also do not contain the *territorial_scope*
attribute.
</x-ecl.accordion>

<x-ecl.accordion label="I would like to sample data from the DSA Transparency Database. How do I do that?">
To obtain a sample of submissions to the DSA Transparency Database, you can use the csv file download link available
above the table displaying the results of a search for statements of reasons. By default, the latest 1000 results will
be available for download. To adapt the content of the sample, you can specify search parameters in
the <a href="{{ route('statement.search') }}">advanced search</a>
page. The first 1000 results from your <a href="{{ route('statement.search') }}">advanced search</a> will then be
available for csv file download.
</x-ecl.accordion>


<x-ecl.accordion label="I would like to get access to the content, for which a statement of reasons was created. How do
I do that?">
The DSA Transparency Database only records the content moderation decisions itself as well as the information
accompanying such decisions which is recorded in a statement of reasons, with the exception of personal data, which
providers of online platforms are required to remove before submission. The DSA Transparency Database does not contain
the content that was subject to moderation. <br />
<br />
For researchers interested in gaining access to the content underlying certain statements of reasons, the data access
mechanism specified in Article 40 of the DSA can provide a way to obtain such access in the future. Once the Digital
Service Coordinators will be established, data access requests can be submitted either to the Digital Service
Coordinator of a researcher’s member state or to the Digital Service Coordinator(s) where the provider of the online
platform(s) in question is established. The Commission is currently drafting a Delegated Act that will lay down
technical and procedural requirements of the Article 40 data access mechanism.
</x-ecl.accordion>

<h2 class="ecl-u-type-heading-2">Platforms FAQ</h2>

<x-ecl.accordion label="I am responsible for implementing Article 24(5) of the DSA as a provider of an online platform.
What steps do I have to go through?">
To set up your statement of reasons submissions under Article 24(5), please contact the Digital Service Coordinator of
your member state. This is the first step required to be onboarded as an online platform with obligations under Article
24(5) of the DSA. Once you are onboarded via your Digital Service Coordinator, you will gain access to a sandbox
environment to test your submissions to the DSA Transparency Database, which you can perform either via an API or a
webform, according to your volume and technical needs. Once the testing phase is completed, you will be able to move to
the production environment of the DSA Transparency Database and can start submitting your statement of reasons via an
API or a webform in production.
</x-ecl.accordion>

<x-ecl.accordion label="What are the technical options for sending statements of reasons to the DSA Transparency
Database?">
Statements of reasons can be submitted either via an API or a webform. For more information about the API, please
consider the <a href="{{ route('profile.page.show', ['api-documentation']) }}">API documentation</a>. The data schema of the web
form is the same as the data schema of the API. For more
information on the webform, please consider the <a href="{{ route('page.show', ['webform-documentation']) }}">webform
documentation</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find the data schema with all statement of reasons attributes used in the DSA
Transparency Database?">
All attributes, which are part of a statement of reasons submission to the DSA Transparency Database, are documented in
the <a href="{{ route('profile.page.show', ['api-documentation']) }}">API documentation</a>. The data schema of the web form is
the same as the data schema of the API. For more information
on the webform, please consider the <a href="{{ route('page.show', ['webform-documentation']) }}">webform
documentation</a>.
</x-ecl.accordion>

<x-ecl.accordion label="What are the API endpoint options for the DSA Transparency Database? Which one would you
recommend for sending statements of reasons at a very high volume?">
The DSA Transparency database has two API endpoints, one which allows to submit one statement of reasons per call and
one which allows to submit from 1 to 100 statements of reasons per call. For more information on the API endpoints,
please consider the <a href="{{ route('profile.page.show', ['api-documentation']) }}">API documentation</a>.<br />
<br />
For consistent high-volume submissions of multiple statements of reasons per minute, we recommend using the batch API
endpoint.
</x-ecl.accordion>

<x-ecl.accordion label="Where do I find information on error codes?">
For detailed information on the possible error code, please consider
the <a href="{{ route('page.show', ['api-documentation']) }}#errors">relevant section in the API documentation</a>.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find instructions on how to use the webform?">
For information and instructions on the webform, please consider the
<a href="{{ route('page.show', ['webform-documentation']) }}">webform documentation</a>.
</x-ecl.accordion>