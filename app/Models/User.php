<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ROOM_PIC = 'room_pic';

    /** Branch Admin: manages exactly one office/branch (DEC-061). */
    public const ROLE_ADMIN = 'admin';

    /** Central Admin across every office/branch. */
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLES = [self::ROLE_USER, self::ROLE_ROOM_PIC, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN];

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /** Admin or Super Admin, by role only (no active-status check). */
    public function isAdministrator(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    /** Roles this account may assign when creating or editing users. */
    public function assignableRoles(): array
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => self::ROLES,
            self::ROLE_ADMIN => [self::ROLE_USER, self::ROLE_ROOM_PIC],
            default => [],
        };
    }

    /** The single branch managed by a Branch Admin. */
    public function managedBranchId(): ?int
    {
        if ($this->role !== self::ROLE_ADMIN) {
            return null;
        }
        $id = $this->branches()->orderBy('branches.id')->value('branches.id');

        return $id === null ? null : (int) $id;
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_USER => 'User',
            self::ROLE_ROOM_PIC => 'Room PIC',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPER_ADMIN => 'Super Admin',
        ];
    }

    public function roleLabel(): string
    {
        return self::roleOptions()[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_USER => 'dashboard',
            self::ROLE_ROOM_PIC => 'pic.dashboard',
            self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN => 'admin.dashboard',
            default => throw new AuthorizationException,
        };
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function managedRooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_pics');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    public function canAccessBranch(int $branchId): bool
    {
        return $this->is_active && ($this->role === self::ROLE_SUPER_ADMIN
            || (in_array($this->role, [self::ROLE_USER, self::ROLE_ROOM_PIC, self::ROLE_ADMIN], true)
                && $this->branches()->whereKey($branchId)->exists()));
    }

    public function notificationRecipients(): HasMany
    {
        return $this->hasMany(WhatsappRecipient::class);
    }

    public function pendingApprovalsCount(): int
    {
        if (! $this->can('access-pic')) {
            return 0;
        }

        return $this->pendingApprovalsQuery()->count();
    }

    /** Pending bookings this PIC/Admin may decide; same scope as the approval queue. */
    public function pendingApprovalsQuery(): Builder
    {
        return Booking::query()->whereIn('room_id', Room::query()->visibleTo($this)->select('rooms.id'))
            ->where('requires_approval', true)
            ->where('status', 'pending')
            ->when(! $this->can('access-admin'), fn ($query) => $query
                ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $this->id)));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
