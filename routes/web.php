<?php
/**
 * Web Routes
 * HoanKiem LAB - Dental Lab Management System
 */

// ============================================
// Middleware Registration
// ============================================

$router->registerMiddleware('auth', function () {
    if (!Auth::getInstance()->check()) {
        header('Location: ' . url('/login'));
        exit;
    }
});

$router->registerMiddleware('role', function ($role) {
    $auth = Auth::getInstance();
    if (!$auth->check()) {
        header('Location: ' . url('/login'));
        exit;
    }
    if (!$auth->hasRole($role)) {
        http_response_code(403);
        require dirname(__DIR__) . '/views/errors/403.php';
        exit;
    }
});

$router->registerMiddleware('guest', function () {
    if (Auth::getInstance()->check()) {
        header('Location: ' . url(Auth::getInstance()->redirectPath()));
        exit;
    }
});

// ============================================
// Public Routes
// ============================================

// Root redirect
$router->get('/', function () {
    $auth = Auth::getInstance();
    if ($auth->check()) {
        header('Location: ' . url($auth->redirectPath()));
    } else {
        header('Location: ' . url('/login'));
    }
    exit;
});

// Auth Routes
$router->get('/login',  'Auth/LoginController@showLogin');
$router->post('/login', 'Auth/LoginController@login');
$router->post('/logout', 'Auth/LoginController@logout');
$router->get('/logout',  'Auth/LoginController@logout');

// Due date change (no login required)
$router->get('/order/change-due-date/{token}', 'Public/DueDateController@show');
$router->post('/order/change-due-date/{token}', 'Public/DueDateController@update');

// ============================================
// Customer Portal (/customer/*)
// ============================================

$router->group('/customer', ['auth', 'role:customer'], function ($router) {
    $router->get('/dashboard', 'Customer/DashboardController@index');
    $router->get('/orders',    'Customer/OrderController@index');
    $router->get('/orders/{id}', 'Customer/OrderController@show');
    $router->post('/orders/{id}/feedback', 'Customer/OrderController@feedback');
    $router->post('/orders/{id}/confirm', 'Customer/OrderController@confirm');
    $router->post('/orders/{id}/return',  'Customer/OrderController@requestReturn');
    $router->get('/notifications',         'Customer/NotificationController@index');
    $router->post('/notifications/read-all', 'Customer/NotificationController@readAll');
    $router->post('/notifications/{id}/read', 'Customer/NotificationController@markRead');
    $router->get('/profile',               'Customer/ProfileController@show');
    $router->post('/profile',              'Customer/ProfileController@update');
    $router->post('/profile/password',     'Customer/ProfileController@changePassword');
    $router->post('/profile/avatar',       'Customer/ProfileController@uploadAvatar');
});

// ============================================
// Staff Portal (/staff/*)
// ============================================

$router->group('/staff', ['auth', 'role:staff'], function ($router) {
    $router->get('/dashboard', 'Staff/DashboardController@index');
    $router->get('/production', 'Staff/ProductionController@index');
    $router->get('/production/{stepId}', 'Staff/ProductionController@show');
    $router->post('/production/{stepId}/start',    'Staff/ProductionController@start');
    $router->post('/production/{stepId}/complete', 'Staff/ProductionController@complete');
    $router->post('/production/{stepId}/rework',   'Staff/ProductionController@rework');
    $router->get('/appointments',  'Staff/AppointmentController@index');
    $router->get('/customers',     'Staff/CustomerInfoController@index');
    $router->get('/customers/{id}','Staff/CustomerInfoController@show');
    $router->get('/notifications', 'Staff/NotificationController@index');
    $router->post('/notifications/read-all', 'Staff/NotificationController@readAll');
    $router->get('/profile',       'Staff/ProfileController@show');
    $router->post('/profile',      'Staff/ProfileController@update');
    $router->post('/profile/password', 'Staff/ProfileController@changePassword');
});

