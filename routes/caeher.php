<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;
use Inertia\Inertia;

/************************* ADMIN ROUTES *************************/

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->middleware('isAdmin')->group(function () {

        Route::get('/', \App\Http\Controllers\Dashboard\IndexAdminDashboardController::class)
            ->name('admin.dashboard');

        Route::prefix('/content')->group(function () {

            Route::get('/', \App\Http\Controllers\Content\IndexContentController::class)
                ->name('admin.content.index');

            Route::get('/datatable', \App\Http\Controllers\Content\DatatableContentController::class)
                ->name('admin.content.datatable');

            Route::put('/pause/{content}', \App\Http\Controllers\Content\PauseContentController::class)
                ->name('admin.content.pause');
            Route::put('/publish/{content}', \App\Http\Controllers\Content\PublishContentController::class)
                ->name('admin.content.publish');

            Route::delete('/{content}/delete', \App\Http\Controllers\Content\DeleteContentController::class)
                ->name('admin.content.delete');

            // Category Routes
            Route::prefix('categories')->group(function () {

                Route::get('/datatable', \App\Http\Controllers\Category\DatatableCategoryController::class)
                    ->name('admin.categories.datatable');
                Route::post('/', \App\Http\Controllers\Category\StoreCategoryController::class)
                    ->name('admin.categories.store');
                Route::put('/{category}', \App\Http\Controllers\Category\UpdateCategoryController::class)
                    ->name('admin.categories.update');
                Route::delete(' /{category}', \App\Http\Controllers\Category\DestroyCategoryController::class)
                    ->name('admin.categories.destroy');

                // Subcategory Routes
                Route::prefix('{category}/subcategories')->group(function () {
                    Route::post('/', \App\Http\Controllers\Subcategory\StoreSubcategoryController::class)
                        ->name('admin.subcategories.store');
                    Route::put('/{subcategory}', \App\Http\Controllers\Subcategory\UpdateSubcategoryController::class)
                        ->name('admin.subcategories.update');
                    Route::delete('/{subcategory}', \App\Http\Controllers\Subcategory\DestroySubcategoryController::class)
                        ->name('admin.subcategories.destroy');
                });
            });
        });
        // Plan Routes

        Route::prefix('plans')->group(function () {
            Route::get('/', \App\Http\Controllers\Plan\IndexPlanController::class)
                ->name('admin.plans.index');
            Route::post('/', \App\Http\Controllers\Plan\StorePlanController::class)
                ->name('admin.plans.store');
            Route::put('/{plan}', \App\Http\Controllers\Plan\UpdatePlanController::class)
                ->name('admin.plans.update');
            Route::delete('/{plan}', \App\Http\Controllers\Plan\DestroyPlanController::class)
                ->name('admin.plans.destroy');
        });

        // User Routes
        Route::prefix('users')->group(function () {
            Route::get('/', \App\Http\Controllers\User\IndexUserController::class)
                ->name('admin.users.index');
            Route::get('/datatable', \App\Http\Controllers\User\DatatableUserController::class)
                ->name('admin.users.datatable');
            Route::post('/', \App\Http\Controllers\User\StoreUserController::class)
                ->name('admin.users.store');
            Route::put('/{user}', \App\Http\Controllers\User\UpdateUserController::class)
                ->name('admin.users.update');
            Route::delete('/{user}', \App\Http\Controllers\User\DestroyUserController::class)
                ->name('admin.users.destroy');
        });

        Route::prefix('profile')->group(function () {
            Route::get('/', \App\Http\Controllers\AdminProfile\ShowAdminProfileController::class)
                ->name('admin.profile.show');
            Route::put('/', \App\Http\Controllers\AdminProfile\UpdateAdminProfileController::class)
                ->name('admin.profile.update');
            Route::post('/update-image', \App\Http\Controllers\AdminProfile\UpdateImageAdminProfileController::class)
                ->name('admin.profile.update-image');
        });

        // Payment Routes
        Route::prefix('payments')->group(function () {
            Route::get('/revenue', \App\Http\Controllers\Payment\RevenueController::class)
                ->name('admin.payments.revenue');
        });
        
        Route::prefix('binacles')->group(function () {
            // datatable
            Route::get('/datatable', \App\Http\Controllers\Binacle\DatatableBinacleController::class)
                ->name('admin.binacles.datatable');
        });
    });

    Route::prefix('subscription')->group(function () {
        Route::get('/', [SubscriptionController::class, 'cancel'])
            ->name('subscription.cancel');
        Route::get('/success', [SubscriptionController::class, 'success'])
            ->name('subscription.success');
        Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])
            ->name('subscription.checkout');
    });

    /**
     * Aca todas las rutas que solo se pueden acceder si el usuario tiene una suscripcion
     */
    Route::middleware(['isUser', 'userHasSubscription'])->group(function () {

        Route::get('dashboard', \App\Http\Controllers\Dashboard\IndexUserDashboardController::class)
            ->name('dashboard');

        Route::get('search', \App\Http\Controllers\SearchController::class)
            ->name('search');

        Route::get('category/{category}', \App\Http\Controllers\Category\ShowCategoryController::class)
            ->name('category.show');

        Route::get('subcategory/{subcategory}', \App\Http\Controllers\Subcategory\ShowSubcategoryController::class)
            ->name('subcategory.show');

        Route::get('movie/{movie}', \App\Http\Controllers\Movie\ShowMovieController::class)
            ->name('user.movie.show');

        Route::get('movie/{movie}/player', \App\Http\Controllers\Movie\ShowPlayerMovieController::class)
            ->name('user.movie.player');

        Route::get('movie/{movie}/player/trailer', \App\Http\Controllers\Movie\ShowPlayerTrailerMovieController::class)
            ->name('user.movie.player.trailer');

        Route::get('serie/{serie}', \App\Http\Controllers\Serie\ShowSerieController::class)
            ->name('user.serie.show');

        Route::get('serie/{serie}/player', \App\Http\Controllers\Serie\ShowPlayerSerieController::class)
            ->name('user.serie.player');

        Route::get('serie/{serie}/season/{season}/chapter/{chapter}/player', \App\Http\Controllers\Serie\ShowPlayerChapterController::class)
            ->name('user.serie.chapter.player');

        Route::get('serie/{serie}/player/trailer', \App\Http\Controllers\Serie\ShowPlayerTrailerSerieController::class)
            ->name('user.serie.player.trailer');

        Route::prefix('channel')->group(function () {
            Route::get('/movie', \App\Http\Controllers\Channel\MovieChannelController::class)
                ->name('channel.movie');
            Route::get('/serie', \App\Http\Controllers\Channel\SerieChannelController::class)
                ->name('channel.serie');
            Route::get('/short', \App\Http\Controllers\Channel\ShortChannelController::class)
                ->name('channel.short');

            Route::get('/{user:username}/movie', \App\Http\Controllers\Channel\ShowMovieChannelController::class)
                ->name('channel.show.movie');
            Route::get('/{user:username}/serie', \App\Http\Controllers\Channel\ShowSerieChannelController::class)
                ->name('channel.show.serie');
            Route::get('/{user:username}/short', \App\Http\Controllers\Channel\ShowShortChannelController::class)
                ->name('channel.show.short');
        });

        Route::prefix('profile')->group(function () {
            Route::get('/', \App\Http\Controllers\UserProfile\ShowProfileController::class)
                ->name('user.profile.show');
            Route::get('/manage', \App\Http\Controllers\UserProfile\ManageProfileController::class)
                ->name('user.profile.manage');
            Route::put('/', \App\Http\Controllers\UserProfile\UpdateProfileController::class)
                ->name('user.profile.update');
            Route::post('/update-image', \App\Http\Controllers\UserProfile\UpdateImageProfileController::class)
                ->name('user.profile.update-image');

            Route::get('/subscription', \App\Http\Controllers\UserProfile\ShowManageSubscriptionProfileController::class)
                ->name('user.profile.subscription');
            Route::post('/subscription/update', \App\Http\Controllers\UserProfile\UpdateSubscriptionProfileController::class)
                ->name('user.profile.subscription.update');
            Route::post('/subscription/cancel', \App\Http\Controllers\UserProfile\CancelSubscriptionProfileController::class)
                ->name('user.profile.subscription.cancel');
        });

        Route::prefix('follows')->group(function () {
            Route::get('/', \App\Http\Controllers\Follow\ShowFollowController::class)
                ->name('user.follows.show');
            Route::post('/{user:id}/add', \App\Http\Controllers\Follow\AddToFollowController::class)
                ->name('user.follows.add');
            Route::post('/{user:id}/remove', \App\Http\Controllers\Follow\RemoveToFollowController::class)
                ->name('user.follows.remove');
        });

        Route::prefix('watchlist')->group(function () {
            Route::get('/', \App\Http\Controllers\Watchlist\ShowWatchlistController::class)
                ->name('user.watchlist.show');
            Route::post('/{type}/{id}/add', \App\Http\Controllers\Watchlist\AddToWatchlistController::class)
                ->name('user.watchlist.add');
            Route::post('/{type}/{id}/remove', \App\Http\Controllers\Watchlist\RemoveToWatchlistController::class)
                ->name('user.watchlist.remove');
            Route::delete('/', \App\Http\Controllers\Watchlist\DeleteWatchlistController::class)
                ->name('user.watchlist.delete');
        });

        Route::prefix('upload')->group(function () {
            Route::get('/movie', \App\Http\Controllers\Movie\CreateMovieController::class)
                ->name('user.upload.movie');
            Route::get('/serie', \App\Http\Controllers\Serie\SelectActionSerieController::class)
                ->name('user.upload.serie');
            Route::get('/serie/create', \App\Http\Controllers\Serie\CreateSerieController::class)
                ->name('user.upload.serie.create');
            Route::get('/serie/edit', \App\Http\Controllers\Serie\EditSerieController::class)
                ->name('user.upload.serie.edit');
            Route::get('/short', \App\Http\Controllers\Short\CreateShortController::class)
                ->name('user.upload.short');
        });

        Route::prefix('movie')->group(function () {

            // Route::post('/{movie}', \App\Http\Controllers\Movie\UpdateMovieController::class)
            //     ->name('user.movie.update'); // metodo post por que lleva archivos
            Route::get('/{movie}/edit', \App\Http\Controllers\Movie\ShowEditMovieController::class)
                ->name('user.movie.edit');
            Route::put('/{movie}', \App\Http\Controllers\Movie\UpdateMovieController::class)
                ->name('user.movie.update');

            Route::post('/{movie}/publish', \App\Http\Controllers\Movie\PublishMovieController::class)
                ->name('user.movie.publish');
            Route::post('/{movie}/upload-horizontal-image', \App\Http\Controllers\Movie\UploadHorizontalImageMovieController::class)
                ->name('user.movie.upload-horizontal-image');
            Route::post('/{movie}/upload-vertical-image', \App\Http\Controllers\Movie\UploadVerticalImageMovieController::class)
                ->name('user.movie.upload-vertical-image');
            Route::post('/{movie}/upload-movie', \App\Http\Controllers\Movie\UploadMovieController::class)
                ->name('user.movie.upload-movie');
            Route::post('/{movie}/upload-trailer', \App\Http\Controllers\Movie\UploadTrailerMovieController::class)
                ->name('user.movie.upload-trailer');
            Route::delete('/{movie}/delete-horizontal-image', \App\Http\Controllers\Movie\DeleteHorizontalImageMovieController::class)
                ->name('user.movie.delete-horizontal-image');
            Route::delete('/{movie}/delete-vertical-image', \App\Http\Controllers\Movie\DeleteVerticalImageMovieController::class)
                ->name('user.movie.delete-vertical-image');
            Route::delete('/{movie}/delete-trailer', \App\Http\Controllers\Movie\DeleteTrailerMovieController::class)
                ->name('user.movie.delete-trailer');
            Route::delete('/{movie}/delete-movie', \App\Http\Controllers\Movie\DeleteMovieMovieController::class)
                ->name('user.movie.delete-movie');
            Route::delete('/{movie}', \App\Http\Controllers\Movie\DestroyMovieController::class)
                ->name('user.movie.destroy');
        });

        Route::prefix('serie')->group(function () {
            Route::get('/{serie}', \App\Http\Controllers\Serie\ShowSerieController::class)
                ->name('user.serie.show');

            Route::get('/{serie}/edit', \App\Http\Controllers\Serie\ShowEditSerieController::class)
                ->name('user.serie.edit');
            Route::put('/{serie}', \App\Http\Controllers\Serie\UpdateSerieController::class)
                ->name('user.serie.update');
            
            Route::post('/{serie}/upload-horizontal-image', \App\Http\Controllers\Serie\UploadHorizontalImageSerieController::class)
                ->name('user.serie.upload-horizontal-image');
            Route::post('/{serie}/upload-vertical-image', \App\Http\Controllers\Serie\UploadVerticalImageSerieController::class)
                ->name('user.serie.upload-vertical-image');
            Route::post('/{serie}/publish', \App\Http\Controllers\Serie\PublishSerieController::class)
                ->name('user.serie.publish');
            Route::post('/{serie}/upload-trailer', \App\Http\Controllers\Serie\UploadTrailerVideoSerieController::class)
                ->name('user.serie.upload-trailer');
            Route::delete('/{serie}/delete-horizontal-image', \App\Http\Controllers\Serie\DeleteHorizontalImageSerieController::class)
                ->name('user.serie.delete-horizontal-image');
            Route::delete('/{serie}/delete-vertical-image', \App\Http\Controllers\Serie\DeleteVerticalImageSerieController::class)
                ->name('user.serie.delete-vertical-image');
            Route::delete('/{serie}/delete-trailer', \App\Http\Controllers\Serie\DeleteTrailerVideoSerieController::class)
                ->name('user.serie.delete-trailer');
            Route::delete('/{serie}', \App\Http\Controllers\Serie\DestroySerieController::class)
                ->name('user.serie.destroy');
            Route::delete('/{serie}/delete-horizontal-image', \App\Http\Controllers\Serie\DeleteHorizontalImageSerieController::class)
                ->name('user.serie.delete-horizontal-image');
            Route::delete('/{serie}/delete-vertical-image', \App\Http\Controllers\Serie\DeleteVerticalImageSerieController::class)
                ->name('user.serie.delete-vertical-image');
            Route::delete('/{serie}/delete-trailer', \App\Http\Controllers\Serie\DeleteTrailerVideoSerieController::class)
                ->name('user.serie.delete-trailer');
            Route::delete('/{serie}', \App\Http\Controllers\Serie\DestroySerieController::class)
                ->name('user.serie.destroy');

            Route::prefix('{serie}/seasons')->group(function () {
                Route::post('/', \App\Http\Controllers\Serie\StoreSeasonSerieController::class)
                    ->name('user.serie.store-season');
                Route::get('/', \App\Http\Controllers\Serie\GetSeasonSerieController::class)
                    ->name('user.serie.get-season');

                Route::delete('/{season}', \App\Http\Controllers\Serie\DestroySeasonSerieController::class)
                    ->name('user.serie.destroy-season');

                Route::prefix('{season}/chapters')->group(function () {
                    Route::get('/', \App\Http\Controllers\Serie\CreateChapterController::class)
                        ->name('user.serie.create-chapter');

                    Route::post('/{chapter}/publish', \App\Http\Controllers\Serie\PublishChapterController::class)
                        ->name('user.serie.publish-chapter');

                    Route::put('/{chapter}', \App\Http\Controllers\Serie\UpdateChapterController::class)
                        ->name('user.serie.update-chapter');

                    Route::post('/{chapter}/upload-thumbnail', \App\Http\Controllers\Serie\UploadThumbnailChapterController::class)
                        ->name('user.serie.upload-thumbnail');
                    Route::post('/{chapter}/upload-chapter-video', \App\Http\Controllers\Serie\UploadChapterVideoChapterController::class)
                        ->name('user.serie.upload-chapter-video');

                    Route::delete('/{chapter}/delete-thumbnail', \App\Http\Controllers\Serie\DeleteThumbnailChapterController::class)
                        ->name('user.serie.delete-thumbnail');
                    Route::delete('/{chapter}/delete-chapter-video', \App\Http\Controllers\Serie\DeleteChapterVideoChapterController::class)
                        ->name('user.serie.delete-chapter-video');
                    Route::delete('/{chapter}', \App\Http\Controllers\Serie\DestroyChapterController::class)
                        ->name('user.serie.destroy-chapter');
                });
            });
        });

        Route::prefix('short')->group(function () {
            Route::get('/', \App\Http\Controllers\Short\IndexShortController::class)
                ->name('user.short.index');
            Route::get('/{short}', \App\Http\Controllers\Short\ShowShortController::class)
                ->name('user.short.show');

            Route::get('/{short}/edit', \App\Http\Controllers\Short\ShowEditShortController::class)
                ->name('user.short.edit');
            Route::put('/{short}/edit', \App\Http\Controllers\Short\UpdateShortController::class)
                ->name('user.short.update');

            Route::post('/{short}', \App\Http\Controllers\Short\UploadShortController::class)
                ->name('user.short.upload');
            Route::put('/{short}', \App\Http\Controllers\Short\PublishShortController::class)
                ->name('user.short.publish');
            Route::delete('/{short}', \App\Http\Controllers\Short\DeleteShortController::class)
                ->name('user.short.delete');
            Route::delete('/{short}/video', \App\Http\Controllers\Short\DeleteShortVideoController::class)
                ->name('user.short.delete-video');
        });
    });
});

Route::post('subscription/webhook', [SubscriptionController::class, 'webhook'])
    ->name('subscription.webhook');
