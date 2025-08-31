#!/bin/bash

# SmartScribe Deployment Script
echo "🚀 Starting SmartScribe deployment..."

# Check if Node.js and npm are installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
npm install

# Build for production
echo "🔨 Building for production..."
npm run build

# Check if build was successful
if [ $? -eq 0 ]; then
    echo "✅ Build completed successfully!"
    echo ""
    echo "📋 Next steps:"
    echo "1. Copy the 'dist/' folder contents to your web server"
    echo "2. Ensure PHP files are accessible at your API endpoints"
    echo "3. Configure your web server (.htaccess for Apache)"
    echo "4. Set up your database and run migrations"
    echo "5. Configure environment variables (.env file)"
    echo ""
    echo "🎉 Deployment preparation complete!"
else
    echo "❌ Build failed. Please check the errors above."
    exit 1
fi