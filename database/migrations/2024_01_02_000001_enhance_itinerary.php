<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('itinerary_events', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('location');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('place_id')->nullable()->after('lng');        // OSM place id
            $table->decimal('toll_cost', 8, 2)->nullable()->after('estimated_cost'); // péages
            $table->integer('travel_minutes')->nullable()->after('toll_cost');       // durée trajet
            $table->integer('rest_minutes')->nullable()->after('travel_minutes');    // pause
            $table->text('places_to_visit')->nullable()->after('rest_minutes');      // lieux JSON
            $table->text('travel_notes')->nullable()->after('places_to_visit');      // notes trajet
        });
    }
    public function down(): void
    {
        Schema::table('itinerary_events', function (Blueprint $table) {
            $table->dropColumn(['lat','lng','place_id','toll_cost','travel_minutes','rest_minutes','places_to_visit','travel_notes']);
        });
    }
};
