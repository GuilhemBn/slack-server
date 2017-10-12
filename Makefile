# ifneq ($(wildcard /local/users/username),)
#   HOME = /local/users/username
# endif

all: stop build-image run

.PHONY: build-image run stop

build-image:
	@docker build -t slack-php-server .

run: build-image
	@docker run -d --name=slack-server \
     --restart=unless-stopped \
     -p 8282:80 \
     slack-php-server

stop:
	@docker stop slack-server >/dev/null 2>&1 || true
	@docker rm slack-server >/dev/null 2>&1 || true
