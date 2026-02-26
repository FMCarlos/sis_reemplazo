<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'created_by',
        'status',
        'motivo',
        'fecha_inicio',
        'fecha_fin',
        'nombre_reemplazo',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (in_array($user->role, [UserRole::GESTION_PERSONAS, UserRole::RRHH], true)) {
            return $query;
        }

        return $query
            ->where('service_id', $user->service_id)
            ->where('created_by', $user->id);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(RequestAction::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
