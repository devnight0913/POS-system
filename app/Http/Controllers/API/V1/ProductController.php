<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $length = $request->get("length"); // Rows display per page

        $order = $request->get('order');
        $columns = $request->get('columns');
        $search = $request->get('search');
        //dd($request->get('category'));
        $columnIndex = $order[0]['column']; // Column index
        $columnName = $columns[$columnIndex]['data']; // Column name
        $columnSortOrder = $order[0]['dir']; // asc or desc
        $searchValue = $search['value']; // Search value

        $totalRecords = Product::select('count(*) as allcount')->count();   // Total records
        $iTotalDisplayRecords = Product::select('count(*) as allcount')->search($searchValue)->count();

        // Fetch records
        $records = Product::with('category')
            ->search($searchValue)
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($length)
            ->get();


            // dd($records);
        $aaData = array();
        foreach ($records as $record) {
            $aaData[] = array(
                "id" => $record->id,
                "image" => $record->image_url,
                "name" => $record->full_name,
                "category" => $record->category->name,
                "cost" => $record->table_view_cost,
                "whole_cost" => $record->whole_cost,
                // "sale_price" => $record->table_view_price,
                "retailsale_price" => $record->table_view_price,
                "sales_price" => $record->table_sales_view_price,
                "wholesale_price" => $record->table_wholesale_view_price,
                "in_stock" => $record->track_stock ? $record->in_stock : 'N/A',
                "is_active" => $record->is_active,
                "status" => $record->status,
                "status_badge_bg_color" => $record->status_badge_bg_color,
                "created_at" => $record->created_at,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $iTotalDisplayRecords,
            "aaData" => $aaData
        );
        
        return response()->json($response);
    }
}
