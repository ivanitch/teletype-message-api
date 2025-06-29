FROM php:8.4-cli

RUN apt-get update && apt-get install -y procps

RUN groupadd -g 1000 www && \
    useradd -u 1000 -ms /bin/bash -g www www && \
    mkdir -p /app && \
    chown -R www:www /app

USER www
WORKDIR /app

CMD ["sleep", "infinity"]