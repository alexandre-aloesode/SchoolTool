# Utilisation de l'image officielle Node.js
FROM node:18

# Définition du répertoire de travail
WORKDIR /app

# Copier les fichiers package.json et package-lock.json pour installer les dépendances
COPY package.json package-lock.json ./

# Installer les dépendances avec npm
RUN npm install

# Copier tous les fichiers de l'application
COPY . .

COPY .env .env

# Exposer le port utilisé par Metro Bundler (Expo)
EXPOSE 8081
EXPOSE 19000 19001 19002

# Lancer Expo sans `--tunnel`
CMD ["npx", "expo", "start", "--tunnel"]


