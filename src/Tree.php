<?php

namespace OpenAdmin\Admin;

use Closure;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use OpenAdmin\Admin\Exception\Handler;
use OpenAdmin\Admin\Tree\Tools;

class Tree extends Grid
{
    /**
     * Options for grid.
     *
     * @var array
     */
    protected $options = [
        'show_pagination'        => true,
        'show_tools'             => true,
        'show_filter'            => true,
        'show_exporter'          => true,
        'show_actions'           => true,
        'show_row_selector'      => false,
        'show_create_btn'        => true,
        'show_column_selector'   => true,
        'show_define_empty_page' => true,
        'show_perpage_selector'  => true,
    ];

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $elementId = 'tree-';

    /**
     * Views for grid to render.
     *
     * @var string
     */
    protected $views = [
        'tree' => 'admin::tree',
        'row' => 'admin::tree.row',
    ];
    /**
     * Header tools.
     *
     * @var Tools
     */
    public $tools;

    /**
     * @var bool
     */
    public $useCreate = true;

    /**
     * @var bool
     */
    public $useSave = true;

    /**
     * @var bool
     */
    public $useRefresh = true;

    public function __construct(Eloquent $model, Closure $builder = null)
    {
        parent::__construct($model, $builder);
        $this->path = \request()->getPathInfo();
        $this->elementId .= uniqid();
        $this->tools = new Tools($this);
        $this->setView($this->views['tree']);
    }

    /**
     * Return all items of the tree.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->model()->getOriginalModel()->toTree()->map(function ($item) {
            $item['text'] = View::make($this->views['row'], [
                'grid' => $this,
                'row' => $this->rows()->first(fn($row) => $row->model()['id'] == $item['model']->id)
            ])->render();
            return $item;
        });
    }

    /**
     * Build tree grid scripts.
     *
     * @return string
     */
    protected function script()
    {
        $url = url($this->path);
        $json = $this->getItems()->toJson();
        return <<<SCRIPT
admin.tree.init('{$this->elementId}','{$url}', $json);
SCRIPT;
    }

    /**
     * Variables in tree template.
     *
     * @return array
     */
    public function variables()
    {
        return [
            'grid'       => $this,
            'id'         => $this->elementId,
            'path'       => $this->path,
            'tools'      => $this->tools->render(),
            'useCreate'  => $this->useCreate,
            'useSave'    => $this->useSave,
            'useRefresh' => $this->useRefresh,
        ];
    }

    /**
     * Get the string contents of the grid view.
     *
     * @return string
     */
    public function render()
    {
        $this->handleExportRequest(true);

        try {
            $this->build();
        } catch (\Exception $e) {
            return Handler::renderException($e);
        }

        $this->callRenderingCallback();

        Admin::script($this->script());
        return Admin::component($this->view, $this->variables());
    }
}
