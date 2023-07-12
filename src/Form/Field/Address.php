<?php

namespace OpenAdmin\Admin\Form\Field;

use OpenAdmin\Admin\Form;

class Address extends Text
{
    protected $view = 'admin::form.address';
    protected $rules = 'nullable';

    public function setForm(Form $form = null)
    {
        $this->form = $form;
        $this->form->enableValidate();
        return $this;
    }

    public function render()
    {
        $text = __('admin.order.on_map');
        $append = <<<HTML
<button type="button"
    id="openMapModal_{$this->id}"
    class="btn btn-link btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#addressOnMapModal_{$this->id}">
   {$text} <i class="icon-map-marked-alt"></i>
</button>
HTML;
        $this->prepend('<i class="icon-map-marked-alt fa-fw"></i>')
            ->append($append)
            ->defaultAttribute('type', 'address');

        return parent::render();
    }
}
