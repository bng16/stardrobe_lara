import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    description: string;
    category: string | null;
    reserve_price: number;
    auction_start: string;
    auction_end: string;
    status: string;
    creator: {
        id: string;
        name: string;
        creator_shop: {
            id: string;
            shop_name: string;
            profile_image: string | null;
            banner_image: string | null;
            bio: string | null;
        };
    };
    images: Array<{
        id: string;
        image_path: string;
        is_primary: boolean;
        display_order: number;
    }>;
}

interface Props {
    product: Product;
    userBid?: {
        amount?: number;
        rank?: number;
    } | null;
}

export default function Show({ product, userBid }: Props) {
    const [selectedImage, setSelectedImage] = useState(0);
    const { data, setData, post, processing, errors } = useForm({
        amount: '',
    });

    const sortedImages = [...product.images].sort((a, b) => a.display_order - b.display_order);
    const isActive = product.status === 'active' && new Date(product.auction_end) > new Date();
    const hasEnded = new Date(product.auction_end) <= new Date();

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('bids.store', product.id));
    };

    return (
        <>
            <Head title={product.title} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            {sortedImages.length > 0 && (
                                <>
                                    <img
                                        src={sortedImages[selectedImage].image_path}
                                        alt={product.title}
                                        className="w-full h-96 object-cover rounded-lg mb-4"
                                    />
                                    {sortedImages.length > 1 && (
                                        <div className="grid grid-cols-5 gap-2">
                                            {sortedImages.map((image, index) => (
                                                <img
                                                    key={image.id}
                                                    src={image.image_path}
                                                    alt={`${product.title} ${index + 1}`}
                                                    className={`w-full h-20 object-cover rounded cursor-pointer ${
                                                        selectedImage === index
                                                            ? 'ring-2 ring-blue-600'
                                                            : 'opacity-70 hover:opacity-100'
                                                    }`}
                                                    onClick={() => setSelectedImage(index)}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </>
                            )}
                        </div>

                        <div className="space-y-6">
                            <div>
                                <h1 className="text-3xl font-bold mb-2">{product.title}</h1>
                                {product.category && (
                                    <span className="inline-block px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full">
                                        {product.category}
                                    </span>
                                )}
                            </div>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Creator</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center space-x-3">
                                        {product.creator.creator_shop.profile_image && (
                                            <img
                                                src={product.creator.creator_shop.profile_image}
                                                alt={product.creator.creator_shop.shop_name}
                                                className="w-12 h-12 rounded-full"
                                            />
                                        )}
                                        <div>
                                            <p className="font-medium">{product.creator.creator_shop.shop_name}</p>
                                            <p className="text-sm text-gray-500">{product.creator.name}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-gray-700 whitespace-pre-wrap">{product.description}</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Auction Details</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <p>
                                        <span className="font-medium">Status:</span>{' '}
                                        <span
                                            className={`px-2 py-1 text-xs rounded-full ${
                                                isActive
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            }`}
                                        >
                                            {hasEnded ? 'Auction Ended' : 'Active'}
                                        </span>
                                    </p>
                                    <p>
                                        <span className="font-medium">Ends:</span>{' '}
                                        {new Date(product.auction_end).toLocaleString()}
                                    </p>
                                    {userBid && (
                                        <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                                            <p className="font-medium text-blue-900">Your Bid</p>
                                            {userBid.amount !== undefined && (
                                                <p className="text-blue-700">Amount: ${userBid.amount}</p>
                                            )}
                                            {userBid.rank !== undefined && (
                                                <p className="text-blue-700">Current Rank: #{userBid.rank}</p>
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {isActive && !hasEnded && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Place Your Bid</CardTitle>
                                        <CardDescription>
                                            Minimum bid: ${product.reserve_price}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={handleSubmit} className="space-y-4">
                                            <div>
                                                <label htmlFor="amount" className="block text-sm font-medium mb-2">
                                                    Bid Amount
                                                </label>
                                                <input
                                                    id="amount"
                                                    type="number"
                                                    step="0.01"
                                                    min={product.reserve_price}
                                                    value={data.amount}
                                                    onChange={(e) => setData('amount', e.target.value)}
                                                    className="w-full rounded-md border-gray-300 shadow-sm"
                                                    required
                                                />
                                                {errors.amount && (
                                                    <p className="mt-1 text-sm text-red-600">{errors.amount}</p>
                                                )}
                                            </div>

                                            <Button type="submit" disabled={processing} className="w-full">
                                                {processing ? 'Submitting...' : userBid ? 'Update Bid' : 'Place Bid'}
                                            </Button>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}

                            {hasEnded && (
                                <Card>
                                    <CardContent className="py-6 text-center">
                                        <p className="text-lg font-medium text-gray-700">Auction Ended</p>
                                        <p className="text-sm text-gray-500 mt-2">
                                            This auction has closed
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
