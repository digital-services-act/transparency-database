import os
import sys
import json
import time
import uuid
import random
import requests
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timedelta
from typing import List, Dict, Any
from dotenv import load_dotenv
from faker import Faker

load_dotenv()

API_URL = os.getenv("API_URL")
API_TOKEN = os.getenv("API_TOKEN")

if not API_URL or not API_TOKEN:
    print("❌ Error: API_URL and API_TOKEN must be set in .env file")
    sys.exit(1)

HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': f'Bearer {API_TOKEN}'
}

faker = Faker()

with open("./fixtures/statements.json", "r") as file:
    STATEMENT_DATA = json.load(file)

def generate_statement(puid: str = None, **overrides) -> Dict[str, Any]:
    """Generate a realistic statement matching the API schema."""
    recent_date = (datetime.now() - timedelta(days=random.randint(1, 30))).strftime("%Y-%m-%d")

    statement = {
        "puid": puid or str(uuid.uuid4()),
        "decision_visibility": [random.choice(STATEMENT_DATA["decision_visibility"])],
        "decision_monetary": random.choice(STATEMENT_DATA["decision_monetary"]),
        "decision_provision": random.choice(STATEMENT_DATA["decision_provision"]),
        "decision_account": random.choice(STATEMENT_DATA["decision_account"]),
        "account_type": random.choice(STATEMENT_DATA["account_type"]),
        "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
        "decision_ground_reference_url": faker.url(),
        "incompatible_content_ground": faker.sentence(),
        "incompatible_content_explanation": faker.paragraph(),
        "incompatible_content_illegal": "Yes",
        "content_type": random.sample(STATEMENT_DATA["content_type"], random.choice([1, 2, 3])),
        "category": random.choice(STATEMENT_DATA["categories"]),
        "category_specification": random.sample(STATEMENT_DATA["category_specification"], random.choice([1, 2, 3, 4])),
        "territorial_scope": random.sample(STATEMENT_DATA["territorial_scope"], random.choice([1, 2, 3, 4])),
        "content_language": random.choice(STATEMENT_DATA["content_language"]),
        "content_date": recent_date,
        "application_date": recent_date,
        "end_date_monetary_restriction": recent_date,
        "decision_facts": faker.paragraph(),
        "source_type": "SOURCE_TRUSTED_FLAGGER",
        "automated_detection": "No",
        "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
        "end_date_visibility_restriction": None,
        "end_date_account_restriction": None,
        "end_date_service_restriction": None,
        "platform_name": "Platform 1",
        "permalink": faker.url()
    }
    statement.update(overrides)
    return statement

def inject_duplicates_single(statements: List[Dict], duplicate_rate: float = 0.1) -> List[Dict]:
    """
    Inject duplicate PUIDs into statements list in clusters.
    Simulates real-world scenario where duplicates come from the same client
    in quick succession (retries, double-clicks, etc).

    duplicate_rate: percentage of statements that should be duplicates (0.0 to 1.0)
    """
    if len(statements) < 2 or duplicate_rate <= 0:
        return statements

    num_duplicates = int(len(statements) * duplicate_rate)

    if num_duplicates == 0:
        return statements

    # Create clusters of duplicates (2-4 duplicates per cluster)
    cluster_size_range = (2, 4)
    duplicates_created = 0

    while duplicates_created < num_duplicates and duplicates_created < len(statements) - 1:
        # Random cluster size
        cluster_size = min(
            random.randint(*cluster_size_range),
            num_duplicates - duplicates_created,
            len(statements) - duplicates_created
        )

        if cluster_size < 2:
            break

        # Pick a random position for this cluster
        # Ensure we have enough space for the cluster
        max_start = len(statements) - cluster_size
        if max_start < 0:
            break

        cluster_start = random.randint(0, max_start)

        # Use the first statement's PUID for the entire cluster
        source_puid = statements[cluster_start]['puid']

        # Apply same PUID to cluster (they'll be close together in the list)
        for i in range(cluster_start + 1, min(cluster_start + cluster_size, len(statements))):
            statements[i]['puid'] = source_puid
            duplicates_created += 1

    return statements

