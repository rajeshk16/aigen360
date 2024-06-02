@extends('admin.layouts.app')
@section('page_title', __('Use Cases'))

@section('css')
@endsection
@section('content')
<!-- Main content -->
<div class="col-sm-12 list-container" id="use-case-list-container">
    <div class="card">
        <div class="card-header bb-none pb-0">
            <h5>{{ __('Use Cases') }}</h5>
            <x-backend.group-filters :groups="$groups" :column="'status'" />
            <div class="card-header-right my-2 mx-md-0 mx-sm-4">
                <x-backend.button.batch-delete class="me-1" />
                @if (in_array('Modules\OpenAI\Http\Controllers\Admin\UseCasesController@create', $prms))
                    <x-backend.button.add-new label="{{ __('Add :x', [ 'x' => __('Use Case') ]) }}" class="me-1" href="{{ route('admin.use_case.create') }}" />
                @endif
                <x-backend.button.export  class="me-1"/>
                <x-backend.button.filter class="me-0" />
            </div>
        </div>
        <x-backend.datatable.filter-panel class="mx-1">
            <div class="col-md-6">
                <x-backend.datatable.input-search />
            </div>
            <div class="col-md-3">
                <select class="select2 filter" name="useCaseId">
                    <option value="">{{ __('All Use Cases') }}</option>
                    @foreach ($useCases as $useCase)
                        <option value="{{ $useCase->id }}">{{ $useCase->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="select2-hide-search filter" name="status">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="Active">{{ __('Active') }}</option>
                    <option value="Inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
        </x-backend.datatable.filter-panel>
        <x-backend.datatable.table-wrapper class="user-list-wallet user-list-processing-message need-batch-operation"
            data-namespace="\Modules\OpenAI\Entities\UseCase" data-column="id">
            @include('admin.layouts.includes.yajra-data-table')
        </x-backend.datatable.table-wrapper>
        @include('admin.layouts.includes.delete-modal')
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    'use strict';
    var searchURI = "{{ route('find.users.ajax') }}";
    var pdf = "{{ in_array('Modules\OpenAI\Http\Controllers\Admin\UseCasesController@pdf', $prms) ? '1' : '0' }}";
    var csv = "{{ in_array('Modules\OpenAI\Http\Controllers\Admin\UseCasesController@csv', $prms) ? '1' : '0' }}";
    var listContaner = "use-case-list-container";
    var endRoute = "/use-case/";
</script>

<script src="{{ asset('public/dist/js/custom/permission.min.js') }}"></script>
<script src="{{ asset('Modules/OpenAI/Resources/assets/js/admin/use-case.min.js') }}"></script>
<script src="{{ asset('public/dist/js/custom/document-list.min.js') }}"></script>
@endsection
