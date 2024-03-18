DATA DOWNLOAD | _18 March 2024_

### CSV Export Structure

In our efforts to deliver CSV exports of the statements in a timely and efficient manner. We are moving to a
structure that will allow processing to be carried out in memory before committing the data to disk and file.

The current structure will become 1 large zip file, with multiple zip files within. 
Each inner zip file will contain 1 million records maximum broken up into CSV parts of 100 000.

We see at present that this will allow us to produce very large data sets quickly. If more 
improvement is required this structure may under go further modifications but for the foreseeable future
we are satisfied that we are able to accommodate the data being submitted as of today.

---

API | _18 March 2024_

### Reintroduction of PUID duplication error

We are planning to reintroduce the HTTP 422 Error back on the both single and multiple API statement
creation endpoints. As the transparency database has grown to include more than 5 Billion records the 
capability to detect duplicated PUIDs foreach platform became compromised and we were forced to abandon
this feature temporarily. We are happy to announce though that we are reintroducing this feature 
using a different methodology as of today. 

The 422 PUID duplication error and feature is still under evaluation and in all likeliness will be 
deprecated over the longer term as it was apparent that some platforms were over relying on the endpoint
error code to de-duplicate their own infrastructures and data.

---

DASHBOARD | _18 March 2024_

### Deduplication of Statements February 21st thru Present

After the reintroduction of the 422 API Error Response, the process of de-duplicating Statements of Reason 
that have been submitted by platforms with duplicated PUID fields will start.

The first phase will begin with a soft deletion and thus only the visual Dashboard and statistics will be 
affected. Soon there after, we will evaluate if additional passes need to be carried out to identify and 
remove even more duplicates. 

Lastly another determination will be made as to whether or not the full deletion of the 
duplicated Statements needs to be carried out as this will then have an impact of the CSV data downloads.