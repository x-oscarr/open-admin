@include("admin::form._header")

        <div class="input-group">
            @if ($prepend)
                <span class="input-group-text with-icon">{!! $prepend !!}</span>
            @endif
            <input {!! $attributes !!} />
            @if ($append)
                <span class="input-group-text clearfix">{!! $append !!}</span>
            @endif

            <div class="modal fade" id="addressOnMapModal_{{ $id }}" tabindex="-1" aria-labelledby="addressOnMapModalLabel_{{ $id }}" aria-hidden="true">
                <div class="modal-dialog" style="max-width: 800px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addressOnMapModalLabel_{{ $id }}">{{ $value }}</h5>
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

            <script defer>
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
                    var googleMap = document.getElementById('gmap');
                    var button = document.getElementById('openMapModal_{{ $id }}');
                    var title = document.getElementById('addressOnMapModalLabel_{{ $id }}');
                    var addressInput = document.getElementById('{{ $id }}');
                    var cache = null;
                    button.addEventListener('click', function (e) {
                        if(cache !== addressInput.value) {
                            googleMap.src = urlGenerator(addressInput.value);
                            cache = title.innerHTML = addressInput.value;
                        }
                    });
                });
            </script>
        </div>

@include("admin::form._footer")
