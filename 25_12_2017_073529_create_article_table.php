<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration 
{
    public function up() 
    {
        Schema::create('article', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 256);
            $table->index('title', 'idx_title');
            $table->timestamps();
        });
    }

    public function down() 
    {
        Schema::dropIfExists('article');
    }

}
