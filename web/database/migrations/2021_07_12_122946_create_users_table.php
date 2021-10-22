<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->index();

            $table->string('display_name', 50)->nullable();

            $table->char('slug', 9)
                ->unique()
                ->index()
                ->comment('unique user code');

            $table->string('phone', 50)->nullable();

            $table->string('email', 100)->nullable();

            $table->integer('total_time')
                ->nullable()
                ->comment('Total time spent by the user for all appointments');

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
        Schema::dropIfExists('users');
    }
}
