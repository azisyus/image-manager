<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAltColumnToManagedImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('managed_images', function (Blueprint $table) {
            $table->json('alt')->nullable();
        });
        Azizyus\ImageManager\DB\Models\ManagedImage::query()->update([
            'alt' => '{}',
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('managed_images', function (Blueprint $table) {
            $table->dropColumn('alt');
        });
    }
}
