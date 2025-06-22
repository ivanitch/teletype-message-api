# teletype-message-api

![Debian](https://img.shields.io/badge/Debian-12-A81D33?logo=debian&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-28.1-2496ED?logo=docker&logoColor=white)
![Yii2](https://img.shields.io/badge/Yii2-2.0-83B81A?logo=yii&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-1.27-009639?logo=nginx&logoColor=white)
![PHP-FPM](https://img.shields.io/badge/PHP_FPM-8.4-777BB4?logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17.5-4169E1?logo=postgresql&logoColor=white)

## Установка проекта ✨
```bash
git clone git@github.com:ivanitch/teletype-message-api.git teletype-message-api

# Или в текущую директорию
git clone git@github.com:ivanitch/teletype-message-api.git .
```

## Запуск контейнеров Docker 🚀
```bash  
make build && make up && make app
```

## В Docker-контейнере установить зависимости
```
composer install
```

## Postman
```
GET https://neo-teletype.app/
```
Видим:
```
Hello world! 👋 | Yii version 2.0.52
```

## Дополнительно 🔗
- [Описание задачи](Task.md)
- [Решение задачи](Solution.md)
