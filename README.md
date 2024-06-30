# Uno-server (PHP)

## Table of contents
- [Uno-server (PHP)](#uno-server-php)
  - [Table of contents](#table-of-contents)
  - [About](#about)
  - [Features](#features)
  - [Status](#status)
  - [Future ideas/Improvements/Features](#future-ideasimprovementsfeatures)
  - [Prerequisites](#prerequisites)
  - [Setup](#setup)
  - [Project structure](#project-structure)
  - [Note](#note)
  - [License](#license)


## About

This is a PHP 8.3 WebSocket server for the Uno game. It is built using the Ratchet library. The server is responsible for managing the game state, rooms, and players. It also handles the communication between the clients and the server.
The server is designed to be used with the [Uno-client]() project. (The client is not yet implemented).
A SQLite database is used to manage the users (username, password).


## Features
- Communication between the server and the clients
- User registration and login (using a MySQL database)
- Seperate rooms for each game
- Game logic (card validation, turn management, etc.)

## Status
The project is still in development. The server runs but has not been tested yet. The client is not yet implemented.

## Future ideas/Improvements/Features
- Implement a chat functionality
- Implement a ranking system


## Prerequisites
- Docker
- Docker-compose

## Setup
1. Clone the repository
```bash
git clone "https://github.com/KreativeName1/Uno-server.git"
```
2. Change directory to the project folder
```bash
cd Uno-server
```
3. Build the docker image
```bash
docker-compose build
```
4. Run the docker container
```bash
docker-compose up
```


## Project structure
```
Uno-server
├─ .env
├─ app
│  ├─ Card.class.php
│  ├─ CardColor.enum.php
│  ├─ CardType.enum.php
│  ├─ Collection.class.php
│  ├─ DBConnection.class.php
│  ├─ Game.class.php
│  ├─ Player.class.php
│  ├─ Room.class.php
│  ├─ UserManager.class.php
│  ├─ composer.json
│  ├─ composer.lock
│  ├─ server.class.php
│  └─ vendor
│     ├─ autoload.php
├─ docker-compose.yml
├─ Dockerfile
└─ README.md

```
## Note
This is my first online multiplayer game project. 

## License
[![MIT License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
```
Uno-server
├─ app
│  ├─ Card.class.php
│  ├─ CardColor.enum.php
│  ├─ CardType.enum.php
│  ├─ Collection.class.php
│  ├─ DBConnection.class.php
│  ├─ Game.class.php
│  ├─ GameServer.class.php
│  ├─ Player.class.php
│  ├─ Room.class.php
│  ├─ UserManager.class.php
│  ├─ composer.json
│  ├─ composer.lock
│  ├─ startup.php
│  ├─ tests
│  │  └─ DBConnectionTest.php
├─ data
│  └─ database.db
├─ Dockerfile
├─ docker-compose.yml
└─ README.md

```