// ============================================
// Shipper Portal (/shipper/*)
// ============================================

$router->group('/shipper', ['auth', 'role:shipper'], function ($router) {
    $router->get('/dashboard', 'Shipper/DashboardController@index');
    $router->get('/deliveries/{id}', 'Shipper/DashboardController@show');
    $router->post('/deliveries/{id}/accept', 'Shipper/DashboardController@accept');
    $router->post('/deliveries/{id}/location', 'Shipper/DashboardController@updateLocation');
    $router->post('/deliveries/{id}/delivered', 'Shipper/DashboardController@markDelivered');
});

// ============================================
// Admin Portal (/admin/*)
// ============================================

$router->group('/admin', ['auth', 'role:admin'], function ($router) {

    // Dashboard
    $router->get('/dashboard', 'Admin/DashboardController@index');

    // Orders
    $router->get('/orders',              'Admin/OrderController@index');
    $router->get('/orders/create',       'Admin/OrderController@create');
    $router->post('/orders/create',      'Admin/OrderController@store');
    $router->get('/orders/{id}',         'Admin/OrderController@show');
    $router->get('/orders/{id}/edit',    'Admin/OrderController@edit');
    $router->post('/orders/{id}/edit',   'Admin/OrderController@update');
    $router->post('/orders/{id}/confirm','Admin/OrderController@confirm');
    $router->post('/orders/{id}/cancel', 'Admin/OrderController@cancel');
    $router->post('/orders/{id}/delivery-status', 'Admin/OrderController@updateDeliveryStatus');
    $router->post('/orders/{id}/approve-return',  'Admin/OrderController@approveReturn');

    // Users - Customers
    $router->get('/customers',           'Admin/UserController@customers');
    $router->get('/customers/create',    'Admin/UserController@createCustomer');
    $router->post('/customers/create',   'Admin/UserController@storeCustomer');
    $router->get('/customers/{id}',      'Admin/UserController@showCustomer');
    $router->get('/customers/{id}/edit', 'Admin/UserController@editCustomer');
    $router->post('/customers/{id}/edit','Admin/UserController@updateCustomer');
    $router->post('/customers/{id}/toggle','Admin/UserController@toggleCustomer');

    // Users - Staff
    $router->get('/staff',           'Admin/UserController@staff');
    $router->get('/staff/create',    'Admin/UserController@createStaff');
    $router->post('/staff/create',   'Admin/UserController@storeStaff');
    $router->get('/staff/{id}',      'Admin/UserController@showStaff');
    $router->get('/staff/{id}/edit', 'Admin/UserController@editStaff');
    $router->post('/staff/{id}/edit','Admin/UserController@updateStaff');
    $router->post('/staff/{id}/toggle','Admin/UserController@toggleStaff');

    // Production management
    $router->get('/production',          'Admin/ProductionController@index');
    $router->get('/production/{orderId}','Admin/ProductionController@show');
    $router->post('/production/{stepId}/assign', 'Admin/ProductionController@assign');
    $router->post('/production/{stepId}/qc-pass','Admin/ProductionController@qcPass');

    // Materials
    $router->get('/materials',          'Admin/MaterialController@index');
    $router->get('/materials/create',   'Admin/MaterialController@create');
    $router->post('/materials/create',  'Admin/MaterialController@store');
    $router->get('/materials/{id}/edit','Admin/MaterialController@edit');
    $router->post('/materials/{id}/edit','Admin/MaterialController@update');
    $router->post('/materials/{id}/transaction','Admin/MaterialController@transaction');

    // Deliveries
    $router->get('/deliveries',          'Admin/DeliveryController@index');
    $router->get('/deliveries/{id}',     'Admin/DeliveryController@show');
    $router->post('/deliveries/{id}/update', 'Admin/DeliveryController@update');

    // Appointments
    $router->get('/appointments',        'Admin/AppointmentController@index');
    $router->get('/appointments/create', 'Admin/AppointmentController@create');
    $router->post('/appointments/create','Admin/AppointmentController@store');
    $router->get('/appointments/{id}/edit','Admin/AppointmentController@edit');
    $router->post('/appointments/{id}/edit','Admin/AppointmentController@update');
    $router->post('/appointments/{id}/delete','Admin/AppointmentController@destroy');

    // Revenue
    $router->get('/revenue',             'Admin/RevenueController@index');
    $router->get('/revenue/by-customer', 'Admin/RevenueController@byCustomer');

    // Payments
    $router->post('/payments/create',    'Admin/PaymentController@store');
    $router->post('/payments/{id}/confirm','Admin/PaymentController@confirm');

    // Articles
    $router->get('/articles',            'Admin/ArticleController@index');
    $router->get('/articles/create',     'Admin/ArticleController@create');
    $router->post('/articles/create',    'Admin/ArticleController@store');
    $router->get('/articles/{id}/edit',  'Admin/ArticleController@edit');
    $router->post('/articles/{id}/edit', 'Admin/ArticleController@update');
    $router->post('/articles/{id}/delete','Admin/ArticleController@destroy');

    // Promotions
    $router->get('/promotions',           'Admin/PromotionController@index');
    $router->get('/promotions/create',    'Admin/PromotionController@create');
    $router->post('/promotions/create',   'Admin/PromotionController@store');
    $router->get('/promotions/{id}/edit', 'Admin/PromotionController@edit');
    $router->post('/promotions/{id}/edit','Admin/PromotionController@update');
    $router->post('/promotions/{id}/delete','Admin/PromotionController@destroy');

    // Branches
    $router->get('/branches',           'Admin/BranchController@index');
    $router->get('/branches/create',    'Admin/BranchController@create');
    $router->post('/branches/create',   'Admin/BranchController@store');
    $router->get('/branches/{id}/edit', 'Admin/BranchController@edit');
    $router->post('/branches/{id}/edit','Admin/BranchController@update');

    // Feedbacks
    $router->get('/feedbacks',           'Admin/FeedbackController@index');
    $router->get('/feedbacks/{id}',      'Admin/FeedbackController@show');
    $router->post('/feedbacks/{id}/reply','Admin/FeedbackController@reply');

    // Notifications
    $router->get('/notifications',        'Admin/NotificationController@index');
    $router->post('/notifications/send',  'Admin/NotificationController@send');
    $router->post('/notifications/read-all', 'Admin/NotificationController@readAll');

    // Settings
    $router->get('/settings',            'Admin/SettingController@index');
    $router->post('/settings',           'Admin/SettingController@update');

    // Product Types
    $router->get('/product-types',       'Admin/ProductTypeController@index');
    $router->get('/product-types/create','Admin/ProductTypeController@create');
    $router->post('/product-types/create','Admin/ProductTypeController@store');
    $router->get('/product-types/{id}/edit','Admin/ProductTypeController@edit');
    $router->post('/product-types/{id}/edit','Admin/ProductTypeController@update');

    // Profile
    $router->get('/profile',             'Admin/ProfileController@show');
    $router->post('/profile',            'Admin/ProfileController@update');
    $router->post('/profile/password',   'Admin/ProfileController@changePassword');
});

// ============================================
// AJAX / API endpoints
// ============================================

$router->group('/api', ['auth'], function ($router) {
    $router->get('/notifications/count', function () {
        $auth = Auth::getInstance();
        $db = Database::getInstance();
        $count = $db->count('notifications', 'user_id = ? AND is_read = 0', [$auth->id()]);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    });

    $router->get('/orders/{id}/steps', function ($id) {
        $db = Database::getInstance();
        $steps = $db->fetchAll("SELECT * FROM production_steps WHERE order_id = ? ORDER BY step_number", [(int)$id]);
        header('Content-Type: application/json');
        echo json_encode($steps);
        exit;
    });
});
