# Funkčné požiadavky – ServiceDesk MVP

## 1. Účel a rozsah

ServiceDesk je webová aplikácia na evidenciu a riešenie problémov
s firemnými IT zariadeniami. Každý tiket sa týka jedného konkrétneho
zariadenia.

Prvá verzia podporuje zamestnancov a technikov s predvytvorenými účtami.
Zariadenia sú vopred evidované v databáze a pridelené konkrétnemu
zamestnancovi alebo označené ako spoločné.

## 2. Používateľské roly

### Zamestnanec

- Vytvára tikety pre svoje alebo spoločné zariadenia.
- Zobrazuje zoznam a detail svojich tiketov.
- Upravuje názov, opis a zariadenie svojich tiketov v stave Nový.
- Pridáva komentáre k svojim tiketom.

### Technik

- Zobrazuje zoznam a detail všetkých tiketov.
- Filtruje tikety podľa stavu a priority.
- Preberá nepridelené tikety.
- Mení prioritu a stav tiketov, ktoré má pridelené.
- Pridáva komentáre k tiketom, ktoré má pridelené.
- Pri vyriešení zapisuje spôsob riešenia.

## 3. Funkčné požiadavky

### FR-01 – Prihlásenie

Používateľ sa prihlási pomocou predvytvoreného účtu.

Akceptačné kritériá:
- Platné prihlasovacie údaje umožnia prístup do aplikácie.
- Neplatné údaje zobrazia chybové hlásenie a neumožnia prístup.
- Neprihlásený používateľ nemá prístup k tiketom ani zariadeniam.
- Po prihlásení sa uplatňujú oprávnenia používateľovej roly.

### FR-02 – Odhlásenie

Používateľ sa môže odhlásiť z aplikácie.

Akceptačné kritériá:
- Odhlásenie ukončí používateľovu reláciu.
- Ďalší prístup k chráneným funkciám vyžaduje nové prihlásenie.

### FR-03 – Výber zariadenia

Zamestnanec pri vytvorení alebo povolenej úprave tiketu vyberie zariadenie.

Akceptačné kritériá:
- Môže vybrať zariadenie pridelené sebe alebo spoločné zariadenie.
- Nemôže vybrať zariadenie pridelené inému zamestnancovi.
- Každý tiket musí byť spojený práve s jedným zariadením.

### FR-04 – Vytvorenie tiketu

Zamestnanec vytvorí tiket zadaním názvu, opisu problému, zariadenia
a priority.

Akceptačné kritériá:
- Názov a opis nesmú byť prázdne ani obsahovať iba medzery.
- Zariadenie a platná priorita sú povinné.
- Predvolená priorita je Stredná.
- Systém automaticky priradí jedinečné ID, autora, čas vytvorenia
  a stav Nový.
- Nový tiket nemá prideleného technika.
- Neplatné zadanie zobrazí chyby a tiket sa nevytvorí.

### FR-05 – Zobrazenie tiketov zamestnanca

Zamestnanec zobrazí zoznam a detail svojich tiketov.

Akceptačné kritériá:
- Zoznam obsahuje ID, názov, zariadenie, stav, prioritu a čas vytvorenia.
- Detail obsahuje aj opis, prideleného technika, komentáre a spôsob riešenia, ak bol zapísaný.
- Zamestnanec nemá prístup k tiketom iných zamestnancov.

### FR-06 – Úprava nového tiketu

Zamestnanec môže upraviť názov, opis a zariadenie svojho tiketu,
pokiaľ je v stave Nový.

Akceptačné kritériá:
- Úprava podlieha rovnakým kontrolám ako vytvorenie tiketu.
- Autor, ID, čas vytvorenia a stav sa úpravou nemenia.
- Po prevzatí tiketu už zamestnanec nemôže meniť tieto údaje.
- Zamestnanec po vytvorení nemôže meniť prioritu tiketu.

### FR-07 – Prehľad tiketov technika

Technik zobrazí zoznam a detail všetkých tiketov.

Akceptačné kritériá:
- Zoznam obsahuje ID, názov, autora, zariadenie, stav, prioritu,
  prideleného technika a čas vytvorenia.
- Technik môže filtrovať podľa stavu a priority.
- Filtre možno použiť súčasne a následne zrušiť.
- Detail obsahuje všetky údaje tiketu a komentáre.

### FR-08 – Prevzatie tiketu

Technik si môže prevziať nepridelený tiket v stave Nový.

