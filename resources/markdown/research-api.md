## Overview

This documentation describes the DSA Transparency Database Research API. Its endpoints are designed to enable
programmatic access to and queries of statements of reasons (SORs) for academic and policy research into platforms’
content moderation practices.

By providing specialized access to search and analyse data within the statement_index of the DSA Transparency Database
in OpenSearch, the Research API supports a wide range of technically advanced research and investigative applications.
In enabling programmatic analysis, the DSA Transparency Database Research API complements the other analytical tools of
the DSA Transparency Database, namely its public [dashboard](/dashboard) for quick exploration and visualisation of the
data and the [dsa-tdb](https://code.europa.eu/dsa/transparency-database/dsa-tdb) analytical package enabling advanced
analysis of individually downloaded statements of reasons.

## Purpose and Scope

The DSA Transparency Database Research API empowers interested stakeholders with the relevant technical knowledge to
retrieve specific subsets of data within the OpenSearch statement_index of the DSA Transparency Database and to perform
complex queries based on their research interests. As such, it lends itself in particular to facilitate longitudinal and
cross-platform studies, i.e. to the systematic investigation of trends and patterns in the data.

In line with the DSA Transparency Database [data retention policy](/page/data-retention-policy), the statement_index
only contains statements of reasons submitted by platforms within the last 6 months. Older statements of reasons are not
available through the Research API endpoints. The DSA Transparency Database Research API endpoints are specifically
designed for programmatic statistical and pattern analysis, NOT for bulk data collection. You can find an overview of
other tools to analyse the data in the DSA Transparency Database [here](/explore-data/overview).

## How to Get Access

1.&nbsp;Create an EU Login Account. Please find the instructions to create an EU Login
Account [here](https://trusted-digital-identity.europa.eu/eu-login-help/external-self-registered-account-faq/how-do-i-create-my-eu-login-account_en).

2.&nbsp;Visit the DSA Transparency Database Page by
clicking [here](https://transparency.dsa.ec.europa.eu/profile/start).

3.&nbsp;Contact the DSA Helpdesk at [CNECT-DSA-HELPDESK@ec.europa.eu](mailto:CNECT-DSA-HELPDESK@ec.europa.eu) with your
EU Login details and express your interest in obtaining an authentication token for the Research API. The DSA Helpdesk
will process your request and update your account with the appropriate permissions.

4.&nbsp;Log into the DSA Transparency Database website with your EU Login Account and test your access with basic
queries

## Use Conditions & Limitations

1.&nbsp;By receiving your authentication token, you agree to use it responsibly & within the limitations specified in
this documentation.

2.&nbsp;You must keep your authentication token confidential and not share it with any third party. You are solely and
entirely responsible for all uses of your authentication token.

3.&nbsp;Limits are placed on the number of API requests you can make using your authentication token. You agree to, and
will not attempt to circumvent, such limitations. Exceeding these limits will lead to your authentication token being
temporarily blocked from making further requests.

4.&nbsp;The maximum response size of an API request is 5MB.

5.&nbsp;The maximum execution time of an API request is 30 seconds.

6.&nbsp;The maximum result size is 1000 rows per query and there is no pagination support.

7.&nbsp;In line with the DSA Transparency Database [data retention policy](/page/data-retention-policy), the
statement_index only contains statements of reasons submitted by platforms within the last 6 months. As such, older
statements are not available through these API endpoints.

8.&nbsp;All endpoints are read-only. No modifications to the statement_index data are possible through these endpoints.

9.&nbsp;The Research endpoints are NOT intended for downloading large volumes of individual statements of reasons.
The [data download](/explore-data/download) section of the website enables bulk data download.

## Available Endpoints

<table class="ecl-table">
  <thead>
    <tr>
      <th>Endpoint</th>
      <th>Method</th>
      <th>Description</th>
      <th>Use Case</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/search</code></td>
      <td>POST</td>
      <td>Complex search using OpenSearch DSL</td>
      <td>Detailed filtering and complex queries</td>
    </tr>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/sql</code></td>
      <td>POST</td>
      <td>SQL-like queries for analysis</td>
      <td>Statistical analysis and aggregations</td>
    </tr>
<tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/count</code></td>
      <td>POST</td>
      <td>Count documents matching query</td>
      <td>Quick statistics and volume analysis</td>
    </tr>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/query</code></td>
      <td>POST</td>
      <td>Search using OpenSearch DQL</td>
      <td>Domain-specific querying</td>
    </tr>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/{date}[/{fields}]</code></td>
      <td>GET</td>
      <td>Aggregated statistics by date</td>
      <td>Trend analysis and patterns</td>
    </tr>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/labels</code></td>
      <td>GET</td>
      <td>Available label definitions</td>
      <td>Understanding classification values</td>
    </tr>
    <tr>
      <td><code>https://transparency.dsa.ec.europa.eu/api/v1/research/platforms</code></td>
      <td>GET</td>
      <td>Platform information</td>
      <td>Platform metadata and identifiers</td>
    </tr>
  </tbody>
</table>

## Authentication

All endpoints require authentication using a Bearer token. See [How to get access](#how-to-get-access) for the process
of obtaining an authentication token. All requests must use HTTPS.

**Header Format:**

```http
Authorization: Bearer <your-token>
```

**Base URL:**
All Research API endpoints are accessible under the base URL:

```http
https://transparency.dsa.ec.europa.eu/api/v1/research
```

For detailed information on how to construct OpenSearch DSL queries, refer to
the [OpenSearch Query DSL Documentation](https://opensearch.org/docs/latest/query-dsl/).

## Statement Index Schema

The statement_index contains the following fields that can be used in your queries:

<table class="ecl-table">
  <thead>
    <tr>
      <th scope="col">Field</th>
      <th scope="col">Type</th>
      <th scope="col">Description</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>account_type</td><td>keyword</td><td>Type of account</td></tr>
    <tr><td>application_date</td><td>date</td><td>Date of application of a moderation decision</td></tr>
    <tr><td>automated_decision</td><td>keyword</td><td>Automated decision indicator</td></tr>
    <tr><td>automated_detection</td><td>boolean</td><td>Whether detection was automated</td></tr>
    <tr><td>category</td><td>keyword</td><td>Statement category</td></tr>
    <tr><td>category_addition</td><td>text</td><td>Additional category information</td></tr>
    <tr><td>category_specification</td><td>text</td><td>Category specification details</td></tr>
    <tr><td>content_date</td><td>date</td><td>Date of the content</td></tr>
    <tr><td>content_language</td><td>keyword</td><td>Language of the content</td></tr>
    <tr><td>content_type</td><td>text</td><td>Type of content</td></tr>
    <tr><td>content_type_other</td><td>text</td><td>Other content type details</td></tr>
    <tr><td>content_type_single</td><td>keyword</td><td>Single content type identifier</td></tr>
    <tr><td>created_at</td><td>date</td><td>Creation timestamp</td></tr>
    <tr><td>decision_account</td><td>keyword</td><td>Account decision</td></tr>
    <tr><td>decision_facts</td><td>text</td><td>Decision facts</td></tr>
    <tr><td>decision_ground</td><td>keyword</td><td>Ground for decision</td></tr>
    <tr><td>decision_monetary</td><td>keyword</td><td>Monetary decision</td></tr>
    <tr><td>decision_monetary_other</td><td>text</td><td>Other monetary decision details</td></tr>
    <tr><td>decision_provision</td><td>keyword</td><td>Decision provision</td></tr>
    <tr><td>decision_visibility</td><td>text</td><td>Visibility decision</td></tr>
    <tr><td>decision_visibility_other</td><td>text</td><td>Other visibility decision details</td></tr>
    <tr><td>decision_visibility_single</td><td>keyword</td><td>Single visibility decision</td></tr>
    <tr><td>id</td><td>long</td><td>Unique identifier</td></tr>
    <tr><td>illegal_content_explanation</td><td>text</td><td>Explanation of illegal content</td></tr>
    <tr><td>illegal_content_legal_ground</td><td>text</td><td>Legal ground for illegal content</td></tr>
    <tr><td>incompatible_content_explanation</td><td>text</td><td>Explanation of incompatible content</td></tr>
    <tr><td>incompatible_content_ground</td><td>text</td><td>Ground for incompatible content</td></tr>
    <tr><td>method</td><td>keyword</td><td>Method used</td></tr>
    <tr><td>platform_id</td><td>long</td><td>Platform identifier</td></tr>
    <tr><td>platform_name</td><td>text</td><td>Name of the platform</td></tr>
    <tr><td>platform_uuid</td><td>text</td><td>Platform UUID</td></tr>
    <tr><td>platform_vlop</td><td>boolean</td><td>Platform VLOP status</td></tr>
    <tr><td>puid</td><td>text</td><td>PUID identifier</td></tr>
    <tr><td>received_date</td><td>date</td><td>Date received</td></tr>
    <tr><td>source_identity</td><td>text</td><td>Identity of the source</td></tr>
    <tr><td>source_type</td><td>keyword</td><td>Type of source</td></tr>
    <tr><td>territorial_scope</td><td>text</td><td>Territorial scope</td></tr>
    <tr><td>url</td><td>text</td><td>URL reference</td></tr>
    <tr><td>uuid</td><td>text</td><td>UUID identifier</td></tr>
  </tbody>
</table>

#### Notes:

- Fields of type keyword can be used for exact matches and/or as keys for aggregations (as in an SQL group-by).
- Fields of type text are indexed and can be queried with text search (for instance, filtering with an SQL LIKE - statement)
- Fields of type date accept ISO 8601 format
- Fields of type boolean accept true/false values
- Fields of type long are numeric identifiers

## Detailed Endpoint Documentation

## SEARCH

This endpoint enables complex search using OpenSearch DSL. For detailed information on how to construct OpenSearch DSL
queries, refer to the [OpenSearch Query DSL Documentation](https://opensearch.org/docs/latest/query-dsl/).

### Endpoint Name

**POST** `https://transparency.dsa.ec.europa.eu/api/v1/research/search`

### Endpoint-Specific Limitations

- Results are limited to **1000 rows** per query.
- Total hits are tracked accurately (`track_total_hits` is enabled).
- For bulk downloads, please use the [data download](/explore-data/download) section of the website.

### Example Use Cases

#### Analysis of Scams & Fraud Moderation Patterns

- Tracks scams & fraud moderation across a six-month period.
- Compares platform responses to scams & fraud.
- Identifies temporal patterns in scams & fraud content moderation.

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "match": {
                        "category": "STATEMENT_CATEGORY_SCAMS_AND_FRAUD"
                    }
                }
            ],
            "filter": [
                {
                    "range": {
                        "received_date": {
                            "gte": "2024-01-01",
                            "lte": "2024-06-30"
                        }
                    }
                }
            ]
        }
    }
}
```

#### Regional Content Moderation Analysis

- Compares content moderation approaches across EU member states.
- Analyzes regional variations in moderation decisions based on illegality.
- Exposes cross-border differences.

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "terms": {
                        "territorial_scope": [
                            "DE",
                            "FR",
                            "IT"
                        ]
                    }
                }
            ],
            "filter": [
                {
                    "term": {
                        "decision_ground": "DECISION_GROUND_ILLEGAL_CONTENT"
                    }
                }
            ]
        }
    }
}
```

#### Analysis of the Use of Automated Means in Content Moderation

- Evaluates the use of automated means in content moderation.

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "term": {
                        "automated_detection": true
                    }
                }
            ],
            "should": [
                {
                    "term": {
                        "decision_ground": "DECISION_GROUND_ILLEGAL_CONTENT"
                    }
                },
                {
                    "term": {
                        "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT"
                    }
                }
            ],
            "minimum_should_match": 1
        }
    }
}
```

### Example Request Body & Response

### Request Body

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "term": {
                        "platform_id": 22
                    }
                },
                {
                    "term": {
                        "category": "STATEMENT_CATEGORY_ANIMAL_WELFARE"
                    }
                }
            ],
            "filter": [
                {
                    "range": {
                        "received_date": {
                            "gte": "2024-01-01",
                            "lte": "2024-06-30"
                        }
                    }
                }
            ]
        }
    }
}
```

