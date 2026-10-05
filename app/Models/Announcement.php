<?php

namespace App\Models;

use App\Observers\AnnouncementObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(AnnouncementObserver::class)]
#[Fillable([
    'author_id',
    'title',
    'content',
    'audience',
    'published_at',
    'expires_at',
    'is_pinned',
])]
class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_pinned' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_recipients');
    }

    /**
     * Pengumuman yang boleh dilihat user: audience "all", atau dia jadi penerima
     * eksplisit ketika admin memilih target per karyawan.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('audience', 'all')
            ->orWhereHas('recipients', fn ($r) => $r->whereKey($user->id)));
    }

    /**
     * Sudah tayang dan belum kedaluwarsa. Dipakai home karyawan dan halaman detail
     * supaya keduanya pakai aturan yang sama.
     */
    public function scopeCurrentlyPublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
