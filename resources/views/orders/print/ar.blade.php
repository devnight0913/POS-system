
<html lang="ar" dir="rtl">

<head>
    <title>{{ $order->number }} </title>
    <style>
        @page { size: auto;  margin: 0mm; }
        .table1 {
            border-collapse: collapse;
        }
        .table1>tbody:before{
            content: "-";
            display: block;
            line-height: 10px;
            color: transparent;
        }
        tr {
            
            min-height: 50px;
        }
        .table1>tbody>tr>td {
            border: 1px solid black;
            text-align: center;
            font-size: 1.2rem;
            padding: 5px;
        }
        .table2 {
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table2>tbody>tr>td {
            border: 1px solid black;
            font-size: 1.5rem;
            width: 200px;
            padding: 5px;
        }
    </style>
</head>

<body>

    <div style="margin: 30px; margin-top: 80px;">
        <div style="margin-top: 2rem; margin-bottom: 0.2rem;text-align: center !important;">

            <!-- @if ($settings->logo)
                <div style="padding-right: 1rem;padding-left: 1rem;margin-bottom: 1rem">
                    {!! $settings->logo  !!}
                </div>
            @else
                @if ($settings->storeName)
                    <div style="font-size: 1.50rem;">{{ $settings->storeName }}</div>
                @endif
            @endif -->
            <div style="font-size: 2.00rem;">Sky Market</div>
            <div style="font-size: 2.00rem;">@lang('SALE INVOICE')</div>
            <div style="font-size: 1.00rem;">{{ $order->number }}</div>
            <div style="font-size: 1.50rem; display: flex; justify-content: flex-end; gap: 20px;">
                <div>@lang('Date') : </div>
                <div>{{ $order->date_view }}</div>
            </div>

            @if ( $order->customer_id )
                <div style="font-size: 1.50rem; display: flex; justify-content: space-between; gap: 30px;">
                    <div style="display: flex; gap: 20px;">
                        <div>@lang('Client') : </div>
                        <div>{{ $order->customer->name }}</div>
                    </div>
                    @if ($settings->currencySymbol)
                        <div>
                            {{ $settings->currencySymbol }}
                        </div>
                    @endif
                </div>
            @endif

            <div style="font-size: 1.50rem; display: flex; gap: 20px;">
                <div>@lang('Address') : </div>
                @if ($settings->storeAddress)
                    <div style="font-size: 1.50rem;">{{ $settings->storeAddress }}</div>
                @endif
            </div>
            <div style="font-size: 1.50rem; display: flex; gap: 20px;">
                <div>@lang('Phone') : </div>
                @if ($settings->storePhone)
                    <div style="font-size: 1.50rem;">{{ $settings->storePhone }}</div>
                @endif
            </div>
        </div>
        <div style="margin-top: 20px;">
            <table style="width: 100%;" class="table1">
                <thead>
                    <tr>
                        <th style="font-size: 1.50rem; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">@lang('Name')</div>
                        </th>
                        <th style="font-size: 1.50rem; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">@lang('Quantity')</div>
                        </th>
                        <th style="font-size: 1.50rem; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">@lang('U Price')</div>
                        </th>
                        <th style="font-size: 1.50rem; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">@lang('Total')</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->order_details as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ currency_format($detail->price) }}</td>
                            <td>{{ currency_format($detail->total) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4">
                            <div style="float: right;">
                                <table class="table2" style="margin-top: 30px;">
                                    <tbody>
                                        <tr>
                                            <td>@lang('Total')</td>
                                            <td>{{ currency_format($order->subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                    <tbody>
                                        <tr>
                                            <td>@lang('Discount') </td>
                                            <td>$ {{ $order->discount }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                  <!--  <tbody>
                                        <tr>
                                            <td>@lang('Discount')</td>
                                            <td>{{ currency_format($order->discount * $order->subtotal) }}</td>
                                        </tr>
                                    </tbody> -->
                                    <tbody>
                                        <tr>
                                            <td>Delivery </td>
                                            <td>${{ $order->delivery_charge }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                    <tbody>
                                        <tr>
                                            <td>@lang('Total After Discount')</td>
                                            <td>
                                                <div>{{ currency_format($order->total) }}</div>
                                                <div>{{ $order->receipt_exchange_rate }}</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="height: 50px;">
                           شكرا لزيارتكم
                        </td>
                    </tr>
                   
                    
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.print();
    });
</script>