Akceptačné kritériá:
- Technik sa priradí ako riešiteľ a stav sa zmení na V riešení.
- Priradenie technika a zmena stavu prebehnú ako jedna operácia.
- Jeden tiket môže mať najviac jedného prideleného technika.
- Pri súčasnom prevzatí dvoma technikmi uspeje iba jeden.
- Už pridelený tiket nemôže prevziať iný technik.

### FR-09 – Zmena priority

Pridelený technik môže meniť prioritu tiketu v stave V riešení.

Akceptačné kritériá:
- Povolené priority sú Nízka, Stredná a Vysoká.
- Prioritu nemôže meniť zamestnanec ani iný technik.
- Zmena priority nemení stav ani prideleného technika.

### FR-10 – Vyriešenie tiketu

Pridelený technik môže zmeniť stav z V riešení na Vyriešený.

Akceptačné kritériá:
- Musí uviesť neprázdny spôsob riešenia.
- Systém zaznamená čas vyriešenia.
- Bez spôsobu riešenia sa stav nezmení.
- Vyriešený tiket zostáva dostupný na zobrazenie.
- Vyriešený tiket nemožno znovu otvoriť ani upravovať.
- Po vyriešení nemožno pridávať ďalšie komentáre.

### FR-11 – Komentáre

Autor tiketu a pridelený technik môžu pridávať komentáre
k nevyriešenému tiketu.

Akceptačné kritériá:
- Komentár nesmie byť prázdny ani obsahovať iba medzery.
- Systém eviduje text, autora a čas pridania.
- Komentáre sa zobrazujú chronologicky od najstaršieho.
- V MVP nemožno komentáre upravovať ani odstraňovať.
- Ostatní technici môžu komentáre čítať, ale nemôžu ich pridávať.

### FR-12 – Kontrola oprávnení

Systém kontroluje oprávnenia na serveri pri každom čítaní alebo zmene dát.

Akceptačné kritériá:
- Zmena ID v URL alebo ručne odoslaná požiadavka neumožní
  prístup k nepovoleným údajom ani vykonanie nepovolenej operácie.
- Zamestnanec môže pracovať iba so svojimi tiketmi.
- Technik môže čítať všetky tikety, ale meniť iba svoje pridelené
  tikety podľa uvedených pravidiel.
- Neoprávnená požiadavka nezmení dáta a zobrazí primerané hlásenie.

## 4. Údaje tiketu

| Údaj | Pravidlo |
|---|---|
| ID | Jedinečné, vytvára systém |
| Názov | Povinný |
| Opis problému | Povinný |
| Zariadenie | Povinné, práve jedno |
| Priorita | Nízka, Stredná alebo Vysoká |
| Stav | Nový, V riešení alebo Vyriešený |
| Autor | Prihlásený zamestnanec, dopĺňa systém |
| Pridelený technik | Prázdny do prevzatia |
| Čas vytvorenia | Dopĺňa systém |
| Spôsob riešenia | Povinný pri vyriešení |
| Čas vyriešenia | Dopĺňa systém pri vyriešení |

Komentáre a história stavov sa evidujú ako samostatné záznamy
priradené k tiketu.

## 5. Životný cyklus tiketu

| Pôvodný stav | Nový stav | Kto vykonáva zmenu | Podmienka |
|---|---|---|---|
| — | Nový | Zamestnanec | Platné vytvorenie tiketu |
| Nový | V riešení | Technik | Prevzatie neprideleného tiketu |
| V riešení | Vyriešený | Pridelený technik | Vyplnený spôsob riešenia |

Iné prechody stavov nie sú v MVP povolené.

## 6. Funkcie mimo rozsahu MVP

- Administrátorské rozhranie.
- Registrácia a obnova hesla.
- Správa používateľov a zariadení cez aplikáciu.
- Prerozdelenie tiketu na iného technika.
- Opätovné otvorenie alebo zrušenie tiketu.
- Odstraňovanie tiketov.
- Prílohy.
- E-mailové notifikácie.
- SLA a automatické eskalácie.
- História zmien stavov a úprav tiketov.

## 7. Podmienky dokončenia MVP

- Všetky funkčné požiadavky sú implementované a ich akceptačné
  kritériá sú overené.
- Zamestnanec dokáže vytvoriť tiket a sledovať jeho riešenie.
- Technik dokáže tiket prevziať, komentovať a vyriešiť.
- Kontroly oprávnení zabránia nepovolenému prístupu a zmenám.
- Dáta zostávajú zachované po reštarte aplikácie a databázy.
- README obsahuje postup prípravy prostredia a spustenia aplikácie.