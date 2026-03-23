import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_KEY || '');

interface Product {
    id: string;
    title: string;
    description: string;
    creator: {
        creator_shop: {
            shop_name: string;
        };
    };
    images: Array<{
        image_path: string;
        is_primary: boolean;
    }>;
}

interface Bid {
    id: string;
    amount: number;
}

interface Props {
    product: Product;
    bid: Bid;
    paymentDeadline: string;
    isExpired: boolean;
}

function PaymentForm({ product, bid, paymentDeadline, isExpired }: Props) {
    const stripe = useStripe();
    const elements = useElements();
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [timeRemaining, setTimeRemaining] = useState('');

    useEffect(() => {
        const interval = setInterval(() => {
            const now = new Date();
            const deadline = new Date(paymentDeadline);
            const diff = deadline.getTime() - now.getTime();

            if (diff <= 0) {
                setTimeRemaining('Expired');
                clearInterval(interval);
            } else {
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                setTimeRemaining(`${hours}h ${minutes}m remaining`);
            }
        }, 1000);

        return () => clearInterval(interval);
    }, [paymentDeadline]);

    const handleSubmit: FormEventHandler = async (e) => {
        e.preventDefault();

        if (!stripe || !elements || isExpired) {
            return;
        }

        setProcessing(true);
        setError(null);

        const cardElement = elements.getElement(CardElement);

        if (!cardElement) {
            setError('Card element not found');
            setProcessing(false);
            return;
        }

        try {
            const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
            });

            if (stripeError) {
                setError(stripeError.message || 'Payment failed');
                setProcessing(false);
                return;
            }

            // Submit to backend
            const response = await fetch(route('payment.store', product.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    payment_method_id: paymentMethod?.id,
                }),
            });

            if (response.ok) {
                window.location.href = route('marketplace.index');
            } else {
                const data = await response.json();
                setError(data.message || 'Payment failed');
            }
        } catch (err) {
            setError('An error occurred during payment');
        } finally {
            setProcessing(false);
        }
    };

    const primaryImage = product.images.find((img) => img.is_primary) || product.images[0];

    return (
        <>
            <Head title="Complete Payment" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {primaryImage && (
                                    <img
                                        src={primaryImage.image_path}
                                        alt={product.title}
                                        className="w-full h-48 object-cover rounded-lg"
                                    />
                                )}
                                <div>
                                    <h3 className="font-medium text-lg">{product.title}</h3>
                                    <p className="text-sm text-gray-500">
                                        by {product.creator.creator_shop.shop_name}
                                    </p>
                                </div>
                                <div className="border-t pt-4">
                                    <div className="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>${bid.amount.toFixed(2)}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Payment Details</CardTitle>
                                <CardDescription>
                                    {isExpired ? (
                                        <span className="text-red-600">Payment deadline expired</span>
                                    ) : (
                                        <span className="text-blue-600">{timeRemaining}</span>
                                    )}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {isExpired ? (
                                    <div className="py-8 text-center">
                                        <p className="text-red-600 font-medium">
                                            Payment deadline has expired
                                        </p>
                                        <p className="text-sm text-gray-500 mt-2">
                                            This order is no longer available for payment
                                        </p>
                                    </div>
                                ) : (
                                    <form onSubmit={handleSubmit} className="space-y-6">
                                        <div>
                                            <label className="block text-sm font-medium mb-2">
                                                Card Details
                                            </label>
                                            <div className="p-3 border rounded-md">
                                                <CardElement
                                                    options={{
                                                        style: {
                                                            base: {
                                                                fontSize: '16px',
                                                                color: '#424770',
                                                                '::placeholder': {
                                                                    color: '#aab7c4',
                                                                },
                                                            },
                                                            invalid: {
                                                                color: '#9e2146',
                                                            },
                                                        },
                                                    }}
                                                />
                                            </div>
                                        </div>

                                        {error && (
                                            <div className="p-3 bg-red-50 text-red-600 rounded-md text-sm">
                                                {error}
                                            </div>
                                        )}

                                        <Button
                                            type="submit"
                                            disabled={!stripe || processing}
                                            className="w-full"
                                        >
                                            {processing ? 'Processing...' : `Pay $${bid.amount.toFixed(2)}`}
                                        </Button>
                                    </form>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

export default function Show(props: Props) {
    return (
        <Elements stripe={stripePromise}>
            <PaymentForm {...props} />
        </Elements>
    );
}
