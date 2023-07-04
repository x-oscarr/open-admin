<?php

namespace OpenAdmin\Admin\Form\Tabs;

use Illuminate\Support\Collection;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Form\Field\Tabbed;

class Tabs
{
    protected $list;
    protected ?Tab $activeTab = null;
    protected Form $form;
    protected string $fieldId;

    public function __construct(Form $form, string $fieldId)
    {
        $this->form = $form;
        $this->fieldId = $fieldId;
        $this->list = new Collection();
    }

    /**
     * Use tab to split form.
     *
     * @param string  $title
     * @param \Closure $content
     * @param bool    $active
     *
     * @return $this
     */
    public function tab($title, \Closure $content, bool $active = false): self
    {
        $tab = $this->createTab($this, $title)->insert($content);

        if($active || !$this->getActiveTab()) {
            $this->setActiveTab($tab);
        }

        $this->list->add($tab);
        return $this;
    }

    public function getId(): string
    {
        return $this->fieldId;
    }

    /**
     * Get all tabs.
     *
     * @return Collection
     */
    public function getTabs()
    {
        return $this->list;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->list->isEmpty();
    }

    /**
     * Set Tab instance.
     *
     * @return Tab
     */
    public function createTab(Tabs $tabs, string $title): Tab
    {
        return new Tab($this, $title);
    }

    /**
     * @return null|Tab
     */
    public function getActiveTab(): ?Tab
    {
        return $this->activeTab;
    }

    /**
     * @param Tab $activeTab
     */
    public function setActiveTab(int|Tab $activeTab): void
    {
        if(is_int($activeTab)) {
            $activeTab = $this->getTabs()->get($activeTab);
        }

        $this->activeTab = $activeTab;
    }
}
