FROM mysql:8.0

ARG PUID
ARG PGID

# Set Timezone

RUN ln -snf /usr/share/zoneinfo/CET /etc/localtime && echo CET > /etc/timezone

# Done

WORKDIR /srv/app
