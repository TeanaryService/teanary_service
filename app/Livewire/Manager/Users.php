<?php

namespace App\Livewire\Manager;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public $search = '';
    public $userGroupIdFilter = null;
    public $emailVerifiedFilter = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'userGroupIdFilter' => ['except' => null],
        'emailVerifiedFilter' => ['except' => null],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->userGroupIdFilter = null;
        $this->emailVerifiedFilter = null;
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        $query = User::query()
            ->with(['userGroup.userGroupTranslations'])
            ->withCount('orders')
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->userGroupIdFilter, function (Builder $query) {
                $query->where('user_group_id', $this->userGroupIdFilter);
            })
            ->when($this->emailVerifiedFilter !== null, function (Builder $query) {
                if ($this->emailVerifiedFilter === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($this->emailVerifiedFilter === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function getUserGroupsProperty()
    {
        $locale = app()->getLocale();
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
        
        return UserGroup::with('userGroupTranslations')
            ->get()
            ->map(function ($group) use ($lang) {
                $translation = $group->userGroupTranslations->where('language_id', $lang?->id)->first();
                $group->display_name = $translation?->name ?? $group->userGroupTranslations->first()?->name ?? $group->id;
                return $group;
            })
            ->sortBy('display_name');
    }

    public function render()
    {
        return view('livewire.manager.users', [
            'users' => $this->users,
            'userGroups' => $this->userGroups,
        ])->layout('components.layouts.manager');
    }
}
