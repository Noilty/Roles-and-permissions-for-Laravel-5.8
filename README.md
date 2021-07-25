# Roles-and-permissions-for-laravel-5.8
## Подключите трейт к модели User
* use Notifiable, HasRolesAndPermissions;
## Seeds
* Настройте Seeds и подключить их (DatabaseSeeder)
## Provider
* Подключите провайдеры (config/app.php)
    * \App\Providers\RolesServiceProvider::class,
    * \App\Providers\PermissionServiceProvider::class,
## Middleware
* Подключите мидлвар (App\Http\Kernel.php в protected $routeMiddleware)
  * 'role'  =>  \App\Http\Middleware\RoleMiddleware::class,
