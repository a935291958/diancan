/**
 * 点餐草稿：勾选的菜品、规格、时段，提交前暂存
 */
import { defineStore } from 'pinia'
import { formatDay, getDefaultMealType } from '@/utils/date'

export const useOrderDraftStore = defineStore('orderDraft', {
  state: () => ({
    meal_type: getDefaultMealType(),
    order_date: formatDay(new Date()),
    items: [],
  }),

  getters: {
    selectedCount: (state) => state.items.length,
    hasItems: (state) => state.items.length > 0,
  },

  actions: {
    setMealType(type) {
      this.meal_type = type
    },

    setOrderDate(date) {
      this.order_date = date
    },

    upsertItem(item) {
      const index = this.items.findIndex((row) => Number(row.food_id) === Number(item.food_id))
      if (index >= 0) {
        this.items.splice(index, 1, { ...this.items[index], ...item })
        return
      }
      this.items.push(item)
    },

    removeItem(foodId) {
      this.items = this.items.filter((row) => Number(row.food_id) !== Number(foodId))
    },

    isSelected(foodId) {
      return this.items.some((row) => Number(row.food_id) === Number(foodId))
    },

    clear() {
      this.items = []
      this.meal_type = getDefaultMealType()
      this.order_date = formatDay(new Date())
    },
  },
})
