<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use GuzzleHttp\Client;
// use App\Http\Controllers\LineRichMenuController;
// Admin Controllers
use App\Http\Controllers\Admin\{
    AuthController as AdminAuthController,
    AdminController,
    UserController,
    ProductController,
    OrderController,
    PromotionController,
    PromotionBookingController as AdminPromotionBookingController,
    DashboardController as AdminDashboardController,
    NewsController,
    GalleryController,
    ReportController,
    CategoryController,
    LineRichMenuController,
    LineWebhookController
};

// User Controllers
use App\Http\Controllers\User\{
    AuthController as UserAuthController,
    SocialController,
    DashboardController,
    ProfileController,
    BookingController as UserBookingController,
    ShopController,
    PromotionController as UserPromotionController,
    PageController,
    PromotionBookingController,
    CartController, 
    UserOrderController,
    UsernewsController as UserNewsController,
    HomeController,
    UserGalleryController
};

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/line/webhook', [LineWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function() {
    // Guest Routes
    Route::middleware('guest:admin')->group(function() {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    // Protected Admin Routes
    Route::middleware('auth:admin')->group(function() {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Resources
        Route::resources([
            'users' => UserController::class,
            'products' => ProductController::class,
            'orders' => OrderController::class,
            'promotions' => PromotionController::class,
            'news' => NewsController::class,
            'gallery' => GalleryController::class,
        ]);
      Route::get('line-richmenu', [LineRichMenuController::class, 'index'])->name('line-richmenu.index');
Route::get('line-richmenu/create', [LineRichMenuController::class, 'create'])->name('line-richmenu.create');
Route::post('line-richmenu/create', [LineRichMenuController::class, 'create'])->name('line-richmenu.create.post');
Route::post('line-richmenu/set-default/{richMenuId}', [LineRichMenuController::class, 'setDefault'])->name('line-richmenu.set-default');
Route::delete('line-richmenu/delete/{richMenuId}', [LineRichMenuController::class, 'delete'])->name('line-richmenu.delete');

// Proxy รูป Rich Menu จาก LINE API
Route::get('line-richmenu/image/{richMenuId}', function($richMenuId) {
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->get("https://api-data.line.me/v2/bot/richmenu/{$richMenuId}/content", [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
            ]
        ]);
        return Response::make($response->getBody(), 200, [
            'Content-Type' => $response->getHeaderLine('Content-Type')
        ]);
    } catch (\Exception $e) {
        return response('Error: ' . $e->getMessage(), 500);
    }
})->name('line-richmenu.image');
Route::post('line-richmenu/set-default/{richMenuId}', [LineRichMenuController::class, 'setDefault'])->name('line-richmenu.set-default');
Route::delete('line-richmenu/delete/{richMenuId}', [LineRichMenuController::class, 'delete'])->name('line-richmenu.delete');
Route::post('line-richmenu/create', [LineRichMenuController::class, 'create'])->name('line-richmenu.create.post');
// Route::post(uri: '/line/webhook', [LineWebhookController::class, 'handle']);
// Orders
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

        // Products
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::resource('categories', CategoryController::class);

        // AJAX categories
            Route::post('api/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        // Promotion Bookings
        Route::resource('promotion-bookings', AdminPromotionBookingController::class)->parameters([
            'promotion-bookings' => 'booking'
        ]);
        Route::patch('promotion-bookings/{booking}/payment', [AdminPromotionBookingController::class, 'updatePaymentStatus'])->name('promotion-bookings.update-payment');
        Route::get('promotion-bookings/{booking}/payment-slip', [AdminPromotionBookingController::class, 'downloadPaymentSlip'])->name('promotion-bookings.download-slip');
        Route::patch('promotion-bookings/{booking}/update-status', [AdminPromotionBookingController::class, 'updateStatus'])->name('promotion-bookings.update-status');
        Route::patch('promotion-bookings/{booking}/update-payment-status', [AdminPromotionBookingController::class, 'updatePaymentStatus'])->name('promotion-bookings.update-payment-status');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function() {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
            Route::get('/promotions', [ReportController::class, 'promotions'])->name('promotions');
            Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
            Route::get('/sales-report', [ReportController::class, 'salesReport'])->name('sales-report');
        });

        // Admin Profile & Settings
        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Logout
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function() {
        // Authentication
        Route::get('login', [UserAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [UserAuthController::class, 'login'])->name('login.submit');
        Route::get('register', [UserAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [UserAuthController::class, 'register'])->name('register.submit');

        // Password Reset
        Route::get('forgot-password', [UserAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [UserAuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [UserAuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->name('password.update');

        // Social Login
        Route::get('auth/google', [SocialController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('auth/google/callback', [SocialController::class, 'handleGoogleCallback'])->name('auth.google.callback');
        
        
    });
Route::post('user/social/disconnect/{provider}', [SocialController::class, 'disconnectSocial'])
    ->name('user.social.disconnect');
    Route::get('/user/auth/line/callback', [SocialController::class, 'handleLineCallback'])->name('user.social.callback.line');
    
    // Protected Routes
    Route::middleware('auth')->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/bookings', [DashboardController::class, 'bookings'])->name('dashboard.bookings');
        Route::get('dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');

        // Profile Management
        Route::prefix('profile')->name('profile.')->group(function() {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
            Route::get('/social', [ProfileController::class, 'social'])->name('social');
        });

        // Social Account Management
        Route::prefix('social')->name('social.')->group(function() {
            Route::post('connect/{provider}', [SocialController::class, 'connect'])->name('connect');
            Route::delete('disconnect/{provider}', [SocialController::class, 'disconnect'])->name('disconnect');
            Route::get('connect/line', [ProfileController::class, 'connectLine'])->name('connect.line');
            Route::get('callback/line', [ProfileController::class, 'handleLineCallback'])->name('callback.line');
            Route::post('user/social/disconnect/{provider}', [SocialController::class, 'disconnectSocial'])
    ->name('user.social.disconnect');
        });


        // Bookings
        Route::resource('bookings', UserBookingController::class);
        Route::patch('bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');

        // Shop
        Route::prefix('shop')->name('shop.')->group(function() {
            Route::get('/', [ShopController::class, 'index'])->name('index');
            Route::get('/product/{product}', [ShopController::class, 'show'])->name('product');
            Route::get('/cart', [CartController::class, 'index'])->name('cart');
            Route::post('/cart/add/{product}', [CartController::class, 'addToCart'])->name('add-to-cart');
            Route::patch('/cart/update', [CartController::class, 'updateCart'])->name('update-cart');
            Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart'])->name('cart.remove');
            Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('clear-cart');
            Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');
            Route::post('/checkout', [ShopController::class, 'processCheckout'])->name('process-checkout');
            Route::get('/orders', [ShopController::class, 'orders'])->name('orders');
            Route::get('/orders/{order}', [ShopController::class, 'showOrder'])->name('orders.show');
            Route::get('/orders/{order}/print', [ShopController::class, 'printOrder'])->name('orders.print');
            Route::get('/orders/{order}/invoice', [ShopController::class, 'downloadInvoice'])->name('orders.invoice');
            Route::get('/orders/{order}/receipt', [ShopController::class, 'downloadReceipt'])->name('orders.receipt');
            Route::get('/orders/{order}/quotation', [ShopController::class, 'downloadQuotation'])->name('orders.quotation');
            Route::get('/orders/{order}/payment-slip', [ShopController::class, 'downloadPaymentSlip'])->name('orders.payment-slip');
            Route::post('/orders/{order}/payment', [ShopController::class, 'uploadPaymentSlip'])->name('orders.upload-payment');
        });

        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [UserOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [UserOrderController::class, 'show'])->name('show');
            Route::post('/{order}/upload-payment', [UserOrderController::class, 'uploadPayment'])->name('upload-payment');
            Route::get('/{order}/quotation', [UserOrderController::class, 'downloadQuotation'])->name('quotation');
        });

        // Promotions & Bookings
        Route::prefix('promotions')->name('promotions.')->group(function() {
            Route::get('/', [UserPromotionController::class, 'index'])->name('index');
            Route::get('/{promotion}', [UserPromotionController::class, 'show'])->name('show');
        });

        // Promotion Bookings
        Route::prefix('promotion-bookings')->name('promotion-bookings.')->group(function() {
            Route::get('/', [PromotionBookingController::class, 'index'])->name('index');
            Route::get('/create/{promotion}', [PromotionBookingController::class, 'create'])->name('create');
            Route::post('/{promotion}', [PromotionBookingController::class, 'store'])->name('store');
            Route::get('/{booking}', [PromotionBookingController::class, 'show'])->name('show');
            Route::post('/{booking}/payment', [PromotionBookingController::class, 'uploadPaymentSlip'])->name('upload-payment');
            Route::get('/{booking}/quotation', [PromotionBookingController::class, 'downloadQuotation'])->name('quotation');
        });

        // ข่าวสารสำหรับ user
        Route::get('news', [UserNewsController::class, 'index'])->name('news.index');
        Route::get('news/{news}', [UserNewsController::class, 'show'])->name('news.show');

        // Logout
        Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
    });

    // Static Pages (public)
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('about', [PageController::class, 'about'])->name('about');
        Route::get('contact', [PageController::class, 'contact'])->name('contact');
        Route::post('contact', [PageController::class, 'sendContact'])->name('contact.send');
        Route::get('privacy-policy', [PageController::class, 'privacy'])->name('privacy');
        Route::get('terms-of-service', [PageController::class, 'terms'])->name('terms');
        Route::get('faq', [PageController::class, 'faq'])->name('faq');
    });

    // Public Routes
    Route::get('menu', [PageController::class, 'menu'])->name('menu');
    Route::get('gallery', [PageController::class, 'gallery'])->name('gallery');
    Route::get('main', [HomeController::class, 'index'])->name('main');
    Route::get('gallery', [UserGalleryController::class, 'index'])->name('gallery.index');
    Route::get('news', [UserNewsController::class, 'index'])->name('news.index');
    Route::get('news/{news}', [UserNewsController::class, 'show'])->name('news.show');
});

/*
|--------------------------------------------------------------------------
| Debug Routes (Only in Debug Mode)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::get('/debug-google', function() {
        dd([
            'env_client_id' => env('GOOGLE_CLIENT_ID'),
            'env_client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'env_redirect' => env('GOOGLE_REDIRECT_URI'),
            'config_client_id' => config('services.google.client_id'),
            'config_client_secret' => config('services.google.client_secret'),
            'config_redirect' => config('services.google.redirect')
        ]);
    });

    Route::get('/check-config', function() {
        Artisan::call('config:clear');
        $config = config('services.google');
        dd([
            'config' => $config,
            'exists' => config()->has('services.google'),
            'provider_config' => config('services')
        ]);
    });
}