<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('total_target', 12, 2)->nullable(); // Budget cible total
            $table->timestamps();
        });

        // Entrées de budget (ex: "100€ semaine 1", "200€ semaine 3")
        Schema::create('budget_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->date('date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // Dépenses (sur place + prévisions)
        Schema::create('budget_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('category'); // logement, transport, nourriture, activités, shopping, autre
            $table->decimal('amount', 12, 2);
            $table->date('date')->nullable();
            $table->enum('type', ['actual', 'planned'])->default('actual'); // réelle ou prévue
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // Tableau activités / tarifs (référentiel prix)
        Schema::create('budget_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // ex: "Musée du Louvre"
            $table->string('category')->nullable();
            $table->decimal('price_per_person', 10, 2)->default(0);
            $table->integer('persons')->default(1);
            $table->boolean('is_planned')->default(true);
            $table->boolean('is_paid')->default(false);
            $table->date('date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_activities');
        Schema::dropIfExists('budget_expenses');
        Schema::dropIfExists('budget_incomes');
        Schema::dropIfExists('budgets');
    }
};
