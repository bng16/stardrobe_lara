import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

interface Statistics {
    total_auctions: number;
    active_auctions: number;
    sold_auctions: number;
    unsold_auctions: number;
}

interface Auction {
    id: string;
    title: string;
    status: string;
    auction_end: string;
    bids_count: number;
    highest_bid: number | null;
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

interface Props {
    statistics: Statistics;
    auctions: {
        data: Auction[];
        current_page: number;
        last_page: number;
    };
}

export default function Dashboard({ statistics, auctions }: Props) {
    const handleExportAll = () => {
        window.location.href = route('admin.auctions.export');
    };

    const handleExportSingle = (auctionId: string) => {
        window.location.href = route('admin.auctions.export.single', auctionId);
    };

    return (
        <>
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="flex justify-between items-center">
                        <h1 className="text-3xl font-bold">Admin Dashboard</h1>
                        <Button onClick={handleExportAll} variant="outline">
                            Export All Auctions (JSON)
                        </Button>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-gray-500">
                                    Total Auctions
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-3xl font-bold">{statistics.total_auctions}</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-gray-500">
                                    Active Auctions
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-3xl font-bold text-green-600">
                                    {statistics.active_auctions}
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-gray-500">
                                    Sold Auctions
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-3xl font-bold text-blue-600">
                                    {statistics.sold_auctions}
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-medium text-gray-500">
                                    Unsold Auctions
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-3xl font-bold text-gray-600">
                                    {statistics.unsold_auctions}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>All Auctions</CardTitle>
                            <CardDescription>View and manage all auction listings</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Product
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Creator
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Bids
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Highest Bid
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Ends
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {auctions.data.map((auction) => (
                                            <tr key={auction.id}>
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center space-x-3">
                                                        {auction.images[0] && (
                                                            <img
                                                                src={auction.images[0].image_path}
                                                                alt={auction.title}
                                                                className="w-12 h-12 object-cover rounded"
                                                            />
                                                        )}
                                                        <span className="font-medium">{auction.title}</span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {auction.creator.creator_shop.shop_name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        className={`px-2 py-1 text-xs rounded-full ${
                                                            auction.status === 'active'
                                                                ? 'bg-green-100 text-green-800'
                                                                : auction.status === 'sold'
                                                                ? 'bg-blue-100 text-blue-800'
                                                                : 'bg-gray-100 text-gray-800'
                                                        }`}
                                                    >
                                                        {auction.status}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {auction.bids_count}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {auction.highest_bid ? `$${auction.highest_bid}` : '-'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                    {new Date(auction.auction_end).toLocaleString()}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex space-x-2">
                                                        <Link href={route('admin.bids.index', auction.id)}>
                                                            <span className="text-blue-600 hover:text-blue-800 text-sm">
                                                                View Bids
                                                            </span>
                                                        </Link>
                                                        <span className="text-gray-300">|</span>
                                                        <button
                                                            onClick={() => handleExportSingle(auction.id)}
                                                            className="text-green-600 hover:text-green-800 text-sm"
                                                        >
                                                            Export
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
