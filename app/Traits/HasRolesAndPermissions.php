<?php
namespace App\Traits;

use App\Role;
use App\Permission;

trait HasRolesAndPermissions
{
    /**
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    /**
     * Чтобы проверить, есть ли у текущего залогиненного Пользователя Роль,
     * мы добавим новую функцию в трейт HasRolesAndPermissions
     *
     * @param mixed ...$roles
     * @return bool
     */
    public function hasRole(...$roles) {
        foreach ($roles as $role) {
            if ($this->roles->contains('slug', $role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Для проверки прав доступа текущего пользователя,
     * мы добавим два нижеприведенных метода в наш трейт
     *
     * @param $permission
     * @return bool
     */
    protected function hasPermission($permission)
    {
        return (bool) $this->permissions->where('slug', $permission->slug)->count();
    }

    /**
     * Метод проверяет, содержат ли права пользователя заданное право,
     * если да, то тогда он вернет true, а иначе false
     *
     * Эта функция проверяет, привязана ли Роль с Правами к Пользователю
     *
     * Теперь у нас есть метод, который будет проверять,
     * есть ли у Пользователя Права напрямую или через Роль
     *
     * @param $permission
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        //return $this->hasPermission($permission);
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    /**
     * Как мы знаем, у нас между Ролями и Правами есть отношение «Многие ко Многим».
     * Это позволяет нам проверять, есть ли у Пользователя Права через его Роль
     *
     * @param $permission
     * @return bool
     */
    public function hasPermissionThroughRole($permission)
    {
        foreach ($permission->roles as $role){
            if($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    # Выдача Прав (permissions) пользователю (users_permissions)

    /**
     * Метод получает все Права на основе переданного массива
     *
     * @param array $permissions
     * @return mixed
     */
    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug', $permissions)->get();
    }

    /**
     * Мы передаем Права в виде массива и получаем все
     * Права из базы данных на основе массива
     *
     * @param mixed ...$permissions
     * @return $this
     */
    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);

        if ($permissions === null) {
            return $this;
        }

        $this->permissions()->saveMany($permissions);

        return $this;
    }

    # Выдача Ролей (Roles) пользователю (users_roles)
    protected function getAllRoles($roles)
    {
        return Role::whereIn('slug', $roles)->get();
    }

    public function giveRolesTo(...$roles)
    {
        $roles = $this->getAllRoles($roles);

        if ($roles === null) {
            return $this;
        }

        $this->roles()->saveMany($roles);

        return $this;
    }

    # Удаление Прав

    /**
     * Чтобы удалить Права Пользователя,
     * мы передаем Права методу deletePermissions() и
     * удаляем все прикрепленные Права с помощью метода detach()
     *
     * @param mixed ...$permissions
     * @return $this
     */
    public function deletePermissions(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);

        return $this;
    }

    /**
     * Метод фактически удаляет все Права Пользователя,
     * а затем переназначает предоставленные для него Права
     *
     * @param mixed ...$permissions
     * @return HasRolesAndPermissions
     */
    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionsTo($permissions);
    }
}
