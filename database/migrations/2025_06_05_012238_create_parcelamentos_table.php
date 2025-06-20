<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parcelamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('conta_id')->nullable()->constrained('contas');
            $table->string('descricao', 100);
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_parcela', 10, 2);
            $table->integer('total_parcelas');
            $table->integer('parcelas_pagas')->default(0);
            $table->date('data_primeira_parcela');
            $table->integer('dia_vencimento');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcelamentos');
    }
};
