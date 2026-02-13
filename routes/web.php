<?php

use App\Http\Controllers\DatabaseVelocityController;
use App\Http\Controllers\DataDownloadController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogMessagesController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test-log', function () {
    \Log::info('🎉 Test log to stdout is working!');

    return 'Logged!';
});

Route::get('/test-s3-presigned', function () {
    $filename = 'test-'.Str::random(16).'.txt';
    $content = 'This is a test file created at '.now()->toDateTimeString();

    // Upload to S3 with private visibility
    $disk = Storage::disk('s3ds');
    $disk->put($filename, $content, ['visibility' => 'private']);

    // Generate presigned URL valid for 10 minutes
    $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(10));

    return response()->json([
        'message' => 'Test file uploaded successfully',
        'filename' => $filename,
        'presigned_url' => $presignedUrl,
        'expires_in' => '10 minutes',
    ]);
});

Route::get('/test-s3-list', function () {
    $disk = Storage::disk('s3ds');

    try {
        // List all files (limited to first 20)
        $files = collect($disk->files())->take(20)->values();

        return response()->json([
            'bucket' => config('filesystems.disks.s3ds.bucket'),
            'endpoint' => config('filesystems.disks.s3ds.endpoint'),
            'file_count' => $files->count(),
            'files' => $files,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/test-s3-existing/{filename}', function (string $filename) {
    $disk = Storage::disk('s3ds');
    $results = [];

    // Generate presigned URL for existing file
    try {
        $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(10));
        $results['presigned_url'] = $presignedUrl;
    } catch (\Exception $e) {
        $results['presigned_error'] = $e->getMessage();
    }

    // Also try direct client approach
    try {
        $client = $disk->getClient();
        $bucket = config('filesystems.disks.s3ds.bucket');

        $cmd = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $filename,
        ]);

        $request = $client->createPresignedRequest($cmd, '+10 minutes');
        $results['direct_presigned_url'] = (string) $request->getUri();
    } catch (\Exception $e) {
        $results['direct_presigned_error'] = $e->getMessage();
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
})->where('filename', '.*');

Route::get('/test-s3-debug', function () {
    $disk = Storage::disk('s3ds');
    $filename = 'test-'.Str::random(16).'.txt';
    $content = 'Test file at '.now()->toDateTimeString();
    $results = [
        'config' => [
            'default_disk' => config('filesystems.default'),
            'bucket' => config('filesystems.disks.s3ds.bucket'),
            'region' => config('filesystems.disks.s3ds.region'),
            'endpoint' => config('filesystems.disks.s3ds.endpoint'),
            'url' => config('filesystems.disks.s3ds.url'),
            'use_path_style' => config('filesystems.disks.s3ds.use_path_style_endpoint'),
            'visibility' => config('filesystems.disks.s3ds.visibility'),
        ],
    ];

    // Step 1: Test upload
    try {
        $uploaded = $disk->put($filename, $content);
        $results['step1_upload'] = ['success' => $uploaded];
    } catch (\Exception $e) {
        $results['step1_upload'] = ['error' => $e->getMessage()];
    }

    // Step 2: Generate presigned URL (doesn't require the file to exist)
    try {
        $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(10));
        $results['step2_presigned_url'] = ['url' => $presignedUrl];
    } catch (\Exception $e) {
        $results['step2_presigned_url'] = ['error' => $e->getMessage()];
    }

    // Step 3: Generate public URL
    try {
        $publicUrl = $disk->url($filename);
        $results['step3_public_url'] = ['url' => $publicUrl];
    } catch (\Exception $e) {
        $results['step3_public_url'] = ['error' => $e->getMessage()];
    }

    // Step 4: Try to get the S3 client directly and create presigned request
    try {
        $client = $disk->getClient();
        $bucket = config('filesystems.disks.s3ds.bucket');

        $cmd = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $filename,
        ]);

        $request = $client->createPresignedRequest($cmd, '+10 minutes');
        $directPresignedUrl = (string) $request->getUri();

        $results['step4_direct_presigned'] = [
            'url' => $directPresignedUrl,
            'client_endpoint' => $client->getEndpoint()->__toString(),
            'client_region' => $client->getRegion(),
        ];
    } catch (\Exception $e) {
        $results['step4_direct_presigned'] = ['error' => $e->getMessage()];
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

Route::middleware(['force.auth'])->group(static function () {
    // Your routes that require authentication in non-production environments
    Route::middleware(['auth'])->group(static function () {
        Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback', [FeedbackController::class, 'send'])->name('feedback.send');
        Route::group(['middleware' => ['can:create statements']], static function () {
            Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
            Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');
        });

        Route::prefix('/admin/')->group(static function () {
            Route::group(['middleware' => ['can:administrate']], static function () {
                Route::delete('log-messages', [LogMessagesController::class, 'destroy'])->name('log-messages.destroy');
                Route::get('database-velocity', [DatabaseVelocityController::class, 'index'])->name('database-velocity.index');
            });
            Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index')->can('view platforms');
            Route::get('log-messages', [LogMessagesController::class, 'index'])->name('log-messages.index')->can('view logs');
        });

        Route::resource('user', UserController::class, ['middleware' => ['can:create users']]);
        Route::resource('platform', PlatformController::class, ['middleware' => ['can:create platforms']]);

        Route::get('/profile/start', [ProfileController::class, 'profile'])->name('profile.start');
        Route::get('/profile/page/{page}', [PageController::class, 'profileShow'])->name('profile.page.show');
        Route::get('/profile/api', [ProfileController::class, 'apiIndex'])->name('profile.api.index')->can('generate-api-key');
        Route::post('/profile/api/new-token', [ProfileController::class, 'newToken'])->name('profile.api.new-token')->can('generate-api-key');

    });

    Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');

    Route::get('/statement/csv', [StatementController::class, 'exportCsv'])->name('statement.export');
    Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');

    Route::get('/statement/{statement:uuid}', [StatementController::class, 'show'])
        ->name('statement.show');

    Route::get('/explore-data/download/{uuid?}', [DataDownloadController::class, 'index'])->name('dayarchive.index');
    Route::get('/explore-data/download-file/{dayArchive}/{type}', [DataDownloadController::class, 'download'])->name('dayarchive.download');

    Route::view('/explore-data/overview', 'explore-data.overview')->name('explore-data.overview');
    Route::view('/explore-data/toolbox', 'explore-data.toolbox')->name('explore-data.toolbox');

    Route::get('/daily-archives', static fn () => Redirect::to(route('dayarchive.index'), 301));
    Route::get('/data-download', static fn () => Redirect::to(route('dayarchive.index'), 301));

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/ping-redis', function () {
        try {
            $cacheDriver = config('cache.default');
            Redis::ping();
            $dbsize = Redis::command('DBSIZE');
            $counter = Redis::incr('ping-redis-counter');

            return "Successfully connected to Redis! Cache driver: {$cacheDriver}. DB Size: {$dbsize}. This page has been viewed {$counter} times.";
        } catch (\Exception $e) {
            $cacheDriver = config('cache.default');

            return 'Failed to connect to Redis: '.$e->getMessage().". Cache driver is '{$cacheDriver}'.";
        }
    });

});
