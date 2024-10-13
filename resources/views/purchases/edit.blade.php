@extends('layouts.app')
@section('title', __('Edit') . ' ' . __('Purchase'))

@section('content')
    <div class="d-flex align-items-center justify-content-center mb-3">
        <div class="h4 mb-0 flex-grow-1">@lang('Edit') @lang('Purchase')</div>
        <x-back-btn href="{{ route('purchases.index') }}" />
    </div>
    <form action="{{ route('purchases.update', $purchase) }}" method="POST" role="form">
        @method('PUT')
        @csrf
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier" class="form-label">@lang('Supplier')</label>
                        <select name="supplier" id="supplier" class="form-select">
                            <option value="">@lang('Select Supplier')</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected($purchase->supplier_id == $supplier->id)>{{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('suppliers.create') }}" class="small text-decoration-none">
                            + @lang('Create New Supplier')
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">@lang('Date')</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', $purchase->date->format('Y-m-d')) }}">
                        @error('date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class=" mb-3">
                    <label for="reference_number" class="form-label">@lang('Reference Number')</label>
                    <input type="text" name="reference_number"
                        class="form-control @error('reference_number') is-invalid @enderror"
                        value="{{ old('reference_number', $purchase->reference_number) }}">
                    @error('reference_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex">
                    <div class="form-label flex-grow-1">@lang('Items')*</div>
                    <div>
                        <a href="{{ route('products.create') }}" class="small text-decoration-none">
                            + @lang('Create New Item')
                        </a>
                    </div>
                </div>
                <div class=" table-responsive mb-3">

                    <table class="table table-bordered mb-1" id="table-items">
                        <thead>
                            <tr>
                                <th class=" text-center text-decoration-none fw-bold">@lang('Item')</th>
                                <th class=" text-center text-decoration-none fw-bold">@lang('Quantity')</th>
                                <th class=" text-center text-decoration-none fw-bold">
                                    @lang('Unit Cost') ({{ $currency }})
                                </th>
                                <th class=" text-center text-decoration-none fw-bold"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody">

                            @foreach ($purchase->purchase_details as $detail)
                                <tr>
                                    <td>
                                        <select class="form-select" name="item[]" required>
                                            <option value="">@lang('Select Item')</option>
                                            @foreach ($categories as $category)
                                                <optgroup label="{{ $category->name }}">
                                                    @foreach ($category->products as $product)
                                                        <option value="{{ $product->id }}" @selected($detail->product_id == $product->id)>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class=" form-control input-stock focus-select-text text-center"
                                            name="quantity[]" value="{{ $detail->quantity }}"
                                            value="{{ $detail->quantity }}" required>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control input-number focus-select-text text-center"
                                            value="{{ $detail->cost }}" name="cost[]" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-link p-0 text-danger btn-remove"><svg
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="hero-icon-sm">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg></button>
                                    </td>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary btn-xs" id="btn-new-item">+ @lang('Add New Item')</button>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="notes" class="form-label">@lang('Notes')</label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $purchase->notes) }}</textarea>

                    @error('notes')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="btn btn-primary px-4">@lang('Save')</button>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('script')
    <script>
        var tbody = document.querySelector('#tbody');
        var newItemBtn = document.querySelector('#btn-new-item');
        newItemBtn.addEventListener('click', function() {
            tbody.insertAdjacentHTML(
                'beforeend',
                '<tr><td><select class="form-select" name="item[]" required> <option value="">@lang('Select Item')</option>@foreach ($categories as $category)<optgroup label="{{ $category->name }}">@foreach ($category->products as $product)<option value="{{ $product->id }}" @selected($detail->product_id == $product->id)>{{ $product->name }}</option>@endforeach</optgroup>@endforeach</select></td><td><input type="text" class=" form-control input-stock focus-select-text text-center" name="quantity[]" value="{{ $detail->quantity }}" required></td><td><input type="text" class="form-control input-number focus-select-text text-center" value="{{ $detail->cost }}" name="cost[]" required></td>' +
                '<td><button type="button" class="btn btn-link p-0 text-danger btn-remove"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="hero-icon-sm"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button></td></tr>'
            );

            var keys = '0123456789.';
            var checkInputNumber = function(e) {
                var key = typeof e.which == 'number' ? e.which : e.keyCode;
                var start = this.selectionStart,
                    end = this.selectionEnd;
                var filtered = this.value.split('').filter(filterInput);
                this.value = filtered.join('');
                var move = filterInput(String.fromCharCode(key)) || key == 0 || key == 8 ? 0 : 1;
                this.setSelectionRange(start - move, end - move);
            };
            var filterInput = function(val) {
                return keys.indexOf(val) > -1;
            };
            var formControlOnFocusList = [].slice.call(document.querySelectorAll('.focus-select-text'));
            formControlOnFocusList.map(function(formControlOnFocusElement) {
                formControlOnFocusElement.addEventListener('focus', () => {
                    formControlOnFocusElement.select();
                });
            });
            var numberInputList = [].slice.call(document.querySelectorAll('.input-number'));
            numberInputList.map(function(numberInputElement) {
                numberInputElement.addEventListener('input', checkInputNumber);
            });

            var stockKeys = '0123456789.-';
            var checkInputStock = function(e) {
                var key = typeof e.which == 'number' ? e.which : e.keyCode;
                var start = this.selectionStart,
                    end = this.selectionEnd;
                var filtered = this.value.split('').filter(filterInputStock);
                this.value = filtered.join('');
                var move = filterInputStock(String.fromCharCode(key)) || key == 0 || key == 8 ? 0 : 1;
                this.setSelectionRange(start - move, end - move);
            };
            var filterInputStock = function(val) {
                return stockKeys.indexOf(val) > -1;
            };

            var stockInputList = [].slice.call(document.querySelectorAll('.input-stock'));
            stockInputList.map(function(numberInputElement) {
                numberInputElement.addEventListener('input', checkInputStock);
            });
        });
        document.addEventListener('click', function(event) {
            if (event.target.matches('.btn-remove, .btn-remove *')) {
                event.target.closest('tr').remove();
            }
        }, false);
    </script>
@endpush
