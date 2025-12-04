simple-build:
	docker compose --profile simple build

medium-build:
	docker compose --profile medium build

hard-build:
	docker compose --profile hard build



simple-up:
	docker compose --profile simple up -d

medium-up:
	docker compose --profile medium up -d

hard-up:
	docker compose --profile hard up -d




simple-down:
	docker compose --profile simple down

medium-down:
	docker compose --profile medium down

hard-down:
	docker compose --profile hard down


flush:
	docker compose --profile simple down --rmi local --volumes
	docker compose --profile medium down --rmi local --volumes
	docker compose --profile hard down --rmi local --volumes
