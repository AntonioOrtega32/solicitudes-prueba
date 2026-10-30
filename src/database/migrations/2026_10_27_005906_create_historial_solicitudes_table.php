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
        Schema::create('historial_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('accion');
            $table->text('comentario')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['solicitud_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_solicitudes');
    }
};
