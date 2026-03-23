import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    description: string;
    category: string | null;
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
    filters: {
        category?: string;
        max_price?: string;
        ending_soon?: boolean;
    };
}

export default function Index({ products, filters }: Props) {
    const [category, setCategory] = useState(filters.category || '');
    const [maxPrice, setMaxPrice] = useState(filters.max_price || '');
    const [endingSoon, setEndingSoon] = useState(filters.ending_soon || false);

    const applyFilters = () => {
        router.get(
            route('marketplace.index'),
            {
                category: category || undefined,
                max_price: maxPrice || undefined,
                ending_soon: endingSoon || undefined,
            },
            { preserveState: true }
        );
    };

    const clearFilters = () => {
        setCategory('');
        setMaxPrice('');
        setEndingSoon(false);
        router.get(route('marketplace.index'));
    };

    return (
        <>
            <Head title="Marketplace" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1 className="text-3xl font-bold mb-6">Open Market</h1>

                    <Card className="mb-6">
                        <CardHeader>
                            <CardTitle>Filters</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label htmlFor="category" className="block text-sm font-medium mb-2">
                                        Category
                                    </label>
                                    <input
                                        id="category"
                                        type="text"
                                        value={category}
                                        onChange={(e) => setCategory(e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                        placeholder="e.g., Art, Electronics"
                                    />
                                </div>

                                <div>
                                    <label htmlFor="max_price" className="block text-sm font-medium mb-2">
                                        Max Price
                                    </label>
                                    <input
                                        id="max_price"
                                        type="number"
                                        step="0.01"
                                        value={maxPrice}
                                        onChange={(e) => setMaxPrice(e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                        placeholder="e.g., 100.00"
                                    />
                                </div>

                                <div className="flex items-end">
                                    <label className="flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            checked={endingSoon}
                                            onChange={(e) => setEndingSoon(e.target.checked)}
                                            className="rounded border-gray-300"
                                        />
                                        <span className="text-sm">Ending within 24 hours</span>
                                    </label>
                                </div>
                            </div>

                            <div className="mt-4 flex space-x-2">
                                <Button onClick={applyFilters}>Apply Filters</Button>
                                <Button variant="outline" onClick={clearFilters}>
                                    Clear
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

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
                                <p className="text-gray-500">No products available at the moment.</p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </>
    );
}
