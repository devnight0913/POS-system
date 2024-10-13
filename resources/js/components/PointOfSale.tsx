import React, { Component } from 'react';
import ReactDOM from 'react-dom/client';
import Swal from 'sweetalert2';
import { ICategory } from '../interfaces/category.interface';
import { IProduct } from '../interfaces/product.interface';
import httpService from '../services/http.service';
import { currency_format, floatValue, swalConfig, t } from '../utils';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { isFullScreen, toogleFullScreen } from '../fullscreen';
import { ICustomer } from '../interfaces/customer.interface';
import { Modal } from 'bootstrap';
import uuid from 'react-uuid';
import { ArrowRightIcon, XCircleIcon, Squares2X2Icon, ArrowPathIcon, ArrowLeftIcon, ChevronDownIcon } from '@heroicons/react/24/outline';
import { UserCircleIcon } from '@heroicons/react/24/solid';

const priceFilterList = [
    "Retail Prices",
    "Wholesale Prices",
    "Super Dealer Prices"
];

interface ICartItem extends IProduct {
    cartId: string;
    quantity: number | undefined;
}

type Props = {
    settings: any;
};

type State = {
    categories: ICategory[];
    products: IProduct[];
    customers: ICustomer[];
    customer: ICustomer | undefined;
    customerName: string | null;
    customerEmail: string | null;
    customerMobile: string | null;
    customerCity: string | null;
    customerBuilding: string | null;
    customerStreet: string | null;
    customerFloor: string | null;
    customerApartment: string | null;
    cart: ICartItem[];
    showProducts: boolean;
    categoryName: string | null;
    total: number;
    subtotal: number;
    tax: number | undefined;
    vatType: string;
    deliveryCharge: number | undefined;
    discount: number | undefined;
    hasaudio: boolean | undefined;
    tenderAmount: number | undefined;
    searchValue: string | null;
    remarks: string | null;
    orderType: string;
    isFullScreen: boolean;
    isLoading: boolean;
    isLoadingCategories: boolean;
    currentPrice: number;
    productSearch: string | null;
};

class PointOfSale extends Component<Props, State> {
    constructor(props: Props) {
        super(props);

        this.state = {
            categories: [],
            products: [],
            cart: [],
            customers: [],
            customer: undefined,
            customerName: null,
            customerEmail: null,
            customerMobile: null,
            customerCity: null,
            customerBuilding: null,
            customerStreet: null,
            customerFloor: null,
            customerApartment: null,
            showProducts: false,
            categoryName: null,
            orderType: 'takeout',
            subtotal: 0,
            total: 0,
            tax: 0,
            vatType: this.getAppSettings().vatType,
            deliveryCharge: 0,
            hasaudio: true,
            discount: 0,
            searchValue: null,
            remarks: null,
            isFullScreen: isFullScreen(),
            tenderAmount: 0,
            isLoading: false,
            isLoadingCategories: true,
            currentPrice: 0,
            productSearch: null,
        };
    }
    componentDidMount() {
        var settings = this.getAppSettings();

        this.setState({ tax: settings.taxRate });
        this.setState({ deliveryCharge: settings.deliveryCharge });
        this.setState({ discount: settings.discount });
        this.setState({ hasaudio: settings.newItemAudio });
        this.setState({ vatType: settings.vatType });

        this.getCategories();
        this.calculateTotal();
        this.setState({ tenderAmount: this.state.total });
        window.onbeforeunload = event => {
            return 'Are you sure?';
        };
    }

    specialCustomerPrice = (prod: IProduct): number => {
        if (!this.state.customer) return prod.price || 0;
        if (this.state.customer.order_details.length == 0) return prod.price || 0;
        var newProd = this.state.customer.order_details.find(p => p.product_id === prod.id);
        if (!newProd) return prod.price || 0;
        return newProd.price || 0;
    };

    getCategories = (): void => {
        // console.log("ASDASD");
        httpService
            .get(`inventory/categories`)
            .then((response: any) => {
                this.setState({ categories: response.data.data });                
            })
            .finally(() => {
                this.setState({ isLoadingCategories: false });
            });
    };