### Response

```json
{
    "status": "success",
    "data": {
        "took": 476,
        "timed_out": false,
        "num_reduce_phases": 2,
        "_shards": {
            "total": 640,
            "successful": 640,
            "skipped": 0,
            "failed": 0
        },
        "hits": {
            "total": {
                "value": 177303,
                "relation": "eq"
            },
            "max_score": 31.229053,
            "hits": [
                {
                    "_index": "statement_product_640_2",
                    "_id": "26271619559",
                    "_score": 31.229053,
                    "_source": {
                        "id": 26271619559,
                        "platform_name": "Example Platform",
                        "category": "STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH",
                        "decision_ground": "DECISION_GROUND_ILLEGAL_CONTENT",
                        "content_type": [
                            "CONTENT_TYPE_TEXT"
                        ],
                        "territorial_scope": [
                            "AT",
                            "BE",
                            "DE"
                        ]
                    }
                }
            ]
        }
    }
}
```

### Response Fields

- **took**: Time in milliseconds for OpenSearch to execute the search.
- **timed_out**: Whether the search timed out.
- **num_reduce_phases**: Number of reduce phases in the search.
- **_shards**: Information about the shards searched.
    - **total**: Total number of shards.
    - **successful**: Number of shards that responded successfully.
    - **skipped**: Number of shards skipped.
    - **failed**: Number of shards that failed.
