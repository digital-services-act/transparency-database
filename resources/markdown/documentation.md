# Information for Developers

Would you like to use the services of DORIS and the Drive-In?

This application here is merely a public-facing user interface to text
analysis services from the Drive-In.

You, as a developer, can also utilize these services in your own
applications. The Drive-In services are open to use to those with
a valid developer token key. We offer both synchronous and asynchronous
versions of the API, with different requirements and for different
use cases.

The Drive-In is running in AWS, therefore **it should not be
used with "sensitive non-classified" (SNC) documents.**

## Requesting a Developer Token Key

To request a key, contact the DORIS team at [CNECT-ECDORIS@ec.europa.eu](mailto:CNECT-ECDORIS@ec.europa.eu).

### Example Key

This is what a basic developer key will look like:

Ex: ```FJ5ydo14yA4Y1BeasVGhgaYG4KzLkJPd8dqYOzoY``` (not a real key)

## Quick Start / HowTo

You will be making **POST** https requests to endpoints located here:

```https://analytics-api.cnect.eu/```

For the asynchronous service, the websocket URL is:

```wss://analytics-ws.cnect.eu/```

The asynchronous pipeline is slightly more complicated and is described
later in this document.

To use the Drive-In services you need to use a server side, non public facing
development stack such as PHP, JSP, ASP, C#, Python, and others.

**Do Not** make requests to the DriveIn services publicly using Javascript
in the user's browser.

### Your Token

The requests will need to have your token encoded as a header in the HTTPS request.
Additionally you need to specify that you are accepting json responses as well.

in **PHP** this will look like this:

```php
    $headers = [];
    $headers['x-api-key'] = 'YOUR TOKEN KEY';
    $headers['Accept'] = 'application/json';
```

in **Python** this will look like this:
```python
    x_api_key = "YOUR TOKEN KEY"
    headers = {'Accept': 'application/json', 'x-api-key': x_api_key}
```

### Basic Payload Body

In addition to setting this header in your requests, the body of the outgoing request
will then need to have a JSON encoded payload (body). This payload will take on
the form of the following template for all services aside from the textreuse one, which is intrinsically different:

```json
{
  "service": "service_name",
  "text": "The text that you are going to run an analysis on",
  "parameters": {
    // additional parameters depending on the service
  }
}
```

### The Response

You will receive a response status of `200 OK` when everything has gone according
to plan. The response body will then contain the results (as JSON) of what was
requested.

Anything other than a response of `200 OK` means that something in the requests
was not correct. This can be a range of issues from an improper token to invalid
parameters. The response body will contain an error message alerting you to the
issue at hand.

E.g.:

```json
{"error":true,"message":"This service doesn't exist"}  
```

## Example Code

It is highly recommended that you put your requests into a "Try-Catch" scheme and log
any errors that you are receiving as a response.

In **PHP** this is how we are making the requests:

```php
    // Actual php code in the DriveIn UI that you use here.
    
    $payload = new stdClass();
    $payload->service = $service; // keywords, sentiment, ...
    $payload->text = $text;

    if ($parameters) {
        $payload->parameters = $parameters;
    }

    try {
        
        $response = Http::withHeaders($headers)
            ->withBody(json_encode($payload), 'application/json')
            ->post('https://analytics-api.cnect.eu/');
        
        // Oh didily doh, we did not get a 200    
        if ($response->status() !== Response::HTTP_OK) {
            Log::error('Drivein Response: ' . $response->body());
            return null;
        }
        
        return $response->json();  // <-- This is the results
    
    } catch (Exception $e) {
        Log::error('Drivein Request: ' . $e->getMessage());
    }
    
    // Should NOT get here.
    return null;
```

In **Python**:

```python

    # Request with try-except
    import json, requests

    headers = {'Accept': 'application/json', 'x-api-key': x_api_key}
    URL = "https://analytics-api.cnect.eu/"

    payload = json.dumps(
        {
    "service":"entities",
    "text": "Ursula von der Leyen was born in Brussels"
        }
    )
    try:
        response = requests.post(URL, headers = headers, data = payload)
        entities = json.loads(response.content.decode())
    except requests.exceptions.HTTPError as e:
        print(f"## ERROR, {e}")

```

## Limitations

The length of text that you may submit when doing a synchronous request is
200 000 (200k) characters. If you are looking to do larger requests than that
you may want to skip further in this documentation to sections concerning
asynchronous requests.

