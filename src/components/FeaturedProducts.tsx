```tsx
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { formatPrice } from '@/lib/utils';

interface Product {
  id: number;
  name: string;
  slug: string;
  price: number;
  sale_price: number | null;
  image: string;
  category_name: string;
}

export function FeaturedProducts() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/featured-products')
      .then((res) => res.json())
      .then((data) => {
        setProducts(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching featured products:', error);
        setLoading(false);
      });
  }, []);

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <section className="py-16 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl font-bold text-gray-900 mb-4">Featured Products</h2>
          <p className="text-gray-600">Handpicked premium Canadian products</p>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {products.map((product) => (
            <Link
              key={product.id}
              to={`/product/${product.slug}`}
              className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
            >
              <div className="aspect-w-1 aspect-h-1">
                <img
                  src={`/images/products/${product.image}`}
                  alt={product.name}
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="p-4">
                <span className="text-sm text-gray-500">{product.category_name}</span>
                <h3 className="text-lg font-semibold text-gray-900 mt-1">{product.name}</h3>
                <div className="mt-2">
                  {product.sale_price ? (
                    <>
                      <span className="text-red-600 font-medium">
                        {formatPrice(product.sale_price)}
                      </span>
                      <span className="text-gray-400 line-through ml-2">
                        {formatPrice(product.price)}
                      </span>
                    </>
                  ) : (
                    <span className="text-gray-900 font-medium">{formatPrice(product.price)}</span>
                  )}
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
```