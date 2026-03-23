import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    description: string;
    reserve_price: number;
    status: string;
    creator: {
        creator_shop: {
            shop_name: string;
        };
    };
}

interface Bid {
    id: string;
    user_name: string;
    user_email: string;
    amount: number;
    rank: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    product: Product;
    bids: Bid[];
}

export default function Show({ product, bids }: Props) {
    return (
        <>
            <Head title={`Bids - ${product.title}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{product.title}</CardTitle>
                            <p className="text-sm text-gray-500 mt-2">
                                by {product.creator.creator_shop.shop_name}
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <p>
                                    <span className="font-medium">Reserve Price:</span> ${product.reserve_price}
                                </p>
                                <p>
                                    <span className="font-medium">Status:</span>{' '}
                                    <span
                                        className={`px-2 py-1 text-xs rounded-full ${
                                            product.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : product.status === 'sold'
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-gray-100 text-gray-800'
                                        }`}
                                    >
                                        {product.status}
                                    </span>
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>All Bids (Admin View)</CardTitle>
                            <p className="text-sm text-gray-500 mt-2">
                                Full bid visibility with amounts
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Rank
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Bidder
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Email
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Amount
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Submitted
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Updated
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {bids.map((bid) => (
                                            <tr
                                                key={bid.id}
                                                className={bid.rank === 1 ? 'bg-yellow-50' : ''}
                                            >
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className="font-bold">#{bid.rank}</span>
                                                    {bid.rank === 1 && (
                                                        <span className="ml-2 text-xs text-yellow-600">Winner</span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{bid.user_name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {bid.user_email}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap font-medium">
                                                    ${bid.amount.toFixed(2)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(bid.created_at).toLocaleString()}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(bid.updated_at).toLocaleString()}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {bids.length === 0 && (
                                <div className="py-12 text-center">
                                    <p className="text-gray-500">No bids have been placed yet.</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
