<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'name' => $this->name,
            'price' => $this->price,
            'wholesale_price' => $this->wholesale_price,
            'retailsale_price' => $this->retailsale_price,
            'image_url' => $this->image_url,
            'barcode' => $this->barcode,
            'wholesale_barcode' => $this->wholesale_barcode,
            'retail_barcode' => $this->retail_barcode,
            'sku' => $this->sku,
            'wholesale_sku' => $this->wholesale_sku,
            'retail_sku' => $this->retail_sku,
            'in_stock' => $this->in_stock,
            'track_stock' => (bool)$this->track_stock,
            'continue_selling_when_out_of_stock' => (bool)$this->continue_selling_when_out_of_stock,
        ];
    }
}
