<template>
  <view class="member-list">
    <view v-if="!list.length" class="member-list__empty">
      <u-empty mode="data" text="暂无家庭成员" />
    </view>
    <view v-else class="member-list__card">
      <view
        v-for="item in list"
        :key="item.id || memberUidOf(item)"
        class="member"
        @click="onClick(item)"
      >
        <u-avatar :src="memberAvatarOf(item)" size="40" />
        <view class="flex-1">
          <text class="member__name">{{ memberNameOf(item) }}</text>
          <text v-if="showJoinTime" class="member__time">{{ joinText(item) }}</text>
        </view>
        <u-tag :text="roleText(item)" size="mini" :type="isAdmin(item) ? 'warning' : 'info'" plain />
        <text v-if="showRemove && !isAdmin(item)" class="member__remove" @click.stop="onRemove(item)">
          移除
        </text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 家庭成员列表
 * 展示昵称、头像、角色（管理员 / 普通成员）
 *
 * @example
 * <MemberList :list="members" :admin-uid="adminUid" show-remove @remove="onRemove" />
 */
import { memberAvatarOf, memberNameOf, memberUidOf } from '@/utils/biz'
import { formatDate } from '@/utils/date'

const props = defineProps({
  list: { type: Array, default: () => [] },
  /** 家庭管理员 uid，对应 family.admin_uid */
  adminUid: { type: [Number, String], default: 0 },
  showRemove: { type: Boolean, default: false },
  showJoinTime: { type: Boolean, default: true },
})

const emit = defineEmits(['click', 'remove'])

function isAdmin(item) {
  return memberUidOf(item) === Number(props.adminUid)
}

function roleText(item) {
  return isAdmin(item) ? '管理员' : '普通成员'
}

function joinText(item) {
  if (!item.create_time) return '刚加入'
  return `加入于 ${formatDate(item.create_time, 'YYYY-MM-DD')}`
}

function onClick(item) {
  emit('click', item)
}

function onRemove(item) {
  emit('remove', item)
}
</script>

<style lang="scss" scoped>
.member-list {
  &__card {
    background: $bg-white;
    border-radius: $radius-md;
    box-shadow: $shadow-card;
    overflow: hidden;
  }

  &__empty {
    padding: 40rpx 0;
  }
}

.member {
  display: flex;
  align-items: center;
  gap: 20rpx;
  padding: 24rpx;

  &__name {
    display: block;
    font-weight: 600;
    font-size: 28rpx;
  }

  &__time {
    display: block;
    margin-top: 4rpx;
    font-size: 22rpx;
    color: $text-tips;
  }

  &__remove {
    margin-left: 12rpx;
    font-size: 24rpx;
    color: $color-danger;
  }
}
</style>
