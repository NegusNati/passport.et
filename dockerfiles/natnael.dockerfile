# Stage 1: Build the React app
FROM node:16-alpine as build

WORKDIR /app

# Copy package.json and package-lock.json (or yarn.lock) to the working directory
COPY ./src/natnael/package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the app's source code
COPY ./src/natnael/ ./

# Build the React app for production
RUN npm run build

# Stage 2: Serve the app with Nginx
FROM nginx:alpine

# Copy the built files from the first stage to the Nginx html directory
COPY --from=build /app/dist /usr/share/nginx/html

# Copy the custom Nginx configuration
COPY ./dockerfiles/nginx/natnael.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
