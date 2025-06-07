<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('hotels', function (Blueprint $table) {
      $table->integer('session_lifetime')->default(60)->after('logo');
    });
  }

  public function down()
  {
    Schema::table('hotels', function (Blueprint $table) {
      $table->dropColumn('session_lifetime');
    });
  }
};
