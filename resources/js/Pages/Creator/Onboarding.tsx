    import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function Onboarding() {
    const { data, setData, post, processing, errors } = useForm({
        shop_name: '',
        bio: '',
        profile_image: null as File | null,
        banner_image: null as File | null,
    });

    const [profilePreview, setProfilePreview] = useState<string | null>(null);
    const [bannerPreview, setBannerPreview] = useState<string | null>(null);
    const [step, setStep] = useState(1);

    const handleProfileImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('profile_image', file);
            setProfilePreview(URL.createObjectURL(file));
        }
    };

    const handleBannerImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('banner_image', file);
            setBannerPreview(URL.createObjectURL(file));
        }
    };

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('creator.onboarding.store'));
    };

    return (
        <>
            <Head title="Complete Your Shop Setup" />

            <div className="min-h-screen bg-gray-50 py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <div className="flex items-center justify-center space-x-4">
                            {[1, 2, 3].map((s) => (
                                <div key={s} className="flex items-center">
                                    <div
                                        className={`w-10 h-10 rounded-full flex items-center justify-center ${
                                            step >= s ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'
                                        }`}
                                    >
                                        {s}
                                    </div>
                                    {s < 3 && <div className="w-16 h-1 bg-gray-300 mx-2" />}
                                </div>
                            ))}
                        </div>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>
                                {step === 1 && 'Shop Information'}
                                {step === 2 && 'Profile Image'}
                                {step === 3 && 'Banner Image'}
                            </CardTitle>
                            <CardDescription>
                                {step === 1 && 'Choose your shop name and write a bio'}
                                {step === 2 && 'Upload a profile image for your shop'}
                                {step === 3 && 'Add a banner image to make your shop stand out'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {step === 1 && (
                                    <>
                                        <div>
                                            <label htmlFor="shop_name" className="block text-sm font-medium mb-2">
                                                Shop Name *
                                            </label>
                                            <input
                                                id="shop_name"
                                                type="text"
                                                value={data.shop_name}
                                                onChange={(e) => setData('shop_name', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm"
                                                required
                                            />
                                            {errors.shop_name && (
                                                <p className="mt-1 text-sm text-red-600">{errors.shop_name}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="bio" className="block text-sm font-medium mb-2">
                                                Bio
                                            </label>
                                            <textarea
                                                id="bio"
                                                value={data.bio}
                                                onChange={(e) => setData('bio', e.target.value)}
                                                rows={4}
                                                maxLength={1000}
                                                className="w-full rounded-md border-gray-300 shadow-sm"
                                                placeholder="Tell buyers about yourself and your creations..."
                                            />
                                            <p className="mt-1 text-sm text-gray-500">
                                                {data.bio.length}/1000 characters
                                            </p>
                                            {errors.bio && (
                                                <p className="mt-1 text-sm text-red-600">{errors.bio}</p>
                                            )}
                                        </div>

                                        <Button type="button" onClick={() => setStep(2)} className="w-full">
                                            Next
                                        </Button>
                                    </>
                                )}

                                {step === 2 && (
                                    <>
                                        <div>
                                            <label htmlFor="profile_image" className="block text-sm font-medium mb-2">
                                                Profile Image
                                            </label>
                                            <input
                                                id="profile_image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/jpg"
                                                onChange={handleProfileImageChange}
                                                className="w-full"
                                            />
                                            {profilePreview && (
                                                <img
                                                    src={profilePreview}
                                                    alt="Profile preview"
                                                    className="mt-4 w-32 h-32 object-cover rounded-full mx-auto"
                                                />
                                            )}
                                            <p className="mt-2 text-sm text-gray-500 text-center">
                                                Max 2MB, JPEG/PNG
                                            </p>
                                            {errors.profile_image && (
                                                <p className="mt-1 text-sm text-red-600">{errors.profile_image}</p>
                                            )}
                                        </div>

                                        <div className="flex space-x-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setStep(1)}
                                                className="flex-1"
                                            >
                                                Back
                                            </Button>
                                            <Button type="button" onClick={() => setStep(3)} className="flex-1">
                                                Next
                                            </Button>
                                        </div>
                                    </>
                                )}

                                {step === 3 && (
                                    <>
                                        <div>
                                            <label htmlFor="banner_image" className="block text-sm font-medium mb-2">
                                                Banner Image
                                            </label>
                                            <input
                                                id="banner_image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                onChange={handleBannerImageChange}
                                                className="w-full"
                                            />
                                            {bannerPreview && (
                                                <img
                                                    src={bannerPreview}
                                                    alt="Banner preview"
                                                    className="mt-4 w-full h-48 object-cover rounded-lg"
                                                />
                                            )}
                                            <p className="mt-2 text-sm text-gray-500 text-center">
                                                Max 5MB, JPEG/PNG/WebP
                                            </p>
                                            {errors.banner_image && (
                                                <p className="mt-1 text-sm text-red-600">{errors.banner_image}</p>
                                            )}
                                        </div>

                                        <div className="flex space-x-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setStep(2)}
                                                className="flex-1"
                                            >
                                                Back
                                            </Button>
                                            <Button type="submit" disabled={processing} className="flex-1">
                                                {processing ? 'Setting Up...' : 'Complete Setup'}
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
