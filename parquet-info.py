#!/usr/bin/env python3
import os
import sys
import argparse
from typing import Optional
import pyarrow.parquet as pq
import pandas as pd
import humanize


def get_parquet_info(file_path: str, show_schema: bool = False, sample_rows: Optional[int] = None) -> None:
    """
    Print information about a parquet file including size, rows, columns, and optionally schema and sample data
    """
    # Get file size
    file_size = os.path.getsize(file_path)
    print(f"File: {os.path.abspath(file_path)}")
    print(f"Size: {humanize.naturalsize(file_size, binary=True)}")
    
    # Read parquet metadata
    parquet_file = pq.ParquetFile(file_path)
    num_row_groups = parquet_file.num_row_groups
    total_rows = parquet_file.metadata.num_rows
    
    print(f"\nRow Groups: {num_row_groups}")
    print(f"Total Rows: {total_rows:,}")
    
    # Get schema information
    schema = parquet_file.schema_arrow
    print(f"Columns: {len(schema.names)}")
    
    if show_schema:
        print("\nSchema:")
        for i, field in enumerate(schema, 1):
            print(f"{i}. {field.name}: {field.type}")
    
    # Show sample if requested
    if sample_rows:
        print(f"\nFirst {sample_rows} rows:")
        df = parquet_file.read().to_pandas().head(sample_rows)
        print(df.to_string())
        
        # Show memory usage per column
        print("\nMemory Usage per Column:")
        for col in df.columns:
            mem_usage = df[col].memory_usage(deep=True)
            print(f"{col}: {humanize.naturalsize(mem_usage, binary=True)}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Display information about a parquet file')
    parser.add_argument('file', help='Path to the parquet file')
    parser.add_argument('-s', '--schema', action='store_true', help='Show detailed schema information')
    parser.add_argument('-r', '--rows', type=int, help='Number of sample rows to display')
    
    args = parser.parse_args()
    
    if not os.path.exists(args.file):
        print(f"Error: File not found: {args.file}")
        sys.exit(1)
        
    try:
        get_parquet_info(args.file, args.schema, args.rows)
    except Exception as e:
        print(f"Error reading parquet file: {str(e)}")
        sys.exit(1)
