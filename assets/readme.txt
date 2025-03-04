=== Bricks Form - Ecomail Integration ===
Contributors: adamkotala
Donate link: https://webypolopate.cz/
Tags: bricks, ecomail, form, integration, newsletter
Requires at least: 5.6
Tested up to: 6.7.2
Stable tag: 1.2.2
Requires PHP: 7.4
License: Proprietary
License URI: https://webypolopate.cz/produkty/propojeni-ecomail-pro-bricks-builder/

Propojte vaše Bricks formuláře s newslettrovou službou Ecomail rychle & jednoduše.

== Description ==

Tento plugin umožňuje odesílat data z formulářů vytvořených v Bricks Builderu přímo do Ecomail. Díky tomu můžete snadno sbírat kontakty a přidávat je do svých seznamů v Ecomail.

= Funkce =

* Přidává akci "Ecomail" do nastavení formulářů v Bricks Builderu
* Dynamicky načítá seznamy kontaktů z vašeho Ecomail účtu
* Umožňuje mapovat pole formuláře na standardní i vlastní pole v Ecomail
* Podporuje přidávání tagů ke kontaktům
* Podporuje všechny základní možnosti Ecomail API (automatizace, notifikace, aktualizace existujících kontaktů, atd.)
* Obsahuje licenční systém pro správu aktualizací a podpory
* Zahrnuje debug mód pro snadné řešení problémů
* Využívá cache pro seznamy kontaktů pro zlepšení výkonu

== Installation ==

1. Nahrajte složku `integrate-ecomail-bricks` do adresáře `/wp-content/plugins/`
2. Aktivujte plugin v administraci WordPressu
3. Přejděte do Nastavení > Bricks Form - Ecomail a zadejte svůj Ecomail API klíč
4. Aktivujte licenci v záložce "Licence"

== Frequently Asked Questions ==

= Jak získám API klíč? =

API klíč najdete ve svém účtu Ecomail v sekci Správa účtu > Pro vývojáře > API klíč.

= Jaká pole mohu mapovat? =

Můžete mapovat standardní pole jako email, jméno, příjmení, telefon, město a zemi. Navíc můžete přidat libovolná vlastní pole, která máte definována ve svém Ecomail účtu.

= Jak přidám tagy ke kontaktům? =

V nastavení formuláře v sekci "Tagy" zadejte každý tag na nový řádek. Tagy budou automaticky přiřazeny kontaktu při přihlášení.

= Proč se některá pole neodesílají do Ecomail? =

Zkontrolujte, zda jsou pole správně namapována. Zapněte debug mód v nastavení formuláře pro podrobné logování a zjištění problému.

= Proč se seznamy načítají pomalu? =

Seznamy jsou nyní ukládány do cache na 1 hodinu, což by mělo výrazně zrychlit načítání. Pokud potřebujete aktualizovat seznamy, použijte tlačítko "Vymazat cache seznamů" v nastavení pluginu.

== Screenshots ==

1. Nastavení API klíče
2. Aktivace licence
3. Mapování polí formuláře
4. Nastavení tagů a dalších možností

== Changelog ==

= 1.2.2 =
* Aktualizována kompatibilita s WordPress 6.7.2

= 1.2.1 =
* Vylepšeny popisy nastavení pro přepínače v nastavení formuláře
* Odstraněna vlastní potvrzovací zpráva - nyní se používá výchozí zpráva nastavená ve formuláři
* Opraveno zpracování přepínačů pro správné odesílání hodnot true/false do API
* Přidán banner pluginu pro lepší vizuální identitu

= 1.2.0 =
* Opraveno zpracování přepínačů pro správné odesílání hodnot true/false do API

= 1.1.0 =
* Přidána podpora pro tagy - nyní můžete přiřadit tagy kontaktům při přihlášení
* Opraveno zpracování polí formuláře - nyní jsou všechna pole správně odesílána do Ecomail
* Upraveno mapování polí pro kompatibilitu s Ecomail API (firstname → name, lastname → surname)
* Vylepšeno mapování polí formuláře pro lepší kompatibilitu s různými verzemi Bricks
* Přidána podpora pro vlastní pole
* Přidán debug mód pro snadnější řešení problémů
* Přidána cache pro seznamy kontaktů pro zlepšení výkonu
* Vylepšena dokumentace a uživatelské rozhraní
* Upozornění o licenci se nyní zobrazuje pouze na stránkách nastavení pluginu

= 1.0.0 =
* Počáteční vydání s integrací pro Bricks Form, Ecomail API a licencování SureCart

== Upgrade Notice ==

= 1.2.2 =
Tato verze aktualizuje kompatibilitu s WordPress 6.7.2.

= 1.2.1 =
Tato verze vylepšuje popisy nastavení, opravuje zpracování přepínačů a přidává banner pluginu.

= 1.2.0 =
Tato verze opravuje zpracování přepínačů pro správné odesílání hodnot true/false do API.

= 1.1.0 =
Tato verze přidává podporu pro tagy, opravuje zpracování polí formuláře a přidává další vylepšení.