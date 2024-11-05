<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('passwords', static function (Blueprint $table): void {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('login');
            $table->string('resource');
            $table->text('comment')->nullable();
            $table->text('hash');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passwords');
    }
};
