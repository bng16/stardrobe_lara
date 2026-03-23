import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    description: string;
    auction_end: string;
    creator: {
        id: string;
        name: string;
        creator_shop: {
            id: string;
            shop_name: string;
            profile_image: string | null;
        };
    };
    images: Array<{
        id: string;
        image_path: string;
        is_primary: boolean;
    }>;
}

interface Props {
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
    };
    hasFollows: boolean;
}

export default function ForYou({ products, hasFollows }: Props) {
    return (
        <>
            <Head title="For You" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 className="text-3xl font-bold mb-6">For You</h1>
                    <p className="text-gray-600 mb-6">
                        Products from creators you follow
                    </p>

                    {!hasFollows ? (
                        <Card>
                            <CardContent className="py-12 text-center">
                                <p className="text-gray-500 mb-4">
                                    You're not following any creators yet.
                                </p>
                                <p className="text-gray-500 mb-6">
                                    Follow creators to see their products here!
                                </p>
                                <Link href={route('marketplace.index')}>
                                    <Button>Browse Marketplace</Button>
                                </Link>
                            </CardContent>
                        </Card>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {products.data.map((product) => {
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
                                                    <CardDescription className="line-clamp-2">
                                                        {product.description}
                                                    </CardDescription>
                                                </CardHeader>
                                                <CardContent>
                                                    <div className="space-y-2">
                                                        <div className="flex items-center space-x-2">
                                                            {product.creator.creator_shop.profile_image && (
                                                                <img
                                                                    src={product.creator.creator_shop.profile_image}
                                                                    alt={product.creator.creator_shop.shop_name}
                                                                    className="w-6 h-6 rounded-full"
                                                                />
                                                            )}
                                                            <span className="text-sm text-gray-600">
                                                                {product.creator.creator_shop.shop_name}
                                                            </span>
                                                        </div>
                                                        <p className="text-sm text-gray-500">
                                                            Ends: {new Date(product.auction_end).toLocaleString()}
                                                        </p>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </Link>
                                    );
                                })}
                            </div>

                            {products.data.length === 0 && (
                                <Card>
                                    <CardContent className="py-12 text-center">
                                        <p className="text-gray-500">
                                            No active products from creators you follow.
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </>
                    )}
                </div>
            </div>
        </>
    );
}
