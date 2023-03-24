<div class="tree-item">
    @foreach($grid->visibleColumns() as $column)
        <div {!! $row->getColumnAttributes($column->getName()) !!}
             data-bs-toggle="tooltip"
             data-bs-placement="top"
             title="{{ $column->getLabel() }}">
            {!! $row->column($column->getName()) !!}
        </div>
    @endforeach
</div>
