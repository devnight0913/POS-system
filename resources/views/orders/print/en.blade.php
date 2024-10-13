<html lang="en" dir="ltr">

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
            font-size: 13px;
            padding: 5px;
        }
        .table2 {
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table2>tbody>tr>td {
            border: 1px solid black;
            font-size: 13px;
            width: 130px;
            padding: 5px;
        }
    </style>
</head>

<body>

    <div style="margin: 10px; margin-top: 30px;">
        <div style="margin-top: 1rem; margin-bottom: 0.2rem;text-align: center !important;">
<!--
            @if ($settings->logo)
                <div style="padding-right: 1rem;padding-left: 1rem;margin-bottom: 1rem">
                    <div>{!! $settings->logo  !!}</div>
                </div>
            @else
                @if ($settings->storeName)
                    <div style="font-size: 1rem;">{{ $settings->storeName }}</div>
                @endif
            @endif
    -->

            <div style="font-size: 13px;">Sky Market</div>
            <div style="font-size: 13px;">@lang('SALE INVOICE')</div>
            <div style="font-size: 13px;">{{ $order->number }}</div>
            <div style="font-size: 13px; display: flex; justify-content: flex-end; gap: 10px;">
                <div>@lang('DATE') : </div>
                <div>{{ $order->date_view }}</div>
            </div>

            @if ( $order->customer_id )
                <div style="font-size: 1rem; display: flex; justify-content: space-between; gap: 15px;">
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

            <div style="font-size:10px; display: flex; gap: 10px;">
                <div>@lang('Address') : </div>
                @if ($settings->storeAddress)
                    <div style="font-size: 13px;">{{ $settings->storeAddress }}</div>
                @endif
            </div>
         
            <div style="font-size:10px; display: flex; gap: 10px;">
                <div> السجل التجاري: </div>
                @if ($settings->storePhone)
                    <div style="font-size: 13px;">2069478</div>
                @endif
            </div>
            <div style="font-size:10px; display: flex; gap: 10px;">
                <div>@lang('Phone') : </div>
                @if ($settings->storePhone)
                    <div style="font-size: 13px;">{{ $settings->storePhone }}</div>
                @endif
            </div>
        </div>
        <div style="margin-top: 20px;">
            <table style="width: 100%;" class="table1">
                <thead>
                    <tr>
                        <th style="font-size: 10px; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 1px;">@lang('Name')</div>
                        </th>
                        <th style="font-size: 10px; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 1px;">@lang('Qty')</div>
                        </th>
                        <th style="font-size: 10px; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 1px;">@lang('U Price')</div>
                        </th>
                        <th style="font-size: 10px; border: none;">
                            <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 1px;">@lang('Total')</div>
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
                                            <td>Total</td>
                                            <td>{{ currency_format($order->subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                    <tbody>
                                        <tr>
                                            <td>Discount</td>
                                            <td>$ {{ $order->discount }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                  <!--  <tbody>
                                        <tr>
                                            <td>Disc</td>
                                            <td>{{ currency_format($order->discount * $order->subtotal) }}</td>
                                        </tr>
                                    </tbody> -->
                                    <tbody>
                                        <tr>
                                            <td>Delivery </td>
                                            <td>$ {{ $order->delivery_charge }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table2">
                                    <tbody>
                                        <tr>
                                            <td>Total After Discount</td>
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
                        شكرا لزيارتكم" من دون حدا سي
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
