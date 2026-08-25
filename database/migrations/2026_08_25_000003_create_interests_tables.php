<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->timestamps(); });
        Schema::create('interest_user', function (Blueprint $table) { $table->foreignId('interest_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->primary(['interest_id','user_id']); });
    }
    public function down(): void { Schema::dropIfExists('interest_user'); Schema::dropIfExists('interests'); }
};
