<?php

namespace Tests\Feature;

use Dcat\Admin\Models\Administrator;
use Tests\TestCase;

class UsersTest extends TestCase
{
    protected $login = true;

    public function testUsersIndexPage()
    {
        $this->visit('admin/auth/users')
            ->see('Administrator');
    }

    public function testCreateUser()
    {
        $user = [
            'username'              => 'Test',
            'name'                  => 'Name',
            'password'              => '123456',
            'password_confirmation' => '123456',
        ];

        // create user
        $this->visit('admin/auth/users/create')
            ->see('Create')
            ->submitForm('Submit', $user)
            ->seePageIs('admin/auth/users')
            ->seeInDatabase(config('admin.database.users_table'), ['username' => $user['username'], 'name' => $user['name']]);

        // assign role to user
        $this->visit('admin/auth/users/2/edit')
            ->see('Edit')
            ->submitForm('Submit', ['roles' => [1]])
            ->seePageIs('admin/auth/users')
            ->seeInDatabase(config('admin.database.role_users_table'), ['user_id' => 2, 'role_id' => 1]);

        $this->visit('admin/auth/logout')
            ->dontSeeIsAuthenticated('admin')
            ->seePageIs('admin/auth/login')
            ->submitForm('Login', ['username' => $user['username'], 'password' => $user['password']])
            ->see('dashboard')
            ->seeIsAuthenticated('admin')
            ->seePageIs('admin');

        $this->assertTrue($this->app['auth']->guard('admin')->getUser()->isAdministrator());

        $this->see('<span>Users</span>')
            ->see('<span>Roles</span>')
            ->see('<span>Permission</span>')
            ->see('<span>Operation log</span>')
            ->see('<span>Menu</span>');
    }

    public function testUpdateUser()
    {
        $this->visit('admin/auth/users/'.$this->user->id.'/edit')
            ->see('Edit')
            ->submitForm('Submit', ['name' => 'test', 'roles' => [1]])
            ->seePageIs('admin/auth/users')
            ->seeInDatabase(config('admin.database.users_table'), ['name' => 'test']);
    }

    public function testResetPassword()
    {
        $password = 'odjwyufkglte';

        $data = [
            'password'              => $password,
            'password_confirmation' => $password,
            'roles'                 => [1],
        ];

        $this->visit('admin/auth/users/'.$this->user->id.'/edit')
            ->see('Edit')
            ->submitForm('Submit', $data)
            ->seePageIs('admin/auth/users')
            ->visit('admin/auth/logout')
            ->dontSeeIsAuthenticated('admin')
            ->seePageIs('admin/auth/login')
            ->submitForm('Login', ['username' => $this->user->username, 'password' => $password])
            ->see('dashboard')
            ->seeIsAuthenticated('admin')
            ->seePageIs('admin');
    }
}