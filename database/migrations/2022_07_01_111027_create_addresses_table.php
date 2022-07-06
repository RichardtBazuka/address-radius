<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     * 
            $result = [
                'address' => Arr::get($house, 'address.adgangsadresse.address'),
                'area'    => Arr::get($house, 'area.total'),
                'heating' => Arr::get($house, 'heating.warmup'),
                'used_by' => Arr::get($house, 'general.rent_type'),
                'type' => Arr::get($house, 'general.building_type'),
            ];
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address', 300);
            $table->integer('zip', 300);
            $table->string('city');
            $table->integer('area');
            $table->string('heating', 300);
            $table->string('used_by', 300);
            $table->string('type', 300);
            $table->unsignedBigInteger('address_lookup_id');
            $table->timestamps();

            $table->foreign('address_lookup_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
