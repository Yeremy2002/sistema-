<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->integer('session_lifetime')->default(60)->after('updated_at');
    });
  }

  public function down()
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->dropColumn('session_lifetime');
    });
  }
};
