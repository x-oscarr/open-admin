<?php

namespace OpenAdmin\Admin\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use OpenAdmin\Admin\Tree;

trait ModelTree
{
    protected static array $branchOrder = [];
    protected string $parentColumn = 'parent_id';
    protected string $titleColumn = 'title';
    protected string $orderColumn = 'order';
    protected ?Closure $queryCallback = null;

    /**
     * Get children of current node.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->parentColumn);
    }

    /**
     * Get parent of current node.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->parentColumn);
    }

    /**
     * @return string
     */
    public function getParentColumn(): string
    {
        return $this->parentColumn;
    }

    /**
     * Set parent column.
     */
    public function setParentColumn($column): void
    {
        $this->parentColumn = $column;
    }

    /**
     * Get title column.
     */
    public function getTitleColumn(): string
    {
        return $this->titleColumn;
    }

    /**
     * Set title column.
     */
    public function setTitleColumn($column): void
    {
        $this->titleColumn = $column;
    }

    /**
     * Get order column name.
     */
    public function getOrderColumn(): string
    {
        return $this->orderColumn;
    }

    /**
     * Set order column.
     */
    public function setOrderColumn($column): void
    {
        $this->orderColumn = $column;
    }

    /**
     * Set query callback to model.
     */
    public function withQuery(?Closure $query = null): self
    {
        $this->queryCallback = $query;

        return $this;
    }

    /**
     * Format data to tree like array.
     */
    public function toTree(): Collection
    {
        return $this->allNodes()->map(function ($item) {
            return [
                'model' => $item,
                'id' => $item->id,
                'parent' => $item->parent_id,
                'text' => $item->id
            ];
        });
    }

    /**
     * Get all elements.
     */
    public function allNodes(): Collection
    {
        $query = $this->newQuery();
        if ($this->queryCallback instanceof Closure) {
            $query = call_user_func($this->queryCallback, $query);
        }
        return $query->orderBy($this->getOrderColumn())->get();
    }

    /**
     * Set the order of branches in the tree.
     */
    protected static function setBranchOrder(array $order): void
    {
        static::$branchOrder = array_flip(Arr::flatten($order));

        static::$branchOrder = array_map(function ($item) {
            return ++$item;
        }, static::$branchOrder);
    }

    /**
     * Save tree order from a tree like array.
     */
    public static function saveOrder($tree = [], $parentId = null): void
    {
        if (empty(static::$branchOrder)) {
            static::setBranchOrder($tree);
        }

        foreach ($tree as $branch) {
            $node = static::find($branch['id']);

            $node->{$node->getParentColumn()} = $parentId;
            $node->{$node->getOrderColumn()} = static::$branchOrder[$branch['id']];
            $node->save();

            if (isset($branch['children'])) {
                static::saveOrder($branch['children'], $branch['id']);
            }
        }
    }

    /**
     * Get options for Select field in form.
     */
    public static function selectOptions(Closure $closure = null, $rootText = 'ROOT'): array
    {
        $options = (new static())->withQuery($closure)->buildSelectOptions();
        $options = collect($options);
        if($rootText) {
            $options->prepend($rootText, 0);
        }
        return $options->toArray();
    }

    /**
     * Build options of select field in form.
     */
    protected function buildSelectOptions(iterable $nodes = [], $parentId = 0, $prefix = '', $space = '&nbsp;'): array
    {
        $prefix = $prefix ?: '┝'.$space;

        $options = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $index => $node) {
            if ($node[$this->parentColumn] == $parentId) {
                $node[$this->titleColumn] = $prefix.$space.$node[$this->titleColumn];

                $childrenPrefix = str_replace('┝', str_repeat($space, 6), $prefix).'┝'.str_replace(['┝', $space], '', $prefix);

                $children = $this->buildSelectOptions($nodes, $node[$this->getKeyName()], $childrenPrefix);

                $options[$node[$this->getKeyName()]] = $node[$this->titleColumn];

                if ($children) {
                    $options += $children;
                }
            }
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $branch) {
            $parentColumn = $branch->getParentColumn();

            if (Request::has($parentColumn) && Request::input($parentColumn) == $branch->getKey()) {
                throw new \Exception(trans('admin.parent_select_error'));
            }

            if (Request::has('_order')) {
                $order = Request::input('_order');

                Request::offsetUnset('_order');

                (new Tree(new static()))->saveOrder($order);

                return false;
            }

            return $branch;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->where($this->parentColumn, $this->getKey())->delete();

        return parent::delete();
    }
}
