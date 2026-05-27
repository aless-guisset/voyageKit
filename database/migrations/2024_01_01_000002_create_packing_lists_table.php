<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Templates réutilisables de listes
        Schema::create('list_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // ex: "Valise été", "Nourriture road trip"
            $table->string('type'); // packing, grocery, todo, shopping
            $table->string('icon')->default('📋');
            $table->boolean('is_public')->default(false); // templates partagés
            $table->timestamps();
        });

        // Listes attachées à un voyage OU standalone
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('list_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('packing'); // packing, grocery, shopping, todo
            $table->string('icon')->default('🧳');
            $table->timestamps();
        });

        Schema::create('packing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // vêtements, hygiène, tech, etc.
            $table->decimal('quantity', 8, 2)->default(1);
            $table->string('unit')->nullable(); // pièces, kg, L, etc.
            $table->decimal('unit_price', 10, 2)->nullable(); // pour le décompte d'achat
            $table->boolean('is_checked')->default(false);
            $table->boolean('need_to_buy')->default(false);
            $table->string('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Items de template (seed for new lists)
        Schema::create('list_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->boolean('need_to_buy')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_template_items');
        Schema::dropIfExists('packing_items');
        Schema::dropIfExists('packing_lists');
        Schema::dropIfExists('list_templates');
    }
};
