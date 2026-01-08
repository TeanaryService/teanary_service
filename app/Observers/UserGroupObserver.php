<?php

namespace App\Observers;

use App\Models\UserGroup;

class UserGroupObserver
{
    /**
     * Handle the UserGroup "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(UserGroup $userGroup): void
    {
        // 删除用户组翻译
        $userGroup->userGroupTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（促销-用户组）
        $userGroup->promotions()->detach();

        // 注意：用户的外键设置为null，不需要删除用户
        // 用户的外键 user_group_id 会在删除时自动设置为 null（在代码层面处理）
    }
}