def inject_duplicates_multi(all_request_statements: List[List[Dict]], duplicate_rate: float = 0.1) -> List[List[Dict]]:
    """
    Inject duplicates for multi endpoint testing.
    Creates two types of duplicates:
    1. Complete duplicate requests (entire batch duplicated)
    2. Cross-request PUID duplicates (one request steals PUIDs from another)

    duplicate_rate: percentage of requests that should have duplicates (0.0 to 1.0)
    """
    if len(all_request_statements) < 2 or duplicate_rate <= 0:
        return all_request_statements

    num_duplicate_requests = int(len(all_request_statements) * duplicate_rate)

    if num_duplicate_requests == 0:
        return all_request_statements

    # Split duplicates between complete duplicates and PUID theft
    # 50% complete request duplicates, 50% PUID theft
    num_complete_duplicates = num_duplicate_requests // 2
    num_puid_theft = num_duplicate_requests - num_complete_duplicates

    # Type 1: Complete duplicate requests (same entire batch)
    for _ in range(num_complete_duplicates):
        if len(all_request_statements) < 2:
            break

        # Pick a random source request to duplicate
        source_idx = random.randint(0, len(all_request_statements) - 1)
        # Pick a random target to replace with the duplicate
        target_idx = random.choice([i for i in range(len(all_request_statements)) if i != source_idx])

        # Deep copy the entire request
        all_request_statements[target_idx] = [stmt.copy() for stmt in all_request_statements[source_idx]]

    # Type 2: Cross-request PUID theft
    # Pick random statements from one request and inject their PUIDs into another
    for _ in range(num_puid_theft):
        if len(all_request_statements) < 2:
            break

        # Pick two different requests
        source_idx = random.randint(0, len(all_request_statements) - 1)
        target_idx = random.choice([i for i in range(len(all_request_statements)) if i != source_idx])

        source_request = all_request_statements[source_idx]
        target_request = all_request_statements[target_idx]

        if len(source_request) == 0 or len(target_request) == 0:
            continue

        # Steal 1-5 PUIDs from source request
        num_stolen = min(random.randint(1, 5), len(source_request), len(target_request))

        # Pick random PUIDs from source
        source_puids = random.sample([stmt['puid'] for stmt in source_request], num_stolen)

        # Inject into random positions in target
        target_indices = random.sample(range(len(target_request)), num_stolen)

        for puid, target_pos in zip(source_puids, target_indices):
            target_request[target_pos]['puid'] = puid

    return all_request_statements

def send_single_request(statement: Dict) -> Dict[str, Any]:
    """Send a single statement request."""
    url = f"{API_URL}/statement"
    start_time = time.time()

    try:
        response = requests.post(url, headers=HEADERS, json=statement, timeout=30)
        duration = time.time() - start_time

        return {
            'success': response.status_code in [200, 201],
            'status_code': response.status_code,
            'duration': duration,
            'error': None if response.status_code in [200, 201] else response.text[:200]
        }
    except Exception as e:
        duration = time.time() - start_time
        return {
            'success': False,
            'status_code': 0,
            'duration': duration,
            'error': str(e)
        }

def send_multi_request(statements: List[Dict]) -> Dict[str, Any]:
    """Send a multi statement request."""
    url = f"{API_URL}/statements"
    start_time = time.time()

    try:
        payload = {'statements': statements}
        response = requests.post(url, headers=HEADERS, json=payload, timeout=30)
        duration = time.time() - start_time

        return {
            'success': response.status_code in [200, 201],
            'status_code': response.status_code,
            'duration': duration,
            'error': None if response.status_code in [200, 201] else response.text[:200],
            'num_statements': len(statements)
        }
    except Exception as e:
        duration = time.time() - start_time
        return {
            'success': False,
            'status_code': 0,
            'duration': duration,
            'error': str(e),
            'num_statements': len(statements)
        }

def test_single_endpoint(num_requests: int, concurrency: int, duplicate_rate: float = 0.1):
    """
    Test single endpoint with concurrent requests.

    Args:
        num_requests: Total number of requests to make
        concurrency: Number of concurrent workers
        duplicate_rate: Percentage of requests with duplicate PUIDs (0.0 to 1.0)
    """
    print(f"\n{'='*80}")
    print(f"🧪 Testing SINGLE endpoint")
    print(f"{'='*80}")
    print(f"Total requests: {num_requests}")
    print(f"Concurrency: {concurrency}")
    print(f"Duplicate rate: {duplicate_rate * 100}%")
    print()

    # Generate all statements first
    statements = [generate_statement() for _ in range(num_requests)]

    # Inject duplicates
    statements = inject_duplicates_single(statements, duplicate_rate)

    results = []
    start_time = time.time()

    with ThreadPoolExecutor(max_workers=concurrency) as executor:
        futures = [executor.submit(send_single_request, stmt) for stmt in statements]

        completed = 0
        for future in as_completed(futures):
            completed += 1
            result = future.result()
            results.append(result)

            # Progress indicator
            if completed % 10 == 0 or completed == num_requests:
                print(f"Progress: {completed}/{num_requests}", end='\r')

    total_duration = time.time() - start_time

    # Analyze results
    print_results("SINGLE", results, total_duration, num_requests)

