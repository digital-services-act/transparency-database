## DSA Transparency Database Research API Documentation

### Overview

This documentation describes the DSA Transparency Database Research API. Its endpoints are designed to enable programmatic access to and queries of statements of reasons (SORs) for academic and policy research into platforms' content moderation practices.

By providing specialized access to search and analyze data within the `statement_index` of the DSA Transparency Database in OpenSearch, the Research API supports a wide range of technically advanced research and investigative applications. It complements the public dashboard and the `dsa-tdb` analytical package for advanced analysis.

---

### Purpose and Scope

The Research API allows stakeholders with relevant technical expertise to retrieve specific subsets of data within the OpenSearch `statement_index` and perform complex queries. It is designed to support longitudinal and cross-platform studies of trends and patterns in content moderation decisions.

**Data Retention Policy:**
- The `statement_index` contains statements of reasons submitted by platforms within the last **6 months**.
- Older statements are **not available** via the Research API.
- API endpoints are **not** intended for bulk data collection.

For alternative data analysis tools, refer to the [DSA Transparency Database](#).

---

### How to Get Access

1. **Create an EU Login Account:** [Instructions Here](#).
2. **Visit the DSA Transparency Database Page.**
3. **Contact the DSA Helpdesk:**
    - Email: `CNECT-DSA-HELPDESK@ec.europa.eu`
    - Provide your EU Login details and request an authentication token.
4. **Log into the DSA Transparency Database** and test your access.

---

### Use Conditions & Limitations

By using the Research API, you agree to:
- Keep your authentication token **confidential**.
- **Not exceed** API request limits, or risk **temporary blocks**.

**Technical Limits:**
- **Max response size:** 5MB
- **Max execution time:** 30 seconds
- **Max results per query:** 1000 rows (no pagination support)
- **Read-only:** No modifications to the `statement_index`
- **No bulk downloads:** Use the data download section instead

---

### Available Endpoints

| Endpoint | Method | Description | Use Case |
|----------|--------|-------------|----------|
| `/api/v1/research/search` | `POST` | Complex search using OpenSearch DSL | Advanced filtering & queries |
| `/api/v1/research/count` | `POST` | Count matching documents | Quick statistics |
| `/api/v1/research/query` | `POST` | Search using OpenSearch DQL | Simple queries |
| `/api/v1/research/sql` | `POST` | SQL-like queries | Statistical aggregations |
| `/api/v1/research/aggregates/{date}[/{fields}]` | `GET` | Aggregated statistics | Trend analysis |
| `/api/v1/research/labels` | `GET` | Available label definitions | Understanding classification |
| `/api/v1/research/platforms` | `GET` | Platform metadata | Platform identification |

---

### Authentication

All endpoints require **Bearer token authentication**.

**Header Format:**
```plaintext
Authorization: Bearer <your-token>
```

**Base URL:**
```plaintext
/api/v1/research
```

Refer to the [OpenSearch Query DSL Documentation](#) for constructing queries.

---

### Statement Index Schema

The `statement_index` includes fields like:

| Field | Type | Description |
|-------|------|-------------|
| `account_type` | `keyword` | Type of account |
| `application_date` | `date` | Date of moderation decision |
| `automated_decision` | `keyword` | Automated decision indicator |
| `content_language` | `keyword` | Language of the content |
| `decision_ground` | `keyword` | Ground for decision |
| `platform_name` | `text` | Name of the platform |
| `received_date` | `date` | Date received |
| `territorial_scope` | `text` | Territorial scope |
| `url` | `text` | URL reference |

**Notes:**
- `keyword`: Exact matches & aggregations
- `text`: Full-text search
- `date`: ISO 8601 format
- `boolean`: `true` / `false`

---

### Example Queries

#### Search Example: Scams & Fraud Moderation
```json
{
    "query": {
        "bool": {
            "must": [{ "match": { "category": "STATEMENT_CATEGORY_SCAMS_AND_FRAUD" }}],
            "filter": [{ "range": { "received_date": { "gte": "2024-01-01", "lte": "2024-06-30" }}}]
        }
    }
}
```

#### SQL Query: Comparative Platform Analysis
```sql
SELECT platform_name, decision_ground, COUNT(*) as decision_count
FROM statement_index
WHERE received_date >= '2024-01-01' AND received_date <= '2024-06-30'
GROUP BY platform_name, decision_ground
ORDER BY platform_name, decision_count DESC;
```

#### Aggregation Example: Decisions Per Platform
```plaintext
GET /api/v1/research/aggregates/2024-06-26/platform_id
```

Response:
```json
{
    "aggregates": [
        {"platform_id": 22, "platform_name": "X", "total": 2783},
        {"platform_id": 23, "platform_name": "App Store", "total": 660}
    ]
}
```

---

### Error Handling

| Code | Meaning | Solution |
|------|---------|----------|
| `401` | Unauthorized | Check authentication credentials |
| `403` | Forbidden | Insufficient permissions |
| `404` | Not Found | Verify endpoint or parameters |
| `500` | Internal Server Error | Simplify query |
| `504` | Gateway Timeout | Optimize query performance |
| `413` | Payload Too Large | Reduce query scope |

For more details, refer to:
- [OpenSearch Query DSL Documentation](#)
- [OpenSearch SQL Documentation](#)
- [OpenSearch DQL Documentation](#)

---

For further assistance, contact `CNECT-DSA-HELPDESK@ec.europa.eu`.
