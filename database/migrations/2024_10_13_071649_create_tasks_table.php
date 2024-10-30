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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained('columns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('deadline')->nullable();
            $table->integer('position')->nullable();
            $table->enum('status', ['To Do', 'In Progress', 'Completed'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
