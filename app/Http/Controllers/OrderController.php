<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Drawer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use \Milon\Barcode\DNS1D;

class OrderController extends Controller
{

    public function index(Request $request): View
    {
        $orders = Order::with('customer', 'user', 'order_details')->search(trim($request->search_query))->latest()->paginate(10);

        $totalDiscount = Order::sum('discount');
        $totalCost = OrderDetail::sum('total_cost');
        $totalSold = OrderDetail::sum('total') - $totalDiscount;
        $totalProfit = $totalSold - $totalCost;


        $totalDiscountToday = Order::whereDate('created_at', Carbon::today())->sum('discount');
        $todayQuery = OrderDetail::whereDay('created_at', Carbon::today());
        $totalOrdersToday =  Order::whereDay('created_at', Carbon::today())->count();
        $totalCostToday = $todayQuery->sum('total_cost');
        $totalSoldToday = $todayQuery->sum('total') - $totalDiscountToday;
        $totalProfitToday = $totalSoldToday - $totalCostToday;


        $thisMonthQuery = OrderDetail::whereMonth('created_at', Carbon::now()->month);
        $totalOrdersThisMonth =  Order::whereMonth('created_at', Carbon::now()->month)->count();
        $totalDiscountThisMonth = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', date('Y'))->sum('discount');

        $totalCostThisMonth = $thisMonthQuery->sum('total_cost');
        $totalSoldThisMonth = $thisMonthQuery->sum('total') - $totalDiscountThisMonth;
        $totalProfitThisMonth = $totalSoldThisMonth - $totalCostThisMonth;

        $thisYearQuery = OrderDetail::whereYear('created_at', date('Y'));
        $totalOrdersThisYear =  Order::whereYear('created_at', date('Y'))->count();
        $totalDiscountThisYear = Order::whereYear('created_at', date('Y'))->sum('discount');

        $totalCostThisYear = $thisYearQuery->sum('total_cost');
        $totalSoldThisYear = $thisYearQuery->sum('total') - $totalDiscountThisYear;
        $totalProfitThisYear = $totalSoldThisYear - $totalCostThisYear;

        return view('orders.index', [
            'orders' => $orders,
            'authors' => User::orderBy('created_at', 'ASC')->get(),
            'settings' => Settings::getValues(),

            'totalOrders' => $orders->total(),
            'totalCost' => currency_format($totalCost),
            'totalSold' => currency_format($totalSold),
            'totalProfit' => currency_format($totalProfit),

            'totalOrdersToday' => $totalOrdersToday,
            'totalCostToday' => currency_format($totalCostToday),
            'totalSoldToday' => currency_format($totalSoldToday),
            'totalProfitToday' => currency_format($totalProfitToday),


            'totalOrdersThisMonth' => $totalOrdersThisMonth,
            'totalCostThisMonth' => currency_format($totalCostThisMonth),
            'totalSoldThisMonth' => currency_format($totalSoldThisMonth),
            'totalProfitThisMonth' => currency_format($totalProfitThisMonth),

            'totalOrdersThisYear' => $totalOrdersThisYear,
            'totalCostThisYear' => currency_format($totalCostThisYear),
            'totalSoldThisYear' => currency_format($totalSoldThisYear),
            'totalProfitThisYear' => currency_format($totalProfitThisYear),
        ]);
    }


    public function show(string $id): View
    {
        $order = Order::with('customer', 'user', 'order_details', 'order_details.product.category')
            ->findOrFail($id);

        return view('orders.show', [
            'order' => $order
        ]);
    }

    public function edit(string $id): View
    {
        $order = Order::with('customer', 'user', 'order_details', 'order_details.product')->findOrFail($id);

        return view('orders.edit', [
            'order' => $order,
        ]);
    }

