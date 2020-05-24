@if (session()->has('error') || session()->has('success') || session()->has('status') || session()->has('info'))
    <div class="row mb-3">
        <div class="col-12">
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissable fade show" role="alert">
                    {{{ session()->get('error') }}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session()->has('success'))
                <div class="alert alert-success alert-fadeout fade show" role="alert">
                    {{{ session()->get('success') }}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session()->has('status'))
                <div class="alert alert-success alert-fadeout fade show" role="alert">
                    {{{ session()->get('status') }}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info alert-fadeout fade show" role="alert">
                    {{{ session()->get('info') }}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
