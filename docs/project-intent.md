# Zámer projektu ServiceDesk

## Cieľ projektu

Cieľom projektu je vytvoriť webovú aplikáciu na evidenciu a riešenie problémov s firemnými IT zariadeniami, ako sú počítače, notebooky a tlačiarne.

Aplikácia nahradí neprehľadné hlásenie problémov prostredníctvom e-mailov, telefonátov či osobnej komunikácie jednotnou evidenciou požiadaviek — **tiketov**. Umožní sledovať, ktorého zariadenia sa problém týka, kto ho rieši a akým spôsobom bol vyriešený.

## Rozsah prvej verzie

- Systém budú používať **zamestnanci** a **technici** s predvytvorenými účtami.
- Zariadenia budú vopred evidované v databáze a pridelené konkrétnemu zamestnancovi alebo označené ako spoločné.
- Správa používateľov a zariadení prostredníctvom administrátorského rozhrania je plánovaná až v ďalšej fáze.

## Zamestnanec

Zamestnanec po prihlásení vytvorí tiket, v ktorom uvedie:

- názov;
- opis problému;
- prioritu;
- jedno zariadenie vybrané zo svojich alebo spoločných zariadení.

Zamestnanec môže prezerať iba vlastné tikety, sledovať ich stav a dopĺňať informácie prostredníctvom komentárov.

### Priority tiketov

| Priorita | Význam |
| --- | --- |
| `LOW` | Problém výrazne neobmedzuje prácu. |
| `NORMAL` | Problém obmedzuje prácu, ale existuje náhradné riešenie. |
| `HIGH` | Problém znemožňuje prácu alebo ovplyvňuje viac zamestnancov. |

Predvolená priorita bude `NORMAL`.

## Technik a priebeh riešenia

Technik má prehľad o všetkých tiketoch.

1. Prevzatím otvoreného, nepriradeného tiketu sa stane jeho riešiteľom a stav sa zmení z `OPEN` na `IN_PROGRESS`.
2. Jeden tiket môže mať najviac jedného prideleného technika. Iba tento technik môže meniť jeho prioritu a stav.
3. Po vyriešení problému technik vyplní povinný opis riešenia a zmení stav na `CLOSED`.

Autor tiketu a technici môžu pridávať komentáre počas riešenia. Uzavretý tiket zostáva dostupný iba na čítanie a v prvej verzii sa znovu neotvára.

### Stavy tiketov

| Stav | Význam |
| --- | --- |
| `OPEN` | Otvorený tiket čakajúci na prevzatie. |
| `IN_PROGRESS` | Tiket prevzal technik a rieši ho. |
| `CLOSED` | Tiket je uzavretý s vyplneným opisom riešenia. |

Povolený priebeh: `OPEN → IN_PROGRESS → CLOSED`.

## Evidované údaje a požiadavky na aplikáciu

Systém automaticky eviduje:

- identifikátor tiketu;
- autora;
- prideleného technika;
- čas vytvorenia, aktualizácie a uzavretia.

Každý komentár obsahuje autora a čas pridania.

Aplikácia zabezpečí:

- prihlásenie;
- kontrolu oprávnení na serveri;
- validáciu vstupov;
- trvalé uloženie údajov v databáze.

## Kritérium dokončenia

Projekt bude splnený, keď bude možné vykonať a otestovať celý postup od nahlásenia problému cez prevzatie a komunikáciu až po uzavretie a zobrazenie výsledku zamestnancovi.

## Vzdelávací cieľ

Vzdelávacím cieľom je samostatne navrhnúť a implementovať webovú aplikáciu, precvičiť databázové modelovanie, prácu s rolami, riadenie stavov a testovanie a vedieť svoje riešenie vysvetliť na pracovnom pohovore.