    public function print(string $id): View
    {
        $order = Order::with('customer', 'user', 'order_details.product.category')->findOrFail($id);


        $view = "orders.print.{$this->settings()->lang}";

        return view($view, [
            'order' => $order
        ]);
    }
    public function printStats(): View
    {
        $orders = Order::with('customer', 'user', 'order_details')->latest()->paginate(10);

        $totalDiscount = Order::sum('discount');
        $totalCost = OrderDetail::sum('total_cost');
        $totalSold = OrderDetail::sum('total') - $totalDiscount;
        $totalProfit = $totalSold - $totalCost;


        $totalDiscountToday = Order::whereDate('created_at', Carbon::today())->sum('discount');
        $todayQuery = OrderDetail::whereDay('created_at', Carbon::today());
        $totalOrdersToday =  Order::whereDay('created_at', Carbon::today())->count();
        $totalCostToday = $todayQuery->sum('total_cost');
        $totalSoldToday = $todayQuery->sum('total') - $totalDiscountToday;
        $totalProfitToday = $totalSoldToday - $totalCostToday;


        $thisMonthQuery = OrderDetail::whereMonth('created_at', Carbon::now()->month);
        $totalOrdersThisMonth =  Order::whereMonth('created_at', Carbon::now()->month)->count();
        $totalDiscountThisMonth = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', date('Y'))->sum('discount');

        $totalCostThisMonth = $thisMonthQuery->sum('total_cost');
        $totalSoldThisMonth = $thisMonthQuery->sum('total') - $totalDiscountThisMonth;
        $totalProfitThisMonth = $totalSoldThisMonth - $totalCostThisMonth;

        $thisYearQuery = OrderDetail::whereYear('created_at', date('Y'));
        $totalOrdersThisYear =  Order::whereYear('created_at', date('Y'))->count();
        $totalDiscountThisYear = Order::whereYear('created_at', date('Y'))->sum('discount');

        $totalCostThisYear = $thisYearQuery->sum('total_cost');
        $totalSoldThisYear = $thisYearQuery->sum('total') - $totalDiscountThisYear;
        $totalProfitThisYear = $totalSoldThisYear - $totalCostThisYear;

        return view('orders.stats-print', [
            'orders' => $orders,
            'authors' => User::orderBy('created_at', 'ASC')->get(),
            'settings' => Settings::getValues(),

            'totalOrders' => $orders->total(),
            'totalCost' => currency_format($totalCost),
            'totalSold' => currency_format($totalSold),
            'totalProfit' => currency_format($totalProfit),

            'totalOrdersToday' => $totalOrdersToday,
            'totalCostToday' => currency_format($totalCostToday),
            'totalSoldToday' => currency_format($totalSoldToday),
            'totalProfitToday' => currency_format($totalProfitToday),


            'totalOrdersThisMonth' => $totalOrdersThisMonth,
            'totalCostThisMonth' => currency_format($totalCostThisMonth),
            'totalSoldThisMonth' => currency_format($totalSoldThisMonth),
            'totalProfitThisMonth' => currency_format($totalProfitThisMonth),

            'totalOrdersThisYear' => $totalOrdersThisYear,
            'totalCostThisYear' => currency_format($totalCostThisYear),
            'totalSoldThisYear' => currency_format($totalSoldThisYear),
            'totalProfitThisYear' => currency_format($totalProfitThisYear),
        ]);
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($order->has_customer) {
            $transaction = Transaction::where('customer_id',  $order->customer_id)->where('reference_number', $order->number)->first();
            if ($transaction) {
                $transaction->delete();
            }
        }
        foreach ($order->order_details as $detail) {
            $product = $detail->product;
            if ($product->track_stock) {
                $product->in_stock += $detail->quantity;
                $product->save();
            }
        }
        $order->delete();
        return Redirect::back()->with("success", __("Deleted"));
    }

