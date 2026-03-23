import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler } from 'react';
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
    images: Array<{
        id: string;
        image_path: string;
        is_primary: boolean;
    }>;
}

interface Props {
    product: Product;
}

export default function Edit({ product }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        title: product.title,
        description: product.description,
        category: product.category || '',
        reserve_price: product.reserve_price.toString(),
        auction_start: product.auction_start.substring(0, 16),
        auction_end: product.auction_end.substring(0, 16),
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('creator.products.update', product.id));
    };

    return (
        <>
            <Head title={`Edit ${product.title}`} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Edit Product</CardTitle>
                            <CardDescription>Update your product listing</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div>
                                    <label htmlFor="title" className="block text-sm font-medium mb-2">
                                        Product Title *
                                    </label>
                                    <input
                                        id="title"
                                        type="text"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    />
                                    {errors.title && (
                                        <p className="mt-1 text-sm text-red-600">{errors.title}</p>
                                    )}
                                </div>

                                <div>
                                    <label htmlFor="description" className="block text-sm font-medium mb-2">
                                        Description *
                                    </label>
                                    <textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={6}
                                        maxLength={5000}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    />
                                    <p className="mt-1 text-sm text-gray-500">
                                        {data.description.length}/5000 characters
                                    </p>
                                    {errors.description && (
                                        <p className="mt-1 text-sm text-red-600">{errors.description}</p>
                                    )}
                                </div>

                                <div>
                                    <label htmlFor="category" className="block text-sm font-medium mb-2">
                                        Category
                                    </label>
                                    <input
                                        id="category"
                                        type="text"
                                        value={data.category}
                                        onChange={(e) => setData('category', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                    {errors.category && (
                                        <p className="mt-1 text-sm text-red-600">{errors.category}</p>
                                    )}
                                </div>

                                <div>
                                    <label htmlFor="reserve_price" className="block text-sm font-medium mb-2">
                                        Reserve Price *
                                    </label>
                                    <input
                                        id="reserve_price"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        value={data.reserve_price}
                                        onChange={(e) => setData('reserve_price', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    />
                                    {errors.reserve_price && (
                                        <p className="mt-1 text-sm text-red-600">{errors.reserve_price}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label htmlFor="auction_start" className="block text-sm font-medium mb-2">
                                            Auction Start *
                                        </label>
                                        <input
                                            id="auction_start"
                                            type="datetime-local"
                                            value={data.auction_start}
                                            onChange={(e) => setData('auction_start', e.target.value)}
                                            className="w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                        {errors.auction_start && (
                                            <p className="mt-1 text-sm text-red-600">{errors.auction_start}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label htmlFor="auction_end" className="block text-sm font-medium mb-2">
                                            Auction End *
                                        </label>
                                        <input
                                            id="auction_end"
                                            type="datetime-local"
                                            value={data.auction_end}
                                            onChange={(e) => setData('auction_end', e.target.value)}
                                            className="w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                        {errors.auction_end && (
                                            <p className="mt-1 text-sm text-red-600">{errors.auction_end}</p>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-2">
                                        Current Images
                                    </label>
                                    <div className="grid grid-cols-5 gap-2 mb-4">
                                        {product.images.map((image) => (
                                            <div key={image.id} className="relative">
                                                <img
                                                    src={image.image_path}
                                                    alt="Product"
                                                    className="w-full h-24 object-cover rounded"
                                                />
                                                {image.is_primary && (
                                                    <span className="absolute top-1 right-1 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                                        Primary
                                                    </span>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="flex space-x-4">
                                    <Link href={route('creator.products.index')} className="flex-1">
                                        <Button type="button" variant="outline" className="w-full">
                                            Cancel
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing} className="flex-1">
                                        {processing ? 'Updating...' : 'Update Product'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
