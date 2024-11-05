#### [Announcement – DB] 2025 Update of the DSA Transparency Database schema in line with the Implementing Regulation on Transparency Reporting

<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 04/11/2024</p>


Today, the Commission <a href="https://digital-strategy.ec.europa.eu/en/library/implementing-regulation-laying-down-templates-concerning-transparency-reporting-obligations" class="ecl-link">published the Implementing Act on Transparency Reporting</a>, which standardizes the form, content and reporting periods of DSA transparency reports. To ensure consistency between the transparency tools of the DSA, the submission schema of the DSA Transparency Database will be updated to reflect the requirements laid down in the Implementing Regulation on Transparency Reporting. As of 1 July 2025, statements of reasons that are submitted to the DSA Transparency Database therefore will have to comply with the updated schema. To allow providers to prepare, the updated schema will be available for extensive testing in the sandbox environment of the DSA Transparency Database in Q2 of 2025.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>


#### [Announcement – DSA-TDB] New open-source package on code.europa.eu
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 29/10/2024</p>


The DSA Transparency Database team is pleased to announce the release of a new open-source package on [code.europa.eu](https://code.europa.eu/info/about), designed to facilitate the download and analysis of data in the Database. The application, called [dsa-tdb](https://code.europa.eu/dsa/transparency-database/dsa-tdb), is a Python package that aims to streamline the data analysis process and support future research.

dsa-tdb is available for use in various formats, including a [command line interface](https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/commands.html), an [interactive mode](https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/index.html), and a [Docker container](https://code.europa.eu/dsa/transparency-database/dsa-tdb/container_registry) serving a Jupyter notebook server out of the box. To access the package and start exploring the DSA Transparency Database data, visit the [dsa-tdb package homepage](https://code.europa.eu/dsa/transparency-database/dsa-tdb) and read the [online documentation](https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/index.html).

We hope this new tool will enhance the user experience of the DSA Transparency Database and support the research community in extracting valuable insights from the database.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>
#### [Announcement – Dashboard] New pages and filters for better separation between platforms
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 15/10/2024</p>

On 14th October 2024, the dashboard of the DSA Transparency Database was updated to include a clearer separation between the Statements of Reasons submitted by Very Large Online Platforms (VLOPs)  and the rest (non-VLOP) platforms. The changes are particularly notable in the first and second pages of the dashboard; unified information for all platforms is available in page 6 (‘all platforms’). A new taxonomy was also added in the ‘Platforms’ filter that allows users to select with a single click all the VLOPs or all the non-VLOPs.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Announcement – DB] Update to the submissions by Google Shopping
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 15/07/2024</p>

Starting from 28 June 2024, submissions to the DSA Transparency Database from Google Shopping no longer include automated notifications provided to merchants when their offers are not eligible for personalised ads targeting as such notifications seem to be outside the scope of the DSA Transparency Database.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Announcement – DB] Consistency check of the Transparency Database completed
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 18/06/2024</p>

On 18 June 2024, the DSA Transparency Database team completed the data consistency check. After the data cleaning, all the daily dumps files have been regenerated to reflect the cleaned content of the database. The dashboard and the descriptive statistics on the website’s homepage have also been updated accordingly. The DSA Transparency Database team will continue to monitor the consistency and quality of the data submitted to the database.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Announcement – DB] Removing duplicated entries from the Transparency Database
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Published 14/05/2024</p>

On Wednesday 22 May 2024, the DSA Transparency Database (TDB) team will perform a data consistency check on the database in order to remove Statement of Reasons (SoR) that may have been either repeatedly or faultily submitted to the database. This is why the number of statements of reasons reported both in the dashboard and the descriptive statistics on the homepage is expected to significantly decrease once the data cleaning procedure is completed.

A following announcement will be published to flag that: i) the data consistency check is completed, and, ii) that the daily dumps' files have been recreated to reflect the cleaned content of the TDB.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

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
