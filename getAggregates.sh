#!/bin/bash
 
start_date="2023-09-25"
end_date=`date "+%Y-%m-%d"`
 
current_date="$start_date"
 
while [[ "$current_date" != "$end_date" ]]; do
    echo "downloading date: $current_date"

    json_url="https://dsa-sor-data-dumps.s3.eu-central-1.amazonaws.com/aggregates-$current_date.json"
    csv_url="https://dsa-sor-data-dumps.s3.eu-central-1.amazonaws.com/aggregates-$current_date.csv"

    wget -q $json_url
    wget -q $csv_url
 
    # Increment current date by 1 day
    current_date=$(date -j -v +1d -f "%Y-%m-%d" "$current_date" "+%Y-%m-%d")
done
