/**
 * 兼容旧引用：菜品接口已迁移至 food.js（对应 food / food_spec 表）
 */
export {
  getFoodList as getDishList,
  getFoodDetail as getDishDetail,
  saveFood as saveDish,
  deleteFood as deleteDish,
} from './food'
