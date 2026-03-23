import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        category: '',
        reserve_price: '',
        auction_start: '',
        auction_end: '',
        images: [] as File[],
    });

    const [imagePreviews, setImagePreviews] = useState<string[]>([]);

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files || []);
        if (files.length > 5) {
            alert('Maximum 5 images allowed');
            return;
        }

        setData('images', files);
        
        const previews = files.map((file) => URL.createObjectURL(file));
        setImagePreviews(previews);
    };

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('creator.products.store'));
    };

    return (
        <>
            <Head title="List New Product" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>List New Product</CardTitle>
                            <CardDescription>
                                Create a new auction listing for your product
                            </CardDescription>
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
                                        Reserve Price * (minimum bid)
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
                                    <label htmlFor="images" className="block text-sm font-medium mb-2">
                                        Product Images * (1-5 images)
                                    </label>
                                    <input
                                        id="images"
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        multiple
                                        onChange={handleImageChange}
                                        className="w-full"
                                        required
                                    />
                                    {imagePreviews.length > 0 && (
                                        <div className="mt-4 grid grid-cols-5 gap-2">
                                            {imagePreviews.map((preview, index) => (
                                                <img
                                                    key={index}
                                                    src={preview}
                                                    alt={`Preview ${index + 1}`}
                                                    className="w-full h-24 object-cover rounded"
                                                />
                                            ))}
                                        </div>
                                    )}
                                    <p className="mt-1 text-sm text-gray-500">
                                        Max 5MB per image, JPEG/PNG/WebP
                                    </p>
                                    {errors.images && (
                                        <p className="mt-1 text-sm text-red-600">{errors.images}</p>
                                    )}
                                </div>

                                <div className="flex space-x-4">
                                    <Link href={route('creator.products.index')} className="flex-1">
                                        <Button type="button" variant="outline" className="w-full">
                                            Cancel
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing} className="flex-1">
                                        {processing ? 'Creating...' : 'List Product'}
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
