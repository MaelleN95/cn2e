FROM node:22-alpine

WORKDIR /var/www/html

RUN npm install -g tailwindcss

CMD ["sh", "-c", "npx tailwindcss -i ./tailwind/app.css -o ./public/build/app.css --watch --poll"]