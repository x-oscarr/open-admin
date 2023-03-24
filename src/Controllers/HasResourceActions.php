<?php

namespace OpenAdmin\Admin\Controllers;

use Symfony\Component\HttpFoundation\Request;

trait HasResourceActions
{
    /**
     * Returns the form with possible callback hooks.
     *
     * @return \OpenAdmin\Admin\Form;
     */
    public function getForm()
    {
        $form = $this->form();
        if (method_exists($this, 'hasHooks') && $this->hasHooks('alterForm')) {
            $form = $this->callHooks('alterForm', $form);
        }

        return $form;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->getForm()->update($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        if(($r = request()) && $r->get('_order')) {
            return $this->treeSave($r);
        }
        return $this->getForm()->store();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->getForm()->destroy($id);
    }

    /**
     * Saving orders for tree structure
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function treeSave(Request $request)
    {
        $validated = $request->validate([
            '_order' => ['required', 'json'],
        ]);
        $orders = json_decode($validated['_order'], true, 512, JSON_THROW_ON_ERROR);

        foreach ($orders as $item) {
            $model = $this->getForm()->model();
            $d = $model::findOrFail($item['id'])
                ->update([
                    $model->getOrderColumn() => $item['order'],
                    $model->getParentColumn() => $item['parent'] ?? null
                ]);
        }

        return $this->getForm()->redirectAfterStore();
    }
}
