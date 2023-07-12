<div class="row ">
    <label class="col-sm-{{$width['label']}} form-label">{{ $label }}</label>
    <div class="col-sm-{{$width['field']}} show-value">
        <div class="">
            <span id="value">{{ $value }}</span> 
            @if($value)
                <button type="button" id="openMapModal" class="btn btn-link btn-sm" data-bs-toggle="modal" data-bs-target="#addressOnMapModal">
                    {{ trans('admin.order.on_map') }}
                    <i class="icon-map-marked-alt"></i></button>
            @endif
        </div>
    </div>
</div>

@if($value)
<div class="modal fade" id="addressOnMapModal" tabindex="-1" aria-labelledby="addressOnMapModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 800px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressOnMapModalLabel">{{ $value }}</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div style="width: 100%">
                    <iframe
                        id="gmap"
                        width="100%"
                        height="600"
                        frameborder="0"
                        scrolling="no"
                        marginheight="0"
                        marginwidth="0"
                        src="https://maps.google.com/maps?output=embed">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function urlGenerator(address) {
        var url = 'https://maps.google.com/maps';
        var query = new URLSearchParams({
            // Address | Query string
            q: address,
            // Language
            hl: 'en',
            // Width
            width: '100%',
            // Height
            height: 600,
            // View type:
            // roadmap - ''
            // satellite - 'k'
            // hybrid - 'h'
            // terrain - 'p'
            t: '',
            // From 0 to 25
            z: 13,
            // Other
            ie: 'UTF8',
            iwloc: 'B',
            output: 'embed',
        });
        return `${url}?${query.toString()}`;
    }

    document.addEventListener('DOMContentLoaded', function(){
        var googleMap = document.getElementById('gmap'),
            value = document.getElementById('value');

        googleMap.src = urlGenerator(value.innerText);
        console.log(googleMap);
    });
</script>

@endif
