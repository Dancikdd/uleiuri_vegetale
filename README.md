# Uleiuri Vegetale

## Laboratorul 3 — Organizarea proiectului PHP și prelucrarea datelor

Proiectul este un site PHP fără bază de date. Antetul și subsolul sunt reutilizate
prin `require` din `site/components/`, iar paginile aplicației se află în
`site/pages/`.

### Pagini și navigare

- `site/index.php` — pagina principală.
- `site/pages/produse.php` — catalogul și căutarea produselor prin GET (`q`).
- `site/pages/comanda.php` — formular POST pentru prelucrarea temporară a unei comenzi.
- `site/pages/despre.php` și `site/pages/contact.php` — informații și date de contact.
- `site/pages/roluri.php` — roluri și matricea drepturilor.
- `site/pages/contul-meu.php` — pagină demonstrativă pentru client.
- `site/pages/administrare.php` — pagină demonstrativă pentru administrator.

### Roluri

Rolul este simulat prin parametrul URL `rol`: `vizitator`, `client` sau
`administrator`. Pagina „Roluri” permite schimbarea lui. Aceasta este doar o
demonstrație didactică, nu autentificare sau control de acces pentru producție.

### Rularea proiectului

Porniți serverul PHP integrat din directorul rădăcină al repository-ului:

```sh
php -S localhost:8000
```

Deschideți `http://localhost:8000/site/`.