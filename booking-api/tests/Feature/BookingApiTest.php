<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Aircraft;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected $userToken;
    protected $adminToken;
    protected $bookingId;
    protected $testUser;
    protected $testAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Запускаем все сидеры
        $this->seed();
        
        // Сохраняем тестовых пользователей
        $this->testUser = User::where('email', 'user@example.com')->first();
        $this->testAdmin = User::where('email', 'admin@example.com')->first();
    }

    protected function getUserToken()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'user123'
        ]);
        return $response->json('token');
    }

    protected function getAdminToken()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123'
        ]);
        return $response->json('token');
    }

    // ==================== АУТЕНТИФИКАЦИЯ ====================

    public function test_01_user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'newuser@test.com',
            'password' => '12345678',
            'last_name' => 'Ivanov',
            'first_name' => 'Egor'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'token', 'user']);
    }

    public function test_02_user_can_login()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'user123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_03_user_can_get_me()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJsonPath('data.email', 'user@example.com');
    }

    public function test_04_non_admin_cannot_access_admin_endpoint()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/airports', [
                'code' => 'ATR',
                'name' => 'Sochi',
                'city' => 'Sochi',
                'country' => 'Russia',
                'timezone' => 'Europe/Moscow'
            ]);

        $response->assertStatus(403);
    }

    public function test_05_user_can_logout()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_06_admin_can_login()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    // ==================== РЕЙСЫ ====================

    public function test_07_can_get_flights_with_filters()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12');

        $response->assertStatus(200);
        // Не проверяем точное количество, просто что есть данные
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_08_can_get_single_flight()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights/1');

        $response->assertStatus(200)
                 ->assertJsonPath('data.flight_number', 'SU100');
    }

    public function test_09_admin_can_create_flight()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/flights', [
                'flight_number' => 'SU1234',
                'origin_airport_id' => 1,
                'dest_airport_id' => 2,
                'aircraft_id' => 1,
                'departure_time' => Carbon::now()->addDays(1)->format('Y-m-d\TH:i:s'),
                'arrival_time' => Carbon::now()->addDays(1)->addHours(2)->format('Y-m-d\TH:i:s'),
                'base_price' => 7500
            ]);

        $response->assertStatus(201);
    }

    public function test_10_admin_can_update_flight()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/flights/1', [
                'base_price' => 8500
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('flights', ['id' => 1, 'base_price' => 8500]);
    }

    public function test_11_admin_can_delete_flight()
    {
        $token = $this->getAdminToken();
        
        // Создаём рейс для удаления
        $flight = Flight::create([
            'flight_number' => 'SU9999',
            'origin_airport_id' => 1,
            'dest_airport_id' => 2,
            'aircraft_id' => 1,
            'departure_time' => Carbon::now()->addDays(1),
            'arrival_time' => Carbon::now()->addDays(1)->addHours(2),
            'base_price' => 5000,
            'status' => 'scheduled'
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/flights/{$flight->id}");

        $response->assertStatus(200);
    }

    public function test_12_can_search_available_flights()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/flights/available?date=2026-03-13'); 

        $response->assertStatus(200);
    }

    // ==================== БРОНИРОВАНИЯ ====================

    public function test_13_user_can_create_booking()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);

        $response->assertStatus(201);
        $this->bookingId = $response->json('data.booking.id');
    }

    public function test_14_user_can_get_my_bookings()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование
        $bookingResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/bookings/my');

        $response->assertStatus(200);
    }

    public function test_15_user_can_get_single_booking()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование
        $bookingResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);
        
        $bookingId = $bookingResponse->json('data.booking.id');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/bookings/{$bookingId}");

        $response->assertStatus(200);
    }

    public function test_16_user_can_cancel_booking()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование
        $bookingResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);
        
        $bookingId = $bookingResponse->json('data.booking.id');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$bookingId}/cancel");

        $response->assertStatus(200);
    }

    public function test_17_admin_can_cancel_booking()
    {
        $adminToken = $this->getAdminToken();
        
        // Находим любое существующее бронирование
        $booking = \App\Models\Booking::first();
        $bookingId = $booking->id;
        
        // Админ отменяет
        $response = $this->withHeader('Authorization', "Bearer $adminToken")
            ->postJson("/api/admin/bookings/{$bookingId}/cancel");
        
        $response->assertStatus(200);
    }

    public function test_18_user_can_pay_booking()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование
        $bookingResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);
        
        $bookingId = $bookingResponse->json('data.booking.id');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$bookingId}/pay", [
                'payment_method' => 'card',
                'card_number' => '4111111111111111'
            ]);

        $response->assertStatus(200);
    }

    public function test_19_cannot_book_overlapping_time()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование на рейс 1
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 1,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);

        // Пытаемся забронировать тот же рейс (должно быть 409 Conflict)
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 1,
                'passengers' => [
                    [
                        'first_name' => 'Petr',
                        'last_name' => 'Ivanov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1111111111',
                        'seat_number' => '14B'
                    ]
                ]
            ]);

        $response->assertStatus(409);
    }

    // ==================== АЭРОПОРТЫ ====================

    public function test_20_can_get_airports()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/airports');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_21_admin_can_create_airport()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/airports', [
                'code' => 'ARR',
                'name' => 'Sochi',
                'city' => 'Sochi',
                'country' => 'Russia',
                'timezone' => 'Europe/Moscow'
            ]);

        $response->assertStatus(201);
    }

    public function test_22_cannot_create_duplicate_airport_code()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/airports', [
                'code' => 'MOW',
                'name' => 'Moscow',
                'city' => 'Moscow',
                'country' => 'Russia',
                'timezone' => 'Europe/Moscow'
            ]);

        $response->assertStatus(422);
    }

    // ==================== САМОЛЁТЫ ====================

    public function test_23_can_get_aircrafts()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/aircrafts');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_24_admin_can_create_aircraft()
    {
        $token = $this->getAdminToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/aircrafts', [
                'model' => 'Boeing 777-300',
                'manufacturer' => 'Boeing',
                'total_seats' => 350
            ]);

        $response->assertStatus(201);
    }

    public function test_25_admin_can_delete_aircraft()
    {
        $token = $this->getAdminToken();
        
        // Создаём самолёт для удаления
        $aircraft = Aircraft::create([
            'model' => 'Boeing 777-300',
            'manufacturer' => 'Boeing',
            'total_seats' => 350
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/aircrafts/{$aircraft->id}");

        $response->assertStatus(200);
    }

    // ==================== ФИЛЬТРАЦИЯ ====================

    public function test_26_can_filter_flights_by_date()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?date=2026-04-01');

        $response->assertStatus(200);
    }

    public function test_27_can_filter_flights_by_departure_city()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?departure_city=Moscow');

        $response->assertStatus(200);
    }

    public function test_28_can_filter_flights_by_min_price()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?min_price=5000');

        $response->assertStatus(200);
    }

    public function test_29_can_filter_flights_by_max_price()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?max_price=10000');

        $response->assertStatus(200);
    }

    public function test_30_can_filter_flights_combined()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12&min_price=4000&max_price=6000');

        $response->assertStatus(200);
    }

    // ==================== ГРАНИЧНЫЕ СЛУЧАИ ====================

    public function test_31_cannot_book_non_existent_flight()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 99999,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);

        $response->assertStatus(422);
    }

    public function test_32_cannot_cancel_already_cancelled_booking()
    {
        $token = $this->getUserToken();
        
        // Создаём бронирование
        $bookingResponse = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/bookings', [
                'flight_id' => 4,
                'passengers' => [
                    [
                        'first_name' => 'Ivan',
                        'last_name' => 'Petrov',
                        'birth_date' => '1990-01-01',
                        'passport_number' => '1234567890',
                        'seat_number' => '12A'
                    ]
                ]
            ]);
        
        $bookingId = $bookingResponse->json('data.booking.id');

        // Отменяем первый раз
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$bookingId}/cancel");

        // Пытаемся отменить второй раз
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/bookings/{$bookingId}/cancel");

        $response->assertStatus(409);
    }

    // ==================== ОТЗЫВЫ ====================

    public function test_33_user_can_add_review()
    {
        $token = $this->getUserToken();
        
        // Получаем пользователя
        $user = User::where('email', 'user@example.com')->first();
        
        // Находим подтверждённое бронирование
        $booking = Booking::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->whereHas('flight', function($q) {
                $q->where('flight_number', 'SU999');
            })
            ->first();
        
        // Если нет в базе - пропускаем
        if (!$booking) {
            $this->markTestSkipped('Нет бронирования на рейс SU999. Запусти сиды заново.');
        }
        
        // Добавляем отзыв
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/flights/{$booking->flight_id}/reviews", [
                'booking_id' => $booking->id,
                'rating' => 5,
                'comment' => 'Great flight, on time'
            ]);

        $response->assertStatus(201);
    }

    public function test_34_can_get_flight_reviews()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights/1/reviews');

        $response->assertStatus(200);
    }

    public function test_35_flight_has_average_rating()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights/1');

        $response->assertStatus(200);
    }

    // ==================== РАСПИСАНИЕ ====================

    public function test_36_can_get_daily_schedule()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights/schedule?date=2026-03-13');

        $response->assertStatus(200);
    }

    public function test_37_can_get_weekly_schedule()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights/schedule?week=2026-03-13');

        $response->assertStatus(200);
    }

    // ==================== ПАГИНАЦИЯ ====================

    public function test_38_can_get_first_page_of_flights()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?page=1&per_page=5');

        $response->assertStatus(200);
    }

    public function test_39_can_get_second_page_of_flights()
    {
        $token = $this->getUserToken();
        
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/flights?page=2&per_page=5');

        $response->assertStatus(200);
    }
}