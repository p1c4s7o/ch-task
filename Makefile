simple-build:
	docker compose --profile simple build

medium-build:
	docker compose --profile medium build


simple-up:
	docker compose --profile simple up -d

medium-up:
	docker compose --profile medium up -d


simple-down:
	docker compose --profile simple down

medium-down:
	docker compose --profile medium down



flush:
	docker compose --profile simple down --rmi local --volumes
	docker compose --profile medium down --rmi local --volumes
