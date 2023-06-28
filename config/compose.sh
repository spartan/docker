#!/usr/bin/env bash

#
# Fix directory path
#
cd `dirname $0`

#
# Load .env file
#
if [ -f ".env" ]; then
    ENV_FILE=".env"
elif [ -f "./../.env" ]; then
    ENV_FILE="./../.env"
elif [ -f "./../../.env" ]; then
    ENV_FILE="./../../.env"
else
  echo "ENV file not found"
  exit 1
fi

. ${ENV_FILE}

FILE=docker-compose.yml
STACK=${DOCKER_STACK}

#
# Run docker-compose
#
case "$1" in
up)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} up -d
    ;;

down)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} down
    ;;

stop)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} stop $2 --timeout=10
    ;;

restart)
    docker-compose --env-file ${ENV_FILE} restart $2
    ;;

update)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} down
    docker-compose --env-file ${ENV_FILE} -f ${FILE} pull
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} up -d
    ;;

rebuild)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} down
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} up --build -d
    ;;

remove | rm)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} down -v --remove-orphans
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} rm -vsf
    ;;

config)
    docker-compose --env-file ${ENV_FILE} -f ${FILE} -p ${STACK} config
    ;;

logs | l)
    docker --env-file ${ENV_FILE} logs -f ${STACK}"_"$2 --since=5m
    ;;

jump | j)
    docker exec -it ${STACK}"_"$2 /bin/sh
    ;;

*)
    echo "Usage: $0 {up|down|stop|restart|update|rebuild|remove|logs|interact|config}"
    ;;
esac
