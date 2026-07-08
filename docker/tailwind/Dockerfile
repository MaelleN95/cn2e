FROM node:22-alpine

WORKDIR /var/www/html

COPY package*.json ./

RUN npm install

CMD ["npm", "run", "dev"]