def test_multi_endpoint(num_requests: int, concurrency: int, statements_per_request: int = 100, duplicate_rate: float = 0.1):
    """
    Test multi endpoint with concurrent requests.

    Args:
        num_requests: Total number of requests to make
        concurrency: Number of concurrent workers
        statements_per_request: Number of statements in each request (max 100)
        duplicate_rate: Percentage of statements with duplicate PUIDs (0.0 to 1.0)
    """
    print(f"\n{'='*80}")
    print(f"🧪 Testing MULTI endpoint")
    print(f"{'='*80}")
    print(f"Total requests: {num_requests}")
    print(f"Statements per request: {statements_per_request}")
    print(f"Concurrency: {concurrency}")
    print(f"Duplicate rate: {duplicate_rate * 100}%")
    print()

    # Generate statements for each request
    all_request_statements = []
    for _ in range(num_requests):
        statements = [generate_statement() for _ in range(statements_per_request)]
        # Inject duplicates within each request
        all_request_statements.append(statements)

    all_request_statements = inject_duplicates_multi(all_request_statements, duplicate_rate)

    results = []
    start_time = time.time()

    with ThreadPoolExecutor(max_workers=concurrency) as executor:
        futures = [executor.submit(send_multi_request, stmts) for stmts in all_request_statements]

        completed = 0
        for future in as_completed(futures):
            completed += 1
            result = future.result()
            results.append(result)

            # Progress indicator
            if completed % 10 == 0 or completed == num_requests:
                print(f"Progress: {completed}/{num_requests}", end='\r')

    total_duration = time.time() - start_time

    # Analyze results
    total_statements = num_requests * statements_per_request
    print_results("MULTI", results, total_duration, num_requests, total_statements)

def print_results(endpoint_type: str, results: List[Dict], total_duration: float, num_requests: int, total_statements: int = None):
    """Print formatted test results."""
    print(f"\n\n{'='*80}")
    print(f"📊 {endpoint_type} Endpoint Results")
    print(f"{'='*80}\n")

    successful = sum(1 for r in results if r['success'])
    failed = len(results) - successful

    status_codes = {}
    for r in results:
        code = r['status_code']
        status_codes[code] = status_codes.get(code, 0) + 1

    durations = [r['duration'] for r in results]
    avg_duration = sum(durations) / len(durations) if durations else 0
    min_duration = min(durations) if durations else 0
    max_duration = max(durations) if durations else 0

    # Sort durations for percentiles
    sorted_durations = sorted(durations)
    p50 = sorted_durations[len(sorted_durations) // 2] if sorted_durations else 0
    p95 = sorted_durations[int(len(sorted_durations) * 0.95)] if sorted_durations else 0
    p99 = sorted_durations[int(len(sorted_durations) * 0.99)] if sorted_durations else 0

    requests_per_second = num_requests / total_duration if total_duration > 0 else 0

    print(f"Total Duration:        {total_duration:.2f}s")
    print(f"Requests/second:       {requests_per_second:.2f}")
    if total_statements:
        statements_per_second = total_statements / total_duration if total_duration > 0 else 0
        print(f"Statements/second:     {statements_per_second:.2f}")
    print()

    print(f"Total Requests:        {num_requests}")
    print(f"Successful:            {successful} ({successful/num_requests*100:.1f}%)")
    print(f"Failed:                {failed} ({failed/num_requests*100:.1f}%)")
    print()

    print("Status Code Distribution:")
    for code, count in sorted(status_codes.items()):
        print(f"  {code}: {count} ({count/num_requests*100:.1f}%)")
    print()

    print(f"Response Times (seconds):")
    print(f"  Min:     {min_duration:.3f}s")
    print(f"  Average: {avg_duration:.3f}s")
    print(f"  Median:  {p50:.3f}s")
    print(f"  95th %:  {p95:.3f}s")
    print(f"  99th %:  {p99:.3f}s")
    print(f"  Max:     {max_duration:.3f}s")
    print()

    # Show some error samples
    errors = [r for r in results if not r['success']]
    if errors:
        print(f"Sample Errors (showing first 3):")
        for i, err in enumerate(errors[:3], 1):
            print(f"  {i}. Status {err['status_code']}: {err['error']}")
        print()

def main():
    """Main entry point."""
    import argparse

    parser = argparse.ArgumentParser(description='Concurrent API testing for statement endpoints')
    parser.add_argument('--endpoint', choices=['single', 'multi', 'both'], default='both',
                        help='Which endpoint to test')
    parser.add_argument('--requests', type=int, default=100,
                        help='Number of requests to make')
    parser.add_argument('--concurrency', type=int, default=10,
                        help='Number of concurrent workers')
    parser.add_argument('--statements-per-request', type=int, default=100,
                        help='Number of statements per multi request (max 100)')
    parser.add_argument('--duplicate-rate', type=float, default=0.1,
                        help='Percentage of duplicate PUIDs (0.0 to 1.0)')

    args = parser.parse_args()

    print(f"\n🚀 Starting Concurrent API Tests")
    print(f"API URL: {API_URL}")
    print(f"Token: {API_TOKEN[:10]}..." if API_TOKEN else "Token: None")

    if args.endpoint in ['single', 'both']:
        test_single_endpoint(args.requests, args.concurrency, args.duplicate_rate)

    if args.endpoint in ['multi', 'both']:
        test_multi_endpoint(args.requests, args.concurrency, args.statements_per_request, args.duplicate_rate)

    print(f"\n✅ All tests complete!\n")


if __name__ == '__main__':
    main()
