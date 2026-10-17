<?php

namespace Modules\Launchpad\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One request to build, provision, and deploy a Saucebase app.
 *
 * @property int $id
 * @property int $user_id
 * @property-read User $user
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $idea
 * @property list<string> $modules
 * @property string $theme
 * @property list<string> $services
 * @property string $status queued, running, live, or failed
 * @property string|null $step
 * @property string|null $log
 * @property string|null $url
 */
class Launch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'idea',
        'modules',
        'theme',
        'services',
    ];

    protected $attributes = [
        'status' => 'queued',
    ];

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'services' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspacePath(): string
    {
        return config('launchpad.workspace').'/'.$this->slug;
    }
}
