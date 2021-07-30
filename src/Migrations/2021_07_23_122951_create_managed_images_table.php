<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManagedImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('managed_images', function (Blueprint $table) {
            $table->id();

            $table->string('fileName');
            $table->string('originalFileName');
            $table->string('size');
            $table->string('extension');
            $table->string('type')->nullable();
            $table->json('variations')->nullable();
            $table->integer('relatedModelId')->nullable();
            $table->string('relatedModel')->nullable();
            $table->unsignedInteger('sort')->nullable();

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
        Schema::dropIfExists('managed_images');
    }
}
