<?php

namespace OpenAdmin\Admin\Show;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use OpenAdmin\Admin\Form\Field\Traits\UploadField;
use OpenAdmin\Admin\Show;
use OpenAdmin\Admin\Widgets\Carousel;

class Field implements Renderable
{
    use Macroable {
        __call as macroCall;
    }
    use UploadField;

    /**
     * @var string
     */
    protected $view = 'admin::show.field';

    /**
     * Name of column.
     *
     * @var string
     */
    protected $name;

    /**
     * Label of column.
     *
     * @var string
     */
    protected $label;

    /**
     * Width for label and field.
     *
     * @var array
     */
    protected $width = [
        'label' => 2,
        'field' => 8,
    ];

    /**
     * Escape field value or not.
     *
     * @var bool
     */
    protected $escape = true;

    /**
     * Field value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Output content (can be HTML)
     */
    protected $content;

    /**
     * Default value (displays when value equal null)
     */
    protected $stub;

    /**
     * Escaping for default value
     */
    protected bool $isEscapeStub = true;

    /**
     * @var Collection
     */
    protected $showAs = [];

    /**
     * Parent show instance.
     *
     * @var Show
     */
    protected $parent;

    /**
     * Relation name.
     *
     * @var string
     */
    protected $relation;

    /**
     * If show contents in box.
     *
     * @var bool
     */
    public $border = false;

    /**
     * @var array
     */
    /*
    protected $fileTypes = [
        'image'      => 'png|jpg|jpeg|tmp|gif',
        'word'       => 'doc|docx',
        'excel'      => 'xls|xlsx|csv',
        'powerpoint' => 'ppt|pptx',
        'pdf'        => 'pdf',
        'code'       => 'php|js|java|python|ruby|go|c|cpp|sql|m|h|json|html|aspx',
        'archive'    => 'zip|tar\.gz|rar|rpm',
        'txt'        => 'txt|pac|log|md',
        'audio'      => 'mp3|wav|flac|3pg|aa|aac|ape|au|m4a|mpc|ogg',
        'video'      => 'mkv|rmvb|flv|mp4|avi|wmv|rm|asf|mpeg',
    ];
    */

    /**
     * Field constructor.
     *
     * @param string $name
     * @param string $label
     */
    public function __construct($name = '', $label = '')
    {
        $this->name = $name;

        $this->label = $this->formatLabel($label);

        $this->showAs = new Collection();
    }

    /**
     * Set parent show instance.
     *
     * @param Show $show
     *
     * @return $this
     */
    public function setParent(Show $show)
    {
        $this->parent = $show;

        return $this;
    }

    /**
     * Get name of this column.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Format label.
     *
     * @param $label
     *
     * @return mixed
     */
    protected function formatLabel($label)
    {
        $label = $label ?: ucfirst($this->name);

        return str_replace(['.', '_'], ' ', $label);
    }

