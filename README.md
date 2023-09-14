# Seatmaster 🪑📅

Repozitář pro projekt Seatmaster, vznikající jako součást bakalářské práce v rámci mého studia na FIT ČVUT 🎓.

## Lokální spuštění

### Stáhni závislosti Composeru ⏬

Ujisti se, že máš nainstalovaný [Composer](https://getcomposer.org/download/)
a spusť příkaz:

```bash
composer install
```

(V závislosti na tom, jak máš nainstalovaný Composer na svém počítači, může být nutné namísto toho spustit `php composer.phar install`.)

### Stáhni závislosti JS ⏬

Ujisti se, že máš nainstalovaný [Yarn](https://classic.yarnpkg.com/lang/en/docs/install/#windows-stable) a spusť příkaz:
```bash
yarn watch
```

### Spusť Symfony server 🏃

Pokud preferuješ Nginx nebo Apache, neváhej ho použít, ale lokální Symfony web-server
funguje bez problémů.

Pokud ještě nemáš na svém počítači nainstalovaný lokální Symfony web-server, následuj
"Downloading the Symfony client" instrukce z této stránky: https://symfony.com/download.

Poté, pro spustění aplikace, otevři terminál a spusť příkaz:

```bash
symfony serve
```

(Pokud právě zažíváš se Symfony web-serverem svoje "poprvé" 👩🏽‍❤️‍👨🏽, dost možná narazíš na
error, který říká, že musíš nejprve nainstalovat certifikáty spuštěním příkazu `symfony server:ca:install`.)

### Spusť databázi 💾

Aplikaci je možné po úpravě souboru .env připojit k libovolné databázi 
(návod viz [Databases and the Doctrine ORM](https://symfony.com/doc/current/doctrine.html)), 
ovšem pro aplikaci je připravený Docker obsahující PostgreSQL databázi.
Lze jej spustit příkazem `docker compose up` (či pro starší verze Dockeru `docker-compose up`).

### Kochej se 🤓

Otevři svůj oblíbený webový prohlížeč, (používáš-li Symfony web-server) zadej https://localhost:8000 a prohlédni si mou aplikaci 🔎.