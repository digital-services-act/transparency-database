To streamline the analysis of <a class="ecl-link" href="{{ route('dayarchive.index') }}">data downloads</a> from the DSA Transparency Database, you can use the <x-ecl.external-link url="https://code.europa.eu/dsa/transparency-database/dsa-tdb" label="open-source dsa-tdb python package" />. The package allows to efficiently carry out a number of data pre- and post-processing tasks at scale thanks to its Spark backend. Specifically, the package allows you to:

-   Easily download the daily dumps, perform their checksum verification and convert them into data processing-ready csv or parquet files.
-   Filter and/or aggregate the statements of reasons across user-selected variables from the database schema, so to get bespoke datasets to create advanced visualisations or answer advanced research questions.
-   Easily develop ad-hoc dashboards and visualisations based on the aggregated data using the Apache Superset framework.

Depending on your technical level, you can access these functionalities 
-   via the high-level command line interface;
-   through a jupyter notebook, directly using the python module’s bindings;
-   through fully functional APIs, either programmatically or using an interactive web-based interface.

To access the package as well as its full technical documentation, you can visit the <x-ecl.external-link url="https://code.europa.eu/dsa/transparency-database/dsa-tdb" label="dsa-tdb"/> page on <x-ecl.external-link url="https://code.europa.eu" label="code.europa.eu"/>.