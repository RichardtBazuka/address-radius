<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressLookupsTable extends Migration
{
    /**
     * Run the migrations.
     *            Arr::get($baseHouse, 'coordinates.long'),
            Arr::get($baseHouse, 'coordinates.lat'),
            Arr::get($request, 'radius')
     * @return void
     */
    public function up()
    {
        Schema::create('address_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('address', 300);
            $table->string('long', 300);
            $table->string('lat', 300);
            $table->integer('radius');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_lookups');
    }
}
