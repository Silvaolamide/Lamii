<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('reports', function (Blueprint $table) { $table->id(); $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete(); $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete(); $table->string('reason',100); $table->text('details')->nullable(); $table->string('status',20)->default('open')->index(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps(); $table->index(['reported_id','status']); }); } public function down(): void { Schema::dropIfExists('reports'); } };
