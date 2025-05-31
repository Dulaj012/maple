import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface CartItem {
  id: number;
  quantity: number;
}

interface CartStore {
  items: CartItem[];
  addItem: (id: number, quantity?: number) => void;
  removeItem: (id: number) => void;
  clearCart: () => void;
}

export const useCart = create<CartStore>()(
  persist(
    (set) => ({
      items: [],
      addItem: (id, quantity = 1) =>
        set((state) => {
          const existingItem = state.items.find((item) => item.id === id);
          if (existingItem) {
            return {
              items: state.items.map((item) =>
                item.id === id ? { ...item, quantity: item.quantity + quantity } : item
              ),
            };
          }
          return { items: [...state.items, { id, quantity }] };
        }),
      removeItem: (id) =>
        set((state) => ({
          items: state.items.filter((item) => item.id !== id),
        })),
      clearCart: () => set({ items: [] }),
    }),
    {
      name: 'cart-storage',
    }
  )
);