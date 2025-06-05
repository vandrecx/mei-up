<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gasto_fixos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('conta_id')->nullable()->constrained('contas');
            $table->string('descricao', 100);
            $table->decimal('valor', 10, 2);
            $table->enum('categoria', ['moradia', 'utilidades', 'transporte', 'outros']);
            $table->integer('dia_vencimento');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gasto_fixos');
    }
};
