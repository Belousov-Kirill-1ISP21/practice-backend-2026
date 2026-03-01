<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Роли
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
        });

        // 2. Пользователи
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->foreignId('role_id')->constrained('roles');
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('passport_number', 20)->unique()->nullable();
            $table->date('birth_date')->nullable();
        });

        // 3. Аэропорты
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 255);
            $table->string('city', 100);
            $table->string('country', 100);
            $table->string('timezone', 50);
        });

        // 4. Самолеты
        Schema::create('aircrafts', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100);
            $table->string('manufacturer', 100);
            $table->integer('total_seats');
        });

        // 5. Рейсы
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number', 10)->unique();
            $table->foreignId('origin_airport_id')->constrained('airports');
            $table->foreignId('dest_airport_id')->constrained('airports');
            $table->foreignId('aircraft_id')->constrained('aircrafts');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('base_price', 10, 2);
            $table->enum('status', ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])
                  ->default('scheduled');
        });

        // 6. Бронирования
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('flight_id')->constrained('flights');
            $table->string('code', 6)->unique();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])
                  ->default('pending');
            $table->dateTime('expires_at');
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])
                  ->default('pending');
        });

        // 7. Пассажиры
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('passport_number', 20)->nullable();
            $table->string('seat_number', 5)->nullable();
            $table->decimal('ticket_price', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passengers');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('flights');
        Schema::dropIfExists('aircrafts');
        Schema::dropIfExists('airports');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};