import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

// Example React component using shadcn/ui
function App() {
    return (
        <div className="min-h-screen bg-background">
            <div className="container mx-auto py-8 space-y-8">
                <div className="text-center">
                    <h1 className="text-4xl font-bold text-foreground mb-4">
                        Laravel + shadcn/ui + Bun
                    </h1>
                    <p className="text-muted-foreground mb-6">
                        Your Laravel application is now set up with shadcn/ui components!
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Button Components</CardTitle>
                            <CardDescription>
                                Various button styles from shadcn/ui
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex flex-wrap gap-2">
                                <Button>Default</Button>
                                <Button variant="secondary">Secondary</Button>
                                <Button variant="outline">Outline</Button>
                                <Button variant="destructive">Destructive</Button>
                                <Button variant="ghost">Ghost</Button>
                                <Button variant="link">Link</Button>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button size="sm">Small</Button>
                                <Button size="default">Default</Button>
                                <Button size="lg">Large</Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Setup Complete</CardTitle>
                            <CardDescription>
                                Everything is configured and ready to use
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-2 text-sm">
                                <li className="flex items-center gap-2">
                                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Laravel 12 with Vite
                                </li>
                                <li className="flex items-center gap-2">
                                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Tailwind CSS v4
                                </li>
                                <li className="flex items-center gap-2">
                                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                    React 19 with TypeScript
                                </li>
                                <li className="flex items-center gap-2">
                                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                    shadcn/ui components
                                </li>
                                <li className="flex items-center gap-2">
                                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Bun package manager
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Next Steps</CardTitle>
                        <CardDescription>
                            Start building your application with these tools
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <h4 className="font-medium mb-2">Add Components</h4>
                                <p className="text-muted-foreground">
                                    Install more shadcn/ui components as needed for your project.
                                </p>
                            </div>
                            <div>
                                <h4 className="font-medium mb-2">Build Features</h4>
                                <p className="text-muted-foreground">
                                    Create your Laravel routes, controllers, and React components.
                                </p>
                            </div>
                            <div>
                                <h4 className="font-medium mb-2">Deploy</h4>
                                <p className="text-muted-foreground">
                                    Your app is ready for development and deployment.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

// Mount React app if there's a root element
const rootElement = document.getElementById('app');
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<App />);
}
