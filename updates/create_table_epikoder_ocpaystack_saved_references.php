<?php

namespace Epikoder\Ocpaystack\Updates;

use October\Rain\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class SavedReference extends Migration
{
    public function up()
    {
        Schema::create('epikoder_ocpaystack_saved_reference', function ($table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id')->unsigned();
            $table->string('reference', 10);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('epikoder_ocpaystack_saved_reference');
    }
}
?>