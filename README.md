# ServiceDesk

Webová aplikácia na evidenciu a riešenie problémov s firemnými IT
zariadeniami. Zamestnanec nahlási problém a technik ho prevezme,
komentuje a vyrieši.

Projekt vznikol ako praktická príprava na pohovor a na precvičenie
PHP, Oracle SQL, práce s transakciami a návrhu aplikácie.

## Implementované funkcie

- Prihlásenie a odhlásenie pomocou predvytvorených účtov.
- Rozlíšenie rolí zamestnanec a technik.
- Vytvorenie tiketu pre vlastné alebo spoločné zariadenie.
- Zamestnanec vidí iba svoje tikety; technik vidí všetky.
- Detail tiketu vrátane zariadenia, autora, technika a časov.
- Prevzatie neprideleného tiketu technikom.
- Zmena priority prideleným technikom.
- Vyriešenie tiketu so zápisom spôsobu riešenia.
- Komentáre autora a prideleného technika.
- Filtrovanie podľa stavu a priority.
- Kontroly oprávnení na serveri a CSRF ochrana formulárov.

Životný cyklus tiketu:

**Nový → V riešení → Vyriešený**

Vyriešený tiket nemožno meniť ani ďalej komentovať.

## Technológie

- PHP 8.3
- Composer a PSR-4 autoloading
- Oracle Database Free 23 v Docker kontajneri
- PHP rozšírenie OCI8 a Oracle Instant Client
- HTML a serverové PHP šablóny
- vlucas/phpdotenv
- Git a GitHub Issues/Projects

Aplikácia používa vlastný jednoduchý router, controllers, services
a repositories. Nepoužíva veľký PHP framework.

## Požiadavky

- PHP 8.3 alebo kompatibilná novšia verzia podľa composer.json.
- Composer.
- PHP rozšírenia OCI8 a mbstring.
- Oracle Instant Client kompatibilný s použitým OCI8 balíkom.
- Docker s Linux kontajnermi.

Na Windows musí OCI8 balík zodpovedať verzii PHP, architektúre,
Thread Safety a použitému kompilátoru. Priečinok Instant Client
musí byť dostupný cez PATH.

Overenie prostredia:

```powershell
php -v
composer --version
php --ri oci8
docker info --format '{{.OSType}}'
```

## Lokálne spustenie

Príkazy spúšťajte z koreňového priečinka projektu.

### 1. Nainštalovanie závislostí

```powershell
composer install
```

### 2. Konfigurácia

Vytvorte lokálny konfiguračný súbor:

```powershell
Copy-Item .env.example .env
```

Vyplňte hodnoty:

```dotenv
APP_NAME=ServiceDesk
APP_TIMEZONE=Europe/Bratislava

DB_HOST=127.0.0.1
DB_PORT=1521
DB_SERVICE=FREEPDB1
DB_USER=servicedesk
DB_PASSWORD=ServiceDesk123
```

Uvedené heslo je iba pre lokálne ukážkové prostredie.
Pri použití vlastných hesiel upravte aj konfiguráciu kontajnera.

Súbor .env sa neukladá do Gitu.
Soubor .env.example obsahuje vzor bez skutočného hesla.

### 3. Prvé spustenie Oracle

Používame komunitný image gvenzl/oracle-free:

```powershell
docker run -d --name servicedesk-oracle -p 127.0.0.1:1521:1521 -e ORACLE_PASSWORD=OracleAdmin123 -e APP_USER=servicedesk -e APP_USER_PASSWORD=ServiceDesk123 -v servicedesk-oracle-data:/opt/oracle/oradata gvenzl/oracle-free:23-slim
```

Sledujte inicializáciu:

```powershell
docker logs -f servicedesk-oracle
```

Počkajte na hlásenie:

```text
DATABASE IS READY TO USE!
```

Sledovanie ukončíte cez Ctrl + C. Kontajner zostane bežať.

Ak kontajner už existuje, použite:

```powershell
docker start servicedesk-oracle
```

Zastavenie databázy:

```powershell
docker stop servicedesk-oracle
```

