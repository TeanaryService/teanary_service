<?php

namespace App\Livewire\Manager;

use App\Models\User;
use App\Services\LocaleCurrencyService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterUserGroupId = null;
    public string $filterEmailVerified = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    #[Computed]
    public function users()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

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
        $translation = $userGroup->userGroupTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $userGroup->userGroupTranslations->first();

        return $first ? $first->name : $userGroup->id;
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $userGroups = \App\Models\UserGroup::with('userGroupTranslations')->get();

        return view('livewire.manager.users', [
            'users' => $this->users,
            'lang' => $lang,
            'userGroups' => $userGroups,
        ])->layout('components.layouts.manager');
    }
}
