# Тестирование Booking API

## 1. Регистрация нового пользователя
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register -H "Content-Type: application/json" -d "{\"email\":\"test4@test.com\",\"password\":\"12345678\",\"last_name\":\"Семёнов\",\"first_name\":\"Семён\"}"
```

## 2. Авторизация зарегистрированного пользователя
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"test4@test.com\",\"password\":\"12345678\"}"
```

## 3. Получение информации о текущем пользователе
```bash
curl -X GET http://127.0.0.1:8000/api/auth/me -H "Authorization: Bearer ВАШ_ТОКЕН"
```

## 4. Выход из системы
```bash
curl -X POST http://127.0.0.1:8000/api/auth/logout -H "Authorization: Bearer ВАШ_ТОКЕН"
```