    storeOrder = (): void => {
        if (this.state.cart.length == 0) {
            toast.error(t('No items has been added!', 'لم يتم إضافة اية اصناف!'));
            return;
        }

        this.setState({ isLoading: true });
        var _deliveryCharge = 0;
        if (this.getAppSettings().enableTakeoutAndDelivery) {
            _deliveryCharge = this.isOrderDelivery() ? this.state.deliveryCharge || 0 : 0;
        } else {
            _deliveryCharge = this.state.deliveryCharge || 0;
        }

        // console.log(this.state.cart);
        httpService
            .post(`/order`, {
                customer: this.state.customer,
                cart: this.state.cart,
                subtotal: this.state.subtotal,
                total: this.state.total,
                tax_rate: this.state.tax || 0,
                vat_type: this.state.vatType,
                delivery_charge: _deliveryCharge,
                discount: this.state.discount || 0,
                remarks: this.state.remarks,
                type: this.state.orderType,
                tender_amount: this.state.tenderAmount || 0,
                price_type: this.state.currentPrice || 0
            })
            .then((response: any) => {
                if (response.data) {
                    this.resetPos();
                    toast.info(t('Saved!', 'تم الحفظ'));
                    this.closeModal('checkoutModal');
                    // console.log(response.data);
                    this.printInvoice(response.data, this.getAppSettings());
                }
            })
            .finally(() => {
                this.setState({ isLoading: false });
            });
    };
    reset = (): void => {
        Swal.fire({
            title: t('Reset?', 'إعادة ضبط'),
            text: t('Any unsaved changes will be lost', 'ستفقد أي تغييرات غير محفوظة'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: t('Reset?', 'إعادة ضبط'),
            cancelButtonText: t('Cancel', 'إلغاء')
        }).then(result => {
            if (result.isConfirmed) {
                this.resetPos();
            }
        });
    };

    resetPos = (): void => {
        var settings = this.getAppSettings();
        this.setState({ products: [] });
        this.setState({ cart: [] });
        this.setState({ customers: [] });
        this.setState({ customer: undefined });
        this.setState({ customerName: null });
        this.setState({ customerEmail: null });
        this.setState({ customerMobile: null });
        this.setState({ customerCity: null });
        this.setState({ customerBuilding: null });
        this.setState({ customerStreet: null });
        this.setState({ customerFloor: null });
        this.setState({ customerApartment: null });
        this.setState({ showProducts: false });
        this.setState({ categoryName: '' });
        this.setState({ subtotal: 0 });
        this.setState({ deliveryCharge: settings.deliveryCharge });
        this.setState({ total: 0 });
        this.setState({ discount: settings.discount });
        this.setState({ tax: settings.taxRate });
        this.setState({ vatType: settings.vatType });
        this.setState({ searchValue: null });
        this.setState({ remarks: null });
        this.setState({ tenderAmount: 0 });
        this.setState({ isLoading: false });
        this.setState({ productSearch: null });
    };

    categoryClick = (category: ICategory): void => {
        this.setState({ showProducts: true });
        this.setState({ products: category.products || [] });
        this.setState({ categoryName: category.name });
    };
    backClick = (): void => {
        this.setState({ showProducts: false });
        this.setState({ products: [] });
        this.setState({ categoryName: '' });
    };

    handleDiscountChange = (event: React.ChangeEvent<HTMLInputElement>): void => {
        var value = event.target.value;
        if (Number(value) < 0) return;
        var discountValue = value == '' ? undefined : Number(value);
        this.setState({ discount: discountValue }, () => {
            this.calculateTotal();
        });
    };

    handleTaxChange = (event: React.ChangeEvent<HTMLInputElement>): void => {
        var value = event.target.value;
        if (Number(value) < 0) return;
        var taxValue = value == '' ? undefined : Number(value);
        this.setState({ tax: taxValue }, () => {
            this.calculateTotal();
        });
    };
    handleDeliveryChargeChange = (event: React.ChangeEvent<HTMLInputElement>): void => {
        var value = event.target.value;
        if (Number(value) < 0) return;
        var deliveryChargeValue = value == '' ? undefined : Number(value);
        this.setState({ deliveryCharge: deliveryChargeValue }, () => {
            this.calculateTotal();
        });
    };

    updateItemPrice = (event: React.ChangeEvent<HTMLInputElement>, item: ICartItem): void => {
        var value = event.target.value;
        let cartItems = this.state.cart;
        let _prod = this.state.cart.find(p => p.cartId === item.cartId);
        if (!_prod) return;
        if (Number(value) < 0) return;
        _prod.price = value == '' ? undefined : Number(value);
        _prod.total = value == '' ? undefined : Number(value)*_prod.quantity;
        this.setState({ cart: cartItems }, () => {
            this.calculateTotal();
        });
    };
    updateItemQtyByClick = (event: any, item: ICartItem, qty: number): void => {
        var value = qty;
        let cartItems = this.state.cart;
        let _prod = this.state.cart.find(p => p.cartId === item.cartId);
        if (!_prod) return;
        if (Number(value) < 0) return;
        _prod.quantity = Number(value) || 1;
        this.setState({ cart: cartItems }, () => {
            this.calculateTotal();
        });
    };

    toggleFullScreen = (): void => {
        toogleFullScreen();
        this.setState({ isFullScreen: !this.state.isFullScreen });
    };
    goToDashboard = (): void => {
        window.location.href = '/';
    };
    calculateTotal = (): void => {
        let _total: number = 0;
        let _subtotal: number = 0;
        if (this.state.cart.length > 0) {
            this.state.cart.map((item: ICartItem) => {
                // console.log(item.retailsale_price);
                // console.log(item.wholesale_price);
                _subtotal += item.total;
                // _subtotal += ((this.state.currentPrice === 0 ? item.retailsale_price : item.wholesale_price) || 0) * (item.quantity || 0);
            });
        }
        let taxValue: number = 0;
        if (this.state.vatType == 'add') {
            if ((this.state.tax || 0) > 0 && (this.state.tax || 0) <= 100) {
                taxValue = (Number(this.state.tax || 0) * Number(_subtotal)) / 100;
            }
        }
        var deliveryCharge: number = 0;
        if (this.getAppSettings().enableTakeoutAndDelivery) {
            if (this.isOrderDelivery()) {
                deliveryCharge = Number(this.state.deliveryCharge || 0);
            }
        } else {
            deliveryCharge = Number(this.state.deliveryCharge || 0);
        }

        _total = Number(_subtotal) + Number(taxValue) + Number(deliveryCharge) - Number(this.state.discount || 0);
        this.setState({ subtotal: _subtotal });
        this.setState({ total: _total });
        this.setState({ tenderAmount: _total });
    };

    getVat = (): number => {
        var vat = this.state.tax || 0;
        if (vat <= 0) return 0;
        var grossAmount = this.state.subtotal || 0;
        var taxAmount = this.getTaxAmount();
        return Math.round(Number(grossAmount) - Number(taxAmount));
    };

    getTaxAmount = (): number => {
        var vat = this.state.tax || 0;
        if (vat <= 0) return 0;
        var grossAmount = this.state.subtotal || 0;
        return Math.trunc(Number(grossAmount) / Number(Number(1) + Number(vat) / Number(100)));
    };

    getTotalTax = (): number => {
        let taxValue: number = 0;
        if (Number(this.state.tax || 0) > 0 && Number(this.state.tax || 0) <= 100) {
            taxValue = (Number(this.state.tax || 0) * Number(this.state.subtotal)) / 100;
        }
        return Number(taxValue);
    };
    getChangeAmount = (): number => {
        return (this.state.tenderAmount || 0) - this.state.total;
    };
    handleTenderAmountChange = (event: React.ChangeEvent<HTMLInputElement>): void => {
        var value = event.target.value;
        if (Number(value) < 0) return;
        var tenderAmount = value == '' ? undefined : Number(value);
        this.setState({ tenderAmount: tenderAmount });
    };
    handleRemarksChange = (event: React.FormEvent<HTMLTextAreaElement>): void => {
        this.setState({ remarks: event.currentTarget.value });
    };
    removeItem = (item: ICartItem): void => {
        let newCartItems = this.state.cart.filter(i => i.cartId != item.cartId);
        this.setState({ cart: newCartItems }, () => this.calculateTotal());
    };
    addToCart = (product: IProduct): void => {
        let cartItem: ICartItem = {
            cartId: uuid(),
            id: product.id,
            name: product.name,
            full_name: product.full_name,
            image_url: product.image_url,
            price: ((this.state.currentPrice === 0) ? product.retailsale_price : product.wholesale_price),
            wholesale_price: product.wholesale_price,
            retailsale_price: product.retailsale_price,
            // barcode: product.barcode,
            wholesale_barcode: product.wholesale_barcode,
            retail_barcode: product.retail_barcode,
            // sku: product.sku,
            wholesale_sku: product.wholesale_sku,
            retail_sku: product.retail_sku,
            in_stock: product.in_stock,
            track_stock: product.track_stock,
            continue_selling_when_out_of_stock: product.continue_selling_when_out_of_stock,
            quantity: 1,
            total: ((this.state.currentPrice === 0) ? product.retailsale_price : product.wholesale_price)
        };

        this.setState({ cart: [cartItem, ...this.state.cart] }, () => {
            this.calculateTotal();
        });
        if (this.state.hasaudio) {
            new Audio('/audio/public_audio_ding.mp3').play();
        }
    };

    handleSearchSubmit = (event: React.FormEvent<HTMLFormElement>): void => {
        event.preventDefault();
        let search = this.state.searchValue;
        if (!search) return;
        let searchValue = search.toLowerCase().trim();
        let productFound = false;
        this.state.categories.map((category: ICategory) => {
            let _prod;
            switch(this.state.currentPrice) {
            case 0:
                _prod = category.products.find(
                    p => p.name.toLowerCase().includes(searchValue) || p?.retail_barcode?.toLowerCase() == searchValue || p?.retail_sku?.toLowerCase() == searchValue
                    // || p?.barcode?.toLowerCase() == searchValue || p?.sku?.toLowerCase() == searchValue
                );
                break;
            case 1:
                _prod = category.products.find(
                    p => p.name.toLowerCase().includes(searchValue) || p?.wholesale_barcode?.toLowerCase() == searchValue || p?.wholesale_sku?.toLowerCase() == searchValue
                    // || p?.barcode?.toLowerCase() == searchValue || p?.sku?.toLowerCase() == searchValue
                );
                break;
            default:
                // _prod = category.products.find(
                //     p => p.name.toLowerCase().includes(searchValue) || p?.barcode?.toLowerCase() == searchValue || p?.sku?.toLowerCase() == searchValue
                // );
                return;
            }
            
            // for(let i = 0;i < this.state.cart.length;i ++)
            // {
            //     if(_prod !== undefined && _prod.id === this.state.cart[i].id) {
            //         _prod = undefined;
            //         break;
            //     }
            // }
            if (_prod) {
                this.addToCart(_prod);               
                productFound = true;
                if (productFound) {
                    this.setState({ searchValue: null });
                    var searchInput: any = document.getElementById('barcode-input');
                    if (searchInput) {
                        searchInput.value = '';
                    }
                }

                return;
            }
        });
        if (!productFound) {
            toast.error(t('No results found!', 'لم يتم العثور على نتائج!'));
        }
    };
    handleSearchChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ searchValue: event.currentTarget.value });
    };

    // productSearchSubmit = (event: React.FormEvent<HTMLFormElement>): void => {};

    productSearchChange = (event: React.FormEvent<HTMLInputElement>): void => {
        let searchValue = event.currentTarget.value.toLocaleLowerCase().trim();
        if(!searchValue){
            this.setState({ showProducts: false });
            this.setState({ products: [] });
            this.setState({ categoryName: null });
            return
        }
        let searchResultProduct: any[] = [];
        this.state.categories.map(category => {
            category.products.map((product) => {
                    if(product.name.toLocaleLowerCase().includes(searchValue)) {
                        searchResultProduct.push(product);
                    }
                }
            )
        });
        this.setState({ showProducts: true });
        this.setState({ products: searchResultProduct || [] });
        this.setState({ categoryName: "Search Result" });
    };

    handleVatTypeChange = (event: any): void => {
        this.setState({ vatType: event.target.value }, () => {
            this.calculateTotal();
        });
    };

    handleCustomerSearchChange = (event: React.FormEvent<HTMLInputElement>): void => {
        var searchQuery = event.currentTarget.value.trim();
        if (!searchQuery) {
            this.setState({ customers: [] });
            return;
        }
        httpService
            .get(`/customers/search/all?query=${searchQuery}`)
            .then((response: any) => {
                this.setState({ customers: response.data.data });
            })
            .finally(() => { });
    };

    setCustomer = (customer: ICustomer): void => {
        this.setState({ customer: customer });
    };

    selectCustomer(customer: ICustomer) {
        this.setState({ customer: customer });
        this.closeModal('customerModal');
    }

    closeModal = (id: string): void => {
        const createModal = document.querySelector(`#${id}`);
        if (createModal) {
            var modalInstance = Modal.getInstance(createModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    };
    getAppSettings = (): any => {
        return JSON.parse(this.props.settings);
    };

    currencyFormatValue = (number: any): any => {
        var settings = this.getAppSettings();
        return currency_format(
            number,
            settings.currencyPrecision,
            settings.currencyDecimalSeparator,
            settings.currencyThousandSeparator,
            settings.currencyPosition,
            settings.currencySymbol,
            settings.trailingZeros
        );
    };

    receiptExchangeRate = (): any => {
        var settings = this.getAppSettings();
        var value = Number(this.state.total) * Number(settings.exchangeRate);
        return currency_format(value, 2, '.', ',', 'before', settings.exchangeCurrency, true);
    };

    removeCustomer() {
        this.setState({ customer: undefined });
    }
    isProductAvailable = (product: IProduct): boolean => {
        if (product.continue_selling_when_out_of_stock) return true;
        if (!product.track_stock) return true;
        if (product.in_stock > 0) return true;
        return false;
    };
    updateItemQuantity = (event: React.ChangeEvent<HTMLInputElement>, item: ICartItem): void => {
        var value = event.target.value;
        let cartItems = this.state.cart;
        let _prod = this.state.cart.find(p => p.cartId === item.cartId);
        if (!_prod) return;
        if (Number(value) < 0) return;
        _prod.quantity = value == '' ? undefined : Number(value);
        _prod.total = value == '' ? undefined : Number(value)*_prod.price;
        this.setState({ cart: cartItems }, () => {
            this.calculateTotal();
        });
    };

    updateItemTotal = (event: React.ChangeEvent<HTMLInputElement>, item: ICartItem): void => {
        var value = event.target.value;
        let cartItems = this.state.cart;
        let _prod = this.state.cart.find(p => p.cartId === item.cartId);
        if (!_prod) return;
        if (Number(value) < 0) return;
        _prod.total = value == '' ? undefined : Number(value);
        this.setState({ cart: cartItems }, () => {
            this.calculateTotal();
        });
    };

    createCustomer = (e: React.FormEvent<HTMLFormElement>): void => {
        e.preventDefault();
        if (!this.state.customerName) {
            toast.error(t('Customer name is required!', 'اسم الزبون مطلوب!'));
            return;
        }
        this.setState({ isLoading: true });
        httpService
            .post(`/customers/create-new`, {
                name: this.state.customerName,
                email: this.state.customerEmail,
                mobile: this.state.customerMobile,
                city: this.state.customerCity,
                building: this.state.customerBuilding,
                street_address: this.state.customerStreet,
                floor: this.state.customerFloor,
                apartment: this.state.customerApartment
            })
            .then((response: any) => {
                this.setCustomer(response.data.data);
                this.setState({ customerName: '' });
                this.setState({ customerEmail: '' });
                this.setState({ customerMobile: '' });
                this.setState({ customerBuilding: '' });
                this.setState({ customerStreet: '' });
                this.setState({ customerFloor: '' });
                this.setState({ customerApartment: '' });
                var form = document.getElementById('create-customer-form') as HTMLFormElement;
                if (form) {
                    form.reset();
                }
                this.closeModal('customerModal');
                toast.info(t('Customer has been created', 'تم إنشاء زبون جديد'));
            })
            .finally(() => {
                this.setState({ isLoading: false });
            });
    };

    handleCustomerNameChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerName: event.currentTarget.value });
    };
    handleCustomerEmailChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerEmail: event.currentTarget.value });
    };
    handleCustomerMobileChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerMobile: event.currentTarget.value });
    };
    handleCustomerCityChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerCity: event.currentTarget.value });
    };
    handleCustomerStreetChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerStreet: event.currentTarget.value });
    };
    handleCustomerBuildingChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerBuilding: event.currentTarget.value });
    };
    handleCustomerFloorChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerFloor: event.currentTarget.value });
    };
    handleCustomerApartmentChange = (event: React.FormEvent<HTMLInputElement>): void => {
        this.setState({ customerApartment: event.currentTarget.value });
    };

    printInvoice = (data: any, settings: any): void => {
        var receipt = window.open(``, 'PRINT', 'height=600,width=300');
        var order = data.order;
        console.log("ORDER", order);
        var barcode = data.barcode;
        if (!receipt) return;
        receipt.document.write(`<html lang="${settings.lang}" dir="${settings.dir}"><head><title>Order Receipt ${order.number}</title>`);

        receipt.document.write(`<style> @page { size: auto;  margin: 0mm; }`);
        receipt.document.write(`.table1 { border-collapse: collapse; }`);
        receipt.document.write(`.table1>tbody:before{ content: "-"; display: block; line-height: 10px; color: transparent; }`);
        receipt.document.write(`tr { min-height: 50px; }`);
        receipt.document.write(`.table1>tbody>tr>td { border: 1px solid black; text-align: center; font-size: 1.2rem; padding: 5px; }`);
        receipt.document.write(`.table2 { border-collapse: collapse; margin-bottom: 15px; }`);
        receipt.document.write(`.table2>tbody>tr>td { border: 1px solid black; font-size: 1.5rem; width: 200px; padding: 5px; } </style> </head> <body>`);

        receipt.document.write(`<div style="margin: 30px; margin-top: 30px;">`);
        receipt.document.write(`<div style="margin-top: 1rem; margin-bottom: 0.2rem;text-align: center !important;">`);

        if (settings.storeName) {
            receipt.document.write(` <div style="padding-right: 1rem;padding-left: 1rem;margin-bottom: 1rem">${settings.logo}</div>`);
        } else {
            if (settings.storeName) {
                receipt.document.write(`<div style="font-size: 1.50rem;">${settings.storeName}</div>`);
            }
        }

        receipt.document.write(`<div style="font-size: 2.00rem;">${t('SALE INVOICE', 'فاتورة البيع')}</div>`);
        receipt.document.write(`<div style="font-size: 1.00rem;">${order.number}</div>`);
        receipt.document.write(`<div style="font-size: 1.50rem; display: flex; justify-content: flex-end; gap: 20px;">`);
        receipt.document.write(`<div>${t("DATE", "التاريخ")} : </div> <div>${order.date_view}</div></div>`);

        if (order.customer_id) {
            receipt.document.write(`<div style="font-size: 1.50rem; display: flex; justify-content: space-between; gap: 30px;">`);
            receipt.document.write(`<div style="display: flex; gap: 20px;">`);
            receipt.document.write(`<div>${t('Client', 'عميل')} : </div> <div>${order.customer.name}</div> </div>`);
            receipt.document.write(`<div> ${settings.currencySymbol} </div> </div>`);
        }

        receipt.document.write(`<div style="font-size: 1.50rem; display: flex; gap: 20px;">`);
        receipt.document.write(`<div>${t("Address", "العنوان")} : </div>`);
        if (settings.storeAddress) {
            receipt.document.write(`<div style="font-size: 1.50rem;">${settings.storeAddress}</div>`);
        }
        receipt.document.write(`</div></div>`);

        receipt.document.write(`<div style="margin-top: 20px;">`);
        receipt.document.write(`<table style="width: 100%;" class="table1">`);
        receipt.document.write(`<thead> <tr>`);
        receipt.document.write(`<th style="font-size: 1.50rem; border: none;">`);
        receipt.document.write(` <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">${t('Name', 'الاسم')}</div>`);
        receipt.document.write(`</th>`);
        receipt.document.write(`<th style="font-size: 1.50rem; border: none;">`);
        receipt.document.write(` <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">${t('Qty', 'الكمية')}</div>`);
        receipt.document.write(`</th>`);
        receipt.document.write(`<th style="font-size: 1.50rem; border: none;">`);
        receipt.document.write(` <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">${t('U Price', 'يو السعر')}</div>`);
        receipt.document.write(`</th>`);
        receipt.document.write(`<th style="font-size: 1.50rem; border: none;">`);
        receipt.document.write(` <div style="border: 2px solid black; display: flex; height: 40px; align-items: center; justify-content: center;margin: 2px;">${t('Total', 'الإجمالي')}</div>`);
        receipt.document.write(`</th>`);
        receipt.document.write(`</tr> </thead>`);

        receipt.document.write(`<tbody>`);
        order.order_details.map((detail: any) => {
            if (receipt) {
                receipt.document.write(`<tr>`);
                receipt.document.write(`<td>${detail.product.name}</td>`);
                receipt.document.write(`<td>${detail.quantity}</td>`);
                receipt.document.write(`<td>${this.currencyFormatValue(detail.price | 0)}</td>`);
                receipt.document.write(`<td>${this.currencyFormatValue(detail.total | 0)}</td>`);
                receipt.document.write('</tr>');
            }
        });


        receipt.document.write(`<tr> <td colspan="4"> <div style="float: right;">`);
        receipt.document.write(`<table class="table2" style="margin-top: 30px;"> <tbody> <tr>`);
        receipt.document.write(`<td>${t('Total', 'الإجمالي')}</td>`);
        receipt.document.write(`<td>${this.currencyFormatValue(order.subtotal | 0)}</td>`);
        receipt.document.write(`</tr> </tbody> </table>`);
        receipt.document.write(`<table class="table2" style="margin-top: 30px;"> <tbody> <tr>`);
        receipt.document.write(`<td>${t('Disc', 'الخصم')} %</td>`);
        receipt.document.write(`<td>${this.currencyFormatValue(order.discount * 100 | 0)}</td>`);
        receipt.document.write(`</tr> </tbody> </table>`);
        receipt.document.write(`<table class="table2" style="margin-top: 30px;"> <tbody> <tr>`);
        receipt.document.write(`<td>${t('Disc', 'الخصم')}</td>`);
        receipt.document.write(`<td>${this.currencyFormatValue(order.discount * order.subtotal | 0)}</td>`);
        receipt.document.write(`</tr> </tbody> </table>`);
        receipt.document.write(`<table class="table2" style="margin-top: 30px;"> <tbody> <tr>`);
        receipt.document.write(`<td>${t('Total After Discount', 'الإجمالي بعد الخصم')}</td> <td>`);
        receipt.document.write(`<div>${this.currencyFormatValue(order.total | 0)}</div>`);
        receipt.document.write(`<div>${order.receipt_exchange_rate}</div>`);
        receipt.document.write(`</td> </tr> </tbody> </table>`);
        receipt.document.write(`</div> </td> </tr>`);

        receipt.document.write(`<tr> <td colspan="4" style="height: 50px;"> 0/100 , شكرا لزيارتك </td> </tr>`);
        receipt.document.write(`<tr> <td colspan="4" style="height: 20px;"> </td> </tr>`);
        receipt.document.write(`<tr> <td colspan="4" style="height: 20px;"> </td> </tr>`);

        receipt.document.write(`</tbody> </table> </div> </div> </body> </html>`);

        receipt.document.close();
        receipt.focus();
        receipt.print();
        receipt.close();
    };

    modalCloseButton = (): React.ReactNode => {
        return <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>;
    };
    modalCloseButtonWhite = (): React.ReactNode => {
        return <button type="button" className="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>;
    };

    handleOrderTypeChange = (event: React.ChangeEvent<HTMLSelectElement>): void => {
        this.setState({ orderType: event.target.value }, () => {
            this.calculateTotal();
        });
    };

    isOrderDelivery = (): boolean => {
        return this.state.orderType == 'delivery';
    };

    allProducts = (): IProduct[] => {
        var products: IProduct[] = [];
        this.state.categories.map((category: ICategory) => {
            category.products.map((product: IProduct) => {
                products.push(product);
            });
        });

        return products;
    };

    handleCloseModal = (): void => {
        this.closeModal('checkoutModal');
    };

    pricesFilter = (): React.ReactNode => {
        return (
            <div className="dropdown">
                <button
                    type="button"
                    className="nav-link bg-white border px-3 py-2 rounded-3 clickable-cell"
                    data-bs-toggle="modal"
                    data-bs-target="#myModal">
                    <div className="d-flex align-items-center">
                        <div className="d-flex justify-content-between align-items-center">
                            <div className="me-5">{priceFilterList[this.state.currentPrice]}</div>
                            <ChevronDownIcon className="hero-icon-xs" />
                        </div>
                    </div>
                </button>
            </div>
        );
    };

    handleSelectRadio = (index: number): void => {
        const cart = this.state.cart.map((item: ICartItem) => {
            let cartItem: ICartItem = {
                cartId: uuid(),
                id: item.id,
                name: item.name,
                full_name: item.full_name,
                image_url: item.image_url,
                price: ((index === 0) ? item.retailsale_price : item.wholesale_price),
                wholesale_price: item.wholesale_price,
                retailsale_price: item.retailsale_price,
                // barcode: item.barcode,
                wholesale_barcode: item.wholesale_barcode,
                retail_barcode: item.retail_barcode,
                // sku: item.sku,
                wholesale_sku: item.wholesale_sku,
                retail_sku: item.retail_sku,
                in_stock: item.in_stock,
                track_stock: item.track_stock,
                continue_selling_when_out_of_stock: item.continue_selling_when_out_of_stock,
                quantity: 1
            };
            return cartItem;
        })
        this.setState({cart: cart, currentPrice: index}, () => {
            this.calculateTotal();
        });
        this.closeModal('myModal');
    }

    pricesFilterModal = (): React.ReactNode => {
        return (
            <div className="modal zoom-out-entrance" id="myModal" tabIndex={-1} aria-labelledby="myModalLabel" aria-hidden="true">
                <div className="modal-dialog modal-dialog-centered">
                    <div className="modal-content">
                        <div className="modal-header">
                            <div className="d-flex justify-content-between w-100 user-select-none cursor-pointer" onClick={() => this.handleSelectRadio(0)}>
                                <div>
                                    {priceFilterList[0]}
                                </div>
                                <input type="radio" checked={this.state.currentPrice === 0}/>
                            </div>
                        </div>
                        <div className="modal-body">
                            <div className="d-flex justify-content-between user-select-none cursor-pointer" onClick={() => this.handleSelectRadio(1)}>
                                <div>
                                    {priceFilterList[1]}
                                </div>
                                <input type="radio" checked={this.state.currentPrice === 1}/>
                            </div>
                        </div>
                        <div className="modal-footer">
                            <div className="d-flex justify-content-between w-100 user-select-none cursor-pointer" onClick={() => this.handleSelectRadio(2)}>
                                <div>
                                    {priceFilterList[2]}
                                </div>
                                <input type="radio" checked={this.state.currentPrice === 2}/>
                            </div>
                        </div>
                    </div>
                </div>
            </div >
        );
    };

    render(): JSX.Element {
        return (
            <React.Fragment>
                <div className="d-flex py-3">
                    <div className="d-flex flex-grow-1">
                        <div className="flex-grow-1">
                            <button className="btn btn-primary me-2" onClick={event => this.goToDashboard()}>
                                <span className="d-flex align-items-center">
                                    <Squares2X2Icon className="hero-icon me-1" /> {t('Dashboard', 'الرئيسية')}
                                </span>
                            </button>
                            <button className="btn btn-light me-5 bg-white border" data-bs-toggle="modal" data-bs-target="#customerModal">
                                <span className="d-flex align-items-center">
                                    <UserCircleIcon className="hero-icon me-1" /> {t('Customer', 'الزبون')}
                                </span>
                            </button>
                            <button className="btn btn-danger" onClick={event => this.reset()}>
                                <span className="d-flex align-items-center">
                                    <ArrowPathIcon className="hero-icon me-1" /> {t('Reset?', 'إعادة ضبط')}
                                </span>
                            </button>
                        </div>
                        {this.pricesFilter()}
                        {this.pricesFilterModal()}
                    </div>
                    <div className="d-flex">
                        {this.getAppSettings().enableTakeoutAndDelivery && (
                            <select
                                name="order-type"
                                id="order-type"
                                className="form-select form-select-lg px-5"
                                defaultValue="takeout"
                                onChange={e => this.handleOrderTypeChange(e)}>
                                <option value="takeout">{t('Takeout', 'أستلام')}</option>
                                <option value="delivery">{t('Delivery', 'توصيل')}</option>
                            </select>
                        )}
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <div className="card w-100 card-gutter rounded-0">
                            <div className="card-header bg-white border-bottom-0 p-0">
                                <form onSubmit={event => this.handleSearchSubmit(event)}>
                                    <input
                                        type="search"
                                        className="form-control form-control-lg rounded-0"
                                        name="search"
                                        id="barcode-input"
                                        placeholder={t('Scan barcode or search by name or SKU', 'امسح الباركود ضوئيًا أو ابحث بالاسم أو SKU')}
                                        onChange={event => this.handleSearchChange(event)}
                                    />
                                </form>
                                <table className="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <td width={300} className="p-3 fw-bold">
                                                {t('Item', 'الصنف')}
                                            </td>
                                            <td width={150} className="text-center p-3 fw-bold">
                                                {t('Quantity', 'الكمية')}
                                            </td>
                                            <td width={150} className="text-center p-3 fw-bold">
                                                {t('Total', 'المجموع')}
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div className="card-body p-0 overflow-auto" id="cartItems">
                                <table className="table table-bordered mb-0">
                                    <tbody>
                                        {this.state.cart.length > 0 ? (
                                            <React.Fragment>
                                                {this.state.cart.map((item: ICartItem) => {
                                                    return (
                                                        <tr key={item.cartId}>
                                                            <td width={300}>
                                                                <div className="d-flex mb-2">
                                                                    <div className="d-flex flex-grow-1">
                                                                        <div className="me-2 d-flex align-items-center">
                                                                            <img src={item.image_url} alt="img" className="rounded-2" height={50} />
                                                                        </div>
                                                                        <div>
                                                                            <div className="fw-bold">{item.full_name}</div>
                                                                            <div className="fw-normal">
                                                                                <input
                                                                                    type="number"
                                                                                    className="form-control text-center"
                                                                                    value={item.price}
                                                                                    onFocus={e => e.target.select()}
                                                                                    onChange={e => this.updateItemPrice(e, item)}
                                                                                />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="me-auto d-flex align-items-center">
                                                                        <XCircleIcon
                                                                            className="hero-icon-sm align-middle text-danger cursor-pointer user-select-none"
                                                                            onClick={event => this.removeItem(item)}
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td width={150} className="p-0 align-middle text-center">
                                                                <input
                                                                    type="number"
                                                                    className="form-control text-center fw-bold form-control-lg"
                                                                    value={item.quantity}
                                                                    onFocus={e => e.target.select()}
                                                                    onChange={event => this.updateItemQuantity(event, item)}
                                                                />
                                                            </td>
                                                            <td width={150} className="text-center align-middle fw-bold">
                                                                <div className='d-flex align-items-center justify-content-center'>
                                                                    <span className=''>$</span>
                                                                    <input
                                                                        type="number"
                                                                        className="ml-2 w-50 form-control text-center form-control-lg"
                                                                        value={item.total}
                                                                        onFocus={e => e.target.select()}
                                                                        onChange={event => this.updateItemTotal(event, item)}
                                                                    />
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </React.Fragment>
                                        ) : (
                                            <React.Fragment>
                                                <tr>
                                                    <td colSpan={3} className="p-3 text-center align-middle fs-5">
                                                        {t('No items added...', 'لا توجد اصناف مضافة ...')}
                                                    </td>
                                                </tr>
                                            </React.Fragment>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                            <div className=" card-footer p-0 bg-white" id="orderDetails">
                                <table className="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <td width={200}>
                                                {t('Customer', 'الزبون')}:{' '}
                                                {this.state.customer ? <span className="fw-bold">{this.state.customer.name}</span> : 'N/A'}
                                                {this.state.customer && (
                                                    <div className="float-end">
                                                        <XCircleIcon
                                                            className="hero-icon-sm align-middle text-danger cursor-pointer user-select-none"
                                                            onClick={event => this.removeCustomer()}
                                                        />
                                                    </div>
                                                )}
                                            </td>
                                            <td width={200} className="text-end">
                                                {t('Subtotal', 'المجموع')}
                                            </td>
                                            <td width={200} className="align-middle text-center">
                                                {this.currencyFormatValue(this.state.subtotal)}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                width={200}
                                                className="text-start align-middle clickable-cell"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deliveryChargeModal">
                                                {this.getAppSettings().enableTakeoutAndDelivery ? (
                                                    <>
                                                        {this.isOrderDelivery() && (
                                                            <>
                                                                {t('Delivery Charge', 'رسوم التوصيل')} :{' '}
                                                                {this.state.deliveryCharge
                                                                    ? this.currencyFormatValue(this.state.deliveryCharge)
                                                                    : 'N/A'}
                                                            </>
                                                        )}
                                                    </>
                                                ) : (
                                                    <>
                                                        {t('Delivery Charge', 'رسوم التوصيل')} :{' '}
                                                        {this.state.deliveryCharge ? this.currencyFormatValue(this.state.deliveryCharge) : 'N/A'}
                                                    </>
                                                )}
                                            </td>
                                            <td
                                                width={200}
                                                className="text-end align-middle clickable-cell"
                                                data-bs-toggle="modal"
                                                data-bs-target="#discountModal">
                                                {t('Discount', 'الخصم')}
                                            </td>
                                            <td
                                                width={200}
                                                className="text-center text-danger align-middle clickable-cell"
                                                data-bs-toggle="modal"
                                                data-bs-target="#discountModal">
                                                {this.currencyFormatValue(this.state.discount || 0)}
                                            </td>
                                        </tr>
                                        <tr className="alert-success">
                                            {this.state.vatType == 'add' ? (
                                                <td
                                                    width={200}
                                                    className="text-start clickable-cell"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#taxModal">
                                                    {t('VAT', 'الضريبة')} {this.state.tax}%: {this.currencyFormatValue(this.getTotalTax())}
                                                </td>
                                            ) : (
                                                <td
                                                    width={200}
                                                    className="text-start clickable-cell"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#taxModal">
                                                    <div>
                                                        {t('TAX.AMOUNT', 'قيمة الضريبة')}: {this.currencyFormatValue(this.getTaxAmount())}
                                                    </div>
                                                    <div>
                                                        {t('VAT', 'الضريبة')} {this.state.tax}%: {this.currencyFormatValue(this.getVat())}
                                                    </div>
                                                </td>
                                            )}

                                            <td width={200} className="fw-bold text-end fs-5 align-middle">
                                                {t('Total', 'الإجمالي')}
                                            </td>
                                            <td width={200} className="text-center align-middle fw-bold fs-5">
                                                <div> {this.currencyFormatValue(this.state.total)}</div>
                                                {this.getAppSettings().showExchangeRateOnReceipt && <div>{this.receiptExchangeRate()}</div>}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button
                                type="button"
                                className="btn btn-success py-4 rounded-0 shadow-sm fs-3 btn-lg w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#checkoutModal">
                                {t('CHECKOUT', 'الدفع')}
                            </button>
                            {/* <button type="button" className="btn btn-success py-4 rounded-0 shadow-sm fs-3 btn-lg w-100" onClick={e => this.storeOrder()}>
                                الدفع
                            </button> */}
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="card w-100 card-gutter rounded-0">
                            <div className="card-header d-flex justify-content-between bg-white">
                                <div className="d-flex px-0" style={{ minHeight: 'calc(1.5em + 1rem + 5px)', padding: '0.5rem' }}>
                                    <a className="text-decoration-none cursor-pointer pe-2 fs-5" onClick={event => this.backClick()}>
                                        {t('CATEGORIES', 'الفئات')}
                                    </a>                                    
                                    {this.state.showProducts && (
                                        <div className="d-flex align-items-center">
                                            {this.getAppSettings().dir == 'rtl' ? (
                                                <ArrowLeftIcon className="hero-icon pe-2" />
                                            ) : (
                                                <ArrowRightIcon className="hero-icon pe-2" />
                                            )}
                                            <span className="fw-normal text-muted pe-2 fs-5 text-uppercase" aria-current="page">
                                                {this.state.categoryName}
                                            </span>
                                            {this.getAppSettings().dir == 'rtl' ? (
                                                <ArrowLeftIcon className="hero-icon pe-2" />
                                            ) : (
                                                <ArrowRightIcon className="hero-icon pe-2" />
                                            )}
                                        </div>
                                    )}
                                </div>
                                <form className='mt-1'>
                                    <input
                                        type="search"
                                        className="form-control form-control-md rounded-0"
                                        name="search"
                                        id="product-input"
                                        placeholder="Search Categories"
                                        onChange={event => this.productSearchChange(event)}
                                    />
                                </form>
                            </div>

                            <div className="card-body overflow-auto py-0">
                                {this.state.isLoadingCategories && (
                                    <div className="py-5">
                                        <div className="d-flex justify-content-center m-2">
                                            <div className="spinner-border text-primary" role="status" style={{ width: '4rem', height: '4rem' }}>
                                                <span className="visually-hidden">{t('Loading...', 'جاري التحميل...')}</span>
                                            </div>
                                        </div>
                                        <div className="fw-bold h3 text-center">{t('Loading...', 'جاري التحميل...')}</div>
                                    </div>
                                )}

                                {!this.state.showProducts && (
                                    <React.Fragment>
                                        {this.state.categories.length > 0 && (
                                            <div className="row">
                                                {this.state.categories.map((category: ICategory) => {
                                                    return (
                                                        <div key={category.id} className="col-lg-4 col-md-4 col-sm-6 col-6 mb-0 p-0">
                                                            <div
                                                                className="position-relative w-100 border cursor-pointer user-select-none"
                                                                onClick={event => this.categoryClick(category)}>
                                                                <picture>
                                                                    <source type="image/jpg" srcSet={category.image_url} />
                                                                    <img
                                                                        alt={category.name}
                                                                        src={category.image_url}
                                                                        aria-hidden="true"
                                                                        className="object-fit-cover h-100 w-100"
                                                                    />
                                                                </picture>
                                                                <div className="position-absolute bottom-0 start-0 h-100 d-flex flex-column align-items-center justify-content-center p-4 mb-0 w-100 cell-item-label text-center">
                                                                    <div className="product-name" dir="auto">
                                                                        {category.name}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </React.Fragment>
                                )}
                                {this.state.showProducts && (
                                    <React.Fragment>
                                        {this.state.products.length > 0 && (
                                            <div className="row overflow-auto">
                                                {this.state.products.map((product: IProduct) => {
                                                    return (
                                                        <>
                                                            {this.isProductAvailable(product) && (
                                                                <div key={product.id} className="col-lg-4 col-md-4 col-sm-6 col-6 mb-0 p-0">
                                                                    <div
                                                                        className="position-relative w-100 border cursor-pointer user-select-none"
                                                                        onClick={event => this.addToCart(product)}>
                                                                        <picture>
                                                                            <source type="image/jpg" srcSet={product.image_url} />
                                                                            <img
                                                                                alt={product.name}
                                                                                src={product.image_url}
                                                                                aria-hidden="true"
                                                                                className="object-fit-cover h-100 w-100"
                                                                            />
                                                                        </picture>
                                                                        <div className="position-absolute bottom-0 start-0 h-100 d-flex flex-column align-items-center justify-content-center p-4 mb-0 w-100 cell-item-label text-center">
                                                                            <div className="fw-bold" dir="auto">
                                                                                {product.full_name}
                                                                            </div>
                                                                            <div className="fw-normal">{this.currencyFormatValue((this.state.currentPrice === 0 ? product.retailsale_price: product.wholesale_price) || 0)}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </>
                                                    );
                                                })}
                                            </div>
                                        )}
                                    </React.Fragment>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="modal zoom-out-entrance" id="discountModal" tabIndex={-1} aria-labelledby="discountModalLabel" aria-hidden="true">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title" id="discountModalLabel">
                                    {t('Discount', 'الخصم')}
                                </h5>
                                {this.modalCloseButton()}
                            </div>
                            <div className="modal-body">
                                <h3 className="text-center">{this.currencyFormatValue(this.state.discount || 0)}</h3>
                                <div className="mb-3">
                                    <input
                                        type="number"
                                        className="form-control form-control-lg text-center"
                                        onFocus={e => e.target.select()}
                                        value={this.state.discount}
                                        onChange={this.handleDiscountChange}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    className="modal zoom-out-entrance"
                    id="deliveryChargeModal"
                    tabIndex={-1}
                    aria-labelledby="deliveryChargeModalLabel"
                    aria-hidden="true">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title d-flex" id="deliveryChargeModalLabel">
                                    {t('Delivery Charge', 'رسوم التوصيل')}
                                </h5>
                                {this.modalCloseButton()}
                            </div>
                            <div className="modal-body">
                                <h3 className="text-center">{this.currencyFormatValue(this.state.deliveryCharge || 0)}</h3>
                                <div className="mb-3">
                                    <input
                                        type="number"
                                        className="form-control form-control-lg text-center"
                                        value={this.state.deliveryCharge}
                                        onChange={this.handleDeliveryChargeChange}
                                        onFocus={e => e.target.select()}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="modal zoom-out-entrance" id="taxModal" tabIndex={-1} aria-labelledby="taxModalLabel" aria-hidden="true">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title" id="taxModalLabel">
                                    {t('VAT', 'الضريبة')}
                                </h5>
                                {this.modalCloseButton()}
                            </div>
                            <div className="modal-body">
                                <h3 className="text-center">{this.state.tax || 0}%</h3>
                                <div className="mb-3">
                                    <input
                                        type="number"
                                        className="form-control form-control-lg text-center"
                                        onFocus={e => e.target.select()}
                                        value={this.state.tax}
                                        onChange={this.handleTaxChange}
                                    />
                                </div>
                                <div className="mb-3">
                                    <select
                                        name="vatType"
                                        id="vat-type"
                                        className="form-select form-select-lg"
                                        onChange={this.handleVatTypeChange}
                                        value={this.state.vatType}>
                                        <option value="exclude"> {t('Exclude', 'استثناء')}</option>
                                        <option value="add">{t('Add', 'إضافة')}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="modal zoom-out-entrance" id="customerModal" tabIndex={-1} aria-labelledby="customerModalLabel" aria-hidden="true">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title d-flex" id="customerModalLabel">
                                    {t('Customer', 'الزبون')}
                                </h5>
                                {this.modalCloseButton()}
                            </div>
                            <div className="modal-body p-0">
                                <nav>
                                    <div className="nav nav-tabs nav-justified" id="nav-tab" role="tablist">
                                        <button
                                            className="nav-link rounded-0 active"
                                            id="nav-search-customer-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#nav-search-customer"
                                            type="button"
                                            role="tab"
                                            aria-controls="nav-search-customer"
                                            aria-selected="true">
                                            {t('Search', 'ابحث')}
                                        </button>
                                        <button
                                            className="nav-link rounded-0"
                                            id="nav-create-customer-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#nav-create-customer"
                                            type="button"
                                            role="tab"
                                            aria-controls="nav-create-customer"
                                            aria-selected="false">
                                            {t('Create', 'إنشاء')}
                                        </button>
                                    </div>
                                </nav>
                                <div className="tab-content" id="nav-tabContent">
                                    <div
                                        className="tab-pane fade show active"
                                        id="nav-search-customer"
                                        role="tabpanel"
                                        aria-labelledby="nav-search-customer-tab"
                                        tabIndex={0}>
                                        <div className="position-relative w-100">
                                            <input
                                                type="search"
                                                className="form-control form-control-lg rounded-0 shadow-none"
                                                name="search"
                                                id="search"
                                                autoComplete="off"
                                                placeholder={t('Search...', 'بحث...')}
                                                onChange={event => this.handleCustomerSearchChange(event)}
                                            />
                                        </div>
                                        <div className="overflow-auto" style={{ height: '250px' }}>
                                            {this.state.customers.length > 0 && (
                                                <React.Fragment>
                                                    {this.state.customers.map((cuts: ICustomer) => {
                                                        return (
                                                            <div
                                                                className="py-2 px-3 clickable-cell border-bottom"
                                                                onClick={e => this.selectCustomer(cuts)}
                                                                key={cuts.id}>
                                                                <div className="fw-bold">{cuts.name}</div>
                                                                <div className="small text-muted">{cuts.contact}</div>
                                                                <div className="small text-muted">{cuts.full_address}</div>
                                                            </div>
                                                        );
                                                    })}
                                                </React.Fragment>
                                            )}
                                        </div>
                                    </div>
                                    <div
                                        className="tab-pane fade p-3"
                                        id="nav-create-customer"
                                        role="tabpanel"
                                        aria-labelledby="nav-create-customer-tab"
                                        tabIndex={0}>
                                        <form method="POST" onSubmit={this.createCustomer} role="form" id="create-customer-form">
                                            <div className="mb-3">
                                                <label className=" form-label fw-bold">{t('Customer Name', 'اسم الزبون')}*</label>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-lg"
                                                    onChange={event => this.handleCustomerNameChange(event)}
                                                />
                                            </div>
                                            <div className="mb-3">
                                                <label className=" form-label fw-bold">{t('Email', 'البريد الإلكتروني')}</label>

                                                <input
                                                    type="email"
                                                    className="form-control form-control-lg"
                                                    onChange={event => this.handleCustomerEmailChange(event)}
                                                />
                                            </div>
                                            <div className="mb-3">
                                                <label className=" form-label fw-bold">{t('Mobile Number', 'رقم الجوال')}</label>
                                                <input
                                                    type="tel"
                                                    className="form-control form-control-lg"
                                                    onChange={event => this.handleCustomerMobileChange(event)}
                                                />
                                            </div>
                                            <div className="text-muted">{t('Address', 'العنوان')}</div>
                                            <div className="mb-3">
                                                <label className=" form-label fw-bold">{t('City', 'المدينة')}</label>
                                                <input
                                                    type="text"
                                                    className="form-control form-control-lg"
                                                    onChange={event => this.handleCustomerCityChange(event)}
                                                />
                                            </div>
                                            <div className="row">
                                                <div className="col-md-6 mb-3">
                                                    <label className=" form-label fw-bold">{t('Street', 'الشارع')}</label>
                                                    <input
                                                        type="text"
                                                        className="form-control form-control-lg"
                                                        onChange={event => this.handleCustomerStreetChange(event)}
                                                    />
                                                </div>
                                                <div className="col-md-6 mb-3">
                                                    <label className=" form-label fw-bold">{t('Building', 'المبنى')}</label>
                                                    <input
                                                        type="text"
                                                        className="form-control form-control-lg"
                                                        onChange={event => this.handleCustomerBuildingChange(event)}
                                                    />
                                                </div>
                                            </div>
                                            <div className="row">
                                                <div className="col-md-6 mb-3">
                                                    <label className=" form-label fw-bold">{t('Floor', 'الطابق')}</label>
                                                    <input
                                                        type="text"
                                                        className="form-control form-control-lg"
                                                        onChange={event => this.handleCustomerFloorChange(event)}
                                                    />
                                                </div>
                                                <div className="col-md-6 mb-3">
                                                    <label className=" form-label fw-bold">{t('Apartment', 'الشقة')}</label>
                                                    <input
                                                        type="text"
                                                        className="form-control form-control-lg"
                                                        onChange={event => this.handleCustomerApartmentChange(event)}
                                                    />
                                                </div>
                                            </div>
                                            <button className="btn btn-primary btn-lg w-100" type="submit" disabled={this.state.isLoading}>
                                                {t('Create Customer', 'إنشاء زبون جديد')}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="modal zoom-out-entrance" id="checkoutModal" tabIndex={-1} aria-labelledby="checkoutModalLabel" aria-hidden="true">
                    <div className="modal-dialog modal-dialog-centered modal-lg">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title" id="checkoutModalLabel"></h5>
                                {this.modalCloseButton()}
                            </div>
                            <div className="modal-body py-0">
                                <div className="row">
                                    <div className="col-6 py-3 bg-primary-sec">
                                        <table className="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td className="text-danger-sec"> {t('Subtotal', 'المجموع')}</td>
                                                    <td className="text-white">{this.currencyFormatValue(this.state.subtotal)}</td>
                                                </tr>
                                                <tr>
                                                    <td className="text-danger-sec"> {t('Quantity', 'الكمية')}</td>
                                                    <td className="text-white">
                                                        {this.state.cart.reduce(function (prev, current) {
                                                            return prev + +(current.quantity || 0);
                                                        }, 0)}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td className="text-danger-sec"> {t('Discount', 'الخصم')}</td>
                                                    <td className="text-white">{this.currencyFormatValue(this.state.discount || 0)}</td>
                                                </tr>
                                                <tr>
                                                    <td className="text-danger-sec"> {t('Delivery Charge', 'رسوم التوصيل')}</td>
                                                    <td className="text-white">{this.currencyFormatValue(this.state.deliveryCharge || 0)}</td>
                                                </tr>
                                                {this.state.vatType == 'add' ? (
                                                    <tr>
                                                        <td className="text-danger-sec">
                                                            {' '}
                                                            {t('VAT', 'الضريبة')} {this.state.tax || 0}%
                                                        </td>
                                                        <td className="text-white">{this.currencyFormatValue(this.getTotalTax())}</td>
                                                    </tr>
                                                ) : (
                                                    <>
                                                        <tr>
                                                            <td className="text-danger-sec"> {t('Tax Amount', 'القيمة الضريبية')}</td>
                                                            <td className="text-white">{this.currencyFormatValue(this.getTaxAmount())}</td>
                                                        </tr>
                                                        <tr>
                                                            <td className="text-danger-sec">
                                                                {t('VAT', 'الضريبة')} {this.state.tax || 0}%
                                                            </td>
                                                            <td className="text-white">{this.currencyFormatValue(this.getVat())}</td>
                                                        </tr>
                                                    </>
                                                )}
                                                <tr className="fw-bold">
                                                    <td className="text-danger-sec"> {t('Total', 'المجموع الإجمالي')}</td>
                                                    <td className="text-white">{this.currencyFormatValue(this.state.total)}</td>
                                                </tr>
                                                {this.state.customer && (
                                                    <React.Fragment>
                                                        <tr>
                                                            <td className="text-danger-sec align-middle"> {t('Customer', 'الزبون')}</td>
                                                            <td className="text-white">{this.state.customer.name}</td>
                                                        </tr>
                                                        <tr>
                                                            <td className="text-danger-sec align-middle">{t('Contact', 'الاتصال')}</td>
                                                            <td className="text-white">{this.state.customer.contact}</td>
                                                        </tr>
                                                        <tr>
                                                            <td className="text-danger-sec align-middle">{t('Address', 'العنوان')}</td>
                                                            <td className="text-white">{this.state.customer.full_address}</td>
                                                        </tr>
                                                    </React.Fragment>
                                                )}
                                            </tbody>
                                        </table>
                                        <hr />
                                        <div className="mb-3">
                                            <label htmlFor="remarks" className="form-label text-danger-sec">
                                                {t('Notes', 'الملاحظات')}
                                            </label>
                                            <textarea
                                                className="form-control"
                                                id="remarks"
                                                rows={3}
                                                onChange={event => this.handleRemarksChange(event)}>
                                                {this.state.remarks}
                                            </textarea>
                                        </div>
                                    </div>
                                    <div className="col-6 py-3 bg-body d-flex flex-column">
                                        <div className="text-center text-danger"> {t('CHECKOUT', 'الدفع')}</div>
                                        <hr />
                                        <div className="mb-3">
                                            <div className="form-label text-center"> {t('Tender Amount', 'المبلغ المدفوع')}</div>
                                            <input
                                                type="number"
                                                className="form-control form-control-lg text-center"
                                                value={this.state.tenderAmount?.toFixed(2)}
                                                onFocus={e => e.target.select()}
                                                onChange={this.handleTenderAmountChange}
                                            />
                                        </div>
                                        <hr />
                                        <table className="table table-borderless d-none">
                                            <tbody>
                                                <tr className="fw-bold">
                                                    <td className="text-danger-sec">
                                                        {this.getChangeAmount() < 0 ? t('Owe', 'مدين') : t('Change', 'الباقي')}
                                                    </td>
                                                    <td className="text-end">{this.currencyFormatValue(this.getChangeAmount())}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div className="mt-auto">
                                            {/* <button className="btn btn-primary btn-lg py-3 w-100 disabled cursor-not-allowed mb-3">
                                                <i className="bi bi-credit-card me-2"></i> {t('PAY WITH CARD', 'الدفع بالبطاقة')}
                                            </button> */}
                                            <button
                                                className="btn btn-primary btn-lg py-3 w-100"
                                                disabled={this.state.isLoading}
                                                onClick={e => this.storeOrder()}>
                                                {t('SUBMIT', 'حفظ')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <ToastContainer position="bottom-left" autoClose={2000} pauseOnHover theme="colored" hideProgressBar={true} />
            </React.Fragment>
        );
    }
}
export default PointOfSale;

const element = document.getElementById('pos');
if (element) {
    const props = Object.assign({}, element.dataset);
    const root = ReactDOM.createRoot(element);
    root.render(<PointOfSale settings={''} {...props} />);
}