- **hits**: Search results.
    - **total.value**: Total number of matching documents.
    - **total.relation**: Relationship of the count ("eq" means exact).
    - **max_score**: Highest relevance score among results.
    - **hits**: Array of matching documents.
        - **_index**: Index name.
        - **_id**: Document ID.
        - **_score**: Document's relevance score.
        - **_source**: Original document data.

### Notes

- The request body should be a valid OpenSearch DSL query.
- The search is performed on the `statement_index`.
- Results are paginated by default (size: 10).
- A maximum size limit may apply to protect server resources.

---

## SQL

This endpoint enables SQL-like queries using OpenSearch SQL functionality. For detailed guidance, refer to
the [OpenSearch SQL Documentation](https://opensearch.org/docs/latest/sql/).

### Endpoint Name

**POST** `https://transparency.dsa.ec.europa.eu/api/v1/research/sql`

### Endpoint-Specific Limitations

- OpenSearch SQL is a subset of standard SQL with specific limitations.
- Queries can ONLY be executed against the `statement_index`.
- Any `LIMIT/OFFSET` in queries will be automatically replaced with `LIMIT 1000 OFFSET 0`.
- No support for complex window functions.
- Limited support for subqueries.
- No support for `HAVING` clause.
- Limited `JOIN` support (no other indices to join with).
- Limited function availability.
- No support for `CTEs` (Common Table Expressions).
- No support for `UNION` operations.

### Query Constraints

- `FROM` clause must always be `FROM statement_index`.
- Results are always limited to **1000 rows**.
- No pagination support (`OFFSET` is always `0`).

For larger result sets:

- Use aggregations where possible
- Break queries into smaller time ranges
- Consider using the Search or Aggregates endpoints instead

For more complex analysis needs that exceed OpenSearch SQL capabilities (such as window functions or complex
aggregations), consider

- Using the Search API with DSL queries
- Using the Aggregates endpoint
- Performing additional analysis in your preferred statistical software
- Breaking down complex queries into simpler parts

### Example use cases

1.&nbsp;Comparative platform analysis:

- Analyses the distribution of moderation decisions across platforms
- Performs basic cross-platforms comparisons

```json
SELECT
platform_name,
decision_ground,
COUNT(*) as decision_count
FROM statement_index
WHERE received_date >= '2024-01-01'
AND received_date <= '2024-06-30'
GROUP BY platform_name, decision_ground
ORDER BY platform_name, decision_count DESC;
```

2.&nbsp;Automated vs Manual Decision Analysis:

- Reveals automation patterns across content types
- Displays platform-specific use of automation in content moderation
- Performs comparisons across different content types

```json
SELECT
content_type_single,
automated_decision,
platform_name,
COUNT(*) as decision_count
FROM statement_index
WHERE received_date = '2024-06-26'
GROUP BY content_type_single, automated_decision, platform_name
ORDER BY decision_count DESC;
```

3.&nbsp;Basic Temporal Analysis:

- Displays daily trends in content moderation
- Tracks changes in platforms’ behaviour over time
- Reveals category-specific patterns

```json
SELECT
received_date,
platform_name,
category,
COUNT(*) as statement_count,
AVG(CASE WHEN automated_detection = true THEN 1.0 ELSE 0.0 END) as automation_rate
FROM statement_index
WHERE received_date >= '2024-01-01'
AND received_date <= '2024-06-30'
GROUP BY received_date, platform_name, category
ORDER BY received_date, platform_name;
```

### Example Request Body & Response

### Request Body:

```json
{
    "query": "SELECT * FROM statement_index WHERE platform_name = 'example'",
    "format": "json"
    // Optional: returns results in JSON format
}
```

### Response Formats:

#### Default format:

```json
{
    "schema": [
        {
            "name": "decision_account",
            "type": "keyword"
        },
        {
            "name": "account_type",
            "type": "keyword"
        },
        {
            "name": "decision_provision",
            "type": "keyword"
        }
        // ... additional fields
    ],
    "datarows": [
        [
            null,
            null,
            "DECISION_PROVISION_PARTIAL_SUSPENSION",
            "2024-07-07 01:31:21",
            "AUTOMATED_DECISION_PARTIALLY",
            "CONTENT_TYPE_PRODUCT",
            "a1d9afd8-2fc9-4e29-827b-80578117f200",
            null,
            null,
            "CONTENT_TYPE_PRODUCT",
            null,
            "The affected listings do not meet the requirements of the Electronical and Electronic Equipment Act (ElektroG – the German WEEE law).",
            "bfea46d8e2fe89727d3d351f8818f0f3cd076741f43ecab9d47beda4872fb0f8d1c43b40c943be8e2b33b6b6be998824de1e1f29d9daf27dc4ce22de7db942ac",
            "1ebd7d59-6f2f-48b0-92ea-2fe3265b52f5",
            "2024-07-07 00:00:00",
            null,
            null,
            "Amazon Store",
            21177743029,
            "API_MULTI",
            "DECISION_VISIBILITY_CONTENT_DISABLED",
            true,
            null,
            "SOURCE_VOLUNTARY",
            null,
            null,
            null,
            null,
            "DECISION_VISIBILITY_CONTENT_DISABLED",
            "Violation of the Electronical and Electronic Equipment Act (ElektroG – the German WEEE law).",
            "DE",
            null,
            28
        ]
        // ... additional rows
    ]
}
```

#### JSON format (when "format": "json" is specified):

```json
{
    "took": 170,
    "timed_out": false,
    "num_reduce_phases": 2,
    "_shards": {
        "total": 640,
        "successful": 640,
        "skipped": 0,
        "failed": 0
    },
    "hits": {
        "total": {
            "value": 5467899,
            "relation": "gte"
        },
        "max_score": 1,
        "hits": [
            {
                "_index": "statement_product_640_2",
                "_id": "21177743029",
                "_score": 1,
                "_source": {
                    "id": 21177743029,
                    "decision_visibility": [
                        "DECISION_VISIBILITY_CONTENT_DISABLED"
                    ],
                    "decision_visibility_single": "DECISION_VISIBILITY_CONTENT_DISABLED",
                    "category_specification": [],
                    "decision_visibility_other": null,
                    "decision_monetary": null,
                    "decision_monetary_other": null
                }
            }
            // ... additional results
        ]
    }
}
```

## COUNT

This endpoint returns the count of documents matching the provided OpenSearch DSL query.

### Endpoint Name

**POST** `https://transparency.dsa.ec.europa.eu/api/v1/research/count`

### Example use cases

1.&nbsp;Volume analysis of moderated content:

- Measures illegal content prevalence
- Tracks moderation volume over time

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "term": {
                        "decision_ground": "DECISION_GROUND_ILLEGAL_CONTENT"
                    }
                }
            ],
            "filter": [
                {
                    "range": {
                        "received_date": {
                            "gte": "2024-01-01",
                            "lte": "2024-06-30"
                        }
                    }
                }
            ]
        }
    }
}
```

2.&nbsp;Analysis of Content Type distribution:

- Shows distribution of content types across VLOPs
- Reveals platform-specific content patterns

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "exists": {
                        "field": "content_type_single"
                    }
                }
            ],
            "filter": [
                {
                    "term": {
                        "platform_vlop": true
                    }
                }
            ]
        }
    }
}
```

