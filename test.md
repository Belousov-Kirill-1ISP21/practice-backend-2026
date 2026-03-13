# Тестирование Booking API

## Протестировать всё можно с помощью:
```bash
php artisan test  
```

# Для ручного тестирвоания конкретных эндпоинтов:

# Аутентификация

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
## 6. Войти как админ:
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"admin@example.com\",\"password\":\"admin123\"}"
```

# Рейсы

## 7. Получение списка всех рейсов с фильтрацией
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12" -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 8. Получение детальной информации о конкретном рейсе
```bash
curl -X GET http://127.0.0.1:8000/api/flights/1 -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 9. Создание нового рейса (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/flights -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_number\":\"SU1234\",\"origin_airport_id\":1,\"dest_airport_id\":2,\"departure_time\":\"2026-04-01T10:00:00\",\"arrival_time\":\"2026-04-01T13:00:00\",\"aircraft_id\":1,\"base_price\":7500}"
```
## 10. Обновление данных рейса (только для админа)
```bash
curl -X PUT http://127.0.0.1:8000/api/flights/1 -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"base_price\":8500}"
```
## 11. Удаление рейса (только для админа)
```bash
curl -X DELETE http://127.0.0.1:8000/api/flights/5 -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 12. Поиск свободных рейсов на конкретную дату
```bash
curl -X GET "http://127.0.0.1:8000/api/flights/available?date=2026-03-13" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# Бронирования


## 13. Создание нового бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_id\":4,\"passengers\":[{\"first_name\":\"Иван\",\"last_name\":\"Петров\",\"birth_date\":\"1990-01-01\",\"passport_number\":\"1234567890\",\"seat_number\":\"12A\"}]}"
```
## 14. Получение списка своих бронирований
```bash
curl -X GET http://127.0.0.1:8000/api/bookings/my -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 15. Получение детальной информации о бронировании
```bash
curl -X GET http://127.0.0.1:8000/api/bookings/3 -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 16. Отмена бронирования пользователем
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/5/cancel -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 17. Отмена бронирования администратором
```bash
curl -X POST http://127.0.0.1:8000/api/admin/bookings/5/cancel -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 18. Оплата бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/4/pay -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"payment_method\":\"card\",\"card_number\":\"4111111111111111\"}"
```
## 19. Попытка забронировать на пересекающееся время 
```bash
curl -X POST http://127.0.0.1:8000/api/bookings -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_id\":1,\"passengers\":[{\"first_name\":\"Петр\",\"last_name\":\"Иванов\",\"birth_date\":\"1990-01-01\",\"passport_number\":\"1111111111\",\"seat_number\":\"14B\"}]}"
```

# Аэропорты


## 20. Получение списка всех аэропортов
```bash
curl -X GET http://127.0.0.1:8000/api/airports -H "Authorization: Bearer ВАШ_ТОКЕН"
```
## 21. Добавление нового аэропорта (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/airports -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"code\":\"ARR\",\"name\":\"Sochi\",\"city\":\"Sochi\",\"country\":\"Russia\",\"timezone\":\"Europe/Moscow\"}"
```
## 22. Попытка добавить аэропорт с существующим кодом 
```bash
curl -X POST http://127.0.0.1:8000/api/airports -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"code\":\"MOW\",\"name\":\"Moscow\",\"city\":\"Moscow\",\"country\":\"Russia\",\"timezone\":\"Europe/Moscow\"}"
```


# Самолёты


## 23. Получение списка всех самолетов
```bash
curl -X GET http://127.0.0.1:8000/api/aircrafts -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 24. Добавление нового самолета (только для админа)
```bash
curl -X POST http://127.0.0.1:8000/api/aircrafts -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"model\":\"Boeing 777-300\",\"manufacturer\":\"Boeing\",\"total_seats\":350}"
```

## 25. Удаление самолета (только для админа)
```bash
curl -X DELETE http://127.0.0.1:8000/api/aircrafts/4 -H "Authorization: Bearer ВАШ_ТОКЕН"
```


# Фильтрация


## 26. Поиск рейсов по дате вылета
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?date=2026-04-01" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 27. Поиск рейсов по городу отправления
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 28. Поиск рейсов с минимальной ценой
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?min_price=5000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 29. Поиск рейсов с максимальной ценой
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?max_price=10000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 30. Комбинированный поиск рейсов
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?departure_city=Moscow&arrival_city=Saint%20Petersburg&date=2026-03-12&min_price=4000&max_price=6000" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# Тесты граничных случаев

## 31. Попытка создания бронирования на несуществующий рейс
```bash
curl -X POST http://127.0.0.1:8000/api/bookings -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"flight_id\":4444,\"passengers\":[{\"first_name\":\"Ivan\",\"last_name\":\"Petrov\",\"birth_date\":\"1990-01-01\",\"passport_number\":\"1234567890\",\"seat_number\":\"12A\"}]}"
```

## 32. Попытка отмены уже отмененного бронирования
```bash
curl -X POST http://127.0.0.1:8000/api/bookings/5/cancel -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# Отзывы и расписание

# 33. Добавление отзыва на рейс (только после завершённого бронирования)
```bash
curl -X POST http://127.0.0.1:8000/api/flights/1/reviews -H "Content-Type: application/json" -H "Authorization: Bearer ВАШ_ТОКЕН" -d "{\"rating\":5,\"comment\":\"Great flight, on time\",\"booking_id\":1}"
```

# 34. Получение всех отзывов на рейс
```bash
curl -X GET http://127.0.0.1:8000/api/flights/1/reviews -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# 35. Получение среднего рейтинга рейса 
```bash
curl -X GET http://127.0.0.1:8000/api/flights/1 -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# 36. Расписание рейсов на день 
```bash
curl -X GET "http://127.0.0.1:8000/api/flights/schedule?date=2026-03-13" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# 37. Расписание рейсов на неделю
```bash
curl -X GET "http://127.0.0.1:8000/api/flights/schedule?week=2026-03-13" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

# Пагинация

## 38. Пагинация списка рейсов (страница 1)
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?page=1&per_page=5" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 39. Пагинация списка рейсов (страница 2)
```bash
curl -X GET "http://127.0.0.1:8000/api/flights?page=2&per_page=5" -H "Authorization: Bearer ВАШ_ТОКЕН"
```

