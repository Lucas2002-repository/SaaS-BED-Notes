
The package uses Docker Official Images for PHP, MongoDB, Nginx and Composer. ([Docker Hub][1])

## Initial setup

Copy the example environment file:

```bash
cp .env.docker.example .env.docker
```

Docker Compose does not automatically read `.env.docker`, so either rename it:

```bash
cp .env.docker.example .env
```

or run commands with:

```bash
docker compose --env-file .env.docker up -d
```

Before publishing the PHP image, replace this placeholder in `compose.yaml` and the `Makefile`:

```text
REPLACE_WITH_COLLEGE_ORG
```

For example:

```text
ghcr.io/nmtafe/laravel-php:8.4-bookworm-v1
```

## Common commands

```bash
make build
make up
make install
make key
make test
make logs
make down
```

The Laravel application will be available at:

```text
http://localhost:8080
```

The files currently use readable pinned version tags. Once IT approves the images, each `FROM` and `image:` reference should also be pinned to the approved `sha256` digest for full immutability.

[1]: https://hub.docker.com/_/php?utm_source=chatgpt.com "php - Official Image | Docker Hub"
