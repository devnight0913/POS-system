export interface IProduct {
    id: string;
    name: string;
    full_name: string;
    image_url: string;
    wholesale_barcode: string;
    retail_barcode: string;
    wholesale_sku: string;
    retail_sku: string;
    price: number | undefined;
    wholesale_price: number | undefined;
    retailsale_price: number | undefined;
    in_stock: number;
    track_stock: boolean;
    continue_selling_when_out_of_stock: boolean;
}
