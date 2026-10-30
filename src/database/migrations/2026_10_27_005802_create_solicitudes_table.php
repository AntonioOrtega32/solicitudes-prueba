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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->nullable()->unique();
            $table->foreignId('solicitante_id')->constrained('users');
            $table->foreignId('tipo_solicitud_id')->constrained('tipos_solicitud');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('prioridad')->default('media');
            $table->decimal('monto', 14, 2)->nullable();
            $table->json('datos')->nullable();
            $table->string('estado')->default('borrador');
            $table->unsignedTinyInteger('niveles_requeridos')->default(1);
            $table->unsignedTinyInteger('nivel_actual')->default(1);
            $table->unsignedSmallInteger('ciclo')->default(1);
            $table->timestamp('enviada_en')->nullable();
            $table->timestamp('resuelta_en')->nullable();
            $table->timestamps();

            $table->index(['estado', 'prioridad']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
