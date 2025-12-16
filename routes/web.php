<?php

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

    // List all files (limited to first 50)
    $files = collect($disk->files())->take(50)->map(function ($file) use ($disk) {
        return [
            'name' => $file,
            'size' => $disk->size($file),
            'last_modified' => $disk->lastModified($file),
        ];
    });

    return response()->json([
        'bucket' => config('filesystems.disks.s3ds.bucket'),
        'endpoint' => config('filesystems.disks.s3ds.endpoint'),
        'file_count' => $files->count(),
        'files' => $files,
    ]);
});

Route::get('/test-s3-debug', function () {
    $disk = Storage::disk('s3ds');
    $filename = 'test-'.Str::random(16).'.txt';
    $content = 'Test file';

    try {
        // Test upload
        $uploaded = $disk->put($filename, $content);

        // Test if file exists
        $exists = $disk->exists($filename);

        // Get the URL (non-presigned)
        $url = $disk->url($filename);

        // Generate presigned URL
        $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(10));

        // Get adapter info
        $adapter = $disk->getAdapter();
        $client = $adapter->getClient();

        return response()->json([
            'upload_success' => $uploaded,
            'file_exists' => $exists,
            'public_url' => $url,
            'presigned_url' => $presignedUrl,
            'config' => [
                'bucket' => config('filesystems.disks.s3ds.bucket'),
                'region' => config('filesystems.disks.s3ds.region'),
                'endpoint' => config('filesystems.disks.s3ds.endpoint'),
                'use_path_style' => config('filesystems.disks.s3ds.use_path_style_endpoint'),
            ],
            'client_region' => $client->getRegion(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
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
