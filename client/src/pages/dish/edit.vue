<template>
  <view class="page-wrap page-pad">
    <view class="card form-card">
      <u-form label-width="90">
        <u-form-item label="菜品图片">
          <view class="uploader" @click="chooseImage">
            <image v-if="form.food_img" class="uploader__img" :src="form.food_img" mode="aspectFill" />
            <view v-else class="uploader__plus">+</view>
          </view>
        </u-form-item>
        <u-form-item label="菜品名称" required>
          <u-input v-model="form.food_name" maxlength="50" placeholder="例如：番茄炒蛋" border="none" />
        </u-form-item>
        <u-form-item label="分类">
          <view class="cate-row">
            <u-tag
              v-for="item in FOOD_CATEGORIES"
              :key="item"
              :text="item"
              size="mini"
              :plain="form.category !== item"
              type="warning"
              @click="form.category = item"
            />
          </view>
        </u-form-item>
      </u-form>
    </view>

    <view class="card form-card mt-24">
      <text class="block-title">可烹饪成员</text>
      <view v-if="memberList.length" class="member-wrap">
        <u-tag
          v-for="item in memberList"
          :key="uidOf(item)"
          :text="item.nickname || item.user?.nickname || '成员'"
          size="mini"
          :plain="!cookChecked(uidOf(item))"
          type="warning"
          @click="toggleCook(uidOf(item))"
        />
      </view>
      <text v-else class="text-tips">暂无成员，可先去家庭页邀请</text>
    </view>

    <view class="card form-card mt-24">
      <text class="block-title">规格（辣度 / 分量 / 自定义）</text>
      <SpecEditor v-model="specGroups" />
    </view>

    <view class="mt-24">
      <u-button type="primary" :text="form.id ? '保存修改' : '新增菜品'" :loading="submitting" @click="handleSubmit" />
    </view>
  </view>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getFoodDetail, saveFood } from '@/api/food'
import { FOOD_CATEGORIES } from '@/config/constants'
import { useFamilyStore } from '@/store/modules/family'
import { flattenSpecs, groupSpecs, joinCookUids, parseCookUids } from '@/utils/biz'
import { ensureFamily } from '@/utils/family-guard'
import { uploadFile } from '@/utils/request'
import { assertRequired } from '@/utils/validate'

const familyStore = useFamilyStore()
const submitting = ref(false)
const specGroups = ref([])
const memberList = ref([])
const form = reactive({
  id: '',
  food_name: '',
  food_img: '',
  category: '家常菜',
  cook_uids: '',
})

const selectedCooks = ref([])

function uidOf(item) {
  return Number(item.uid || item.user_id || item.user?.id || 0)
}

function cookChecked(uid) {
  return selectedCooks.value.includes(Number(uid))
}

function toggleCook(uid) {
  const id = Number(uid)
  if (selectedCooks.value.includes(id)) {
    selectedCooks.value = selectedCooks.value.filter((item) => item !== id)
    return
  }
  selectedCooks.value = [...selectedCooks.value, id]
}

async function chooseImage() {
  const res = await uni.chooseImage({ count: 1, sizeType: ['compressed'] })
  const filePath = res.tempFilePaths?.[0] || res[1]?.tempFilePaths?.[0]
  if (!filePath) return
  try {
    const data = await uploadFile({ filePath, url: '/v1/upload' })
    form.food_img = data?.url || data?.path || data?.food_img || filePath
  } catch (error) {
    form.food_img = filePath
  }
}

onLoad(async (query) => {
  if (!ensureFamily()) return
  memberList.value = await familyStore.fetchMembers()
  form.id = query?.id || ''
  if (!form.id) return
  const data = await getFoodDetail(form.id)
  form.food_name = data?.food_name || ''
  form.food_img = data?.food_img || ''
  form.category = data?.category || '家常菜'
  form.cook_uids = data?.cook_uids || ''
  selectedCooks.value = parseCookUids(form.cook_uids)
  specGroups.value = groupSpecs(data?.specs || [])
})

async function handleSubmit() {
  if (!ensureFamily()) return
  if (!assertRequired(form.food_name.trim(), '请填写菜品名称')) return
  const invalid = specGroups.value.find((item) => item.spec_name && !item.values.length)
  if (invalid) {
    uni.showToast({ title: `「${invalid.spec_name}」请至少添加一个选项`, icon: 'none' })
    return
  }
  submitting.value = true
  try {
    await saveFood({
      id: form.id,
      family_id: familyStore.currentFamilyId,
      food_name: form.food_name.trim(),
      food_img: form.food_img,
      category: form.category,
      cook_uids: joinCookUids(selectedCooks.value),
      specs: flattenSpecs(specGroups.value),
    })
    uni.showToast({ title: '保存成功', icon: 'success' })
    setTimeout(() => uni.navigateBack(), 400)
  } catch (error) {
    console.warn('[food] 保存失败', error)
  } finally {
    submitting.value = false
  }
}
</script>

<style lang="scss" scoped>
.form-card {
  padding: 20rpx 24rpx 24rpx;
}

.block-title {
  display: block;
  margin-bottom: 16rpx;
  font-size: 28rpx;
  font-weight: 600;
}

.cate-row,
.member-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
}

.uploader {
  width: 160rpx;
  height: 160rpx;
  border-radius: $radius-sm;
  overflow: hidden;
  background: $bg-page;

  &__img {
    width: 100%;
    height: 100%;
  }

  &__plus {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48rpx;
    color: $text-tips;
  }
}
</style>
