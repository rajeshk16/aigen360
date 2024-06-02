@extends('affiliate::layouts.app')
@section('page_title', __('Affiliate Users'))
@section('content')
    <!-- Main content -->
    <div class="col-sm-12 list-container" id="user-list-container">
        <div class="card">
            <div class="card-header d-md-flex justify-content-between align-items-center">
                <h5> <a href="{{ Route::current()->getName() == "users.customer" ? route('users.customer').'?role=3' : route('users.index') }}">{{ Route::current()->getName() == "users.customer" ?  __('Customers') : __('Users') }}</a> </h5>
                <div class="d-md-flex mt-2 mt-md-0 justify-content-end align-items-center">
                    @if(hasPermission('Modules\Affiliate\Http\Controllers\AffiliateController@userDestroy'))
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#batchDelete" class="btn btn-outline-primary mb-0 custom-btn-small d-none">
                        <span class="feather icon-trash-2 {{ languageDirection() == 'ltr' ? 'me-1' : 'ms-1' }}"></span>
                        {{ __('Batch Delete') }} (<span class="batch-delete-count">0</span>)
                    </a>
                    @endif
                    <button class="btn btn-outline-primary custom-btn-small mb-0 collapsed filterbtn {{ languageDirection() == 'ltr' ? 'me-0' : 'ms-0' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="true"
                            aria-controls="filterPanel"><span class="fas fa-filter {{ languageDirection() == 'ltr' ? 'me-1' : 'ms-1' }}"></span>{{ __('Filter') }}</button>
                </div>
            </div>

            <div class="card-header collapse p-0" id="filterPanel">
                <div class="row mx-2 my-2">
                    <div class="col-md-3">
                        <select class="filter select2" name="status">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="Pending">{{ __('Pending') }}</option>
                            <option value="Active">{{ __('Active') }}</option>
                            <option value="Inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="filter select2" name="affiliate_tag">
                            <option value="">{{ __('All Tag') }}</option>
                            @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-0 user-list-wallet user-list-processing-message hide-export {{ hasPermission('Modules\Affiliate\Http\Controllers\AffiliateController@userDestroy') ? 'need-batch-operation' : '' }}"
                 data-namespace="Modules\Affiliate\Entities\AffiliateUser" data-column="id">
                <div class="card-block pt-2 px-2">
                    <div class="col-sm-12 form-tabs px-3">
                        @include('admin.layouts.includes.yajra-data-table')
                    </div>
                </div>
            </div>
            @include('admin.layouts.includes.delete-modal')
        </div>
    </div>
@endsection

