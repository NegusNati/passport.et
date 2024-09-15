# Dockerfile for Vite App
FROM node:18-alpine

WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy the app files
COPY . .

# Build the app
RUN npm run build

# Serve the app from the dist folder
RUN npm install -g http-server
CMD ["http-server", "dist"]


EXPOSE 3000