    /**
     * Get label of the column.
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Field display callback.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function as(callable $callable)
    {
        $this->showAs->push($callable);

        return $this;
    }

    /**
     * Display field using array value map.
     *
     * @param array $values
     * @param null  $default
     *
     * @return $this
     */
    public function using(array $values, $default = null)
    {
        return $this->as(function ($value) use ($values, $default) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if(is_array($value)) {
                return collect((array) $value)->map(function ($item) use (&$values, &$default) {
                    return Arr::get($values, $item, $default);
                });
            }

            return Arr::get($values, $value, $default);
        });
    }

    /**
     * Show field as a image.
     *
     * @param string $server
     * @param int    $width
     * @param int    $height
     *
     * @return $this
     */
    public function image($server = '', $width = 200, $height = 200)
    {
        return $this->unescape()->as(function ($images) use ($server, $width, $height) {
            return collect($images)->map(function ($path) use ($server, $width, $height) {
                if (empty($path)) {
                    return '';
                }

                if (url()->isValidUrl($path)) {
                    $src = $path;
                } elseif ($server) {
                    $src = $server.$path;
                } else {
                    $disk = config('admin.upload.disk');

                    if (config("filesystems.disks.{$disk}")) {
                        $src = Storage::disk($disk)->url($path);
                    } else {
                        return '';
                    }
                }

                return "<img src='$src' style='max-width:{$width}px;max-height:{$height}px' class='img' />";
            })->implode('&nbsp;');
        });
    }

    /**
     * Show field as a carousel.
     *
     * @param int    $width
     * @param int    $height
     * @param string $server
     *
     * @return Field
     */
    public function carousel($width = 300, $height = 200, $server = '')
    {
        return $this->unescape()->as(function ($images) use ($server, $width, $height) {
            $items = collect($images)->map(function ($path) use ($server, $width, $height) {
                if (empty($path)) {
                    return '';
                }

                if (url()->isValidUrl($path)) {
                    $image = $path;
                } elseif ($server) {
                    $image = $server.$path;
                } else {
                    $disk = config('admin.upload.disk');

                    if (config("filesystems.disks.{$disk}")) {
                        $image = Storage::disk($disk)->url($path);
                    } else {
                        $image = '';
                    }
                }

                $caption = '';

                return compact('image', 'caption');
            });

            return (new Carousel($items))->width($width)->height($height);
        });
    }

    /**
     * Show field as a file.
     *
     * @param string $server
     * @param bool   $download
     *
     * @return Field
     */
    public function file($server = '', $download = true)
    {
        $field = $this;

        return $this->unescape()->as(function ($path) use ($server, $download, $field) {
            $name = basename($path);

            $field->border = false;

            $size = $url = '';

            if (url()->isValidUrl($path)) {
                $url = $path;
            } elseif ($server) {
                $url = $server.$path;
            } else {
                $storage = Storage::disk(config('admin.upload.disk'));
                if ($storage->exists($path)) {
                    $url = $storage->url($path);
                    $size = ($storage->size($path) / 1000).'KB';
                }
            }

            if (!$url) {
                return '';
            }

            $download = $download ? "download='$name'" : '';

            return <<<HTML
<div class="card mailbox-arttachment clearfix">
      <span class="mailbox-attachment-icon"><i class="{$field->getFileIcon($name)}"></i></span>
      <div class="card-body">
        <div class="mailbox-attachment-name">
            <i class="icon-paperclip"></i> {$name}
            </div>
            <span class="mailbox-attachment-size">
              {$size}&nbsp;
              <a href="{$url}" class="btn btn-light btn-xs float-end" target="_blank" $download><i class="icon-download"></i></a>
            </span>
      </div>
</div>
HTML;
        });
    }

    /**
     * Show field as a link.
     *
     * @param string|\Closure $href
     * @param string $target
     *
     * @return Field
     */
    public function link($href = '', $target = '_blank')
    {
        return $this->unescape()->as(function ($link, $content) use ($href, $target) {
            if(is_callable($href)) {
                $href = $href($link, $this);
            }

            $href = $href ?: $link;

            return "<a href='$href' target='{$target}'>{$content}</a>";
        });
    }

    /**
     * Show field as labels.
     *
     * @param string $style
     *
     * @return Field
     */
    public function label($style = 'success')
    {
        return $this->unescape()->as(function ($value, $content) use (&$style) {
            if ($content instanceof Arrayable) {
                $content = $content->toArray();
            }

            return collect((array) $content)->map(function ($item) use (&$style) {
                return "<span class='badge bg-{$style}'>$item</span>";
            })->implode('&nbsp;');
        });
    }

    /**
     * Show field as badges.
     *
     * @param array $style - an array with styles for each value [value => style]
     * @param string $default -  default style if no matches were found
     *
     * @return Field
     */
    public function badge(array $style, string $default = '')
    {
        return $this->unescape()->as(function ($value, $content) use (&$style, &$default) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            $content = $content instanceof Arrayable
                ? $content->toArray()
                : (array)$content;

            return collect((array) $value)->map(function ($item, $key) use (&$style, &$default, &$content) {
                $style = Arr::get($style, $item, $default);
                $name = $content[$key] ?? $item;
                return "<span class='badge bg-{$style}'>$name</span>";
            })->implode('&nbsp;');
        });
    }

    /**
     * Add a `dot` before column text.
     *
     * @param array  $options
     * @param string $default
     *
     * @return $this
     */
    public function dot(array $style, string $default = '')
    {
        return $this->unescape()->as(function ($value, $content) use (&$style, &$default) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if ($content instanceof Arrayable) {
                $content = $content->toArray();
            }

            return collect((array) $value)->map(function ($name) use (&$style, &$default, &$content) {
                $style = Arr::get($style, $name, $default);

                return "<span class=\"bg-{$style}\" style='width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;'></span>&nbsp;$content";
            })->implode('&nbsp;&nbsp;');
        });
    }

    /**
     * Show field as bool.
     *
     * @param ?bool $overwrite -
     * @param array $titles - Title for each value (true and false)
     *
     * @return Field
     */
    public function bool(?bool $overwrite = null, iterable $titles = []): Field
    {
        return $this->unescape()->as(function ($value) use ($overwrite, $titles) {
            if(empty($titles)) {
                $titles = ['No', 'Yes'];
            }

            $title = ($overwrite ?? $value && array_key_exists($value, $titles))
                ? "<span class='text-success fw-bolder'>$titles[$value]</span>"
                : "<span class='text-danger fw-bolder'>$titles[$value]</span>";
            $icon = ($overwrite ?? $value)
                ? '<i class="icon-check text-success"></i>'
                : '<i class="icon-times text-danger"></i>';

            return $icon . $title;
        });

    }

    /**
     * Show field as datetime.
     *
     * @param ?string $format
     * @param ?string $timeZone
     *
     * @return Field
     */
    public function datetime(?string $format = null, ?string $timeZone = null): Field
    {
        return $this->unescape()->as(function ($value) use ($format, $timeZone) {
            $datetime = (new DateTime(
                $value,
                new DateTimeZone($timeZone ?? config('admin.timezone')))
            )->format($format ?? config('admin.datetimeFormat'));

            return "<span class='fw-bolder'>$datetime</span>";
        });
    }

    /**
     * Show field as json code.
     *
     * @return Field
     */
    public function json()
    {
        $field = $this;

        return $this->unescape()->as(function ($value) use ($field) {
            if (is_string($value)) {
                $content = json_decode($value, true);
            } else {
                $content = $value;
            }

            if (json_last_error() == 0) {
                $field->border = false;

                return '<pre><code>'.json_encode($content, JSON_PRETTY_PRINT).'</code></pre>';
            }

            return $value;
        });
    }

    public function address(?\Closure $closure = null)
    {
        $this->setView('admin::show.address');
        if(is_callable($closure)) {
            return $closure($this);
        }
        return $this;
    }

    /**
     * Show readable filesize for giving integer size.
     *
     * @return Field
     */
    public function filesize()
    {
        return $this->as(function ($value) {
            return file_size($value);
        });
    }

    /**
     * Get file icon.
     *
     * @param string $file
     *
     * @return string
     */
    public function getFileIcon($file = '')
    {
        $ext = File::extension($file);

        $filetype = 'file';
        foreach ($this->fileTypes as $type => $pattern) {
            if (preg_match($pattern, $ext) === 1) {
                $filetype = $type;
                break;
            }
        }

        return $this->fileTypesIcons[$filetype];
    }

    /**
     * Set view of field
     *
     * @param string|\Closure $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = is_string($view) ? $view : $view($this);
        return $this;
    }

    /**
     * Set escape or not for this field.
     *
     * @param bool $escape
     *
     * @return $this
     */
    public function setEscape($escape = true)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Unescape for this field.
     *
     * @return Field
     */
    public function unescape()
    {
        return $this->setEscape(false);
    }

    /**
     * Set value for this field.
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setValue(Model $model)
    {
        if ($this->relation) {
            if (!$relationValue = $model->{$this->relation}) {
                return $this;
            }

            $this->value = $relationValue;
        } else {
            if (Str::contains($this->name, '.')) {
                $this->value = $this->getRelationValue($model, $this->name);
            } else {
                $this->value = $model->getAttribute($this->name);
            }
        }
        $this->content = $this->value;
        return $this;
    }

    /**
     * Set relation name for this field.
     *
     * @param string $relation
     *
     * @return $this
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @param Model  $model
     * @param string $name
     *
     * @return mixed
     */
    protected function getRelationValue($model, $name)
    {
        list($relation, $key) = explode('.', $name);

        if ($related = $model->getRelationValue($relation)) {
            return $related->getAttribute($key);
        }
    }

    /**
     * Set width for field and label.
     *
     * @param int $field
     * @param int $label
     *
     * @return $this
     */
    public function setWidth($field = 8, $label = 2)
    {
        $this->width = [
            'label' => $label,
            'field' => $field,
        ];

        return $this;
    }

    /**
     * Set default value
     */
    public function setDefault($defaultValue, bool $isHtml = false)
    {
        $this->stub = $defaultValue;
        $this->isEscapeStub = !$isHtml;
        return $this;
    }

    /**
     * Call extended field.
     *
     * @param string|AbstractField|\Closure $abstract
     * @param array                         $arguments
     *
     * @return Field
     */
    protected function callExtendedField($abstract, $arguments = [])
    {
        if ($abstract instanceof \Closure) {
            return $this->as($abstract);
        }

        if (is_string($abstract) && class_exists($abstract)) {
            /** @var AbstractField $extend */
            $extend = new $abstract();
        }

        if ($abstract instanceof AbstractField) {
            /** @var AbstractField $extend */
            $extend = $abstract;
        }

        if (!isset($extend)) {
            admin_warning("[$abstract] is not a valid Show field.");

            return $this;
        }

        if (!$extend->escape) {
            $this->unescape();
        }

        $field = $this;

        return $this->as(function ($value) use ($extend, $field, $arguments) {
            if (!$extend->border) {
                $field->border = false;
            }

            $extend->setValue($value)->setModel($this);

            return $extend->render(...$arguments);
        });
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments = [])
    {
        if ($class = Arr::get(Show::$extendedFields, $method)) {
            return $this->callExtendedField($class, $arguments);
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if ($this->relation) {
            $this->name = $method;
            $this->label = $this->formatLabel(Arr::get($arguments, 0));
        }

        return $this;
    }

    /**
     * Get all variables passed to field view.
     *
     * @return array
     */
    protected function variables()
    {
        if(is_null($this->value)) {
            $this->content = $this->stub;
            $this->escape = $this->isEscapeStub;
        }

        return [
            'value'     => $this->value,
            'content'   => $this->content,
            'escape'    => $this->escape,
            'label'     => $this->getLabel(),
            'wrapped'   => $this->border,
            'width'     => $this->width,
        ];
    }

    /**
     * Render this field.
     *
     * @return string
     */
    public function render()
    {
        if ($this->showAs->isNotEmpty()) {
            $this->showAs->each(function ($callable) {
                $this->content = $callable->call(
                    $this->parent->getModel(),
                    $this->value,
                    $this->content,
                );
            });
        }

        return view($this->view, $this->variables());
    }
}
