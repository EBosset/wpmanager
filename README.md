# WordPress Manager

![WPM](wpm.png)

## Description

This tool lets you install and configure a WordPress site and create its
database in less than 5 minutes.

The original goal of this project was to avoid the need for tools such as
Wamp/Lamp or a local web server (Apache, Nginx) to serve a WordPress project.

The project currently provides **two ways** to run a local WordPress site:

- **Version 2 – Docker (recommended)**: using Docker Desktop and a
  `docker compose` file.
- **Version 1 – Legacy CLI (legacy)**: using `wpm`, WP‑CLI, PHP and MySQL
  installed directly on your system.

If you are starting a new local WordPress project today, you should
**use Version 2 – Docker**. Version 1 is kept for existing workflows and
advanced users who still prefer the CLI approach.

---

## Version 2 – Docker / Docker Compose (recommended)

> **Recommended for most users.**  
> This is a newer, container-based way to run a local WordPress project,
> using Docker Desktop. You don’t need to install PHP, MySQL or WP‑CLI
> on your host system.

### 1. Requirements

- Docker Desktop (Windows, macOS)  
  - Download: https://www.docker.com/products/docker-desktop/
- Docker Engine (Linux)  
  - Documentation: https://docs.docker.com/engine/install/

### 2. Files

This repository contains a `compose.example.yaml` file as a template.

Copy it to `compose.yaml` before running Docker:

```bash
cp compose.example.yaml compose.yaml
```

### 3. Configure the placeholders

Edit `compose.yaml` and replace all `XXXXX_...` placeholders:

- `XXXXX_ROOT_PASSWORD`  
  MySQL root password (choose a strong one).

- `XXXXX_DB_NAME`  
  Name of the WordPress database (e.g. `my_wp_project`).

- `XXXXX_DB_USER`  
  MySQL user dedicated to WordPress (e.g. `wp_user`).

- `XXXXX_DB_PASSWORD`  
  Password for the MySQL user above.

The file defines two services:

- `db` – a `mysql:8.0` container with a persistent volume `db_data`.
- `wordpress` – a `wordpress:latest` container connected to the `db` service,
  exposed on port `8000`.

### 4. Start the stack

From the folder where your `compose.yaml` is located:

```bash
docker compose up -d
```

Docker will:

- Pull the `mysql:8.0` and `wordpress:latest` images (if not already present).
- Create the MySQL database and user with the values you configured.
- Start the WordPress container linked to the database.

### 5. Access WordPress

Open your browser and go to:

- `http://localhost:8000`

You should see the standard WordPress installation screen, using the database
configured in `compose.yaml`.

### 6. Stop and clean up

To stop the containers:

```bash
docker compose down
```

The MySQL data is stored in a named Docker volume (`db_data`) and will persist
between restarts unless you explicitly remove the volume.

To remove containers **and** the volume:

```bash
docker compose down -v
```

---

### Version 2 – Docker / Docker Compose (français)

> **Recommandée pour la plupart des utilisateurs.**  
> Cette approche utilise Docker Desktop pour lancer un projet WordPress local
> sans avoir à installer PHP, MySQL ou WP‑CLI directement sur votre machine.

#### 1. Prérequis

- Docker Desktop (Windows, macOS)  
  - Téléchargement : https://www.docker.com/products/docker-desktop/
- Docker Engine (Linux)  
  - Documentation : https://docs.docker.com/engine/install/

#### 2. Fichiers

Ce dépôt fournit un fichier modèle `compose.example.yaml`.

Copiez-le en `compose.yaml` avant de lancer Docker :

```bash
cp compose.example.yaml compose.yaml
```

#### 3. Remplir les valeurs

Éditez `compose.yaml` et remplacez toutes les valeurs `XXXXX_...` :

- `XXXXX_ROOT_PASSWORD`  
  Mot de passe root MySQL (choisissez-en un fort).

- `XXXXX_DB_NAME`  
  Nom de la base de données WordPress (ex. : `mon_projet_wp`).

- `XXXXX_DB_USER`  
  Utilisateur MySQL dédié à WordPress (ex. : `wp_user`).

- `XXXXX_DB_PASSWORD`  
  Mot de passe de l’utilisateur MySQL ci-dessus.

Le fichier définit deux services :

- `db` – un conteneur `mysql:8.0` avec un volume persistant `db_data`.
- `wordpress` – un conteneur `wordpress:latest` connecté au service `db`,
  exposé sur le port `8000`.

#### 4. Démarrer l’environnement

Depuis le dossier où se trouve votre `compose.yaml` :

```bash
docker compose up -d
```

Docker va :

- Télécharger les images `mysql:8.0` et `wordpress:latest` (si nécessaire).
- Créer la base MySQL et l’utilisateur avec les valeurs configurées.
- Lancer le conteneur WordPress relié à la base de données.

#### 5. Accéder à WordPress

Ouvrez votre navigateur et rendez-vous sur :

- `http://localhost:8000`

Vous devriez voir l’écran d’installation standard de WordPress utilisant
la base configurée dans `compose.yaml`.

#### 6. Arrêter et nettoyer

Pour arrêter les conteneurs :

```bash
docker compose down
```

Les données MySQL sont stockées dans un volume Docker nommé (`db_data`) et
seront conservées entre les redémarrages, sauf si vous supprimez le volume.

Pour supprimer les conteneurs **et** le volume :

```bash
docker compose down -v
```

---

## Version 1 – Legacy CLI (wpm)

> **Legacy / advanced usage.**  
> This is the original way WordPress Manager was used and is kept for
> compatibility with existing workflows. It requires PHP, MySQL and
> WP‑CLI to be installed on your machine.

### Install

```bash
curl -JOL https://github.com/EBosset/wpmanager/releases/latest/download/wpm
sudo chmod +x wpm
sudo mv wpm /usr/local/bin
```

### Requirements

- WP‑CLI  
- MySQL  
- PHP >= 7.4 | ^8.2  

### Usage

Interactive usage:

```bash
wpm
```

`wpm` will guide you through:

- Creating a new database and a MySQL user.
- Downloading and configuring WordPress using WP‑CLI.
- Setting up the local environment to run the site.

---

## Contribution

Issues, suggestions and pull requests are welcome.

- Feel free to open issues for bugs, feature requests or ideas
  (for both the legacy CLI and the Docker setup).
- Pull requests are appreciated for improvements, documentation, or new features.

---

## Credits

Created and maintained by [@EBosset](https://github.com/EBosset).

