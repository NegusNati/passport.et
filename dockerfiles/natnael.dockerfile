FROM nginx:stable-alpine

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

# Install Node.js and NPM
RUN apk add --no-cache nodejs npm

# Create user and group
RUN delgroup dialout
RUN addgroup -g ${GID} --system laravel
RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel
RUN sed -i "s/user  nginx/user laravel/g" /etc/nginx/nginx.conf

# Add custom Nginx configuration
ADD ./nginx/natnael.conf /etc/nginx/conf.d/

# Create the web root directory
RUN mkdir -p /var/www/html

# Set permissions
RUN chown -R laravel:laravel /var/www/html
RUN chmod -R 755 /var/www/html

# Set working directory
WORKDIR /usr/share/nginx/html

# Install dependencies and build the project
COPY ./src/natnael/dist /usr/share/nginx/html
RUN npm install
RUN npm run build

# Expose port 80
EXPOSE 80
