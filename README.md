# Seatmaster 🪑

Repozitář pro projekt Seatmaster, vznikající jako součást bakalářské práce v rámci mého studia na FIT ČVUT 🎓.

## Lokální spuštění

### Stáhni závislosti Composeru ⏬

Ujisti se, že máš nainstalovaný [Composer](https://getcomposer.org/download/)
a spusť příkaz:

```
composer install
```

(V závislosti na tom, jak máš nainstalovaný Composer na svém počítači, může být nutné namísto toho spustit `php composer.phar install`.)

### Spusť Symfony server 🏃

Pokud preferuješ Nginx nebo Apache, neváhej ho použít, ale lokální Symfony web-server
funguje bez problémů.

Pokud ještě nemáš na svém počítači nainstalovaný lokální Symfony web-server, následuj
"Downloading the Symfony client" instrukce z této stránky: https://symfony.com/download.

Poté, pro spustění aplikace, otevři terminál a spusť příkaz:

```
symfony serve
```

(Pokud právě zažíváš se Symfony web-serverem svoje "poprvé" 👩🏽‍❤️‍👨🏽, dost možná narazíš na
error, který říká, že musíš nejprve nainstalovat certifikáty spuštěním příkazu `symfony server:ca:install`.)

Otevři oblíbený webový prohlížeč, zadej `https://localhost:8000` a prohlédni si mou aplikaci 🔎.
