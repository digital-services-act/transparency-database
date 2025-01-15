#!/bin/bash
 
start_date="2023-09-25"
end_date=`date "+%Y-%m-%d"`
 
current_date="$start_date"
 
while [[ "$current_date" != "$end_date" ]]; do
    echo "downloading date: $current_date"

    base_json="aggregates-$current_date.json"
    base_csv="aggregates-$current_date.csv"
    json_url="https://dsa-sor-data-dumps.s3.eu-central-1.amazonaws.com/$base_json"
    csv_url="https://dsa-sor-data-dumps.s3.eu-central-1.amazonaws.com/$base_csv"

    if [ ! -f $base_json ]; then
     curl -s -O $json_url
    fi
    if [ ! -f $base_csv ]; then
      curl -s -O $csv_url
    fi
 
    # Increment current date by 1 day
    current_date=$(date -j -v +1d -f "%Y-%m-%d" "$current_date" "+%Y-%m-%d")
done
