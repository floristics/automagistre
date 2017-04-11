#!/bin/sh

set -e

export DOCKER_BRIDGE_IP=$(/sbin/ip route|awk '/default/ { print $3 }')

if [ ! -z "$GITHUB_AUTH_TOKEN" ]; then
    echo "machine github.com login ${GITHUB_AUTH_TOKEN}" > ~/.netrc
fi

# Skip entrypoint for following commands
case "$1" in
   sh|php|composer|php-cs-fixer) exec "$@" && exit 0;;
esac

case "$APP_ENV" in
   prod|dev|test) ;;
   *) >&2 echo env "APP_ENV" must be in \"prod, dev, test\" && exit 1;;
esac

case "$APP_DEBUG" in
   0) ;;
   1) touch ${APP_DIR}/web/config.php;;
   *) >&2 echo env "APP_DEBUG" must be in \"1, 0\" && exit 1;;
esac

if [ -z "$SYMFONY_ENV" ]; then export SYMFONY_ENV=${APP_ENV}; fi
if [ -z "$SYMFONY_DEBUG" ]; then export SYMFONY_DEBUG=${APP_DEBUG}; fi

COMMAND="$@"
COMPOSER_DEFAULT_EXEC=${COMPOSER_DEFAULT_EXEC:="composer install --no-interaction --prefer-dist --no-scripts"}

if [ "$APP_ENV" == "dev" ]; then
    COMPOSER_EXEC=${COMPOSER_EXEC:="$COMPOSER_DEFAULT_EXEC --optimize-autoloader --verbose --profile"}

    XDEBUG=${XDEBUG:=true}
    OPCACHE=${OPCACHE:=false}
    APCU=${APCU:=false}

elif [ "$APP_ENV" == "test" ]; then
    COMPOSER_EXEC=${COMPOSER_EXEC:="$COMPOSER_DEFAULT_EXEC --apcu-autoloader --no-progress"}

	REQUIREMENTS=${REQUIREMENTS:=true}
	FIXTURES=${FIXTURES:=true}

	COMMAND=${COMMAND:=run-test}

elif [ "$APP_ENV" == "prod" ]; then
    COMPOSER_EXEC=${COMPOSER_EXEC:="$COMPOSER_DEFAULT_EXEC --no-dev --apcu-autoloader --no-progress"}
fi

COMMAND=${COMMAND:=apache}
OPCACHE=${OPCACHE:=true}
APCU=${APCU:=true}
MIGRATION=${MIGRATION:=true}
COMPOSER_SCRIPT=${COMPOSER_SCRIPT:="post-install-cmd"}

enableExt() {
    extension=$1
    docker-php-ext-enable ${extension}
    echo -e " > $extension enabled"
}

if [ "$OPCACHE" == "true" ]; then
    enableExt opcache
fi

if [ "$APCU" == "true" ]; then
    enableExt apcu
fi

if [ "$COMPOSER_EXEC" != "false" ]; then
    ${COMPOSER_EXEC}

    if [ "$COMPOSER_SCRIPT" != "false" ]; then
        composer run-script ${COMPOSER_SCRIPT}
    fi
fi

if [ "$MIGRATION" == "true" ]; then
    bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --quiet
fi

if [ "$FIXTURES" == "true" ]; then
    bin/console doctrine:fixtures:load --no-interaction
fi

if [ "$XDEBUG" == "true" ]; then
    enableExt xdebug
fi

if [ -f ${APP_DIR}/web/config.php ]; then
	sed -i "s~'::1',~'::1', '$DOCKER_BRIDGE_IP',~g" "$APP_DIR/web/config.php"
fi

exec "$COMMAND"
