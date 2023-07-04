<div class="nav-tabs-custom no-border-radius">
    <ul class="nav nav-tabs">

        @foreach($tabs->getTabs() as $tab)
            <li class="nav-item">
                <a class="nav-link {{ $tab->isActive() ? 'active' : '' }}" href="#tab-{{ $tab->getId() }}" data-bs-toggle="tab">
                    {{ __($tab->getTitle()) }} <i class="icon-exclamation-circle text-red hide"></i>
                </a>
            </li>
        @endforeach

    </ul>
    <div class="tab-content fields-group">

        @foreach($tabs->getTabs() as $tab)
            <div class="tab-pane {{ $tab->isActive() ? 'active' : '' }}" id="tab-{{ $tab->getId() }}">
                @foreach($tab->getFields() as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>
        @endforeach

    </div>
</div>
