<div class="card">

    <div class="card-header">
        @if($useSave)
            <div class="btn-group">
                <a class="btn btn-info btn-sm {{ $id }}-save" title="{{ trans('admin.save') }}" onclick="admin.tree.save();"><i class="icon-save"></i><span class="hidden-xs">&nbsp;{{ trans('admin.save') }}</span></a>
            </div>
        @endif

        @if($useRefresh)
            <div class="btn-group">
                <a class="btn btn-warning btn-sm {{ $id }}-refresh" title="{{ trans('admin.refresh') }}" onclick="admin.ajax.reload();"><i class="icon-refresh"></i><span class="hidden-xs">&nbsp;{{ trans('admin.refresh') }}</span></a>
            </div>
        @endif

        <div class="btn-group">
            {!! $tools !!}
        </div>

        @if($useCreate)
            {!! $grid->renderCreateButton() !!}
        @endif

    </div>
    <!-- /.box-header -->
    <div class="card-body no-padding">
        <div class="tree" id="{{ $id }}"></div>
    </div>
    <!-- /.box-body -->
</div>
