<?php

namespace OpenAdmin\Admin\Form\Field;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Form\Field;
use OpenAdmin\Admin\Form\Tabs\Tabs;

class Tabbed extends Field
{
    protected \Closure $tabCallback;
    protected Tabs $tabs;

    public function __construct(\Closure $callback)
    {
        parent::__construct(uniqid('tabbed-'));
        $this->setView('admin::form.tabbed');
        $this->tabCallback = $callback;

    }

    public function setForm(Form $form = null): Field
    {
        parent::setForm($form);
        $this->tabs = new Tabs($form, $this->getId());
        call_user_func($this->tabCallback, $this->tabs);
        return $this;
    }

    public function render() {
//        $this->value($this->getTabs());
        $this->addVariables([
            'tabs' => $this->tabs
        ]);
        return parent::render();
    }
}
