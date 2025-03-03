<?php
/**
 * Bricks Form - Ecomail Integration
 *
 * Plugin pro WordPress, který integruje formuláře Bricks Builderu s Ecomail API.
 *
 * @package integrate-ecomail_bricks
 */

# Bricks Form - Ecomail Integration

Plugin pro WordPress, který integruje formuláře Bricks Builderu s Ecomail API.

## Popis

Tento plugin umožňuje odesílat data z formulářů vytvořených v Bricks Builderu přímo do Ecomail. Díky tomu můžete snadno sbírat kontakty a přidávat je do svých seznamů v Ecomail.

## Funkce

- Přidává akci "Ecomail" do nastavení formulářů v Bricks Builderu
- Dynamicky načítá seznamy kontaktů z vašeho Ecomail účtu
- Umožňuje mapovat pole formuláře na standardní i vlastní pole v Ecomail
- Podporuje přidávání tagů ke kontaktům
- Podporuje všechny základní možnosti Ecomail API (autoresponder, notifikace, aktualizace existujících kontaktů, atd.)
- Obsahuje licenční systém pro správu aktualizací a podpory
- Zahrnuje debug mód pro snadné řešení problémů
- Využívá cache pro seznamy kontaktů pro zlepšení výkonu

## Instalace

1. Nahrajte složku `integrate-ecomail-bricks` do adresáře `/wp-content/plugins/`
2. Aktivujte plugin v administraci WordPressu
3. Přejděte do Nastavení > Bricks Form - Ecomail a zadejte svůj Ecomail API klíč
4. Aktivujte licenci v Nastavení > License Settings

## Použití

### Nastavení API klíče

1. Přihlaste se do svého účtu Ecomail
2. Přejděte do sekce "Nastavení" > "API"
3. Zkopírujte váš API klíč
4. V administraci WordPressu přejděte do Nastavení > Bricks Form - Ecomail
5. Vložte API klíč a uložte nastavení

### Správa cache

Seznamy kontaktů jsou automaticky ukládány do cache na 1 hodinu pro zlepšení výkonu. Pokud jste provedli změny v seznamech v Ecomail, můžete:

1. Přejít do Nastavení > Bricks Form - Ecomail
2. Kliknout na tlačítko "Vymazat cache seznamů"
3. Seznamy budou znovu načteny z Ecomail API

### Vytvoření formuláře

1. V Bricks Builderu vytvořte nový formulář
2. Přidejte potřebná pole (minimálně pole pro email)
3. V nastavení formuláře v sekci "Actions" vyberte "Ecomail"
4. V záložce "Ecomail" vyberte seznam kontaktů a nastavte mapování polí
5. Uložte a publikujte stránku

### Mapování polí

Pro každé pole formuláře můžete nastavit, do kterého pole v Ecomail se má hodnota uložit:

- Email (povinné) - email kontaktu
- Jméno (name) - křestní jméno kontaktu
- Příjmení (surname) - příjmení kontaktu
- Telefon - telefonní číslo kontaktu
- Město - město kontaktu
- Země - země kontaktu
- Vlastní pole - jakákoliv další pole, která máte v Ecomail

### Přidání tagů

Můžete přidat tagy, které budou přiřazeny kontaktu při přihlášení:

1. V nastavení formuláře v sekci "Tagy" zadejte každý tag na nový řádek
2. Tagy pomáhají segmentovat vaše kontakty pro cílené kampaně

### Další nastavení

V nastavení formuláře můžete nakonfigurovat další možnosti:

- Spustit autoresponder - aktivuje automatické odpovědi po přihlášení
- Spustit notifikaci - odešle notifikaci o novém kontaktu
- Aktualizovat existující kontakt - aktualizuje údaje, pokud kontakt již existuje
- Přeskočit potvrzení - přeskočí potvrzovací email (double opt-in)
- Znovu přihlásit - znovu přihlásí kontakty, které se odhlásily
- Debug mód - zapne podrobné logování pro řešení problémů