### Response Format

```json
{
    "status": "success",
    "data": {
        "count": 9630559766,
        "_shards": {
            "total": 640,
            "successful": 640,
            "skipped": 0,
            "failed": 0
        }
    }
}
```

## QUERY

Performs searches using OpenSearch
DQL ([Dashboards Query Language](https://opensearch.org/docs/latest/dashboards/dql/)). DQL is a simple text-based query
language that uses field:value syntax to filter data. This query language resembles the Apache Lucene Query language.

### Endpoint Name

**POST** `https://transparency.dsa.ec.europa.eu/api/v1/research/query`

### Request Format:

```json
{
    "query": "decision_visibility_single: DECISION_VISIBILITY_CONTENT_REMOVED and automated_detection: true"
}
```

### Example use cases

1.&nbsp;Content Removal Pattern Analysis:

Helps researchers:

- Find automatically detected content removals
- Analyze removal patterns

```json
decision_visibility_single: DECISION_VISIBILITY_CONTENT_REMOVED and automated_detection: true
```

2.&nbsp;Regional Analysis:

For analyzing:

- Content moderation in specific regions
- Illegal content patterns by territory

```json
territorial_scope: DE and decision_ground: DECISION_GROUND_ILLEGAL_CONTENT
```

Important Notes:

- Use field:value syntax (e.g., `field: value`)
- Boolean operators: `and`, `or`, `not`
- Use quotes for phrases: `field: "exact phrase"`
- Supports wildcards (*) in both field names and values
- Supports ranges with >, <, >=, <= operators for numeric and date fields

## AGGREGATES

This endpoint returns aggregated statistics for statements for the specified date. Aggregates in OpenSearch are a
powerful way to group and analyze data based on specific fields, similar to SQL's GROUP BY functionality. They help in
summarizing and analyzing large datasets by grouping similar data together, calculating metrics, and discovering
patterns in the data.

### Endpoint Name

**GET** `https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/{date}[/{fields}]`

### Parameters

- date: Required.
    - Format: YYYY-MM-DD (e.g., 2024-02-26)
- fields: Optional.
    - List of specific fields to aggregate on, separated by double underscores (e.g., decision_ground__platform_id)
    - The keyword all to aggregate on all available fields

### Available Aggregation Fields:

- automated_decision
- automated_detection- category
- content_type_single
- decision_account- decision_ground- decision_monetary
- decision_provision
- decision_visibility_single- platform_id- received_date- source_type

### Using Specific Fields vs 'all'

### Specific Fields Approach:

- More focused and performant
- Useful when you have specific questions to answer
- Example:

```json
GET https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/2024-06-26/decision_ground__platform_id
```

- Shows how many decisions of each type were made by each platform
- Provides focused view for comparing platform moderation approaches

### Using 'all':

- Calculates aggregations for all available fields
- More resource-intensive but provides comprehensive overview
- Useful for exploratory analysis and pattern discovery
- Example:

```json
GET https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/2024-06-26/all
```

- Shows all possible breakdowns (by platform, decision type, content type, etc.)
- Helps discover unexpected patterns
- More comprehensive but potentially slower

### Example use cases

1.&nbsp;Default (total for date):

```json
GET https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/2024-06-26
```

Response:

```json
{
    "aggregates": [
        {
            "received_date": "2024-06-26",
            "permutation": "received_date:2024-06-26",
            "total": 55225872
        }
    ],
    "total": 55225872,
    "total_aggregates": 1,
    "date": "2024-06-26",
    "attributes": {
        "1": "received_date"
    },
    "key": "osa__2024-06-26__received_date",
    "cache": "hit",
    "duration": 0.0019,
    "size": 269
}
```

2.&nbsp;Aggregation by platform:

```json
GET https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/2024-06-26/platform_id
```

Response:

```json
{
    "aggregates": [
        {
            "platform_id": 22,
            "permutation": "platform_id:22",
            "platform_name": "X",
            "total": 2783
        },
        {
            "platform_id": 23,
            "permutation": "platform_id:23",
            "platform_name": "App Store",
            "total": 660
        }
    ],
    "total": 3443,
    "total_aggregates": 2,
    "date": "2024-06-26",
    "attributes": {
        "1": "platform_id"
    }
    // ... additional metadata
}
```

3.&nbsp;Aggregation on all fields:

```json
GET https://transparency.dsa.ec.europa.eu/api/v1/research/aggregates/2024-06-26/all
```

Response:

```json
{
    "aggregates": [
        {
            "automated_decision": true,
            "permutation": "automated_decision:true",
            "total": 25000
        },
        {
            "platform_id": 22,
            "permutation": "platform_id:22",
            "platform_name": "X",
            "total": 2783
        }
        // ... results for all other fields
    ],
    "total": 55225872,
    "total_aggregates": 12,
    "date": "2024-06-26",
    "attributes": {
        "1": "automated_decision",
        "2": "automated_detection",
        "3": "category"
        // ... all available fields
    }
    // ... additional metadata
}
```

### Performance Considerations:

- Specific attributes queries are more efficient as they compute fewer aggregations
- 'All' queries might be slower and more resource-intensive
- Combining multiple fields (e.g., decision_ground__platform_id) allows for more complex analysis while maintaining
  reasonable performance

## LABELS

This endpoint returns all available labels and their corresponding keystone values that can be used for filtering in
queries. Keystone values are machine-friendly strings that represent specific categories or attributes in the system.
For example, when filtering statements by category in your queries, you would use the keystone value
STATEMENT_CATEGORY_ANIMAL_WELFARE rather than the human-readable label "Animal Welfare".

### Endpoint Name

**GET** `https://transparency.dsa.ec.europa.eu/api/v1/research/labels`

Response:

```json
{
    "decision_visibilities": {
        "DECISION_VISIBILITY_CONTENT_REMOVED": "Removal of content",
        "DECISION_VISIBILITY_CONTENT_DISABLED": "Disabling access to content",
        "DECISION_VISIBILITY_CONTENT_DEMOTED": "Demotion of content",
        "DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED": "Age restricted content",
        "DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED": "Restricting interaction with content",
        "DECISION_VISIBILITY_CONTENT_LABELLED": "Labelled content",
        "DECISION_VISIBILITY_OTHER": "Other restriction (please specify)"
    },
    "decision_monetaries": {
        "DECISION_MONETARY_SUSPENSION": "Suspension of monetary payments",
        "DECISION_MONETARY_TERMINATION": "Termination of monetary payments",
        "DECISION_MONETARY_OTHER": "Other restriction (please specify)"
    },
    "decision_provisions": {
        "DECISION_PROVISION_PARTIAL_SUSPENSION": "Partial suspension of the provision of the service",
        "DECISION_PROVISION_TOTAL_SUSPENSION": "Total suspension of the provision of the service",
        "DECISION_PROVISION_PARTIAL_TERMINATION": "Partial termination of the provision of the service",
        "DECISION_PROVISION_TOTAL_TERMINATION": "Total termination of the provision of the service"
    }
    // ... additional label categories
}
```

## PLATFORMS

This endpoint returns a list of all platforms in the system along with their unique identifier and VLOP (Very Large
Online Platform) status. The platform IDs can be used for filtering in queries when you need to target specific
platforms.

### Endpoint Name

**GET** `https://transparency.dsa.ec.europa.eu/api/v1/research/platforms`

## Support and Query Responsibility

These API endpoints are provided as-is and act as direct interfaces to the OpenSearch index. Please note:

- Queries are passed directly to the OpenSearch engine
- Users are responsible for constructing valid queries according to OpenSearch documentation
- Support is not provided for query syntax or optimization
- Before reporting issues:
    - Verify your query syntax is correct
    - Check for common errors (date formats, field names)
    - Test simpler versions of complex queries
    - Test your query against the OpenSearch documentation
    - Ensure the error is not due to malformed queries
    - Document reproducible test cases
- Contact support ([CNECT-DSA-HELPDESK@ec.europa.eu](mailto:CNECT-DSA-HELPDESK@ec.europa.eu)) only if you have strong
  evidence of a technical issue with one of the endpoints itself

For more detailed query guidance, refer to:

- [OpenSearch Query DSL Documentation](https://opensearch.org/docs/latest/query-dsl/)
- [OpenSearch SQL Documentation](https://opensearch.org/docs/latest/search-plugins/sql/sql/index/)- [OpenSearch DQL Documentation](https://opensearch.org/docs/latest/dashboards/dql/)

## Error Handling

### Common error responses and their implications:

#### 401: Unauthorized

- Invalid or missing Bearer token
- Check authentication credentials

#### 403: Forbidden

- Insufficient permissions
- Check authentication credentials

#### 404: Not Found

- Invalid endpoint or parameter
- Verify API endpoint URLs
- Check parameter formatting

#### 500: Internal Server Error

- Server-side processing issue
- Document error context
- Consider simplifying complex queries

#### 504: Gateway Timeout

- Query exceeded 30-second timeout
- Optimize query performance
- Break down into smaller time ranges

#### 413: Payload Too Large

- Response exceeds 5MB limit
- Reduce query scope
- Use pagination or date partitioning

