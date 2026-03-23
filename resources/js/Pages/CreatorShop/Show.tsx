import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Shop {
    id: string;
    shop_name: string;
    bio: string | null;
    profile_image: string | null;
    banner_image: string | null;
    creator: {
        id: string;
        name: string;
    };
    products: Array<{
        id: string;
        title: string;
        description: string;
        auction_end: string;
        images: Array<{
            id: string;
            image_path: string;
            is_primary: boolean;
        }>;
    }>;
}

interface Props {
    shop: Shop;
    followerCount: number;
    isFollowing: boolean;
}

export default function Show({ shop, followerCount, isFollowing }: Props) {
    const handleFollowToggle = () => {
        if (isFollowing) {
            router.delete(route('follows.destroy', shop.creator.id));
        } else {
            router.post(route('follows.store', shop.creator.id));
        }
    };

    return (
        <>
            <Head title={shop.shop_name} />

            <div className="min-h-screen bg-gray-50">
                {shop.banner_image && (
                    <div className="w-full h-64 bg-gray-200">
                        <img
                            src={shop.banner_image}
                            alt={`${shop.shop_name} banner`}
                            className="w-full h-full object-cover"
                        />
                    </div>
                )}

                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 -mt-16 relative">
                    <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <div className="flex items-start space-x-6">
                            {shop.profile_image && (
                                <img
                                    src={shop.profile_image}
                                    alt={shop.shop_name}
                                    className="w-32 h-32 rounded-full border-4 border-white shadow-lg"
                                />
                            )}
                            <div className="flex-1">
                                <h1 className="text-3xl font-bold">{shop.shop_name}</h1>
                                <p className="text-gray-600 mt-1">by {shop.creator.name}</p>
                                {shop.bio && (
                                    <p className="text-gray-700 mt-4 whitespace-pre-wrap">{shop.bio}</p>
                                )}
                                <div className="flex items-center space-x-4 mt-4">
                                    <span className="text-sm text-gray-600">
                                        {followerCount} {followerCount === 1 ? 'follower' : 'followers'}
                                    </span>
                                    <Button
                                        onClick={handleFollowToggle}
                                        variant={isFollowing ? 'outline' : 'default'}
                                    >
                                        {isFollowing ? 'Unfollow' : 'Follow'}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 className="text-2xl font-bold mb-6">Active Products</h2>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {shop.products.map((product) => {
                            const primaryImage = product.images.find((img) => img.is_primary) || product.images[0];
                            
                            return (
                                <Link key={product.id} href={route('marketplace.show', product.id)}>
                                    <Card className="hover:shadow-lg transition-shadow cursor-pointer">
                                        {primaryImage && (
                                            <img
                                                src={primaryImage.image_path}
                                                alt={product.title}
                                                className="w-full h-48 object-cover rounded-t-lg"
                                            />
                                        )}
                                        <CardHeader>
                                            <CardTitle className="line-clamp-1">{product.title}</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <p className="text-sm text-gray-500">
                                                Ends: {new Date(product.auction_end).toLocaleString()}
                                            </p>
                                        </CardContent>
                                    </Card>
                                </Link>
                            );
                        })}
                    </div>

                    {shop.products.length === 0 && (
                        <Card>
                            <CardContent className="py-12 text-center">
                                <p className="text-gray-500">No active products at the moment.</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </>
    );
}
