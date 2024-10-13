export interface ICustomer {
    id: string;
    name: string;
    full_address: string;
    contact: string;
    order_details: [
        {
            product_id: string;
            price: number;
        }
    ] | [];
}
