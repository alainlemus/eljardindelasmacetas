<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30);
            $table->string('label')->nullable();
            $table->string('url')->nullable();
            $table->string('phone', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // El número que hasta ahora vivía en WHATSAPP_NUMBER pasa a la tabla para que el
        // botón "Pedir por WhatsApp" siga funcionando igual y desde aquí se administre en el panel.
        $number = preg_replace('/\D+/', '', (string) config('services.whatsapp.number'));

        if ($number !== '') {
            DB::table('social_links')->insert([
                'platform' => 'whatsapp',
                'phone' => $number,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
