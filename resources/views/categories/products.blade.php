@extends('layouts.app')
@section('title', $category->name)

@section('content')

    <div class="d-flex align-items-center justify-content-center mb-3">
        <div class="h4 mb-0 flex-grow-1">
            @lang('Category') "{{ $category->name }}"
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary @if (!Auth::user()->can_create) disabled @endif">
            @lang('Create')
        </a>
    </div>

    <div class="card w-100">
        <div class="card-body">
            <div class="mb-3">
                <form action="{{ route('categories.products.index', $category) }}" role="form">
                    <input type="search" name="search_query" value="{{ Request::get('search_query') }}"
                        class="form-control w-auto" placeholder="@lang('Search...')" autocomplete="off">
                    <div class="form-text">@lang('You can also use a scanner')</div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>@lang('Item')</th>
                            <th>@lang('Category')</th>
                            <th>@lang('Cost')</th>
                            <th>@lang('Price')</th>
                            <th>@lang('In Stock')</th>
                            <th>@lang('status.text')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach ($products as $product)
                            <tr>
                                <td class="align-middle">
                                    <div class=" d-flex align-items-center">
                                        @if ($product->image_path)
                                            <img src="{{ $product->image_url }}" class="rounded me-2" height="35"
                                                alt="{{ $product->name }}">
                                        @endif
                                        <div class="fw-bold"> {{ $product->full_name }}</div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class=" d-flex align-items-center">
                                        @if ($product->category->image_path)
                                            <img src="{{ $product->category->image_url }}" class="rounded me-2"
                                                height="35" alt="{{ $product->category->name }}">
                                        @endif
                                        <div>
                                            <a href="{{ route('categories.products.index', $product->category) }}"
                                                class=" text-decoration-none">
                                                {{ $product->category->name }}
                                            </a>
                                        </div>
                                    </div>

                                </td>
                                <td class="align-middle" lang="en">
                                    {{ $product->table_view_cost }}
                                </td>
                                <td class="align-middle" lang="en">
                                    {{ $product->table_view_price }}
                                </td>
                                <td class="align-middle">
                                    @if ($product->track_stock)
                                        <span class="text-start" dir="ltr" lang="en">
                                            {{ $product->in_stock }}
                                        </span>
                                    @else
                                        <span class="text-muted small">
                                            N/A
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge rounded-pill {{ $product->status_badge_bg_color }}">
                                        {{ $product->status }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="dropdown d-flex">
                                        <button class="btn btn-link me-auto text-black p-0" type="button"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <x-heroicon-o-ellipsis-horizontal class="hero-icon" />
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end animate slideIn shadow-sm"
                                            aria-labelledby="dropdownMenuButton1">
                                            @can_edit
                                            <li>
                                                <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                                    @lang('Edit')
                                                </a>
                                            </li>
                                            @endcan_edit
                                            @can_delete
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                        id="form-{{ $product->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-link p-0 m-0 w-100 text-start text-decoration-none text-danger"
                                                            onclick="submitDeleteForm('{{ $product->id }}')">
                                                            @lang('Delete')
                                                        </button>
                                                    </form>
                                                </a>
                                            </li>
                                            @endcan_delete
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($products->isEmpty())
                    <x-no-data />
                @endif
            </div>
            <div>
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        function submitDeleteForm(id) {
            const form = document.querySelector(`#form-${id}`);
            Swal.fire(swalConfig()).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    topbar.hide();
                }
            });
        }
    </script>
@endpush
