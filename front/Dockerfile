FROM node:18

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm install

COPY . .

COPY .env .env

EXPOSE 8081
EXPOSE 19000 19001 19002

CMD ["npx", "expo", "start", "--tunnel"]


