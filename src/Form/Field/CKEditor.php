<?php

namespace OpenAdmin\Admin\Form\Field;


class CKEditor extends Textarea
{
    protected static $js = [
        '/vendor/open-admin/ckeditor4/ckeditor.js',
    ];

    protected $view = 'admin::form.ckeditor';

    public function setupImageBrowse()
    {
        $this->options['filebrowserBrowseUrl'] = '/admin/media/?select=true&close=true&fn=window.opener.'.$this->id.'_selectFile';
        $this->options['filebrowserImageBrowseUrl'] = '/admin/media?select=true&close=true&fn=window.opener.'.$this->id.'_selectFile';
    }

    public function render()
    {
        $config = config('admin.extensions.ckeditor.config');
        $this->setupImageBrowse();

        $config = json_encode(array_merge($config, $this->options));

        $this->script = <<<JS
function {$this->id}_selectFile(url,file_name)
{
    var dialog = CKEDITOR.dialog.getCurrent();
    dialogName = dialog.getName();

    if ( dialogName == 'link' ) {

        dialog.getContentElement('info', 'url').setValue(url);
        linkDisplayText = dialog.getContentElement('info', 'linkDisplayText')
        if (linkDisplayText.getValue() == ""){
            linkDisplayText.setValue(file_name);
        }

    }else{

        // dialogName == image
        dialog.selectPage('info');
        dialog.getContentElement('info', 'txtUrl').setValue(url);
        dialog.getContentElement('info', 'txtAlt').setValue(file_name);
    }
}
window.{$this->id}_selectFile = {$this->id}_selectFile; // make it globaly available
var editor = CKEDITOR.replace('{$this->id}', $config);
function CKupdate() {
    for (instance in CKEDITOR.instances){
        CKEDITOR.instances[instance].updateElement();
    }
}
admin.form.addSaveCallback(CKupdate);
JS;

        return parent::render();
    }
}