    public function showAnalytics(): View
    {
        $totalPerMonth = OrderDetail::select(
            DB::raw('strftime("%m %Y", created_at) as date'),
            DB::raw('SUM(total) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends(['display_total']);
            });

        $totalDiscountPerMonth = Order::select(
            DB::raw('strftime("%m %Y", created_at) as date'),
            DB::raw('SUM(discount) as sum_discount'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()
            ->each(function ($order) {
                $order->setAppends([]);
            });


        $totalOrdersPerMonth = Order::select(
            DB::raw('strftime("%m %Y", created_at) as date'),
            DB::raw('count(*) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });

        $totalProfitMonth = OrderDetail::select(
            DB::raw('strftime("%m %Y", created_at) as date'),
            DB::raw('SUM(total) - SUM(total_cost) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });
        $totalCostMonth = OrderDetail::select(
            DB::raw('strftime("%m %Y", created_at) as date'),
            DB::raw('SUM(total_cost) as total'),
            DB::raw('max(created_at) as createdAt')
        )->groupBy('date')
            ->orderBy(DB::raw("createdAt"), 'ASC')->take(12)->get()->each(function ($order) {
                $order->setAppends([]);
            });
        return view('orders.analytics.show', [
            'totalPerMonth' => $totalPerMonth,
            'totalOrdersPerMonth' => $totalOrdersPerMonth,
            'totalProfitMonth' => $totalProfitMonth,
            'totalCostMonth' => $totalCostMonth,

            'totalDiscountPerMonth' => $totalDiscountPerMonth,
            'totalDiscountSum' => $totalDiscountPerMonth->sum('sum_discount'),
        ]);
    }

    private function hasCashDrawer(): bool
    {
        return (bool)Settings::getValue(Settings::ENABLE_CASH_DRAWER);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'subtotal' => ['required', 'numeric'],
            'delivery_charge' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'tender_amount' => ['required', 'numeric'],
            //'change' => ['required', 'numeric'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'remarks' => ['nullable', 'string', 'max:3000'],
        ]);

        $oldOrderHasCustomer = $order->has_customer;
        $oldCustomerId = $order->customer_id;



        $tender_amount = $request->tender_amount;
        $total = $request->total;

        $cart = (object) $request->cart;
        $deletedProducts = (object) $request->deletedProducts;
        $order->subtotal = $request->subtotal;
        $order->delivery_charge = $request->delivery_charge;
        $order->discount = $request->discount;
        $order->total = $request->total;
        $order->tender_amount = $request->tender_amount;
        $order->change = $request->change ?? 0;
        $order->tax_rate = $request->tax_rate;
        $order->vat_type = $request->vat_type;
        $order->remarks = $request->remarks;
        $order->exchange_rate = Settings::getValue(Settings::EXCHANGE_RATE);
        $order->exchange_rate_currency = Settings::getValue(Settings::EXCHANGE_CURRENCY);
        $order->price_type = $request->price_type;

        if ($request->has('customer')) {
            $customer = (object) $request->customer;
            $order->customer_id = $customer->id;
        } else {
            $order->customer_id = null;
        }
        $order->save();

        if ($request->has('customer')) {
            $customer = (object) $request->customer;

            if ($oldOrderHasCustomer) {
                $transaction = Transaction::where('customer_id',  $customer->id)->where('reference_number', $order->number)->first();
            } else {
                $transaction = new Transaction();
            }

            $transaction->credit = $tender_amount;
            $transaction->debit = $total;

            $transaction->customer_id = $customer->id;
            $transaction->description = $request->remarks;
            $transaction->reference_number = $order->number;
            $transaction->save();
        } else {
            if ($oldOrderHasCustomer) {
                $transaction = Transaction::where('customer_id',  $oldCustomerId)->where('reference_number', $order->number)->first();
                if ($transaction) {
                    $transaction->delete();
                }
            }
        }


        if ($this->hasCashDrawer()) {
            $drawer = Drawer::where('order_id', $order->id)->first();
            if ($drawer) {
                $drawer->amount = $order->tender_amount;
                $drawer->save();
            }
        }


        $order->order_details()->delete();

        foreach ($cart as $cartItem) {
            $cartItem = (object)$cartItem;
            $product = Product::where('id', $cartItem->id)->first(); // check item if valid
            if ($product) {
                $quantity = $cartItem->quantity > 0 ? $cartItem->quantity : 0; //prevent vegetive numbers
                $productPrice =  $cartItem->price ?? 0;
                // dd($cartItem);
                $oldQuantity = $cartItem->old_quantity ?? 0;
                $order_detail = new OrderDetail();

                $order_detail->quantity = $quantity;

                $order_detail->price = $productPrice;
                $order_detail->wholesale_price = $cartItem->wholesale_price;
                $order_detail->retailsale_price = $cartItem->retailsale_price;
                $order_detail->price_type = $request->price_type;
                $order_detail->cost = $product->cost_value;
                $order_detail->created_at = $order->created_at;

                $order_detail->total = $quantity * $productPrice;
                $order_detail->total_cost = $quantity * $product->cost_value;


                if ($product->track_stock) {
                    $currentInStock =  $product->in_stock;
                    $product->in_stock = $currentInStock + $oldQuantity - $quantity;
                    $product->save();
                }

                $order_detail->product()->associate($product);
                $order_detail->order()->associate($order);
                $order_detail->save();
            }
        }

        foreach ($deletedProducts as $deletedProduct) {
            $deletedProduct = (object)$deletedProduct;
            $product = Product::where('id', $deletedProduct->id)->first(); // check item if valid
            if ($product) {
                $product->in_stock += $deletedProduct->old_quantity;
                $product->save();
            }
        }
        return $this->jsonResponse();
    }



    public function store(Request $request)
    {


        $request->validate([
            'subtotal' => ['required', 'numeric'],
            'delivery_charge' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'tender_amount' => ['required', 'numeric'],
            // 'return_amount' => ['required', 'numeric'],
            //'change' => ['required', 'numeric'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'remarks' => ['nullable', 'string', 'max:3000']
        ]);


        $cart = (object) $request->cart;
        $tender_amount = $request->tender_amount;
        // $return_amount = $request->return_amount;
        $total = $request->total;

        $order = new Order();
        $order->subtotal = $request->subtotal;
        $order->delivery_charge = $request->delivery_charge;
        $order->discount = $request->discount;
        $order->total = $total;
        $order->tender_amount = $tender_amount;
        // $order->return_amount = $return_amount;
        $order->change = $request->change ?? 0;
        $order->vat_type = $request->vat_type;
        $order->tax_rate = $request->tax_rate;
        $order->remarks = $request->remarks;
        $order->type = $request->type;
        $order->show_exchange_rate = Settings::getValue(Settings::SHOW_EXCHANGE_RATE_ON_RECEIPT);;
        $order->exchange_rate = Settings::getValue(Settings::EXCHANGE_RATE);
        $order->exchange_rate_currency = Settings::getValue(Settings::EXCHANGE_CURRENCY);
        $order->price_type = $request->price_type;

        if ($request->has('customer')) {
            $customer = (object) $request->customer;
            $order->customer_id = $customer->id;
        }
        $order->user_id = $request->user()->id;
        $order->save();

        if ($request->has('customer')) {
            $customer = (object) $request->customer;

            $transaction = new Transaction();
            $transaction->credit = $tender_amount;
            $transaction->debit = $total;

            $transaction->customer_id = $customer->id;
            $transaction->description = $request->remarks;
            $transaction->reference_number = $order->number;
            $transaction->save();
        }



        if ($this->hasCashDrawer()) {
            $drawer = new Drawer();
            $drawer->order_id = $order->id;
            $drawer->amount = $tender_amount;
            $drawer->save();
        }

        if ($this->hasCashDrawer()) {
            $Inventory = new Inventory();
            $Inventory->order_id = $order->id;
            if ($request->has('customer')) {
                $customer = (object) $request->customer;
                $Inventory->customer_id = $customer->id;
            }
            $Inventory->amount = $tender_amount;
            $Inventory->save();
        }

        foreach ($cart as $cartItem) {
            $cartItem = (object)$cartItem;
            $product = Product::where('id', $cartItem->id)->first(); // check item if valid
            if ($product) {
                $quantity = $cartItem->quantity > 0 ? $cartItem->quantity : 0; //prevent vegetive numbers
                $productPrice = $cartItem->price ?? 0;
                $productCost = $product->cost_value ?? 0;
                $order_detail = new OrderDetail();
                $order_detail->quantity = $quantity;
                $order_detail->price = $productPrice;
                $order_detail->wholesale_price = $cartItem->wholesale_price;
                $order_detail->retailsale_price = $cartItem->retailsale_price;
                $order_detail->price_type = $request->price_type;
                $order_detail->cost = $productCost;
                $order_detail->total = $quantity * $productPrice;
                $order_detail->total_cost = $quantity * $productCost;
                $order_detail->product()->associate($product);
                $order_detail->order()->associate($order);
                $order_detail->save();


                if ($product->track_stock) {
                    $product->in_stock -= $quantity;
                    $product->save();
                }

            }
        }


        return $this->jsonResponse([
            'order' => Order::with('customer', 'order_details.product.category')->findOrFail($order->id),
            'barcode' => DNS1D::getBarcodeSVG($order->number, 'C128', 2, 30, 'black', false),
            'tenden' => $request->tender_amount
        ]);
    }



    public function filter(Request $request): View
    {
        $now = Carbon::now()->toDateString();
        $fromDate =  is_null($request->from) ? $now : $request->from;
        $toDate =  is_null($request->to) ? $now : $request->to;
        $from = "{$fromDate} 00:00:00";
        $to = "{$toDate} 23:59:59";

        $customerName = $request->name;
        $authorId = $request->author;
        if (!is_null($customerName)) {
            $customer = Customer::search($customerName)->first();
            $orders = $customer->orders()->ofAuthor($authorId)->latest()->whereBetween('created_at', [$from, $to])->paginate(10);
            $totalDiscount = $customer->orders()->ofAuthor($authorId)->whereBetween('created_at', [$from, $to])->sum('discount');
            $totalCost =  $customer->order_details->whereBetween('created_at', [$from, $to])->sum('total_cost');
            $totalSold =  $customer->order_details->whereBetween('created_at', [$from, $to])->sum('total')  - $totalDiscount;


            $totalDiscountPerMonth = $customer->orders()->ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('SUM(discount) as sum_discount'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()
                ->each(function ($order) {
                    $order->setAppends([]);
                });


            $totalPerMonth = $customer->orders()->ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])
                ->get()->each(function ($order) {
                    $order->setAppends([]);
                });

            $totalOrdersPerMonth = $customer->orders()->ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends([]);
                });
        } else {
            $orders = Order::ofAuthor($authorId)->latest()->whereBetween('created_at', [$from, $to])->paginate(10);
            $totalDiscount = Order::ofAuthor($authorId)->whereBetween('created_at', [$from, $to])->sum('discount');
            $totalCost =  OrderDetail::ofAuthor($authorId)->whereBetween('created_at', [$from, $to])->sum('total_cost');
            $totalSold =  OrderDetail::ofAuthor($authorId)->whereBetween('created_at', [$from, $to])->sum('total')  - $totalDiscount;

            $totalPerMonth = Order::ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends(['display_total']);
                });

            $totalDiscountPerMonth = Order::ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('SUM(discount) as sum_discount'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()
                ->each(function ($order) {
                    $order->setAppends([]);
                });

            $totalOrdersPerMonth = Order::ofAuthor($authorId)->select(
                DB::raw('strftime("%m %Y", created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('max(created_at) as createdAt')
            )->groupBy('date')
                ->orderBy(DB::raw("createdAt"), 'ASC')->whereBetween('created_at', [$from, $to])->get()->each(function ($order) {
                    $order->setAppends([]);
                });
        }
        $totalProfit = $totalSold - $totalCost;

        return view('orders.filter', [
            'orders' => $orders,
            'totalOrders' => $orders->total(),
            'totalCost' => currency_format($totalCost),
            'totalSold' => currency_format($totalSold),
            'totalProfit' => currency_format($totalProfit),

            'totalPerMonth' => $totalPerMonth,
            'totalOrdersPerMonth' => $totalOrdersPerMonth,

            'totalDiscountPerMonth' => $totalDiscountPerMonth,
            'totalDiscountSum' => $totalDiscountPerMonth->sum('sum_discount'),

            'toDate' =>  Carbon::parse($toDate)->format('d F Y'),
            'fromDate' =>  Carbon::parse($fromDate)->format('d F Y'),
            'authors' => User::orderBy('created_at', 'ASC')->get(),

        ]);
    }
}
