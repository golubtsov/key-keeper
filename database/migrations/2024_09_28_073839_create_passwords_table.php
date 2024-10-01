<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("passwords", function (Blueprint $table) {
            $table->id();
            $table->integer("user_id")->nullable();
            $table->string("login");
            $table->string("resource");
            $table->text("hash");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("passwords");
    }
};
