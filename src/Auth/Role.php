<?php

declare(strict_types=1);

namespace Auth;

use Core\Database;

class Role
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $permissions;
    public string $created_at;

    private array $permissionsArray = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function find(int $id): ?self
    {
        $data = Database::getInstance()->table('roles')->find($id);
        return $data ? new self($data) : null;
    }

    public static function findBySlug(string $slug): ?self
    {
        $data = Database::getInstance()->table('roles')->where('slug', '=', $slug)->first();
        return $data ? new self($data) : null;
    }

    public static function all(): array
    {
        $results = Database::getInstance()->table('roles')->orderBy('id')->get();
        return array_map(fn($data) => new self($data), $results);
    }

    public static function create(array $data): self
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['permissions'] = $data['permissions'] ?? json_encode([]);
        $id = Database::getInstance()->table('roles')->insertGetId($data);
        $data['id'] = $id;
        return new self($data);
    }

    public function update(array $data): bool
    {
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions'], JSON_THROW_ON_ERROR);
        }
        $result = Database::getInstance()->table('roles')->where('id', '=', $this->id)->update($data);
        if ($result) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            $this->permissionsArray = [];
        }
        return (bool) $result;
    }

    public function delete(): bool
    {
        return Database::getInstance()->table('roles')->where('id', '=', $this->id)->delete() > 0;
    }

    public function getPermissions(): array
    {
        if (empty($this->permissionsArray)) {
            $this->permissionsArray = json_decode($this->permissions, true) ?? [];
        }
        return $this->permissionsArray;
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = json_encode($permissions, JSON_THROW_ON_ERROR);
        $this->permissionsArray = $permissions;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getPermissions();
        return in_array($permission, $permissions, true) || in_array('*', $permissions, true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'permissions' => $this->getPermissions(),
            'created_at' => $this->created_at,
        ];
    }

    public static function getDefaultPermissions(string $slug): array
    {
        return match ($slug) {
            'admin' => ['*'],
            'teacher' => [
                'questions.view',
                'questions.create',
                'questions.edit',
                'questions.delete',
                'questions.import',
                'exams.view',
                'exams.create',
                'exams.edit',
                'exams.delete',
                'exams.publish',
                'exams.duplicate',
                'grading.view',
                'grading.essay',
                'grading.finalize',
                'reports.view',
                'reports.export',
                'classes.view',
                'students.view',
            ],
            'student' => [
                'exams.view',
                'exams.take',
                'results.view',
                'profile.edit',
            ],
            default => [],
        };
    }
}