<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\User;
use App\Models\UserGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Users extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $filterUserGroupId = null;
    public string $filterEmailVerified = '';

    public function updatingFilterUserGroupId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEmailVerified(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterUserGroupId = null;
        $this->filterEmailVerified = '';
        $this->resetPage();
    }

    public function deleteUser(int $id): void
    {
        $this->deleteModel(User::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->users->getCollection();
    }

    public function batchDeleteUsers(): void
    {
        $this->batchDelete(User::class);
    }

    #[Computed]
    public function users()
    {
        $lang = $this->getCurrentLanguage();

        $query = User::query()
            ->with(['userGroup.userGroupTranslations', 'orders']);

        // 搜索：通过名称、邮箱搜索
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // 筛选：用户组
        if ($this->filterUserGroupId) {
            $query->where('user_group_id', $this->filterUserGroupId);
        }

        // 筛选：邮箱验证状态
        if ($this->filterEmailVerified === '1') {
            $query->whereNotNull('email_verified_at');
        } elseif ($this->filterEmailVerified === '0') {
            $query->whereNull('email_verified_at');
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getUserGroupName($userGroup, $lang)
    {
        if (! $userGroup) {
            return '-';
        }

        return $this->translatedField($userGroup->userGroupTranslations, $lang, 'name', (string) $userGroup->id);
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $userGroups = UserGroup::with('userGroupTranslations')->get();

        return view('livewire.manager.users', [
            'users' => $this->users,
            'lang' => $lang,
            'userGroups' => $userGroups,
        ])->layout('components.layouts.manager');
    }
}
