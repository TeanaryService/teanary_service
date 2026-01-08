<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso_code_2', 2)->nullable();
            $table->string('iso_code_3', 3)->nullable();
            $table->boolean('postcode_required')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['country_id', 'language_id']);
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('zone_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['zone_id', 'language_id']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary();

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('session_id')->nullable();

            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('company')->nullable();

            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();

            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete(); // 基础数据，保持外键
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete(); // 基础数据，保持外键

            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('zone_translations');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('country_translations');
        Schema::dropIfExists('countries');
    }
};
