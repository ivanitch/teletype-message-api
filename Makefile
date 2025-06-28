include .env

build:
	@docker compose up -d --build

up:
	@docker compose up -d

stop:
	@docker compose stop

down:
	@docker compose down -v

restart:
	@docker compose restart

reload:
	@docker compose down -v
	@docker compose up -d

ps:
	@docker compose ps

logs:
	@docker compose logs -f

app:
	@docker exec -it $(APP_NAME)_php-fpm /bin/bash

redis:
	@docker exec -it $(APP_NAME)_redis redis-cli

tester:
	# docker exec -it $(APP_NAME)_loadtester php /app/test.php
	docker exec -it $(APP_NAME)_loadtester /bin/bash
