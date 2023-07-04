<?php

namespace OpenAdmin\Admin\Form\Tabs;

use Illuminate\Support\Collection;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Form\Field\Tabbed;

class Tab
{

    protected Tabs $tabs;
    protected string $id;
    protected string $title;
    protected Collection $fields;

    public function __construct(Tabs $tabs, string $title)
    {
        $this->tabs = $tabs;
        $this->id = $tabs->getId() . '-tab-' . ($tabs->getTabs()->count() + 1);
        $this->title = $title;
        $this->fields = new Collection();
    }

    /**
     * Append content into this tab section.
     *
     * @param string   $title
     * @param \Closure $content
     *
     * @return $this
     */
    public function insert(\Closure $content)
    {
        $this->fields = $this->tabs->getForm()->setUnderTab($this, $content);
        return $this;
    }

    public function isActive(): bool
    {
        return $this->tabs->getActiveTab() === $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }
}
