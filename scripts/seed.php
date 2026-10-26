<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\App;
use Core\Config;
use Core\Database;
use Auth\User;
use Auth\Role;

// Bootstrap
$app = new App();
Config::getInstance($app->basePath('.env'));

$db = Database::getInstance();

echo "Running seeders...\n\n";

// Seed Roles
echo "Seeding roles...\n";
$rolesData = require $app->basePath('database/seeds/001_roles.php');
foreach ($rolesData as $roleData) {
    $existing = $db->table('roles')->where('slug', '=', $roleData['slug'])->first();
    if (!$existing) {
        $db->table('roles')->insert($roleData);
        echo "  Created role: {$roleData['name']}\n";
    } else {
        echo "  Role {$roleData['name']} already exists\n";
    }
}

// Seed Admin User
echo "\nSeeding admin user...\n";
$adminData = require $app->basePath('database/seeds/002_admin_user.php');
$role = Role::findBySlug($adminData['role']);
if ($role) {
    $existing = User::findByEmail($adminData['email']);
    if (!$existing) {
        $adminData['role_id'] = $role->id;
        unset($adminData['role']);
        User::create($adminData);
        echo "  Created admin user: {$adminData['email']}\n";
    } else {
        echo "  Admin user already exists\n";
    }
}

// Seed Demo Data
echo "\nSeeding demo data...\n";
$demoData = require $app->basePath('database/seeds/003_demo_data.php');

if (isset($demoData['academic_years'])) {
    foreach ($demoData['academic_years'] as $data) {
        $existing = $db->table('academic_years')->where('name', '=', $data['name'])->first();
        if (!$existing) {
            $db->table('academic_years')->insert($data);
            echo "  Created academic year: {$data['name']}\n";
        }
    }
}

if (isset($demoData['semesters'])) {
    $activeYear = $db->table('academic_years')->where('is_active', '=', 1)->first();
    if ($activeYear) {
        foreach ($demoData['semesters'] as $data) {
            $data['academic_year_id'] = $activeYear['id'];
            $existing = $db->table('semesters')
                ->where('academic_year_id', '=', $activeYear['id'])
                ->where('name', '=', $data['name'])
                ->first();
            if (!$existing) {
                $db->table('semesters')->insert($data);
                echo "  Created semester: {$data['name']}\n";
            }
        }
    }
}

if (isset($demoData['subjects'])) {
    foreach ($demoData['subjects'] as $data) {
        $existing = $db->table('subjects')->where('code', '=', $data['code'])->first();
        if (!$existing) {
            $db->table('subjects')->insert($data);
            echo "  Created subject: {$data['name']}\n";
        }
    }
}

echo "\nSeeding completed!\n";