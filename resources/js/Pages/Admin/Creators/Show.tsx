import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Shop {
    id: string;
    shop_name: string;
    bio: string | null;
    creator: {
        id: string;
        name: string;
        email: string;
    };
}

interface PrivateInfo {
    id: string;
    stripe_account_id: string | null;
    tax_id: string | null;
    payout_email: string | null;
}

interface Props {
    shop: Shop;
    privateInfo: PrivateInfo | null;
}

export default function Show({ shop, privateInfo }: Props) {
    return (
        <>
            <Head title={`Creator Info - ${shop.shop_name}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Creator Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm font-medium text-gray-500">Shop Name</p>
                                <p className="text-lg">{shop.shop_name}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-500">Creator Name</p>
                                <p className="text-lg">{shop.creator.name}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-500">Email</p>
                                <p className="text-lg">{shop.creator.email}</p>
                            </div>
                            {shop.bio && (
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Bio</p>
                                    <p className="text-gray-700 whitespace-pre-wrap">{shop.bio}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Private Payout Information</CardTitle>
                            <p className="text-sm text-red-600 mt-2">
                                Admin Only - Confidential
                            </p>
                        </CardHeader>
                        <CardContent>
                            {privateInfo ? (
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Stripe Account ID</p>
                                        <p className="text-lg font-mono">
                                            {privateInfo.stripe_account_id || 'Not configured'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Tax ID</p>
                                        <p className="text-lg font-mono">
                                            {privateInfo.tax_id || 'Not provided'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-500">Payout Email</p>
                                        <p className="text-lg">
                                            {privateInfo.payout_email || 'Not provided'}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <p className="text-gray-500">No private information configured yet.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