## Řešení problémů

### Debug mód

Pro řešení problémů s odesíláním dat do Ecomail můžete zapnout debug mód:

1. V nastavení formuláře v sekci "Ecomail" zaškrtněte volbu "Debug mód"
2. Odešlete formulář
3. Debug informace budou zapsány do:
   - WordPress debug logu (pokud je WP_DEBUG zapnutý)
   - Speciálního debug souboru v adresáři `/wp-content/uploads/ecomail-debug/`

Debug soubory obsahují podrobné informace o:
- Odeslaných datech
- Mapování polí
- Odpovědi z Ecomail API
- Případných chybách

### Časté problémy

1. **Pole se neodesílají do Ecomail**
   - Zkontrolujte, zda jsou pole správně namapována
   - Ověřte, že ID polí ve formuláři odpovídají mapování
   - Zapněte debug mód a zkontrolujte, jaká data jsou odesílána

2. **Chyba "Email je povinný a musí být správně namapován"**
   - Ujistěte se, že jste správně namapovali pole pro email
   - Zkontrolujte, že pole pro email ve formuláři má správné ID

3. **Kontakt se nepřidal do seznamu**
   - Ověřte platnost API klíče pomocí tlačítka "Otestovat API klíč"
   - Zkontrolujte, zda je vybrán správný seznam kontaktů
   - Zapněte debug mód a zkontrolujte odpověď z Ecomail API

4. **Tagy se nepřidaly ke kontaktu**
   - Zkontrolujte, zda jste správně zadali tagy (každý na nový řádek)
   - Ověřte, že tagy jsou povoleny ve vašem Ecomail účtu
   - Zapněte debug mód a zkontrolujte, zda jsou tagy odesílány v požadavku

## Požadavky

- WordPress 5.6 nebo novější
- PHP 7.4 nebo novější
- Bricks Builder 1.5 nebo novější
- Aktivní účet Ecomail

## Často kladené otázky

### Jak získám API klíč?

API klíč najdete ve svém účtu Ecomail v sekci Nastavení > API.

### Jaká pole mohu mapovat?

Můžete mapovat standardní pole jako email, jméno, příjmení, telefon, město a zemi. Navíc můžete přidat libovolná vlastní pole, která máte definována ve svém Ecomail účtu.

### Jak nastavím vlastní pole?

V nastavení formuláře v sekci "Vlastní pole" zadejte každé pole na nový řádek ve formátu `nazev_pole_v_ecomail:form-field-id`.

### Jak přidám tagy ke kontaktům?

V nastavení formuláře v sekci "Tagy" zadejte každý tag na nový řádek. Tagy budou automaticky přiřazeny kontaktu při přihlášení.

### Proč se kontakt nepřidal do mého seznamu?

Zkontrolujte následující:
- API klíč je správně zadán a platný
- Pole pro email je správně namapováno
- Vybraný seznam kontaktů existuje
- Zadaný email je platný
- Zapněte debug mód pro podrobnější diagnostiku

### Jak mohu zjistit, jaká data jsou odesílána do Ecomail?

Zapněte debug mód v nastavení formuláře. Podrobné informace o odeslaných datech budou zapsány do debug logu a speciálního debug souboru.

### Proč se seznamy načítají pomalu?

Seznamy jsou nyní ukládány do cache na 1 hodinu, což by mělo výrazně zrychlit načítání. Pokud potřebujete aktualizovat seznamy, použijte tlačítko "Vymazat cache seznamů" v nastavení pluginu.

## Podpora

Pro podporu kontaktujte autora na [webypolopate.cz](https://webypolopate.cz).

## Autor

Plugin vytvořil [Adam Kotala](https://webypolopate.cz).

## Licence

Tento plugin je licencován jako proprietární software. Použití je povoleno pouze s platnou licencí.