Dáta sú uložené vo volume servicedesk-oracle-data.

### 4. Vytvorenie schémy

Pripojte SQL klienta, napríklad databázové nástroje PhpStormu:

| Nastavenie | Hodnota |
|---|---|
| Host | 127.0.0.1 |
| Port | 1521 |
| Service name | FREEPDB1 |
| User | servicedesk |
| Password | Podľa .env |

Pod používateľom servicedesk spustite celý súbor:

```text
database/schema.sql
```

Skript vytvorí tabuľky, obmedzenia a indexy.
Je určený na prvé vytvorenie schémy; nie je opakovateľnou migráciou.

### 5. Ukážkové dáta

```powershell
php database/seed.php
```

Seed vytvorí troch používateľov a osem zariadení.
Vyžaduje prázdne tabuľky users a devices.

### 6. Spustenie aplikácie

```powershell
php -S 127.0.0.1:8000 -t public public/index.php
```

Otvorte:

http://127.0.0.1:8000/login

Server zastavíte cez Ctrl + C.
PHP vývojový server je určený iba na lokálny vývoj.

## Ukážkové účty

Spoločné heslo: **Demo12345!**

| E-mail | Rola |
|---|---|
| tomas@example.test | Zamestnanec |
| jana@example.test | Zamestnanec |
| peter@example.test | Technik |

Tomáš má tri zariadenia, Jana dve a tri zariadenia sú spoločné.
Heslá sú v databáze uložené ako hash vytvorený pomocou password_hash().

## Architektúra a dokumentácia

- [Zámer projektu](docs/project-intent.md)
- [Funkčné požiadavky](docs/requirements.md)
- [Dátový model](docs/data-model.md)
- [Architektúra](docs/architecture.md)

| Priečinok | Účel |
|---|---|
| public | Vstupný bod webovej aplikácie |
| config | Konfigurácia, bootstrap a routy |
| src/Core | Router, databázové pripojenie, session a CSRF nástroje |
| src/Controllers | Spracovanie HTTP požiadaviek |
| src/Services | Validácia a pravidlá aplikácie |
| src/Repositories | SQL a komunikácia s Oracle |
| views | HTML šablóny |
| database | Databázová schéma a seed |
| docs | Projektová dokumentácia |

## Bezpečnosť a konzistencia dát

- Heslá sa overujú pomocou password_verify().
- Po prihlásení sa obnovuje ID session.
- Zmeny používajú POST a CSRF token.
- Používateľský text sa pri výstupe escapuje.
- SQL používa bind premenné.
- Autor a rola sa určujú zo session.
- Zamestnanec nemôže čítať cudzie tikety.
- Prevzatie a zmeny tiketu používajú podmienený UPDATE.
- Pridanie komentára uzamkne tiket počas transakcie pomocou FOR UPDATE.
- Časy vytvorenia a vyriešenia sa ukladajú v UTC
  a zobrazujú v Europe/Bratislava.

## Overenie

Vývoj priebežne sprevádzalo manuálne overovanie prihlásenia,
vytvárania tiketov, zobrazenia, prevzatia, zmeny priority,
vyriešenia, komentovania a filtrovania.

Pred úplným dokončením overovania zostáva:
- Kontrola oprávnení medzi dvoma rôznymi technikmi.
- Súčasný pokus dvoch technikov o prevzatie rovnakého tiketu.
- Záverečné zopakovanie celého scenára a nepovolených požiadaviek.

Automatizované testy zatiaľ nie sú implementované.

## Aktuálne obmedzenia a ďalší rozvoj

- Úprava nového tiketu je odložená.
- Bez administrátorského rozhrania a registrácie.
- Bez obnovy hesla, príloh a e-mailových notifikácií.
- Bez histórie zmien a opätovného otvorenia tiketu.
- Opis, komentár a riešenie majú aktuálne limit 4000 bajtov.
- Bez stránkovania zoznamu tiketov.
- Používateľské rozhranie má základnú podobu.
- Produkčné spracovanie chýb a logovanie ešte nie sú dokončené.