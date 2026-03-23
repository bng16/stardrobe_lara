import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Product {
    id: string;
    title: string;
    description: string;
    reserve_price: number;
    auction_start: string;
    auction_end: string;
    status: string;
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
}

export default function Index({ products }: Props) {
    return (
        <>
            <Head title="My Products" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-3xl font-bold">My Products</h1>
                        <Link href={route('creator.products.create')}>
                            <Button>List New Product</Button>
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {products.data.map((product) => {
                            const primaryImage = product.images.find((img) => img.is_primary) || product.images[0];
                            
                            return (
                                <Card key={product.id}>
                                    {primaryImage && (
                                        <img
                                            src={primaryImage.image_path}
                                            alt={product.title}
                                            className="w-full h-48 object-cover rounded-t-lg"
                                        />
                                    )}
                                    <CardHeader>
                                        <CardTitle>{product.title}</CardTitle>
                                        <CardDescription>
                                            {product.description.substring(0, 100)}
                                            {product.description.length > 100 && '...'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2">
                                            <p className="text-sm">
                                                <span className="font-medium">Reserve Price:</span> $
                                                {product.reserve_price}
                                            </p>
                                            <p className="text-sm">
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
                                            <p className="text-sm">
                                                <span className="font-medium">Ends:</span>{' '}
                                                {new Date(product.auction_end).toLocaleString()}
                                            </p>
                                        </div>
                                        <div className="mt-4 flex space-x-2">
                                            <Link href={route('creator.products.edit', product.id)}>
                                                <Button variant="outline" size="sm">
                                                    Edit
                                                </Button>
                                            </Link>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    {products.data.length === 0 && (
                        <Card>
                            <CardContent className="py-12 text-center">
                                <p className="text-gray-500 mb-4">You haven't listed any products yet.</p>
                                <Link href={route('creator.products.create')}>
                                    <Button>List Your First Product</Button>
                                </Link>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </>
    );
}
