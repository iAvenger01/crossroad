.phony: up

up:
	docker-compose up -d
down:
	docker-compose down
stop:
	docker-compose stop
nginx:
	docker-compose exec -ti nginx sh
php:
	docker-compose exec -ti php sh