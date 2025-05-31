```tsx
import { Link } from 'react-router-dom';

export function Hero() {
  return (
    <section className="relative bg-gray-50 py-16 sm:py-24">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <span className="inline-block text-red-600 font-medium mb-4">
              100% Canadian Imported Goods in Sri Lanka
            </span>
            <h1 className="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
              Premium Canadian Products
            </h1>
            <p className="text-xl text-gray-600 mb-8">Delivered to Your Doorstep.</p>
            <div className="space-x-4">
              <Link
                to="/shop"
                className="inline-block bg-red-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-red-700 transition-colors"
              >
                Shop Now
              </Link>
              <Link
                to="/about"
                className="inline-block border border-red-600 text-red-600 px-8 py-3 rounded-lg font-medium hover:bg-red-50 transition-colors"
              >
                Learn More
              </Link>
            </div>
          </div>
          <div className="relative">
            <img
              src="/images/hero.jpg"
              alt="Canadian Products"
              className="rounded-lg shadow-xl"
            />
          </div>
        </div>
      </div>
    </section>
  );
}
```