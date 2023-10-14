<?php

namespace OpenAdmin\Admin\Grid\Displayers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Image extends AbstractDisplayer
{
    public function display($server = '', $width = 200, $height = 200)
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        return collect((array) $this->value)->filter()->map(function ($path) use ($server, $width, $height) {
            if (URL::isValidUrl($path) || strpos($path, 'data:image') === 0) {
                $src = $path;
            } elseif (URL::isValidUrl($server)) {
                $src = rtrim($server, '/').'/'.ltrim($path, '/');
            } elseif (is_string($server)) {
                $src = Storage::disk($server ?: config('admin.upload.disk'))->url($path);
            } else {
                $src = $path;
            }

            return "<img src='$src' style='max-width:{$width}px;max-height:{$height}px' class='img img-thumbnail' />";
        })->implode('&nbsp;');
    }
}
