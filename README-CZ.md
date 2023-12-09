# Seatmaster 🪑📅

Repozitář pro projekt Seatmaster, vytvořený v rámci mé bakalářské práce na FIT ČVUT 🎓.

## Začínáme 🧱⏩🏠

Tyto pokyny vám umožní vytvořit kopii projektu a spustit ji na vašem lokálním počítači pro účely vývoje a testování.

### Předpoklady 🛠️

Než začnete, ujistěte se, že jsou splněny následující předpoklady:

- **Docker** 🐋: Pro kontejnerizaci se používá [Docker](https://docs.docker.com/get-docker/).
  Ujistěte se, že máte nainstalovaný Docker. Měla by fungovat jakákoli nejnovější verze, ale doporučuje se používat poslední stabilní verzi.
- **Node.js** 💚: [Node.js](https://nodejs.org/en/download/) pro běhové prostředí JavaScriptu. Během vývoje používán ve verzi 18.17.1.
- **Yarn** 🧶: [Yarn](https://yarnpkg.com/getting-started/install) pro správu balíčků JavaScriptu.
  Yarn je v tomto projektu upřednostňován před npm pro svůj výkon a spolehlivost. Během vývoje používán ve verzi 1.22.19.

Volitelné pro spouštění testů Panther nebo lokální vývoj:
- **PHP**: [PHP](https://www.php.net/manual/en/install.php) verze 8.1 nebo vyšší s určitými rozšířeními php (php extensions).
  (viz composer.json nebo Dockerfile), Symfony CLI (doporučena verze 5.5.2 nebo vyšší).
  Při vývoji použito PHP ve verzi 8.2.13.
- **Composer**: [Composer](https://getcomposer.org/download/) verze 2.x pro správu závislostí PHP.
  Během vývoje používán ve verzi 2.5.5.
- **Google Chrome**: Měla by vyhovovat jakákoli stabilní verze. Během vývoje používán ve verzi 119.0.6045.159.
- **Chromedriver**: Ujistěte se, že verze Chromedriveru odpovídá verzi prohlížeče Google Chrome.

Poznámka: Výše uvedené verze jsou otestované a je pro tento projekt známo, že fungují. Pokud používáte jiné verze, můžete se potýkat s problémy s kompatibilitou.

### Konfigurace pro vývoj 🪄

1. **Naklonujte repozitář** 🙋‍♂️🙋‍♂️:
   ```
   git clone https://gitlab.fit.cvut.cz/dundejan/seatmaster.git
   cd seatmaster
   ```

2. **Spuštění vývojového prostředí** 🏃‍♂️:
   ```
   make up
   ```
   Poznámka: Příkaz spouští yarn watch v aktuálním terminálu, takže zavřením terminálu či příkazem
   Ctrl+C se hlídač yarn ukončí. Pokud dáváte přednost tomu, aby yarn watch běžel tiše, neváhejte upravit příkaz
   make up tak, aby místo jednoduchého příkazu `@yarn watch` použil například `@nohup yarn watch > /dev/null 2>&1 &`.


3. **Přistupte k aplikaci** 🕺:
    - Aplikace by nyní měla být spuštěna na [localhost](http://localhost) (nebo na určeném portu).

### Spuštění testů 📈

- **Spuštění testů** (s výjimkou testů Panther):
  ```
  make test
  ```

- **Spuštění testů Panther** 🐈‍⬛:
  Testy Panther vyžadují specifické lokální prostředí:
    - **Composer**: Ujistěte se, že máte Composer a spusťte
    - **PHP 8.1 nebo vyšší**: Ujistěte se, že máte lokálně nainstalovanou verzi PHP 8.1 nebo vyšší.
    - **Konzole Symfony**: Komponenta Symfony Console slouží ke spouštění příkazů Doctrine.
    - **Google Chrome nebo jiný prohlížeč**: Ujistěte se, že máte v místním počítači nainstalovaný prohlížeč Google Chrome (nebo prohlížeč, který hodláte používat s Pantherem). Aktuální nastavení je testováno s prohlížečem Google Chrome verze 119.
    - **Chromedriver**: Ujistěte se, že máte nainstalovaný Chromedriver, který odpovídá verzi prohlížeče Google Chrome. Pro Chrome 119 použijte odpovídající verzi Chromedriveru.
    - **Proměnné prostředí**: V souboru `.env.test` nastavte cestu k Chromedriveru a Google Chrome. Například:
      ```
      PANTHER_CHROME_DRIVER_BINARY=/usr/bin/chromedriver
      PANTHER_CHROME_BINARY=/usr/bin/google-chrome
      ```
    - Změňte prohlížeč pro testy Panther aktualizací souboru `.env.test` s příslušnými binárními cestami pro zvolený prohlížeč.

  Spuštění testů Panther:
  ```
  make test-panther
  ```

  Poznámka: Příkazy v rámci `test-panther` využívají místní instalaci PHP a příkazy konzole Symfony k interakci s databází Doctrine, včetně vytváření, aktualizování a rušení schématu testovací databáze a načítání fixtures.

### Lintování a statická analýza 🧪

- Lintování souborů JavaScriptu 🧫:
  ```
  make eslint
  ```

- Provedení statické analýzy PHP 🔬:
  ```
  make php-stan
  ```

### Úklid 🧹

- Vyčištění vygenerovaných souborů a vymazání cache:
  ```
  make clean
  ```

### Obnovení prostředí 🏗️

- Přestavba celého vývojového prostředí:
  ```
  make rebuild
  ```
  Poznámka: Nejedná se o přestavbu kontejnerů docker, ale pouze o vypnutí kontejnerů, vyčištění mezipaměti a opětovné spuštění kontejnerů.
  Pro přestavbu kontejnerů použijete `make docker-build`.

### Ostatní 📜

Všechny příkazy Makefile s popisem si můžete prohlédnout pomocí příkazu
```
make help
```