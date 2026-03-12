# Тестирование Booking API

## 1. Регистрация нового пользователя
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register -H "Content-Type: application/json" -d "{\"email\":\"test4@test.com\",\"password\":\"12345678\",\"last_name\":\"Ivanov\",\"first_name\":\"Egor\"}"
```
## 2. Авторизация зарегистрированного пользователя
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"test4@test.com\",\"password\":\"12345678\"}"
```
## 3. Получение информации о текущем пользователе
```bash
curl -X GET http://127.0.0.1:8000/api/auth/me -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 4. Попытка выполнить команду, доступную только админу не из админа
```bash
curl -X POST http://127.0.0.1:8000/api/airports -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"code\":\"ATR\",\"name\":\"Sochi\",\"city\":\"Sochi\",\"country\":\"Russia\",\"timezone\":\"Europe/Moscow\"}"
```
## 5. Выход из системы
```bash
curl -X POST http://127.0.0.1:8000/api/auth/logout -H "Authorization: Bearer ВАШ_ТОКЕН"
```


# Тестирование эндпоинтов рейсов (Flights)

# Некоторые команды доступны только для администратора. Войти как админ:
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"admin@example.com\",\"password\":\"admin123\"}"
```

## 6. Получение списка всех рейсов с фильтрацией
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 7. Получение детальной информации о конкретном рейсе
```bash
curl -X GET http://127.0.0.1:8000/api/flights/1 -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 8. Создание нового рейса (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/flights -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_number\":\"SU1234\",\"origin_airport_id\":1,\"dest_airport_id\":2,\"departure_time\":\"2026-04-01T10:00:00\",\"arrival_time\":\"2026-04-01T13:00:00\",\"aircraft_id\":1,\"base_price\":7500}"
```
## 9. Обновление данных рейса (только для админа)
```bash
curl -X PUT http://127.0.0.1:8000/api/flights/1 -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"base_price\":8500}"
```
## 10. Удаление рейса (только для админа)
```bash
curl -X DELETE http://127.0.0.1:8000/api/flights/5 -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# Тестирование эндпоинтов бронирований (Bookings)

## 11. Создание нового бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_id\":4,\"passengers\":[{\"first_name\":\"Иван\",\"last_name\":\"Петров\",\"birth_date\":\"1990-01-01\",\"passport_number\":\"1234567890\",\"seat_number\":\"12A\"}]}"
```
## 12. Получение списка своих бронирований
```bash
curl -X GET http://127.0.0.1:8000/api/bookings/my -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 13. Получение детальной информации о бронировании
```bash
curl -X GET http://127.0.0.1:8000/api/bookings/3 -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 14. Отмена бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/5/cancel -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 15. Оплата бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/4/pay -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"payment_method\":\"card\",\"card_number\":\"4111111111111111\"}"
```

# Тестирование справочников (аэропорты)

## 16. Получение списка всех аэропортов
```bash
curl -X GET http://127.0.0.1:8000/api/airports -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 17. Добавление нового аэропорта (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/airports -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"code\":\"ARR\",\"name\":\"Sochi\",\"city\":\"Sochi\",\"country\":\"Russia\",\"timezone\":\"Europe/Moscow\"}"
```

# Тестирование справочников (самолеты)

## 18. Получение списка всех самолетов

```bash
curl -X GET http://127.0.0.1:8000/api/aircrafts -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 19. Добавление нового самолета (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/aircrafts -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"model\":\"Boeing 444-800\",\"manufacturer\":\"Boeing\",\"total_seats\":189}"
```
## 20. Удаление самолета (только для админа)
```bash
curl -X DELETE http://127.0.0.1:8000/api/aircrafts/4 -H "Authorization: Bearer ВАШ_ТОКЕН"
```
# Дополнительные тесты для проверки фильтрации и поиска

## 21. Поиск рейсов по дате вылета
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?date=2026-04-01" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 22. Поиск рейсов по городу отправления
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 23. Поиск рейсов с минимальной ценой
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?min_price=5000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 24. Поиск рейсов с максимальной ценой
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?max_price=10000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 25. Комбинированный поиск рейсов
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12&min_price=4000&max_price=6000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
# Тесты для проверки ошибок и граничных случаев

## 26. Попытка создания бронирования на несуществующий рейс
```bash
curl -X POST http://127.0.0.1:8000/api/bookings -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_id\":4444,\"passengers\":[{\"first_name\":\"Ivan\",\"last_name\":\"Petrov\",\"birth_date\":\"1990-01-01\",\"passport_number\":\"1234567890\",\"seat_number\":\"12A\"}]}"
```
## 27. Попытка отмены уже отмененного бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/5/cancel -H "Authorization: Bearer ВАШ_ТОКЕН"
```