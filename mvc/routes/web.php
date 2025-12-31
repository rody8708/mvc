<?php 
// Inicializar el Router
use App\Core\Router;

$router = new Router();

// Definir rutas antes de despachar la solicitud
$router->addRoute('GET', '/', 'HomeController@index');

$router->addRoute('GET', '/logs', 'LogsController@index');
$router->addRoute('GET', '/logs/fetch', 'LogsController@fetchPage');
$router->addRoute('POST', '/logs/fetch', 'LogsController@fetchPage');



$router->addRoute('GET', '/auth/register', 'AuthController@registerForm');
$router->addRoute('POST', '/auth/register', 'AuthController@register');

$router->addRoute('GET', '/activar', 'AuthController@activate');

$router->addRoute('GET', '/auth/login', 'AuthController@loginForm');
$router->addRoute('POST', '/auth/login', 'AuthController@login');
$router->addRoute('GET', '/auth/logout', 'AuthController@logout');




$router->addRoute('GET', '/admin/users', 'AdminController@manageUsers');
$router->addRoute('POST', '/admin/change-role', 'AdminController@changeRole');
$router->addRoute('POST', '/admin/delete-user', 'AdminController@deleteUser');
$router->addRoute('POST', '/admin/create-user', 'AdminController@createUser');
$router->addRoute('POST', '/admin/update-user', 'AdminController@updateUser');


//$router->addRoute('GET', '/password/request', 'PasswordResetController@requestForm');
//$router->addRoute('POST', '/password/request', 'PasswordResetController@handleRequest');
//$router->addRoute('GET', '/password/reset', 'PasswordResetController@resetForm');
//$router->addRoute('POST', '/password/reset', 'PasswordResetController@handleReset');

$router->addRoute('GET', '/auth/forgot-password', 'AuthController@forgotPasswordForm');
$router->addRoute('POST', '/auth/send-reset-link', 'AuthController@sendResetLink');
$router->addRoute('GET', '/auth/reset-password-form', 'AuthController@resetPasswordForm');
$router->addRoute('POST', '/auth/reset-password', 'AuthController@resetPassword');



$router->addRoute('GET', '/admin/crud-generator', 'ToolController@crudGeneratorView');
$router->addRoute('POST', '/admin/generate-module', 'ToolController@generateCrud');
$router->addRoute('POST', '/admin/delete-module', 'ToolController@deleteModule');
$router->addRoute('GET', '/admin/get-modules', 'ToolController@getModules');




$router->addRoute('GET', '/profile', 'ProfileController@index');
$router->addRoute('POST', '/profile/update', 'ProfileController@updateProfile');
$router->addRoute('POST', '/profile/change-password', 'ProfileController@changePassword');
$router->addRoute('POST', '/profile/toggle-dark-mode', 'ProfileController@toggleDarkMode');
$router->addRoute('POST', '/profile/upload-avatar', 'ProfileController@uploadAvatar');
$router->addRoute('POST', '/profile/change-language', 'ProfileController@changeLanguage');
$router->addRoute('POST', '/profile/delete-account', 'ProfileController@deleteAccount');



$router->addRoute('POST', '/notifications/fetch', 'NotificationController@fetch');
$router->addRoute('POST', '/notifications/mark-as-read', 'NotificationController@markAsRead');
$router->addRoute('POST', '/notifications/mark-all-read', 'NotificationController@markAllAsRead');


$router->addRoute('GET', '/layouts/contact', 'HomeController@contact');
$router->addRoute('GET', '/layouts/privacy', 'HomeController@privacy');
$router->addRoute('GET', '/layouts/help', 'HomeController@help');








// Cargar rutas de todos los módulos generados
foreach (glob(__DIR__ . '/modules/*.php') as $moduleRoute) {
    require_once $moduleRoute;
}


// Depuración: Verificar si la ruta '/' está registrada
//var_dump($router);
//die();

// Ejecutar el enrutador
$router->dispatch($_SERVER['REQUEST_URI']);


?>
