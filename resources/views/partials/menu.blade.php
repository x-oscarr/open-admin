@if(Admin::user()->visible($item->roles->toArray()) && Admin::user()->can($item->permission))
    @if($item->children->isEmpty())
        <li>
            @if(url()->isValidUrl($item->uri))
                <a href="{{ $item->uri }}" target="_blank">
            @else
                 <a href="{{ admin_url($item->uri) }}">
            @endif
                <i class="{{ $item->icon }}"></i>
                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item->title)))))
                    <span>{{ __($titleTranslation) }}</span>
                @else
                    <span>{{ admin_trans($item->title) }}</span>
                @endif

                <span class="menu-badge">{!! $item->badge !!}</span>
            </a>
        </li>
    @else
        <li class="treeview">
            <a href="#" class="has-subs" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $item->id }}" aria-expanded="false">
                <i class="{{ $item->icon }}"></i>
                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item->title)))))
                    <span>{{ __($titleTranslation) }}</span>
                @else
                    <span>{{ admin_trans($item->title) }}</span>
                @endif

                <span class="menu-badge">{!! $item->allBadges !!}</span>
            </a>
            <ul id="collapse-{{ $item->id }}" class="btn-toggle-nav list-unstyled collapse fw-normal pb-1">
                @foreach($item->children as $item)
                    @include('admin::partials.menu', $item)
                @endforeach
            </ul>
        </li>
    @endif
@endif
