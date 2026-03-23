import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    status: string;
}

interface LeaderboardEntry {
    rank: number;
    user_name: string;
    is_winner: boolean;
    amount?: number;
}

interface Props {
    product: Product;
    leaderboard: LeaderboardEntry[];
    isAdmin: boolean;
}

export default function Leaderboard({ product, leaderboard, isAdmin }: Props) {
    return (
        <>
            <Head title={`Leaderboard - ${product.title}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Auction Leaderboard</CardTitle>
                            <p className="text-sm text-gray-500 mt-2">{product.title}</p>
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
                                            {isAdmin && (
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Bid Amount
                                                </th>
                                            )}
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {leaderboard.map((entry) => (
                                            <tr
                                                key={entry.rank}
                                                className={entry.is_winner ? 'bg-yellow-50' : ''}
                                            >
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className="text-lg font-bold">#{entry.rank}</span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {entry.user_name}
                                                </td>
                                                {isAdmin && (
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        ${entry.amount?.toFixed(2)}
                                                    </td>
                                                )}
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {entry.is_winner && (
                                                        <span className="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                            Winner
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {leaderboard.length === 0 && (
                                <div className="py-12 text-center">
                                    <p className="text-gray-500">No bids were placed on this auction.</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
