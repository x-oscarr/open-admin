<?php

namespace OpenAdmin\Admin\Form\Field;

use Illuminate\Support\Arr;
use OpenAdmin\Admin\Form\Field;
use \Illuminate\Support\Facades\View as ViewFacade;

class View extends Field
{
    /**
     * Htmlable.
     *
     * @var string|\Closure
     */
    protected $view = '';

    /**
     * @var array
     */
    protected $viewData;

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var bool
     */
    protected $plain = false;

    /**
     * Create a new Html instance.
     *
     * @param string|Closure $view
     * @param array $data
     * @param string $label
     */
    public function __construct($view, $viewData = [], $label = '')
    {
        $this->view = $view;
        $this->viewData = Arr::get($viewData, 0) ?? [];
        $this->label = $label;
    }

    /**
     * @return $this
     */
    public function plain()
    {
        $this->plain = true;

        return $this;
    }

    /**
     * Render html field.
     *
     * @return string
     */
    public function render()
    {
        if ($this->view instanceof \Closure) {
            $this->view = $this->view->call($this->form->model(), $this->form);
        }

        if ($this->viewData instanceof \Closure) {
            $this->viewData = $this->data->call($this->form->model(), $this->form);
        }

        $this->viewData += [
            'model' => $this->form->model()
        ];

        $view = ViewFacade::make($this->view, $this->viewData);
        if ($this->plain) {
            return $view;
        }

        $viewClass = $this->getViewElementClasses();

        return <<<EOT
<div class="{$viewClass['form-group']}">
    <label class="{$viewClass['label']} form-label">{$this->label}</label>
    <div class="{$viewClass['field']}">
        {$view->render()}
    </div>
</div>
EOT;
    }
}
