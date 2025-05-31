import { Layout } from '@/components/Layout';
import { Hero } from '@/components/Hero';
import { FeaturedProducts } from '@/components/FeaturedProducts';
import { Categories } from '@/components/Categories';
import { Newsletter } from '@/components/Newsletter';

export default function Home() {
  return (
    <Layout>
      <Hero />
      <Categories />
      <FeaturedProducts />
      <Newsletter />
    </Layout>
  );
}