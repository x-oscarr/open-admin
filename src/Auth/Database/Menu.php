<?php

namespace OpenAdmin\Admin\Auth\Database;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenAdmin\Admin\Traits\DefaultDatetimeFormat;
use OpenAdmin\Admin\Traits\ModelTree;
use OpenAdmin\Admin\Traits\OldModelTree;
use OpenAdmin\Admin\Tree\ModelTreeInterface;

/**
 * Class Menu.
 *
 * @property int $id
 *
 * @method where($parent_id, $id)
 */
class Menu extends Model
{
    public const BADGE_MODE_OFF = 0;
    public const BADGE_MODE_FUNCTION = 1;

    use DefaultDatetimeFormat;
    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'parent_id', 'order', 'title', 'icon', 'uri', 'permission'];
    protected $attributes = [
        'parent_id' => 0,
    ];
    protected $casts = [
        'badge_mode'    => 'integer',
        'badge_payload' => 'array'
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.menu_table'));

        parent::__construct($attributes);
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_menu_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id');
    }

    /**
     * @return Collection
     */
    public function allNodes(): Collection
    {
        return $this->prepareQuery()->get();
    }

    public function parentNodes(): Collection
    {
        return $this->prepareQuery()->where('parent_id', 0)->get();
    }

    public function prepareQuery()
    {
        $query = new self();
        $query->setConnection(config('admin.database.connection') ?: config('database.default'));
        $query = $query->newQuery();

        if ($this->queryCallback instanceof Closure) {
            $query = call_user_func($this->queryCallback, $query);
            if (config('admin.check_menu_roles') !== false) {
                $query->with('roles');
            }
        }
        return $query->orderBy($this->getOrderColumn());
    }

    /**
     * determine if enable menu bind permission.
     *
     * @return bool
     */
    public function withPermission()
    {
        return (bool) config('admin.menu_bind_permission');
    }

    public function getBadgeAttribute()
    {
        if($this->badge_mode !== self::BADGE_MODE_FUNCTION) {
            return;
        }

        try {
            ['function' => $function, 'arguments' => $args] = $this->badge_payload;
            return call_user_func($function, ...$args);
        } catch (\Exception $e) {
            Log::error('Menu item badge error [' . $this->id . ']: ' . $e->getMessage());
            return;
        } catch (\Error $e) {
            Log::error('Menu item badge error [' . $this->id . ']: ' . $e->getMessage());
            return;
        }
    }

    public function getAllBadgesAttribute()
    {
        return $this->children->map(fn($item) => $item->badge)->join('&nbsp;');
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function boot()
    {
        static::treeBoot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }
}
