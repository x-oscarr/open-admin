<?php

namespace OpenAdmin\Admin\Tree;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use OpenAdmin\Admin\Tree;

interface ModelTreeInterface
{
    /**
     * Get children of current node.
     */
    public function children(): HasMany;

    /**
     * Get parent of current node.
     */
    public function parent(): BelongsTo;

    /**
     * Get parent column.
     */
    public function getParentColumn(): string;

    /**
     * Set parent column.
     */
    public function setParentColumn($column): void;

    /**
     * Get title column.
     */
    public function getTitleColumn(): string;

    /**
     * Set title column.
     */
    public function setTitleColumn($column): void;

    /**
     * Get order column name.
     */
    public function getOrderColumn(): string;

    /**
     * Set order column.
     */
    public function setOrderColumn($column): void;

    /**
     * Set query callback to model.
     */
    public function withQuery(\Closure $query = null): self;

    /**
     * Format data to tree like array.
     */
    public function toTree(): Collection;

    /**
     * Get all elements.
     */
    public function allNodes(): Collection;

    /**
     * Save tree order from a tree like array.
     */
    public static function saveOrder($tree, $parentId): void;

    /**
     * Get options for Select field in form.
     */
    public static function selectOptions(\Closure $closure = null, $rootText = 'ROOT'): array;
}
