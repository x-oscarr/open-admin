<?php

namespace OpenAdmin\Admin\Form\Field;

use OpenAdmin\Admin\Form\Field;
use OpenAdmin\Admin\Form\Field\Traits\HasValuePicker;
use OpenAdmin\Admin\Form\Field\Traits\PlainInput;

class Text extends Field
{
    use PlainInput;
    use HasValuePicker;

    protected string $icon = 'icon-pencil-alt';

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    protected bool $withoutIcon = false;

    /**
     * Set custom fa-icon.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     *
     * @return $this
     */
    public function suffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * show no icon in font of input.
     *
     * @return $this
     */
    public function withoutIcon(bool $value = true): self
    {
        $this->withoutIcon = $value;

        return $this;
    }

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->initPlainInput();

        if (!$this->withoutIcon || $this->prefix) {
            $this->prepend(($this->withoutIcon ? '' : '<i class="'.$this->icon.'"></i>') . $this->prefix);
        }

        if($this->suffix) {
            $this->append($this->suffix);
        }

        $this->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
            ->defaultAttribute('value', old($this->elementName ?: $this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder())
            ->mountPicker()
            ->addVariables([
                'prepend' => $this->prepend,
                'append'  => $this->append,
            ]);

        return parent::render();
    }

    /**
     * Add inputmask to an elements.
     *
     * @param array $options
     *
     * @return $this
     */
    public function inputmask($options)
    {
        $options = json_encode_options($options);

        //$this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";
        $this->script = "Inputmask({$options}).mask(document.querySelector(\"{$this->getElementClassSelector()}\"));";

        return $this;
    }

    /**
     * Add datalist element to Text input.
     *
     * @param array $entries
     *
     * @return $this
     */
    public function datalist($entries = [])
    {
        $this->defaultAttribute('list', "list-{$this->id}");

        $datalist = "<datalist id=\"list-{$this->id}\">";
        foreach ($entries as $k => $v) {
            $datalist .= "<option value=\"{$k}\">{$v}</option>";
        }
        $datalist .= '</datalist>';

        return $this->append($datalist);
    }
}
