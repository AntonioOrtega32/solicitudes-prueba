<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->foreignId('aprobador_id')->constrained('users');
            $table->unsignedTinyInteger('nivel');
            $table->unsignedSmallInteger('ciclo')->default(1);
            $table->string('estado')->default('pendiente');
            $table->text('comentario')->nullable();
            $table->timestamp('decidida_en')->nullable();
            $table->timestamps();

            $table->unique(['solicitud_id', 'ciclo', 'nivel']);
            $table->index(['aprobador_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprobaciones');
    }
};
