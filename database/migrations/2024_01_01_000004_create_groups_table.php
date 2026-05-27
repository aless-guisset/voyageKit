<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Groupe de voyage (ex: "Famille Dupont", "Les copains")
        Schema::create('trip_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // "Famille Dupont"
            $table->string('icon')->default('👨‍👩‍👧‍👦');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Membres du groupe (Papa, Maman, Léo, Emma...)
        Schema::create('trip_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // "Papa"
            $table->string('avatar_emoji')->default('🧑');
            $table->string('color')->default('#F97316'); // couleur perso
            $table->enum('role', ['adult', 'child', 'teen', 'baby'])->default('adult');
            $table->integer('age')->nullable();
            $table->timestamps();
        });

        // Liaison membres <-> listes de packing (une liste peut appartenir à un membre)
        Schema::table('packing_lists', function (Blueprint $table) {
            $table->foreignId('trip_member_id')->nullable()->after('trip_id')
                  ->constrained('trip_members')->nullOnDelete();
            $table->foreignId('trip_group_id')->nullable()->after('trip_member_id')
                  ->constrained('trip_groups')->nullOnDelete();
        });

        // Liaison membres <-> budget (budget personnalisé par membre)
        Schema::create('member_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_member_id')->constrained('trip_members')->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2)->default(0); // argent alloué à ce membre
            $table->decimal('personal_spending', 12, 2)->default(0); // ses dépenses perso
            $table->timestamps();
        });

        // Liaison membres <-> activités (qui participe à quoi)
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_member_id')->constrained('trip_members')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
        Schema::dropIfExists('member_budgets');
        Schema::table('packing_lists', function (Blueprint $table) {
            $table->dropForeign(['trip_member_id']);
            $table->dropForeign(['trip_group_id']);
            $table->dropColumn(['trip_member_id', 'trip_group_id']);
        });
        Schema::dropIfExists('trip_members');
        Schema::dropIfExists('trip_groups');
    }
};
