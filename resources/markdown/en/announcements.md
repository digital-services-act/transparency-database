#### [Announcement - API] Restoring the 422 error for duplicated platform uid (puid)
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 08/04/2024</p>
On 8th of April 2024, the European Commission restored the 422 – Unprocessable Content error. The API endpoint will return the error whenever a platform is submitting a Statement of Reasons (SoR) containing a platform unique identifier (puid) already found in the SoR previously submitted to the database by the same platform.

Please refer to the [Errors section](/page/api-documentation#errors) of the [API documentation](/page/api-documentation) for further details.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Change - API] Enforcing the platform_uid (puid) format
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 08/04/2024</p>

Starting from 18th of April 2024, the Transparency Database team will enforce the format of the Platform Unique Identifier (puid) to be a 500-characters maximum string containing alphanumeric characters (a-z, A-Z, 0-9), hyphens "-" and underscores "_" only. No spaces, new-line or any other special characters will be accepted.

For example, the puid “344ndbd_3338383-11aST" will be valid, whereas the puid “123.STATE sor/category” will not.

We refer to the [API documentation](/page/api-documentation#additional-explanation-for-statement-attributes) for additional details.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Change – Access to data] Improving the daily dumps file format
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 08/04/2024</p>

On 8th of April 2024, the Transparency Database (TDB) team is updating the structure of the daily dumps CSV files available in the [Data Download](/data-download) section. This change aims at improving the daily dump CSV creation to speed it up and make it more computationally efficient. This will enable to publish the CSV files in a quick and timely manner even with the current high daily volume of Statement of Reasons (SoRs) submitted to the TDB, which is expected to further increase in the coming months when small platforms will onboard.

The new structure will consist of one zip file, with multiple zip files within.  
Each inner zip file will contain 1 million records maximum broken up into CSV parts of 100 000.

For instance, the light version global dump for September 25th 2024 -named sor-global-2023-09-25-light.zip-, will contain several zip files named like sor-global-2023-09-25-light-00000.csv.zip. Each of the latter will contain several CSV chunks, with about 100’000 SoR in each, named sor-global-2023-09-25-light-00000-00000.csv.

The old-format files will be gradually replaced by the new format in the following days.

While the current implementation can easily handle the current daily submissions volume, the TDB team reserves the right to apply further changes to the file structure or to the creation pipeline, shall further improvement be needed to handle increasing daily submission rate.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Announcement – Access to data] Implementing a new data retention policy
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 08/04/2024</p>

Starting from 15th of April 2024, the Transparency Database (TDB) will follow the [data retention policy](/page/data-retention-policy) set up by the European Commission. In particular, each Statement of Reasons (SoR) will be searchable from the [Search Statement of Reasons](/statement) in the six months (180 days) following its insertion into the database. After this period, the SoR will be removed from the search index and will be available in the CSVs of the [daily dumps files](/data-download) and still contributing to the [Dashboard](/dashboard).

The [daily dumps files](/data-download) will be available for 18 months (540 days) after their creation. After this period, they will be archived in a cold storage.

Lastly, the [Dashboard](/dashboard) will contain the aggregated statistics for the last 5 years of data. 

<p class="ecl-u-type-paragraph" style="font-style: italic">
<img width="100%" src="{{asset('/static/images/dsa-retention-policy.png')}}">
</p>
<p class="ecl-u-type-paragraph" style="width:100%; text-align:center; font-style: italic !important; margin-top:-20px"><span style="font-size: smaller">The Data Retention Policy of the DSA Transparency Database.</span></p>

<p class="ecl-u-type-paragraph" style="margin-bottom:100px"></p>
