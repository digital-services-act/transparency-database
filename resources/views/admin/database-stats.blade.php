@extends('layouts/ecl')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>Database Statistics</h2>
                            <p class="text-muted">Statistics for the last {{ $minutes }} minutes</p>
                        </div>
                        <div>
                            <form method="GET" action="{{ route('admin.database-stats') }}" class="form-inline">
                                <div class="input-group">
                                    <select name="minutes" class="form-control">
                                        <option value="10" {{ $minutes == 10 ? 'selected' : '' }}>10 minutes</option>
                                        <option value="30" {{ $minutes == 30 ? 'selected' : '' }}>30 minutes</option>
                                        <option value="60" {{ $minutes == 60 ? 'selected' : '' }}>1 hour</option>
                                        <option value="360" {{ $minutes == 360 ? 'selected' : '' }}>6 hours</option>
                                        <option value="1440" {{ $minutes == 1440 ? 'selected' : '' }}>24 hours</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Total Statements</div>
                                <div class="card-body">
                                    <h3>{{ $totalCount->total ?? 0 }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Elapsed Time</div>
                                <div class="card-body">
                                    @if($elapsedTime)
                                        <h3>{{ $elapsedTime->elapsed_time ?? 0 }} seconds</h3>
                                        <p class="text-muted">From: {{ $elapsedTime->min_time ?? 'N/A' }}</p>
                                        <p class="text-muted">To: {{ $elapsedTime->max_time ?? 'N/A' }}</p>
                                    @else
                                        <h3>0 seconds</h3>
                                        <p class="text-muted">No data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h4>Statements Per Second (Ordered by Count)</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($statementsPerSecond as $stat)
                                            <tr>
                                                <td>{{ $stat->second }}</td>
                                                <td>{{ $stat->rps }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h4>Database Cleanup</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Warning:</strong> This will delete all statements where ID > 1 and truncate the platform_puids table.</p>
                            <form method="POST" action="{{ route('admin.database-cleanup') }}" onsubmit="return confirm('Are you sure you want to clean up the database? This action cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn btn-danger">Clean Database</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
