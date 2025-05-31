```tsx
import { Link } from 'react-router-dom';

const categories = [
  {
    id: 1,
    name: 'Food & Beverages',
    image: '/images/categories/food.jpg',
    slug: 'food-beverages',
  },
  {
    id: 2,
    name: 'Beauty & Personal Care',
    image: '/images/categories/beauty.jpg',
    slug: 'beauty-personal-care',
  },
  {
    id: 3,
    name: 'Health & Wellness',
    image: '/images/categories/health.jpg',
    slug: 'health-wellness',
  },
  {
    id: 4,
    name: 'Home & Living',
    image: '/images/categories/home.jpg',
    slug: 'home-living',
  },
];

export function Categories() {
  return (
    <section className="py-16 bg-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl font-bold text-gray-900 mb-4">Shop by Category</h2>
          <p className="text-gray-600">Explore our wide range of Canadian products</p>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {categories.map((category) => (
            <Link
              key={category.id}
              to={`/shop?category=${category.slug}`}
              className="group relative overflow-hidden rounded-lg"
            >
              <img
                src={category.image}
                alt={category.name}
                className="w-full h-64 object-cover transition-transform group-hover:scale-105"
              />
              <div className="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                <h3 className="text-white text-xl font-semibold">{category.name}</h3>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
```