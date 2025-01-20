import os
import datetime
import gc
import multiprocessing
import concurrent.futures
import argparse
from typing import Optional
from concurrent.futures import ThreadPoolExecutor

import pandas as pd
import pyarrow as pa
import pyarrow.parquet as pq
from sqlalchemy import create_engine, text


def get_mysql_connection_string():
    """Get MySQL connection string from environment variables"""
    db_host = os.getenv('DB_HOST', 'localhost')
    db_port = os.getenv('DB_PORT', '3306')
    db_database = os.getenv('DB_DATABASE', '')
    db_username = os.getenv('DB_USERNAME', '')
    db_password = os.getenv('DB_PASSWORD', '')
    
    return f'mysql+pymysql://{db_username}:{db_password}@{db_host}:{db_port}/{db_database}'

class StatementExporter:
    def __init__(self, connection_string: Optional[str] = None, verbose: bool = False):
        """Initialize the exporter"""
        self.verbose = verbose
        
        # For a 96 vCPU machine, use about 1/3 of the cores for optimal throughput
        self.max_workers = 32  # Fixed number for 96 vCPU instance
        
        # If no connection string provided, get from .env
        if connection_string is None:
            connection_string = get_mysql_connection_string()
        
        self.engine = create_engine(
            connection_string,
            pool_size=self.max_workers,      # Match pool to worker count
            max_overflow=self.max_workers,    # Allow double capacity if needed
            pool_timeout=60,    
            pool_pre_ping=True,
            pool_recycle=3600,
            execution_options={
                "stream_results": True,
                "max_row_buffer": 100000  # Buffer size for streaming
            }
        )
        self.CHUNK_SIZE = 100000
        
        # Load platform data into memory
        if self.verbose:
            print("Loading platform data...")
        with self.engine.connect() as conn:
            platforms_df = pd.read_sql(
                "SELECT id, name FROM platforms",
                conn
            )
        self.platform_lookup = dict(zip(platforms_df.id, platforms_df.name))
        if self.verbose:
            print(f"Loaded {len(self.platform_lookup)} platforms")

    def get_first_id_of_date(self, date: datetime.date) -> Optional[int]:
        """Get first ID for a specific date using second-by-second search"""
        start_of_day = datetime.datetime.combine(date, datetime.time.min)
        if self.verbose:
            print(f"Searching for first ID on {date}")
        
        with self.engine.connect() as conn:
            # Check each second in the first minute
            for second in range(60):
                current_time = start_of_day + datetime.timedelta(seconds=second)
                formatted_time = current_time.strftime('%Y-%m-%d %H:%M:%S')
                if self.verbose:
                    print(f"Checking time: {formatted_time}")
                
                result = conn.execute(text("""
                    SELECT MIN(id) as min_id 
                    FROM statements 
                    WHERE created_at = :timestamp
                """), {"timestamp": formatted_time}).scalar()
                
                if result:
                    if self.verbose:
                        print(f"Found first ID: {result}")
                    return result
                
        if self.verbose:
            print("No ID found in the first minute of the day")
        return None

    def get_last_id_of_date(self, date: datetime.date) -> Optional[int]:
        """Get last ID for a specific date using second-by-second search"""
        end_of_day = datetime.datetime.combine(date, datetime.time.max)
        if self.verbose:
            print(f"Searching for last ID on {date}")
        
        with self.engine.connect() as conn:
            # Check each second in the last minute, going backwards
            for second in range(60):
                current_time = end_of_day - datetime.timedelta(seconds=second)
                formatted_time = current_time.strftime('%Y-%m-%d %H:%M:%S')
                if self.verbose:
                    print(f"Checking time: {formatted_time}")
                
                result = conn.execute(text("""
                    SELECT MAX(id) as max_id 
                    FROM statements 
                    WHERE created_at = :timestamp
                """), {"timestamp": formatted_time}).scalar()
                
                if result:
                    if self.verbose:
                        print(f"Found last ID: {result}")
                    return result
                
        if self.verbose:
            print("No ID found in the last minute of the day")
        return None

    def process_chunk(self, start_id: int, end_id: int, chunk_number: int, total_chunks: int) -> str:
        """Process a chunk of statements and save to parquet"""
        temp_file = os.path.abspath(f"{self.output_dir}/temp_chunk_{chunk_number:05d}.parquet")
        
        # Skip if chunk already exists and is valid
        if os.path.exists(temp_file):
            try:
                # Verify the chunk is readable
                test_table = pq.read_table(temp_file)
                if self.verbose:
                    print(f"Chunk {chunk_number}/{total_chunks} already exists and is valid, skipping...")
                return temp_file
            except Exception:
                if self.verbose:
                    print(f"Existing chunk {chunk_number}/{total_chunks} is invalid, regenerating...")
                os.remove(temp_file)
        
        if self.verbose:
            print(f"Processing chunk {chunk_number}/{total_chunks} (IDs {start_id} to {end_id})")
        
        query = text("""
            SELECT s.*
            FROM statements s
            WHERE s.id >= :start_id AND s.id <= :end_id
            ORDER BY s.id
        """)
        
        df = pd.read_sql(query, self.engine, params={"start_id": start_id, "end_id": end_id})
        df['platform_name'] = df['platform_id'].map(self.platform_lookup)
        
        # Ensure consistent types
        if 'id' in df.columns:
            df['id'] = df['id'].astype('int64')
        if 'platform_id' in df.columns:
            df['platform_id'] = df['platform_id'].astype('int64')
        if 'platform_name' in df.columns:
            df['platform_name'] = df['platform_name'].astype('string')
        
        # Write chunk to parquet
        table = pa.Table.from_pandas(df)
        pq.write_table(table, temp_file, compression='snappy')
        
        del df
        del table
        gc.collect()
        
        return temp_file

    def export_day(self, date: datetime.date, output_dir: str):
        """Export one day's worth of statements to parquet files"""
        self.output_dir = output_dir
        os.makedirs(output_dir, exist_ok=True)
        
        start_time = datetime.datetime.now()
        if self.verbose:
            print(f"Starting export for date: {date}")
        
        first_id = self.get_first_id_of_date(date)
        if not first_id:
            if self.verbose:
                print(f"No starting ID found for {date}")
            return
            
        last_id = self.get_last_id_of_date(date)
        if not last_id:
            if self.verbose:
                print(f"No ending ID found for {date}")
            return
        
        total_records = last_id - first_id + 1
        if self.verbose:
            print(f"Processing {total_records} records from ID {first_id} to {last_id}")
        
        # Split into chunks
        chunks = []
        current_id = first_id
        while current_id <= last_id:
            chunk_end = min(current_id + self.CHUNK_SIZE - 1, last_id)
            chunks.append((current_id, chunk_end))
            current_id = chunk_end + 1
        
        total_chunks = len(chunks)
        if self.verbose:
            print(f"Split into {total_chunks} chunks of {self.CHUNK_SIZE} records each")
            print(f"Using {self.max_workers} parallel workers on {multiprocessing.cpu_count()} available vCPUs")
        
        temp_files = []
        completed_chunks = 0
        
        # Process chunks with parallel processing
        with ThreadPoolExecutor(max_workers=self.max_workers) as executor:
            futures = {
                executor.submit(self.process_chunk, start, end, idx + 1, total_chunks): idx 
                for idx, (start, end) in enumerate(chunks)
            }
            
            for future in concurrent.futures.as_completed(futures):
                chunk_idx = futures[future]
                try:
                    temp_file = future.result()
                    temp_files.append(temp_file)
                    completed_chunks += 1
                    
                    if self.verbose:
                        # Calculate progress
                        elapsed_time = (datetime.datetime.now() - start_time).total_seconds()
                        progress_rate = completed_chunks / total_chunks
                        if progress_rate > 0:
                            total_estimated_time = elapsed_time / progress_rate
                            remaining_time = total_estimated_time - elapsed_time
                            hours = int(remaining_time // 3600)
                            minutes = int((remaining_time % 3600) // 60)
                            print(f"Completed chunk {completed_chunks}/{total_chunks} ({progress_rate*100:.1f}%) - {hours}h {minutes}m left")
                    
                except Exception as e:
                    if self.verbose:
                        print(f"Error processing chunk {chunk_idx + 1}: {str(e)}")
        
        if not temp_files:
            if self.verbose:
                print("No temporary files were created. Something went wrong during processing.")
            return
        
        # Combine all chunks into final file
        if self.verbose:
            print(f"Combining {len(temp_files)} chunks into final file...")
            print("Reading chunks...")
        
        # Read all tables with explicit schema
        tables = []
        schema = None
        for i, temp_file in enumerate(temp_files, 1):
            if self.verbose and i % 50 == 0:
                print(f"Read {i}/{len(temp_files)} chunks...")
            table = pq.read_table(temp_file)
            if schema is None:
                schema = table.schema
            tables.append(table)
        
        if self.verbose:
            print("Concatenating tables...")
        
        # Combine tables
        final_table = pa.concat_tables(tables, promote_options='default')
        
        if self.verbose:
            print("Adding metadata...")
        
        # Add metadata to the schema
        metadata = {
            'date': str(date),
            'total_records': str(total_records),
            'id_range': f"{first_id}-{last_id}",
            'created_at': datetime.datetime.now().isoformat(),
            'created_by': 'StatementExporter',
            'schema_version': '1.0'
        }
        
        # Convert metadata values to bytes as required by pyarrow
        metadata_bytes = {k: str(v).encode('utf-8') for k, v in metadata.items()}
        final_table = final_table.replace_schema_metadata(metadata_bytes)
        
        if self.verbose:
            print(f"Writing final file to {os.path.abspath(f'{output_dir}/statements_{date}.parquet')}...")
        
        # Write the final table
        pq.write_table(final_table, os.path.abspath(f"{output_dir}/statements_{date}.parquet"), compression='snappy')
        
        # Cleanup
        if self.verbose:
            print("Cleaning up temporary files...")
        for temp_file in temp_files:
            try:
                os.remove(temp_file)
            except Exception as e:
                if self.verbose:
                    print(f"Error removing temporary file {temp_file}: {str(e)}")
        
        if self.verbose:
            total_time = (datetime.datetime.now() - start_time).total_seconds()
            print(f"Export completed: {os.path.abspath(f'{output_dir}/statements_{date}.parquet')}")
            print(f"Total records: {total_records}")
            print(f"Total time: {total_time/60:.1f} minutes")
            print(f"Average speed: {total_records/total_time:.1f} records/second")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Export statements to parquet format')
    parser.add_argument('-v', '--verbose', action='store_true', help='Show progress output')
    parser.add_argument('-d', '--date', type=str, 
                       help='Date to export: either days back as number (e.g. 7) or specific date (YYYY-MM-DD), defaults to 1 (yesterday)')
    parser.add_argument('-o', '--output', type=str, default='storage/app', help='Output directory')
    
    args = parser.parse_args()
    
    # Parse date argument
    if args.date is None:
        # Default to yesterday (1 day back)
        export_date = datetime.date.today() - datetime.timedelta(days=1)
    else:
        try:
            # Try parsing as number of days back
            days_back = int(args.date)
            export_date = datetime.date.today() - datetime.timedelta(days=days_back)
        except ValueError:
            try:
                # If not a number, try parsing as YYYY-MM-DD
                export_date = datetime.datetime.strptime(args.date, '%Y-%m-%d').date()
            except ValueError:
                print(f"Error: Date must be either number of days back or in YYYY-MM-DD format")
                exit(1)
    
    exporter = StatementExporter(verbose=args.verbose)
    exporter.export_day(export_date, args.output)
