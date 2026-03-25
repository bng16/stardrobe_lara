<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center">
                    <x-application-logo class="h-8 w-auto" />
                    <span class="ml-2 text-xl font-bold text-gray-900">{{ config('app.name') }}</span>
                </div>
                <p class="mt-4 text-gray-600 text-sm">
                    Connecting creators with collectors through unique auction experiences. Discover exclusive items and support your favorite creators.
                </p>
                <div class="mt-4 flex space-x-4">
                    <!-- Social Media Links -->
                    <a href="#" class="text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out" aria-label="Facebook">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out" aria-label="Twitter">
                        <span class="sr-only">Twitter</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out" aria-label="Instagram">
                        <span class="sr-only">Instagram</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.017 0C8.396 0 7.989.013 6.77.072 5.55.132 4.708.333 3.999.63a6.704 6.704 0 00-2.428 1.58 6.704 6.704 0 00-1.58 2.428c-.297.709-.498 1.551-.558 2.771C-.059 7.989-.045 8.396-.045 12.017c0 3.624-.014 4.031.072 5.25.06 1.22.261 2.062.558 2.771a6.704 6.704 0 001.58 2.428 6.704 6.704 0 002.428 1.58c.709.297 1.551.498 2.771.558 1.219.059 1.626.072 5.25.072 3.624 0 4.031-.013 5.25-.072 1.22-.06 2.062-.261 2.771-.558a6.704 6.704 0 002.428-1.58 6.704 6.704 0 001.58-2.428c.297-.709.498-1.551.558-2.771.059-1.219.072-1.626.072-5.25 0-3.624-.013-4.031-.072-5.25-.06-1.22-.261-2.062-.558-2.771a6.704 6.704 0 00-1.58-2.428A6.704 6.704 0 0018.322.63c-.709-.297-1.551-.498-2.771-.558C14.331.013 13.924 0 12.017 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12.017 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Quick Links</h3>
                <ul class="mt-4 space-y-4">
                    <li>
                        <a href="{{ route('marketplace.index') ?? '#' }}" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Marketplace
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            How It Works
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Become a Creator
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Help Center
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            FAQ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                <ul class="mt-4 space-y-4">
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Terms of Service
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Cookie Policy
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Contact Us
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out">
                            Refund Policy
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="mt-8 border-t border-gray-200 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-base text-gray-400 text-center md:text-left">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
                <div class="mt-4 md:mt-0 flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-gray-500 text-sm transition duration-150 ease-in-out">
                        Accessibility
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500 text-sm transition duration-150 ease-in-out">
                        Sitemap
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>