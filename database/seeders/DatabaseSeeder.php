<?php

namespace Database\Seeders;

use App\Models\Manager;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->flushScoutIndexes();

        Artisan::call('queue:clear');
        Cache::flush();
        File::cleanDirectory(storage_path('app/public'));

        $testEmail = config('testing.user.email', 'test@example.com');
        $testPassword = config('testing.user.password', 'password');

        Manager::factory()->create([
            'name' => 'Test User',
            'email' => $testEmail,
            'password' => Hash::make($testPassword),
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => $testEmail,
            'password' => Hash::make($testPassword),
        ]);

        $this->call(FullSeeder::class);

        $this->call(CountriesTableSeeder::class);
        $this->call(CountryTranslationsTableSeeder::class);
        $this->call(ZonesTableSeeder::class);
        $this->call(ZoneTranslationsTableSeeder::class);

        $this->call(CommerceSeeder::class);

        Artisan::call('scout:sync-index-settings');
    }

    /**
     * 清空所有 scout 索引.
     */
    private function flushScoutIndexes(): void
    {
        collect(config('scout.meilisearch.index-settings'))
            ->keys()
            ->each(function ($model) {
                Artisan::call('scout:flush', ['model' => $model]);
            });
    }
}
