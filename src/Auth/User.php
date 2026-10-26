<?php

declare(strict_types=1);

namespace Auth;

use Core\Database;

class User
{
    public int $id;
    public string $uuid;
    public int $role_id;
    public string $name;
    public string $email;
    public string $password_hash;
    public ?string $avatar;
    public ?string $phone;
    public bool $is_active;
    public ?string $email_verified_at;
    public ?string $last_login_at;
    public string $created_at;
    public string $updated_at;

    private ?Role $role = null;
    private ?array $profile = null;

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
        $data = Database::getInstance()->table('users')->find($id);
        return $data ? new self($data) : null;
    }

    public static function findByUuid(string $uuid): ?self
    {
        $data = Database::getInstance()->table('users')->where('uuid', '=', $uuid)->first();
        return $data ? new self($data) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $data = Database::getInstance()->table('users')->where('email', '=', $email)->first();
        return $data ? new self($data) : null;
    }

    public static function create(array $data): self
    {
        $data['uuid'] = $data['uuid'] ?? uuid();
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => config('app.bcrypt_rounds', 12)]);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = Database::getInstance()->table('users')->insertGetId($data);
        $data['id'] = $id;

        return new self($data);
    }

    public function update(array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => config('app.bcrypt_rounds', 12)]);
            unset($data['password']);
        }

        $result = Database::getInstance()->table('users')->where('id', '=', $this->id)->update($data);
        if ($result) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
        return (bool) $result;
    }

    public function delete(): bool
    {
        return Database::getInstance()->table('users')->where('id', '=', $this->id)->delete() > 0;
    }

    public function getRole(): ?Role
    {
        if ($this->role === null) {
            $this->role = Role::find($this->role_id);
        }
        return $this->role;
    }

    public function getProfile(): ?array
    {
        if ($this->profile === null) {
            $this->profile = Database::getInstance()->table('user_profiles')->where('user_id', '=', $this->id)->first();
        }
        return $this->profile;
    }

    public function hasRole(string $role): bool
    {
        $roleObj = $this->getRole();
        return $roleObj?->slug === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        $roleObj = $this->getRole();
        return $roleObj && in_array($roleObj->slug, $roles, true);
    }

    public function can(string $permission): bool
    {
        $role = $this->getRole();
        if (!$role) return false;

        $permissions = $role->getPermissions();
        return in_array($permission, $permissions, true) || in_array('*', $permissions, true);
    }

    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function setLastLogin(): void
    {
        $this->update(['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('uploads/avatars/' . $this->avatar);
        }
        return asset('assets/images/default-avatar.png');
    }

    public function getDisplayName(): string
    {
        $profile = $this->getProfile();
        if ($profile && !empty($profile['nis'])) {
            return $this->name . ' (' . $profile['nis'] . ')';
        }
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'avatar_url' => $this->getAvatarUrl(),
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => $this->getRole()?->toArray(),
            'profile' => $this->getProfile(),
        ];
    }

    public static function paginate(int $perPage = 15, int $page = 1, array $filters = []): array
    {
        $query = Database::getInstance()->table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(['users.*', 'roles.name as role_name', 'roles.slug as role_slug']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->where('roles.slug', '=', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('users.is_active', '=', $filters['is_active']);
        }

        return $query->orderBy('users.created_at', 'desc')->paginate($perPage, $page);
    }
}