[comment]: <> (### English)

[comment]: <> (The ```text``` parameter for all requests &#40;except for the language detection&#41;)

[comment]: <> (should be translated to English first for best results. DriveIn provides a)

[comment]: <> (service end point to first translate before submitting for analysis.)

[comment]: <> (If you are unsure what language your text is in before making a call then)

[comment]: <> (make a translate request first to surely translate the text to English. Then)

[comment]: <> (make your actual request.)

[comment]: <> (Example,)

[comment]: <> (Outgoing translation request payload.)

[comment]: <> (```json)

[comment]: <> ({)

[comment]: <> (  "service":"translate",)

[comment]: <> (  "text":"Ma\u00eetre Corbeau, sur un arbre perch\u00e9")

[comment]: <> (}  )

[comment]: <> (```)

[comment]: <> (Incoming response payload. Use the "translation" value for your actual request.)

[comment]: <> (```json)

[comment]: <> ({)

[comment]: <> (  "source":"FR",)

[comment]: <> (  "target":"EN",)

[comment]: <> (  "translation":"Master Corbeau, on a perched tree")

[comment]: <> (})

[comment]: <> (```)



## Available Synchronous Services

The Drive-In services that are available to you for use are the following:

* Named-entity recognition (NER), <code>entities</code>: persons, locations, organisations
* Keyword extraction, <code>keywords</code>
* Summarisation, <code>summary</code>
* Sentiment analysis, <code>sentiment</code>
* Language detection, <code>language</code>
* Text similarity, <code>textreuse</code>
* Topic extraction, <code>topic-extraction</code>

You will need to send in the machine name of the service as the service parameter
of your request body payload.

## Examples for Synchronous Services

### Named-entity recognition (NER) Request

Request:
```json
{
    "service": "entities",
    "text": "The Schuman Declaration, or Schuman Plan, was a proposal to place French and West German production of coal and steel under a single authority that later became the European Coal and Steel Community, made by the French foreign minister, Robert Schuman on the 9th of May 1950 (today's Europe Day of the EU), the day after the fifth anniversary of the end of World War II."
}
```

Response:
```json
[
  {"text": "EU", "type": "ORGANIZATION", "score": 1}, 
  {"text": "Robert Schuman", "type": "PERSON", "score": 1}, 
  {"text": "Schuman Plan", "type": "PERSON", "score": 1}, 
  {"text": "The Schuman Declaration", "type": "PERSON", "score": 1}, 
  {"text": "the European Coal and Steel Community", "type": "ORGANIZATION", "score": 1}
]
```  
Do note that in the response above, the <code>score</code> value is solely
present for legacy purposes and can be safely ignored.

Documentation: something about the service

### Keyword extraction

Request:
```json
{
    "service": "keywords",
    "text": "The Schuman Declaration, or Schuman Plan, was a proposal to place French and West German production of coal and steel under a single authority that later became the European Coal and Steel Community, made by the French foreign minister, Robert Schuman on the 9th of May 1950 (today's Europe Day of the EU), the day after the fifth anniversary of the end of World War II."
}
```

Response:
```json
[
    {
        "frequency": 3,
        "lemmatized": "schuman",
        "originals": ["Schuman"],
        "score": 36.60736956556569
    }
]
```
Documentation:

The endpoint will return with a list of keywords. Each keyword is a dictionary containing four keys:
- a ```frequency``` (how often that keyword is present in the text), as integer
- <code>lemmatized</code>: the dictionary form of a keyword. Since we compute the score of a keyword across all its surface forms, we return the dictionary form as well. Example: "explode" is one of such dictionary forms. Returned as a string.
- <code>originals</code>: all surface forms found for the keyword. Example: "explode", "exploded", "exploding", "explodes" etc are all surface forms of "explode". We obviously only return the surface forms present in the text. Returned as a list of string.
- <code>score</code>: a float representing the weight of a keyword. The weight is computed against a current-day reference corpus of English.


### Summarisation

Request:

```json
{
    "service": "summary",
    "text": "Ursula Gertrud von der Leyen; née Albrecht; born 8 October 1958 is a German politician and physician who has been President of the European Commission since 1 December 2019. Prior to her current position, she served in the Cabinet of Germany from 2005 to 2019, ...", # truncated for readability's sake
    "parameters":
        {
            "max_sentences": 5
        }
}
```

Response:

```json
[
    "Ursula Gertrud von der Leyen; née Albrecht; born 8 October 1958 is a German politician and physician who has been President of the European Commission since 1 December 2019.",
    "In the late 1990s, she became involved in local politics in the Hanover region, and she served as a cabinet minister in the state government of Lower Saxony from 2003 to 2005.",
    "When she left office she was the only minister to have served continuously in Angela Merkel's cabinet since Merkel became chancellor.",
    "Von der Leyen is a member of the centre-right Christian Democratic Union (CDU) and its EU counterpart, the European People's Party (EPP).",
    "On 2 July 2019, von der Leyen was proposed by the European Council as the candidate for President of the European Commission."
]
```

Documentation:

Longer texts are summarised into <code>parameters["max_sentences"]</code> sentences, which defaults to 5. This is *extractive* summarisation, and not *abstractive* summarisation. This means that the output will always be present in the original text and not machine-generated, thus avoiding important data quality issues (such as misrepresentation of the original data).


### Sentiment analysis

Request:

```json
{
    "service": "sentiment",
    "text": "The Schuman Declaration, or Schuman Plan, was a proposal to place French and West German production of coal and steel under a single authority that later became the European Coal and Steel Community, made by the French foreign minister, Robert Schuman on the 9th of May 1950 (today's Europe Day of the EU), the day after the fifth anniversary of the end of World War II."
}
```
Response:
```json
{
    "negative_words": [{"score": -2.9, "word": "war"}],
    "positive_words": [{"score": 0.3, "word": "authority"}],
    "score": 1,
    "sentiment_distribution": {"negative": 1, "neutral": 0, "positive": 0},
    "type": "negative"
}
```
Documentation:

This is a lexicon-based approached. The service will:
- list every positive and negative words in a text, as well as its amplitude
- return a sentiment distribution based on how many sentences are positive, negative, or neutral in the given text.

In the example above the one-sentence paragraph is said to be negative (<code>war</code> is more negative than <code>authority</code> is positive), and thus the sentiment distribution tends towards negative (one negative sentence, zero positive sentence, zero neutral sentence).

### Language detection

Request:
```json
{
    "service": "language",
    "text": "The Schuman Declaration, or Schuman Plan, was a proposal to place French and West German production of coal and steel under a single authority that later became the European Coal and Steel Community, made by the French foreign minister, Robert Schuman on the 9th of May 1950 (today's Europe Day of the EU), the day after the fifth anniversary of the end of World War II."
}
```

Response:
```json
{
    "code": "en", 
    "name": "English"
}
```
Documentation:

Text in, language out. We prioritise EU languages, although a fallback is present for non-EU languages.

### Topic extraction

Request:
```json
{
    "service": "topic-extraction",
    "text": "In order to take account of the specific characteristics of the cereals and paddy rice sectors, the power to adopt certain acts should be delegated to the Commission in respect of laying down the quality criteria as regards buying-in and sales of those products."
}
```

Response:
```json
{
    "eurovoc_ids": ["3732", "5360"],
    "labels": ["rice", "cereals"]
}
```
Documentation:

The endpoint returns a dictionary with two ordered lists. The first one contains [EuroVoc](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/eurovoc) IDs as strings whilst the second contains the corresponding English labels. In the example above, EuroVoc ID 3732 refers to "rice": [link on EuroVoc](https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://eurovoc.europa.eu/3732).


### Text similarity

Due to the nature of the tool, the setup is inherently different. Here instead of a text, we need to send a list of texts and a (dis)similarity metric. We recommend sticking to the default <code>jaccard</code> [[link](https://en.wikipedia.org/wiki/Jaccard_index)], although we also provide <code>cosine</code> [[link](https://en.wikipedia.org/wiki/Cosine_similarity)]. The tool vectorises the input sentences, applies the distance metric, and clusters them using [DBScan](https://en.wikipedia.org/wiki/DBSCAN). The tool is mainly used in survey analysis, to detect campaigns/large-scale copypastes.

Request:
```json
{
    "service": "textreuse",
    "text": {
        "distance": "jaccard",
        "answers": [
            "I like bicycles -- this is a much safer way of going around in cities, unlike cars", 
            "Jean-Claude Juncker, born 9 December 1954, is a Luxembourgish politician who served as the 21st Prime Minister of Luxembourg from 1995 to 2013, as Minister for Finances from 1989 to 2009, and as President of the European Commission from 2014 to 2019.", 
            "The computational study of chocolate-covered strawberries has taken off in the past few years and we are seeing increasing interest in the food, from both computational sciences and linguistics.", 
            "The computational study of lexical semantic change (LSC) has taken off in the past few years and we are seeing increasing interest in the field, from both computational sciences and linguistics.", 
            "The computational study of lexical semantic change (LSC) has taken off in the past few years and we are seeing increasing interest in the field, from both computational sciences and linguistics.", 
            "The computational study of lexical semantic change (LSC) has taken off in the past few years and we are seeing increasing interest in the field, from both computational sciences and linguistics.", 
            "The computational study of lexical semantic change (LSC) has taken off in the past few years and we are seeing increasing interest in the field, from both computational sciences and linguistics."
                ]
            }
}
```

Response:

```json
[
    {
        "0": -1, 
        "1": -1, 
        "2": 0, 
        "3": 0, 
        "4": 0, 
        "5": 0, 
        "6": 0
    }
]
```

Documentation:

We return a dictionary with stringified text indices as keys and cluster number (as integer) as values. If the integer is <code>-1</code>, this particular text does not belong in a cluster (and is thus unique). In the example above, texts "0" and "1" do not belong to any cluster, whilst texts "2", "3", "4", "5" and "6" all belong in the same cluster, despite text "2" being different.


## Asynchronous Requests

When needing to make requests with texts larger than 200 000 characters or simply
when you want to analyse the text that is within a supported document file
an asynchronous request and processing will need to be made.

Asynchronous requests make use of the web socket protocol and can be
"longer running". Thus, it is more ideal to handle large requests this way rather
than in a traditional transactional HTTP request and wait scheme.

One caveat to this approach is that a good deal more code will have to be
considered to properly request, wait, and handle the messages traveling
back and forth along the wss channel.

For the asynchronous service, the websocket URL is:

```wss://analytics-ws.cnect.eu/```

The asynchronous service is capable handling the following file types/extensions:

* pdf
* txt
* docx
* doc
* xls
* xlsx
* ppt
* pptx

Do note that you will need to know the MIME type of your file type in order to use the asynchronous services. A list of MIME types for various Office formats is available on [learn.microsoft.com](https://learn.microsoft.com/en-us/previous-versions/office/office-2007-resource-kit/ee309278%28v=office.12%29).


If you have a large piece of text, feel free to put that into a TXT file and then
use this way of processing.

### Services for Asynchronous Requests

* Named-entity recognition (NER), <code>entities</code>: persons, locations, organisations
* Keyword extraction, <code>keywords</code>
* Summarisation, <code>summary</code>
* Sentiment analysis, <code>sentiment</code>
* Language detection, <code>language</code>

### Getting Started

The first step in analysing the text in a document file is to first make a ```POST```
request to obtain a ```process_id``` and ```signed_url```.

Instead of specifying text and the desired service like we did in the synchronous
requests previously we are going to specify all of the desired services we want
to run on this one document.

Make a ```POST``` request with the same headers (token, accepts) you made for
synchronous requests to:

```https://analytics-api.cnect.eu/```

Specify in the body/payload a services array. This array indicates which
services that you would like to run on the document file.

E.g.:

```json
{
  "services": [
    {
      "name": "language"
    },
    {
      "name": "summary"
    },
    {
      "name": "sentiment"
    }
  ]
}
```

The response will be status ```200 OK``` and look something like the following:

```json
{
  "process_id": "6eb5eac3-1dfe-4ced-b628-5bbb4c30e0fc",
  "signed_url": "https://driveinapi-s3-work-files-prod.s3.eu-central-1.amazo....."
}
```

These pieces of information need to be stored and used in later parts of the
processing.

### Upload Your Document

After getting a ```process_id``` and ```signed_url``` you then need to upload
your actual document file to the ```signed_url``` that you received in part 1.

This will need to be done in a ```PUT``` request.

The headers for this request need to specify the content MIME type of the file.
In this example we are uploading a PDF file with the MIME type <code>application/pdf</code>.
These are not the same headers you used previously.

E.g.:

```php
    $uploadHeaders = [];
    $uploadHeaders['content-type'] = 'application/pdf';
```

In **PHP** we do the ```PUT``` request with the request body being the actual file
content.

```php
    $uploadResponse = Http::withHeaders($uploadHeaders)
        ->withBody($fileContent, 'application/pdf')  // $fileContent is file content.
        ->put($signed_url);                          // <-- signed url from step 1.
```

in **Python**, this can be done the following way:

```python
# you need to install websocket-client
import requests, websocket, json

instructions = {
    "services" : [{"name" : "language"}, {"name" : "sentiment"}], # list here services
    }
print("Getting signed url...")
headers = {'Accept': 'application/json', 'x-api-key': API_KEY}
response_json = requests.post(URL, json = instructions, headers = headers).json()

signed_url = response_json["signed_url"] 
process_id = response_json["process_id"] # Keep this saved for later

print("Signed URL:", signed_url)
print("Process ID:", process_id)

filepath = "document.pdf"
content_type = "application/pdf"

with open(filepath, "rb") as f:
  content = f.read()
response = requests.put(signed_url, data=content, headers={"content-type" : content_type})
assert response.status_code == 200
```

Ensure that you receive a response with status ```200 OK```.

### Start the Processing

At this point we now connect to the websocket and begin message interactions
to handle the processing of the document.

When executing this part of the processing you will need to be able to run a
potentially "long running" process. Running this in the users browser using
javascript or other front end development stacks is not going to be ideal.

You want the messages to come to your server infrastructure and be saved as
they are received and without interruption or breakage.

We have been using cron jobs and queue workers very successfully to connect and
wait for results.

Failure to keep reading the wss socket till completion will result in possible
dropped results. You are not able to resubscribe later to a wss channel and
get old messages.

First connect to the wss url:

In **PHP**, we use the [Ratchet Pawl](https://github.com/ratchetphp/Pawl) library:

```php
$loop = Loop::get();
// WSS Connection
connect('wss://analytics-ws.cnect.eu/', [], [], $loop)
    ->then(function($conn) use($loop) {
```
Send a message to start the processing.

```php
    $payload = new stdClass();
    $payload->action = 'process';
    $payload->data = $process_id;  // process_id saved from part 1.

    $conn->send(json_encode($payload));
```

Then in your WSS loop you are going to be waiting for a message of the type:

```results```

Here is an example of what that message will look like.

```json
{
  "process": "6eb5eac3-1dfe-4ced-b628-5bbb4c30e0fc",
  "message": {
    "keywords": "[{\"lemmatized\": \"strum\", \"origi....",
    "entities": "[{\"text\": \"Cynthia Lin\", \"type\": \"PERS....",
    "sentiment": "{\"score\": 0.999, \"type\": \"positive\", \"positive_w...",
    "summary": "[\"HOW TO TUNE AN UKE: String order: G C E A (top to bo...",
    "language": "{\"code\": \"en\", \"name\": \"English\"}"
  },
  "type": "results"
}
```

The ```message``` is then containing the result of the services that were run for this
processing request, keywords, summary, language and so on.

This is how we capture that message in **PHP**

```php
if ($response['type'] === 'results') {

    // Save these results somewhere
    $results = json_encode($response['message']);

    $conn->close();
    $loop->stop();
    return true;
}
```

in **Python**, this is somewhat simpler thanks to the [websocket-client](https://github.com/websocket-client/websocket-client/) library:

```python
def on_open(ws):
    print("Connection opened...")
    payload = {
        "action" : "process",
        "data" : process_id,
    }

    ws.send(json.dumps(payload))
    print("Process request sent...")

def on_message(ws, message):
    response = json.loads(message)
    if response["type"] == "results":
        print("Result:", response["message"])
        ws.close()
    elif response["type"].startswith("error"):
        print("Error:", response["message"])
        ws.close()
    else:
        print("Message:", response["message"])

WS_URL = "wss://analytics-ws.cnect.eu"
ws = websocket.WebSocketApp(WS_URL, on_open=on_open, on_message=on_message)
ws.run_forever()
```

### Full Example in PHP

Here we give you the full example code of how we implement the asynchronous
processing in **PHP**. By having this example you can see a fully working code
snippet. Feel free to re-use and adjust this code to your application.

This is the actual working code in this DriveIn UI application.

You will need to adjust this code to match up with your own application. In ours
we are using an entity ```$document``` to store results and information about our
processing.

```php
public function processDocument(Document $document): bool
{
    $loop = Loop::get();
    // WSS Connection
    connect($this->wss_url, [], [], $loop)->then(function($conn) use($document, $loop) {
        $conn->on('message', function (Message $response) use ($conn, $document, $loop) {

            $contents = (string)$response;
            // Make the response a raw string
            // Then json decode to an object 
            // Helps avoid null message issues and bogus messages
            $response = json_decode($contents, true);

            // Yes we did in fact have a JSON string and it's now an object
            if ($response) {
                
                // Encode the message part to a flat string.
                $results = json_encode($response['message']);
                
                if ($response['type'] === 'results') {
                    
                    Log::notice('Drivein WSS Results: ' . $contents);
                    $document->results = $results;
                    
                    $document->save();
                    $conn->close();
                    $loop->stop();
                    return true; // Happy Path it all worked
                    
                } elseif (str_starts_with("error", $response['type'])) {
                    
                    Log::notice('Drivein WSS Error: ' . $contents);
                    $document->error = $results;
                    
                    $document->save();
                    $conn->close();
                    $loop->stop();
                    return false; // Unhappy path something did not work
                    
                } else {
                
                    // We are getting some kind of other unknown message
                    Log::notice('Drivein WSS Unhandled: ' . $contents);
                    return false;
                    
                }
                
            } else {
            
                // No idea what is going here but we got a message that 
                // wasn't JSON
                Log::notice('Drivein WSS Message was not JSON?: ' . $contents);
                return false;
            
            }

        });

        $conn->on('close', function ($code = null, $reason = null) use ($loop, $document) {
            Log::notice("WSS Connection closed ($code - $reason)");
            $loop->stop();
            if (!$document->results) {
                $document->error = "WSS finished without result!";
                $document->save();
            }
            return false;
        });

        $payload = new stdClass();
        $payload->action = 'process';
        $payload->data = $document->process_id;

        Log::notice('Drivein WSS Initiating the Process Id: ' . $document->process_id);
        $conn->send(json_encode($payload));

    }, function ($e) use($loop, $document) {
    
        Log::error("WSS could not connect: {$e->getMessage()}");
        $document->error = "WSS could not connect: {$e->getMessage()}";
        $document->save();
        $loop->stop();
        return false;
    
    });

    $loop->run();

    return true;
}
```

### Full Example in Python

Here we give you the full example code of how we implement the asynchronous
processing in **Python**. By having this example you can see a fully working code
snippet. Feel free to re-use and adjust this code to your application.

```python
import requests
import websocket
import json

API_KEY = "YOUR-API-KEY"
URL = "https://analytics-api.cnect.eu"
WS_URL = "wss://analytics-ws.cnect.eu"

## 1. Getting the signed URL
def get_signed_url(instructions):

    print("Getting signed url...")
    headers = {'Accept': 'application/json', 'x-api-key': API_KEY}

    response = requests.post(URL, json = instructions, headers = headers)
    response_json = response.json()
    return response_json["signed_url"], response_json["process_id"]


instructions = {
    "services" : [{"name" : "language"}, {"name" : "sentiment"}],
}
signed_url, process_id = get_signed_url(instructions)
print("Signed URL:", signed_url)
print("Process ID:", process_id)

## 2. Upload your file to S3
def upload_to_s3(filepath, signed_url, content_type):

    print("Uploading to s3...")
    with open(filepath, "rb") as f:
        content = f.read()
    response = requests.put(signed_url, data=content, headers={"content-type" : content_type})
    assert response.status_code == 200

filepath = "tests/integration/test_files/test.docx"
content_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
upload_to_s3(filepath, signed_url, content_type)

## 3. Send processing order to service through websocket and wait for results
def on_open(ws):
    print("Connection opened...")
    payload = {
        "action" : "process",
        "data" : process_id,
    }

    ws.send(json.dumps(payload))
    print("Process request sent...")

def on_message(ws, message):
    response = json.loads(message)
    if response["type"] == "results":
        print("Result:", response["message"])
        ws.close()
    elif response["type"].startswith("error"):
        print("Error:", response["message"])
        ws.close()
    else:
        print("Message:", response["message"])
        
ws = websocket.WebSocketApp(WS_URL, on_open=on_open, on_message=on_message)
ws.run_forever()
```